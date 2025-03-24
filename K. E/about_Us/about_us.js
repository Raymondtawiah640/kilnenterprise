// Toggle Additional Content
const learnMoreBtn = document.querySelector('.learn-more-btn');
const additionalContent = document.createElement('div');
additionalContent.innerHTML = `
    <h3>Our Team</h3>
    <p>
        Our team is made up of passionate individuals who are dedicated to delivering the best products and services. 
        From our designers to our customer support team, everyone plays a crucial role in our success.
    </p>
`;
additionalContent.style.display = 'none'; // Hide additional content initially

// Insert additional content after the button
learnMoreBtn.insertAdjacentElement('afterend', additionalContent);

// Toggle visibility on button click
learnMoreBtn.addEventListener('click', () => {
    if (additionalContent.style.display === 'none') {
        additionalContent.style.display = 'block';
        learnMoreBtn.textContent = 'Show Less';
    } else {
        additionalContent.style.display = 'none';
        learnMoreBtn.textContent = 'Learn More';
    }
});