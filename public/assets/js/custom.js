
 window.addEventListener('scroll', function() {
    var header = document.getElementById('header');
    if (window.scrollY > 0) { // Kiểm tra nếu người dùng lăn chuột xuống quá 50px
        header.classList.add('active');
    } else {
        header.classList.remove('active');
    }
});



var mainMenu = document.getElementById('navbarToggler');
    mainMenu.onclick = function() {toggleActive()}
    function toggleActive() {
      mainMenu.classList.toggle("active");
      document.querySelector('header').classList.toggle("show");
    }
    


function myFunctLink(element) {
  location.href = element.attributes.href.value;
}

