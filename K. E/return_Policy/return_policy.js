// Modal for Initiating a Return
const initiateReturnBtn = document.querySelector('.initiate-return-btn');

// Create Modal
const modal = document.createElement('div');
modal.innerHTML = `
    <div class="modal-content">
        <h3>Initiate a Return</h3>
        <p>Please fill out the form below to initiate a return.</p>
        <form id="returnForm">
            <div class="form-group">
                <label for="orderNumber">Order Number</label>
                <input type="text" id="orderNumber" name="orderNumber" placeholder="Enter your order number" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email address" required>
            </div>
            <button type="submit" class="submit-btn">Submit</button>
        </form>
        <button class="close-btn">Close</button>
    </div>
`;

// Style the modal overlay
modal.style.display = 'none';
modal.style.position = 'fixed';
modal.style.top = '0';
modal.style.left = '0';
modal.style.width = '100%';
modal.style.height = '100%';
modal.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
modal.style.zIndex = '1000';
modal.style.justifyContent = 'center';
modal.style.alignItems = 'center';

// Style the modal content
const modalContent = modal.querySelector('.modal-content');
modalContent.style.backgroundColor = '#fff';
modalContent.style.padding = '30px';
modalContent.style.borderRadius = '10px';
modalContent.style.maxWidth = '500px';
modalContent.style.width = '90%';
modalContent.style.textAlign = 'left';
modalContent.style.position = 'relative';

// Center the heading and paragraph
modalContent.querySelector('h3').style.textAlign = 'center';
modalContent.querySelector('p').style.textAlign = 'center';

// Style the form group
const formGroups = modalContent.querySelectorAll('.form-group');
formGroups.forEach(group => {
    group.style.marginBottom = '15px';
});

// Style the inputs
const inputs = modalContent.querySelectorAll('input');
inputs.forEach(input => {
    input.style.width = '100%';
    input.style.padding = '10px';
    input.style.border = '1px solid #ddd';
    input.style.borderRadius = '5px';
    input.style.fontSize = '1rem';
    input.style.color = '#555';
});

// Style the Submit button
const submitBtn = modalContent.querySelector('.submit-btn');
submitBtn.style.display = 'block';
submitBtn.style.margin = '20px auto 10px auto'; // Center the button
submitBtn.style.padding = '10px 20px';
submitBtn.style.backgroundColor = '#333';
submitBtn.style.color = '#fff';
submitBtn.style.border = 'none';
submitBtn.style.borderRadius = '5px';
submitBtn.style.cursor = 'pointer';
submitBtn.style.fontSize = '1rem';
submitBtn.style.transition = 'background-color 0.3s ease';

submitBtn.addEventListener('mouseenter', () => {
    submitBtn.style.backgroundColor = '#555';
});

submitBtn.addEventListener('mouseleave', () => {
    submitBtn.style.backgroundColor = '#333';
});

// Style the Close button
const closeBtn = modalContent.querySelector('.close-btn');
closeBtn.style.display = 'block';
closeBtn.style.margin = '10px auto 0 auto'; // Center the button
closeBtn.style.padding = '10px 20px';
closeBtn.style.backgroundColor = '#ccc';
closeBtn.style.color = '#333';
closeBtn.style.border = 'none';
closeBtn.style.borderRadius = '5px';
closeBtn.style.cursor = 'pointer';
closeBtn.style.fontSize = '1rem';
closeBtn.style.transition = 'background-color 0.3s ease';

closeBtn.addEventListener('mouseenter', () => {
    closeBtn.style.backgroundColor = '#bbb';
});

closeBtn.addEventListener('mouseleave', () => {
    closeBtn.style.backgroundColor = '#ccc';
});

// Append modal to the body
document.body.appendChild(modal);

// Show modal on button click
initiateReturnBtn.addEventListener('click', () => {
    modal.style.display = 'flex';
});

// Close modal on close button click
closeBtn.addEventListener('click', () => {
    modal.style.display = 'none';
});

// Handle form submission
modal.querySelector('#returnForm').addEventListener('submit', (e) => {
    e.preventDefault();
    alert('Return request submitted successfully!');
    modal.style.display = 'none';
});