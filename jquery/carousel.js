import { carouselData } from '../scripts/data.js';

$(document).ready(function() {
    const $carouselContent = $('#carousel-content');
    const $prevSlideBtn = $('#prev-slide');
    const $nextSlideBtn = $('#next-slide');

    if ($carouselContent.length && typeof carouselData !== 'undefined') {
        let currentSlide = 0;
        let slideInterval;

        function renderSlide(index) {
            const data = carouselData[index];
            
            $carouselContent.fadeTo(200, 0, function() {
                $(this).html(`
                    <div class="destination">
                        <a href="${data.link}">
                            <img src="${data.image}" alt="${data.title}, ${data.country}">
                            <div class="circle-text">
                                <h3>${data.title}</h3>
                                <p class="country">${data.country}</p>
                                <p class="description">${data.text}</p>
                            </div>
                        </a>
                    </div>
                `).fadeTo(200, 1);
            });
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

        $nextSlideBtn.on('click', function() {
            nextSlide();
            resetAutoplay();
        });

        $prevSlideBtn.on('click', function() {
            prevSlide();
            resetAutoplay();
        });

        renderSlide(currentSlide);
        startAutoplay();
    }
});