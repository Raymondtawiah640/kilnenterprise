// Carousel Functionality
const carousel = document.querySelector('.product-carousel');
const productCards = document.querySelectorAll('.product-card');
let currentIndex = 0;

function moveCarousel(direction) {
    const cardWidth = productCards[0].offsetWidth + 20; // Include gap
    const maxIndex = productCards.length - 1;

    currentIndex += direction;
    if (currentIndex < 0) currentIndex = maxIndex;
    if (currentIndex > maxIndex) currentIndex = 0;

    const offset = -currentIndex * cardWidth;
    carousel.style.transform = `translateX(${offset}px)`;
}

// Auto Slide Change
setInterval(() => {
    moveCarousel(1);
}, 5000);