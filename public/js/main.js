(function($) {
  'use strict';

  $('#preloader').fadeOut();

  var testimonialSlider = $('#client-testimonial');
  testimonialSlider.owlCarousel({
    navigation: true,
    pagination: false,
    slideSpeed: 1000,
    stopOnHover: true,
    autoPlay: true,
    items: 1,
    animateIn: 'fadeIn',
    animateOut: 'fadeOut',
    addClassActive: true,
    itemsDesktop: [1199, 1],
    itemsDesktopSmall: [980, 1],
    itemsTablet: [768, 1],
    itemsTabletSmall: [767, 1],
    itemsMobile: [479, 1]
  });

  $('#client-testimonial').find('.owl-prev').html('<i class="lni-chevron-left"></i>');
  $('#client-testimonial').find('.owl-next').html('<i class="lni-chevron-right"></i>');

  var showcaseSlider = $('.showcase-slider');
  showcaseSlider.owlCarousel({
    navigation: false,
    pagination: true,
    slideSpeed: 1000,
    margin: 10,
    stopOnHover: true,
    autoPlay: true,
    items: 5,
    responsive: {
      1199: { items: 5 },
      992: { items: 4 },
      768: { items: 3 },
      480: { items: 2 },
      0: { items: 1 }
    }
  });

  $(window).on('scroll', function() {
    var scroll = $(window).scrollTop();

    if (scroll < 20) {
      $('.navbar-area').removeClass('sticky menu-bg');
    } else {
      $('.navbar-area').addClass('sticky menu-bg');
    }
  });

  $('.page-scroll').on('click', function(event) {
    var hash = this.hash;

    if (!hash || hash.charAt(0) !== '#' || !$(hash).length) {
      return;
    }

    event.preventDefault();

    try {
      var position = $(hash).offset().top - 60;
      $('html').animate({ scrollTop: position }, 900);
    } catch (e) {
      console.warn('Scroll animation error:', e);
    }
  });

  var scrollLink = $('.page-scroll');

  $(window).on('scroll', function() {
    if (!scrollLink || scrollLink.length === 0) {
      return;
    }

    var scrollbarLocation = $(this).scrollTop();

    scrollLink.each(function() {
      var hash = this.hash;

      if (!hash || hash.charAt(0) !== '#' || !$(hash).length) {
        return;
      }

      try {
        var sectionOffset = $(hash).offset().top - 73;

        if (sectionOffset <= scrollbarLocation) {
          $(this).parent().addClass('active');
          $(this).parent().siblings().removeClass('active');
        }
      } catch (e) {
        console.warn('Scroll offset calculation error:', e);
      }
    });
  });

  $('.navbar-nav a').on('click', function() {
    $('.navbar-collapse').removeClass('show');
  });

  $('.navbar-toggler').on('click', function() {
    $(this).toggleClass('active');
  });

  $('.navbar-nav a').on('click', function() {
    $('.navbar-toggler').removeClass('active');
  });

  $('.video-popup').magnificPopup({
    disableOn: 700,
    type: 'iframe',
    mainClass: 'mfp-fade',
    removalDelay: 160,
    preloader: false,
    fixedContentPos: false
  });

  var offset = 200;
  $(window).scroll(function() {
    if ($(this).scrollTop() > offset) {
      $('.back-to-top').fadeIn(400);
    } else {
      $('.back-to-top').fadeOut(400);
    }
  });

  $('.back-to-top').on('click', function(event) {
    event.preventDefault();
    $('html, body').animate({ scrollTop: 0 }, 600);
    return false;
  });

  $(window).on('load', function() {
    if ($('.navbar-collapse').length) {
      try {
        $('body').scrollspy({ target: '.navbar-collapse', offset: 195 });
      } catch (e) {
        console.warn('Scrollspy initialization error:', e);
      }
    }

    $(window).on('scroll', function() {
      if ($(window).scrollTop() > 100) {
        $('.fixed-top').addClass('menu-bg');
      } else {
        $('.fixed-top').removeClass('menu-bg');
      }
    });
  });

  function close_toggle() {
    if ($(window).width() <= 768) {
      $('.navbar-collapse a').on('click', function() {
        $('.navbar-collapse').collapse('hide');
      });
    } else {
      $('.navbar .navbar-inverse a').off('click');
    }
  }

  close_toggle();
  $(window).resize(close_toggle);

  if ($.fn.nivoLightbox) {
    $('.lightbox').nivoLightbox({
      effect: 'fadeScale',
      keyboardNav: true
    });
  }

  if ($('#world-map-markers').length && $.fn.mapael) {
    $('#world-map-markers').mapael({
      map: {
        name: 'usa_states',
        zoom: {
          enabled: true,
          maxLevel: 10
        }
      }
    });
  }
})(jQuery);
