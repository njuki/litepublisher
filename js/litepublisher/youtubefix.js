/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

;(function ($, document, window) {
$(document).ready(function() {
window.setTimeout(function() {
var prettyClose = $.prettyPhoto.close;
$.prettyPhoto.close = function() {
// if youtube opened in pretty
var iframe = $('iframe[src*=youtube]', '#pp_full_res');
if (iframe.length == 0) {
prettyClose();
} else {
iframe.attr("src", "");
window.setTimeout(function() {
prettyClose();
    }, 100);
    }
};
}, 20);
});
}(jQuery, document, window));