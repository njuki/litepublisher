/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

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
  $(document).ready(function() {
    $("div[rel='tabs']").each(function() {
      var idview = $(this).attr("id").split("_").pop();
      if (idview == "1") {
        $("#customsidebar_1").attr("disabled", "disabled");
        $("#disableajax_1").attr("disabled", "disabled");
        $("#delete_1").attr("disabled", "disabled");
        var disabled = [];
      } else {
        var checked = $("#customsidebar_" + idview).attr("checked");
        var disabled = checked ? [] : [0];
        $("#disableajax_" + idview).attr("disabled", checked ? "disabled" : false);
      }
      
      $(this).tabs({
        beforeLoad: litepubl.uibefore,
        disabled: disabled,
        selected: disabled.length == 0 ? 0 : 1,
        show: function(event, ui) {
          if (ui.index == 0) {
            var idview = $(ui.panel).attr("id").split("_").pop();
            var showlist = $("#appendwidget_" + idview).data("showlist");
            show_append_widgets(showlist);
          } else {
            show_append_widgets(false);
          }
        }
        
      });
    });
    
    $("input[id^='customsidebar_']").click(function() {
      var idview = $(this).attr("id").split("_").pop();
      var checked = $(this).attr("checked");
      $("#disableajax_" + idview).attr("disabled", checked ? "disabled" : "");
      $( "#viewtab_" + idview ).tabs( "option", "disabled", checked  ? [] : [0]);
    });
    
  $("#allviewtabs").tabs({         beforeLoad: litepubl.uibefore});
    
    $("input[id^='delete_']").click(function() {
      var idview = $(this).attr("id").split("_").pop();
      $.confirmdelete(function() {
        $("#action").val("delete");
        $("#action_value").val(idview);
        $("#form").submit();
      });
    });
    
    $("#form").submit(function() {
      if ("delete" == $("#action").val()) return;
      $("#action").val("widgets");
      submit_views();
      //return false;
    });
    
    $(".view_sidebar li").click(widget_clicked);
    
    $(".view_sidebars").each(function() {
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
    
    $("#append_widgets").sortable({
      connectWith: ".view_sidebar",
      helper: "clone"
    });
    
    $("input[id^='widget_delete_']").click(function() {
      var a = $(this).attr("id").split("_");
      var idwidget = a.pop();
      var idview = a.pop();
      $.confirmdelete(function() {
        $("#widget_" + idview + "_" + idwidget).remove();
        $("#widgetoptions_" + idview + "_" + idwidget).hide();
      });
    });
    
    //remember init state
    $("input[id^='inline_']").each(function() {
      $(this).data("enabled", ! $(this).attr("disabled"));
    });
    //ajax options of single widget
    $("input[id^='ajax_']").click(function() {
      var checked = $(this).attr("checked");
      var id = "#" + $(this).attr("id").replace("ajax_", "inline_");
      if ($(id).data("enabled")) {
        $(id).attr("disabled", checked ? "" : "disabled");
      }
    });
    
    $("a[id^='appendwidget_']").click(function() {
      var showlist = ! $(this).data("showlist");
      $(this).data("showlist", showlist);
      show_append_widgets(showlist);
      return false;
      
      
  });   });
}

if (window.jqloader ===  undefined) {
  _init_views();
} else {
  var script = window.jqloader.load(ltoptions.files + '/js/litepublisher/admin.' + $.fn.jquery + '.min.js');
  script.done(_init_views);
}