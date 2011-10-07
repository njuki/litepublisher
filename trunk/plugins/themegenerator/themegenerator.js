/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function set_color(name, value) {
for (var i = ltoptions.colors.length - 1; i >= 0; i--) {
var item = ltoptions.colors[i];
if (name == item['name']) {
var propvalue = item['propvalue'].replace('%%' + name + '%%', value);
propvalue = propvalue.replace(/%%(\w*+)%%/g, function (str, name2, offset, s) {
return $('#text-color-' + name2).val();
});
$(item['sel']).css(item['propname'], propvalue);
}
}
}

$(document).ready(function() {
$("#showmenucolors").click(function() {
$("#menucolors").slideToggle();
});

$("input[id^='colorbutton']").ColorPicker({
	onSubmit: function(hsb, hex, rgb, el) {
		$("#text-color-" + $(el).attr("rel")).val(hex);
		$(el).ColorPickerHide();
set_color($(el).attr("rel"), hex);
	},

	onBeforeShow: function () {
var edit = "#text-color-" + $(this).attr("rel");
$(this).ColorPickerSetColor($(edit).val());
	}
});

});