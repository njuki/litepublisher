function getidview(name) {
var a = name.split('_');
return a[1];
}

function set_action(name, value) {
$("#action").val($name);
$("action_value").val(value);
}

function submit_views() {
for (var i =0; i < ltoptions.allviews.length; i++) {
var idview = ltoptions.allviews[i];
var idwidgets = "#widgets_" + idview + "_";
$("ul[id^='view_sidebar_" + idview + "_']").each(function() {
var sidebar = $(this).attr("id").split("_").pop();
var widgets = $.map($(this).sortable('toArray'), function(v, index) {
return v.split("_").pop();
});
$(idwidgets + sidebar).val(widgets.join(","));
//} catch(e) { alert(e.message); }
});
}
}

function confirm_dialog(sel, fn) {
$(sel).dialog( {
autoOpen: true,
modal: true,
buttons: [
{
        text: "Ok",
        click: function() {
 $(this).dialog("close"); 
if ($.isFunction(fn)) fn();
}
    },
{
        text: "Cancel",
        click: function() { $(this).dialog("close"); }
    }
]
} );

}

function init_views() {
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
confirm_dialog("#dialog_view_delete", function() {
$("#action").val("delete");
$("#action_value").val(idview);
$("form").submit();
});
});

$("form").submit(function() {
if ("delete" == $("action").val()) return;
$("#action").val("sidebars");
submit_views();
return false;
});

$(".view_sidebar li").click(function() {
var id = $(this).attr("id");
var a = id.split("_");
$("#widgetoptions_"+ a[1] + " div").hide();
$("#widgetoptions_" + a[1] + "_" + a[2]).show();
});

$(".view_sidebars").each(function() {
$(".view_sidebar", this).sortable({
			connectWith: $(".view_sidebar", this)
});
});

$("input[id^='widget_delete_']").click(function() {
var a = $(this).attr("id").split("_");
var idwidget = a.pop();
var idview = a.pop();
confirm_dialog("#dialog_widget_delete", function() {
$("#widget_" + idview + "_" + idwidget).remove();
$("#widgetoptions_" + idview + "_" + idwidget).hide();
});
});


});
});
}