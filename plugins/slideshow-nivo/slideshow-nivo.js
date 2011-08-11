/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

  $(document).ready(function() {
    var images = $("a[rel^='prettyPhoto']");
if (images.length) {
$("#slider-nivo").wrap('<div class="slider-wrapper theme-default"><div class="ribbon"></div></div>');
alert($("#slider-nivo").parent.html());
var holder = $("#slider-nivo");
images.each(function() {
var url = $(this).attr("href");
var rel = $('img', this).attr("src");
$('<img src="' + url + '" alt="' + url + '" rel="' + rel + '" />').appentTo(holder);
});

var url = ltoptions.url + "/plugins/slider-nivo/";
$.load_css(url + "nivo-slider.css");
$.load_css(url + "themes/default/default.css");
$.load_script(url + "jquery.nivo.slider.pack.js", function() {
$(window).load(function() {
    $('#slider-nivo').nivoSlider({
controlNavThumbsFromRel: true
});
});
});
}
});