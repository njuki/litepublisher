$(document).ready(function(){     
$("#themetree").find('ul').hide();

$("#themetree").find('a').click(function() {
$(this).parent().find("ul").toggle();
var d = $(this).data("theme");
if (d) {
alert(d);
} else {
$(this).data("theme", "new");
alert('added');
}

return false;
});
});
