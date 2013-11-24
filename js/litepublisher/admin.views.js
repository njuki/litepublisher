/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/
(function ($, document, window) {
function submit_views() {
  for (var idview in ltoptions.allviews) {
    var idwidgets = "#widgets_" + idview + "_";
    $("ul[id^='view_sidebar_" + idview + "_']").each(function() {
      var sidebar = $(this).attr("id").split("_").pop();
      var widgets = $.map($(this).sortable('toArray'), function(v, index) {
        return v.split("_").pop();
      });
      $(idwidgets + sidebar).val(widgets.join(","));
    // catch(e) { alert(e.message); }
    });
  }
}

function show_append_widgets(show) {
  if (show) {
    $("#appendwidgets").show();
  } else {
    $("#appendwidgets").hide();
  }
}

function widget_clicked() {
  var a = $(this).attr("id").split("_");
  $("div[id^='widgetoptions_"+ a[1] + "_']").hide();
  $("#widgetoptions_" + a[1] + "_" + a[2]).show();
}

function _init_views() {
var form = $("form:first");
/* removed from html
        $("#checkbox-customsidebar_1", form).attr("disabled", "disabled");
        $("#checkbox-disableajax_1", form).attr("disabled", "disabled");
        $("#submitbutton-delete_1", form).attr("disabled", "disabled");
        */

$(".admintabs        ", form).each(function() {
var tab = $(this);
var customdata = tab.data("custom");
if (!customdata || !("idview" in customdata)) return;
var idview = customdata.idview;
      if (idview == "1") {
        var disabled = [];
      } else {
        var checked = $("#checkbox-customsidebar_" + idview, tab).attr("checked");
        var disabled = checked ? [] : [0];
        $("#checkbox-disableajax_" + idview, tab).attr("disabled", checked ? "disabled" : false);
      }
      
tab.tabs("option", {
        disabled: disabled,
        active: disabled.length == 0 ? 0 : 1,
        activate: function(event, ui) {
          if (        ui.newTab.index() == 0) {
            var idview = $(ui.newTab).closest(".admintabs").data("custom").idview;
            var showlist = $("#appendwidget_" + idview, form).data("showlist");
            show_append_widgets(showlist);
          } else {
            show_append_widgets(false);
          }
        }
        
      });
});
    
    $("input[name^='customsidebar_']", form).click(function() {
    var self = $(this);
      var idview = self.attr("id").split("_").pop();
      var checked = self.attr("checked");
      $("#checkbox-disableajax_" + idview).attr("disabled", checked ? "disabled" : "");
self.closest(".admintabs").tabs( "option", "disabled", checked  ? [] : [0]);
    });
    
    $("input[name^='delete_']", form).click(function() {
      var idview = $(this).attr("name").split("_").pop();
      $.confirmdelete(function() {
        $("#action", form).val("delete");
        $("#action_value", form).val(idview);
form.submit();
      });
    });
    
form.submit(function() {
      if ("delete" == $("#hidden-action", form).val()) return;
      $("#hidden-action", form).val("widgets");
      submit_views();
      //return false;
    });
    
    $(".view_sidebar li", form).click(widget_clicked);
    
    $(".view_sidebars", form).each(function() {
      $(".view_sidebar", this).sortable({
        connectWith: $(".view_sidebar", this),
        
        receive: function(event, ui) {
          if ($(ui.sender).attr("id") == "append_widgets") {
            var id = $(ui.item).attr("id").split("_").pop();
            if ($("li[id$='_" + id + "']", this).length > 1) {
              $(ui.sender).sortable('cancel');
            } else {
              var a = $(this).attr("id").split("_");
              a.pop();
              $(ui.item).attr("id", "widget_" + a.pop() + "_" + id);
              //$(ui.item).click(widget_clicked);
            }
            
          }
        }
        
      });
    });
    
    $("#append_widgets", form).sortable({
      connectWith: ".view_sidebar",
      helper: "clone"
    });
    
    $("button[id^='submit-widget_delete_']", form).click(function() {
      var a = $(this).attr("id").split("_");
      var idwidget = a.pop();
      var idview = a.pop();
      $.confirmdelete(function() {
        $("#widget_" + idview + "_" + idwidget, form).remove();
        $("#widgetoptions_" + idview + "_" + idwidget, form).hide();
      });
    });
    
    //remember init state
    $("input[id^='inline_']", form).each(function() {
      $(this).data("enabled", ! $(this).attr("disabled"));
    });
    //ajax options of single widget
    $("input[id^='ajax_']", form).click(function() {
      var checked = $(this).attr("checked");
      var id = "#" + $(this).attr("id").replace("ajax_", "inline_");
      if ($(id).data("enabled")) {
        $(id).attr("disabled", checked ? "" : "disabled");
      }
    });
    
    $("a[id^='appendwidget_']", form).click(function() {
      var showlist = ! $(this).data("showlist");
      $(this).data("showlist", showlist);
      show_append_widgets(showlist);
      return false;
});
   }

$(document).ready(function() {
_init_views();
});
}(jQuery, document, window));