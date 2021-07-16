$(document).ready(function () {
    $(window).on('scroll', function(e) {
        if ($(window).scrollTop() > 92) {
            $('.navbar-vinalife').addClass('scroll');
        } else {
            $('.navbar-vinalife').removeClass('scroll');
        }
    });
});

