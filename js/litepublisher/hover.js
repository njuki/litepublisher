(function ($, window) {
  'use strict';

$.ready2(function() {
var body = $("body");
var disableclass = 'disable-hover';
var     timer;

window.addEventListener('scroll', function() {
  clearTimeout(timer);
  if(!body.hasClass(disableclass)) body.addClass(disableclass);

  timer = setTimeout(function(){
    body.removeClass(disableclass);
  }, 500);
}, false);

});
}(jQuery, window));