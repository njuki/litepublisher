function getidview(name) {
var a = name.split('_');
return a[1];
}

function widget_clicked() {
var iditem = $(this).attr("id");
var a = iditem.split("_");
var idview = a[1];
var idwidget = a[2];
}

function set_action(name, value) {
$("#action").val($name);
$("action_value").val(value);
}

function submit_views() {
//var a = $(".
$(".viewsidebar li").each(function() {
});
}

function init_views() {
    $('<link rel="stylesheet" type="text/css" href="'+ ltoptions.files + '/js/jquery/ui-1.8.10/redmond/jquery-ui-1.8.10.custom.css" />').appendTo("head");
    $.getScript(ltoptions.files + '/js/jquery/ui-1.8.10/jquery-ui.lists.1.8.10.custom.min.js', function() {
      $(document).ready(function() {
$("div[rel='tabs']").tabs({ 
cache: true,
   show: function(event, ui) {
/*
switch (ui.index) {
case 0:
$("ul", ui.panel).removeClass("view_sidebars").children("li").removeClass("view_sidebar");
alert($(ui.panel  ).parent().html());
$("ul", ui.panel).addClass("view_sidebars")
.children("li").addClass("view_sidebar");
alert($(ui.panel  ).html());
break;

default:
$("ul", ui.panel).removeClass("view_sidebars").children("li").removeClass("view_sidebar");
}
*/
}
});

$("#allviewtabs").tabs({ cache: true });


  $("input[id^='delete_']").click(function() {
$("#action").val("delete");
$("#action_value").val(idview);
$("form").submit();
});

$("form").submit(function() {
if ("delete" == $("action").val()) return;
$("#action").val("sidebars");
submit_views();
});
});
});
}