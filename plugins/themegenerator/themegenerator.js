/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function set_color(name, value) {
for (var i = 0, l =ltoptions.colors.length ; i < l; i++) {
var item = ltoptions.colors[i];
if (name == item['name']) {
var propvalue = item['value'].replace('%%' + name + '%%', value);
var a = propvalue.split('%%');
if (a.length >= 2) {
var name2= a[1];
propvalue = propvalue.replace('%%' + name2 + '%%', $('#text-color-' + name2).val());
}

$(item['sel']).css(item['propname'], propvalue);
}
}
}

$(document).ready(function() {
$("#showmenucolors").click(function() {
$("#menucolors").slideToggle();
return false;
});

$("input[id^='colorbutton']").ColorPicker({
	onSubmit: function(hsb, hex, rgb, el) {
		$("#text-color-" + $(el).attr("rel")).val(hex);
		$(el).ColorPickerHide();
try {
set_color($(el).attr("rel"), hex);
} catch(e) { alert(e.message); }
	},

//onShow: function() {$(".colorpicker_submit").append('<a href="">submit</a>');},

	onBeforeShow: function () {
var edit = "#text-color-" + $(this).attr("rel");
$(this).ColorPickerSetColor($(edit).val());
	}
});

});