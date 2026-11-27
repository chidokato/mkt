var swiper = new Swiper(".mySwiper-product-thumr", {
  freeMode: true,
  watchSlidesProgress: true,
  breakpoints: {
        0: {
            slidesPerView: 5,
            spaceBetween: 2,
        },
        640: {
            slidesPerView: 5,
            spaceBetween: 2,
        },
        768: {
            slidesPerView: 7,
            spaceBetween: 2,
        },
        1024: {
            slidesPerView: 10,
            spaceBetween: 2,
        },
    }
});

var swiper2 = new Swiper(".mySwiper-product", {
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    },
    thumbs: {
        swiper: swiper,
    },
    breakpoints: {
        0: {
            slidesPerView: 1,
            spaceBetween: 2,
        },
        640: {
            slidesPerView: 1,
            spaceBetween: 2,
        },
        768: {
            slidesPerView: 2,
            spaceBetween: 2,
        },
        1024: {
            slidesPerView: 2,
            spaceBetween: 2,
        },
    }
});


document.addEventListener('DOMContentLoaded', function () {
// Lấy tất cả các cặp slider
    const sliders = document.querySelectorAll('.content-img');

    sliders.forEach((slider, index) => {
        // Lấy phần tử swiper
        const mySwiperElem = slider.querySelector('.slider-section-thumbs');
        const mySwiper2Elem = slider.querySelector('.slider-section');
        
        // Kiểm tra sự tồn tại của các phần tử
        if (mySwiperElem && mySwiper2Elem) {
            // Tạo unique class name cho từng slider
            const mySwiperClass = 'slider-section-thumbs-' + index;
            const mySwiper2Class = 'slider-section-' + index;
            
            // Thêm class name cho các container swiper
            mySwiperElem.classList.add(mySwiperClass);
            mySwiper2Elem.classList.add(mySwiper2Class);

            // Khởi tạo Swiper cho các container swiper
            var swiper = new Swiper('.' + mySwiperClass, {
                spaceBetween: 2,
                slidesPerView: 10,
                freeMode: true,
                watchSlidesProgress: true,
                breakpoints: {
                    0: {
                        slidesPerView: 5,
                        spaceBetween: 2,
                    },
                    640: {
                        slidesPerView: 5,
                        spaceBetween: 2,
                    },
                    768: {
                        slidesPerView: 7,
                        spaceBetween: 2,
                    },
                    1024: {
                        slidesPerView: 10,
                        spaceBetween: 2,
                    },
                }
            });

            var swiper2 = new Swiper('.' + mySwiper2Class, {
                spaceBetween: 2,
                slidesPerView: 1,
                // navigation:false,
                // navigation: {
                //     nextEl: '.swiper-button-next',
                //     prevEl: '.swiper-button-prev',
                // },
                thumbs: {
                    swiper: swiper,
                },

            });
        }
    });
});

var swiper = new Swiper(".mySwiper-section-1", {
  spaceBetween: 2,
  slidesPerView: 1,
  pagination: {
    el: ".swiper-pagination",
  },
});

var swiper = new Swiper(".mySwiper-section-2", {
  spaceBetween: 2,
  slidesPerView: 2,
  pagination: {
    el: ".swiper-pagination",
  },
});


function openTab(evt, tabName) {
    var i, tabcontent, tablinks;

    // Hide all tab contents
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }

    // Remove the active class from all tab links
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }

    // Show the current tab and add an "active" class to the button that opened the tab
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.className += " active";
}
