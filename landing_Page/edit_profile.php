<?php
session_start();
require 'db_connect.php'; // Ensure this file initializes $pdo

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Initialize variables
$user = null;
$errors = [];
$success = false;

// Fetch current user data
try {
    $query = "SELECT id, name, email, phone FROM users WHERE id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $errors[] = "Failed to load profile: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs using modern methods
    $name = trim($_POST['name']); // Remove whitespace from the beginning and end
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL); // Use FILTER_SANITIZE_EMAIL for email
    $phone = preg_replace('/[^0-9\+\-\(\)\s]/', '', $_POST['phone']); // Allow only numbers and common phone characters
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';

    // Validate inputs
    if (empty($name)) {
        $errors[] = "Name is required";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    // Only validate passwords if they're provided
    if (!empty($current_password) || !empty($new_password)) {
        if (empty($current_password)) {
            $errors[] = "Current password is required to change password";
        } elseif (empty($new_password)) {
            $errors[] = "New password is required";
        } elseif (strlen($new_password) < 8) {
            $errors[] = "New password must be at least 8 characters";
        }
    }

    // If no errors, update database
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Verify current password if changing password
            if (!empty($current_password)) {
                $query = "SELECT password FROM users WHERE id = :user_id";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!password_verify($current_password, $result['password'])) {
                    throw new Exception("Current password is incorrect");
                }

                // Update with new password
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $query = "UPDATE users SET name = :name, email = :email, phone = :phone, password = :password WHERE id = :user_id";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
                $stmt->bindParam(':password', $password_hash, PDO::PARAM_STR);
                $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            } else {
                // Update without changing password
                $query = "UPDATE users SET name = :name, email = :email, phone = :phone WHERE id = :user_id";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
                $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            }

            if (!$stmt->execute()) {
                throw new Exception("Failed to update profile");
            }

            $pdo->commit();
            $success = true;

            // Update session data if needed
            $_SESSION['user_name'] = $name;

            // Refresh user data
            $query = "SELECT id, name, email, phone FROM users WHERE id = :user_id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Kiln Enterprise</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="shortcut icon" href="assets/images/logo1.ico" type="image/x-icon">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --error-color: #e74c3c;
            --text-color: #333;
            --light-gray: #f5f5f5;
            --border-radius: 8px;
            --box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .edit-profile-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .edit-profile-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-color);
        }
        
        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="password"] {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 1rem;
        }
        
        .error-message {
            color: var(--error-color);
            font-size: 0.9rem;
            margin-top: 0.3rem;
        }
        
        .success-message {
            color: var(--secondary-color);
            background: #d4edda;
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
        }
        
        .btn {
            display: inline-block;
            background: var(--primary-color);
            color: white;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #2980b9;
        }
        
        .password-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
        }
        
        @media (max-width: 768px) {
            .edit-profile-container {
                padding: 0 0.5rem;
            }
            
            .edit-profile-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Include your header/navigation here -->
    
    <main class="edit-profile-container">
        <div class="edit-profile-card">
            <h2>Edit Profile</h2>
            
            <?php if ($success): ?>
                <div class="success-message">
                    Your profile has been updated successfully!
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="error-message" style="margin-bottom: 1.5rem; color: var(--error-color);">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="edit_profile.php">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>
                
                <div class="password-section">
                    <h3>Change Password (optional)</h3>
                    
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password">
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password">
                        <small class="error-message">Leave blank to keep current password</small>
                    </div>
                </div>
                
                <button type="submit" class="btn">Save Changes</button>
                <a href="profile.php" style="margin-left: 1rem; color: var(--primary-color);">Cancel</a>
            </form>
        </div>
    </main>
    
    <footer class="site-footer">
        <div class="footer-content">
            <h1>Need Help?</h1>
            <p>Our customer service is available 24/7</p>
            <p>Email: enterprisekiln@gmail.com</p>
            <div class="social-links">
                <a href="#" target="_blank">Facebook</a>
                <a href="#" target="_blank">Twitter</a>
                <a href="#" target="_blank">Instagram</a>
            </div>
        </div>
    </footer>
</body>
</html>