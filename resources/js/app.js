import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

import Swiper from 'swiper/bundle';
import 'swiper/css/bundle';

document.addEventListener('DOMContentLoaded', function () {
    const swiperThumbs = new Swiper('.mySwiperThumbs', {
        loop: true,
        spaceBetween: 10,
        slidesPerView: 4,
        freeMode: true,
        watchSlidesProgress: true,
        direction: 'vertical', // 👈 Por defecto en desktop

        // 👇 Breakpoints para hacerlo horizontal en pantallas pequeñas
        breakpoints: {
            0: { // Mobile
                direction: 'horizontal',
            },
            768: { // Desktop
                direction: 'vertical',
            }
        }
    });

    const swiperMain = new Swiper('.mySwiper2', {
        loop: true,
        spaceBetween: 10,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        thumbs: {
            swiper: swiperThumbs,
        },
    });
});
