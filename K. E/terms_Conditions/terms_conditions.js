// Accept Terms Button
const acceptTermsBtn = document.querySelector('.accept-terms-btn');

acceptTermsBtn.addEventListener('click', () => {
    const confirmation = confirm('By clicking "OK", you agree to our Terms and Conditions.');
    if (confirmation) {
        alert('Thank you for accepting our Terms and Conditions!');
        // You can add additional logic here, such as saving the user's acceptance.
    } else {
        alert('You must accept the Terms and Conditions to continue.');
    }
});