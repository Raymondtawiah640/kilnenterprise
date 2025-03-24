// Tab Navigation
const tabLinks = document.querySelectorAll('.account-nav a');
const tabContents = document.querySelectorAll('.tab-content');

tabLinks.forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();

        // Remove active class from all links and tabs
        tabLinks.forEach(link => link.classList.remove('active'));
        tabContents.forEach(content => content.classList.remove('active'));

        // Add active class to the clicked link and corresponding tab
        link.classList.add('active');
        const tabId = link.getAttribute('data-tab');
        document.getElementById(tabId).classList.add('active');
    });
});

// Handle Profile Form Submission
const profileForm = document.getElementById('profileForm');
profileForm.addEventListener('submit', (e) => {
    e.preventDefault();
    alert('Profile updated successfully!');
});

// Handle Settings Form Submission
const settingsForm = document.getElementById('settingsForm');
settingsForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    if (password === confirmPassword) {
        alert('Password updated successfully!');
    } else {
        alert('Passwords do not match. Please try again.');
    }
});