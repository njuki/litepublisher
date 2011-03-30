function getidview(name) {
var a = name.split('_');
return a[1];
}

function widget_clicked() {
var iditem = $I(this).attr("id");
var a = iditem.split("_");
var idview = a[1];
var idwidget = a[2];
}

function set_action(name, value) {
$("#action").val($name);
$("action_value").val(value);
}

function submit_views() {
var a = $(".
$(".viewsidebar li").each(function() {
var 
});
}

function init_views() {
$(function() {
  $("input[id^='delete_']").click(function() {
$("#action").val($"delete");
$("#action_value").val(idview);
$("form").submit();
});

$("form").submit(function() {
if ("delete" == $("action").val()) return;
$("#action").val("sidebars");
submit_views();
});
});