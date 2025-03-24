// Accept Policy Button
const acceptPolicyBtn = document.querySelector('.accept-policy-btn');

acceptPolicyBtn.addEventListener('click', () => {
    const confirmation = confirm('By clicking "OK", you agree to our Privacy Policy.');
    if (confirmation) {
        alert('Thank you for accepting our Privacy Policy!');
        // You can add additional logic here, such as saving the user's acceptance.
    } else {
        alert('You must accept the Privacy Policy to continue.');
    }
});