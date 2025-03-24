// Accordion Functionality
const faqQuestions = document.querySelectorAll('.faq-question');

faqQuestions.forEach(question => {
    question.addEventListener('click', () => {
        // Toggle active class on the clicked question
        question.classList.toggle('active');

        // Toggle the answer visibility
        const answer = question.nextElementSibling;
        if (answer.style.maxHeight) {
            answer.style.maxHeight = null;
            answer.style.padding = '0 15px';
        } else {
            answer.style.maxHeight = answer.scrollHeight + 'px';
            answer.style.padding = '15px';
        }

        // Close other answers
        faqQuestions.forEach(otherQuestion => {
            if (otherQuestion !== question && otherQuestion.classList.contains('active')) {
                otherQuestion.classList.remove('active');
                const otherAnswer = otherQuestion.nextElementSibling;
                otherAnswer.style.maxHeight = null;
                otherAnswer.style.padding = '0 15px';
            }
        });
    });
});