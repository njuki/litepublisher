/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window) {
$.fn.litetabs = function(params) {
if (params == "select") return setselected(arguments[1]);
if (params == "setindex") return setindex(this, arguments[1]);

var options = $.extend({
select: $.noop,
show: $.noop
}, params);

function setselected(a) {
var link = $(a);
var id = link.attr("href");
id = id.substring(id.indexOf("#"));
var content = $(id);
if (options.select(content) === false) return;

link.closest("ul").find("a.active").each(function() {
var self = $(this);
self.removeClass("active");
id = self.attr("href");
id = id.substring(id.indexOf("#"));
$(id).hide();
});

link.addClass("active");
content.show();
options.show(content);
}

function setindex(list, index) {
setselected($("a", list).get(index));
}

				$(this).each(function(){
setindex(this, 0);
				});

					$(this).off("click.litetabs").on("click.litetabs", "a", function(e){
try {
if (!$(this).hasClass("active")) setselected(this);
						e.preventDefault();
      } catch(e) { alert('error ' + e.message); }
					});
return this;
};
}(jQuery, document, window));