/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function if_exists(sel, fn) {
$(document).ready(function() {
var items = $(sel);
if (items.length > 0) {
if ($.isFunction(fn)) fn(items);
}
});
}

function load_if_exists(url, sel, fn) {
if_exists(sell, function(items) {
$.getScript(url, function() {
if ($.isFunction(fn)) fn(items);
});
});
}