// Carousel Functionality
const offerCards = document.querySelectorAll('.offer-card');
const dots = document.querySelectorAll('.dot');
let currentSlide = 0;

function showSlide(index) {
    offerCards.forEach((card, i) => {
        card.classList.remove('active');
        dots[i].classList.remove('active');
    });
    offerCards[index].classList.add('active');
    dots[index].classList.add('active');
}

dots.forEach((dot, index) => {
    dot.addEventListener('click', () => {
        currentSlide = index;
        showSlide(currentSlide);
    });
});

// Auto Slide Change
setInterval(() => {
    currentSlide = (currentSlide + 1) % offerCards.length;
    showSlide(currentSlide);
}, 5000);

// Countdown Timer
function updateCountdown() {
    const countdownElements = document.querySelectorAll('.countdown');
    countdownElements.forEach(element => {
        const endTime = new Date(element.getAttribute('data-end')).getTime();
        const now = new Date().getTime();
        const timeLeft = endTime - now;

        if (timeLeft > 0) {
            const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

            element.textContent = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        } else {
            element.textContent = "Offer Expired";
        }
    });
}

setInterval(updateCountdown, 1000);
updateCountdown(); // Initial call