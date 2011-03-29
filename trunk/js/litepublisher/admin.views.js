function set_action(name, value) {
$("#action").val($name);
$("action_value").val(value);
}

$(function() {
  $("input[id^='delete_']").click(function() {
$("#action").val($"delete");
$("#action_value").val(idview);
$("form").submit();
});

$("form").submit(function() {
if ("delete" == $("action").val()) return;
$("#action").val("sidebars");

});
});