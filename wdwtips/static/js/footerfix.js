/**
 * footerfix.js
 * Author: Adam Beagle
 *
 * Moves footer to bottom of page on pages that are not as tall as the viewport.
 * Note selectors are specific to this project
 */
(function() {
  // This function pilfered years ago from I-don't-remember-where
  function debounce(a,b,c){var d;return function(){var e=this,f=arguments;clearTimeout(d),d=setTimeout(function(){d=null,c||a.apply(e,f)},b),c&&!d&&a.apply(e,f)}};

  // Resize .content-wrapper to make page full height of browser, so footer
  // is on bottom and doesn't look weird
  function footerFix() {
    var headerH = document.querySelector('body>header').offsetHeight,
        topicsH = document.querySelector('nav.topics').offsetHeight,
        footerH = document.querySelector('body>footer').offsetHeight,
        content = document.querySelector('.content-wrapper'),
        windowH = window.innerHeight;

    content.style['min-height'] = (windowH - (headerH + footerH + topicsH) - 20) + 'px';
  }

  footerFix();

  // Called on resize, but debounced to every 100ms so it's not spammed
  window.addEventListener('resize', debounce(footerFix, 100));
}());