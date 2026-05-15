import { carouselData } from './data.js';

document.addEventListener('DOMContentLoaded', function() {
    const carouselContent = document.getElementById('carousel-content');
    const prevSlideBtn = document.getElementById('prev-slide');
    const nextSlideBtn = document.getElementById('next-slide');

    const today = new Date();
    const tomorrow = new Date();
    tomorrow.setDate(today.getDate() + 1);
    const formatDate = (date) => date.toISOString().split('T')[0];

    if (carouselContent && typeof carouselData !== 'undefined') {
        let currentSlide = 0;
        let slideInterval;

        function renderSlide(index) {
            const data = carouselData[index];
            
            carouselContent.style.opacity = 0;


            const checkInVal = formatDate(today);
            const checkOutVal = formatDate(tomorrow);
            const destination = encodeURIComponent(data.title);
            let link = `discover.php?search=${destination}&check-in=${checkInVal}&check-out=${checkOutVal}`

            setTimeout(() => {
                carouselContent.innerHTML = `
                    <div class="destination">
                        <a href="${link}">
                            <img src="${data.image}" alt="${data.title}, ${data.country}">
                            <div class="circle-text">
                                <h3>${data.title}</h3>
                                <p class="country">${data.country}</p>
                                <p class="description">${data.text}</p>
                            </div>
                        </a>
                    </div>
                `;
                carouselContent.style.opacity = 1;
            }, 200);
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % carouselData.length;
            renderSlide(currentSlide);
        }

        function prevSlide() {
            currentSlide = (currentSlide - 1 + carouselData.length) % carouselData.length;
            renderSlide(currentSlide);
        }

        function startAutoplay() {
            slideInterval = setInterval(nextSlide, 3000);
        }

        function resetAutoplay() {
            clearInterval(slideInterval);
            startAutoplay();
        }

        nextSlideBtn.addEventListener('click', () => {
            nextSlide();
            resetAutoplay();
        });

        prevSlideBtn.addEventListener('click', () => {
            prevSlide();
            resetAutoplay();
        });

        renderSlide(currentSlide);
        startAutoplay();
    }
});