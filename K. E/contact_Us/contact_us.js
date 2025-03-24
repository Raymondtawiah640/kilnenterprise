// Form Validation and Submission
const contactForm = document.getElementById('contactForm');

contactForm.addEventListener('submit', (e) => {
    e.preventDefault(); // Prevent form submission

    // Get form values
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const subject = document.getElementById('subject').value.trim();
    const message = document.getElementById('message').value.trim();

    // Simple validation
    if (!name || !email || !subject || !message) {
        alert('Please fill out all fields.');
        return;
    }

    // Simulate form submission (replace with actual submission logic)
    console.log('Form submitted:', { name, email, subject, message });
    alert('Thank you for contacting us! We will get back to you soon.');

    // Clear form fields
    contactForm.reset();
});