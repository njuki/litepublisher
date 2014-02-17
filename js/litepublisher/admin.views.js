/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window) {
  litepubl.Adminview = Class.extend({

  init: function() {
var form = $("#admin-view-form");
var tabs = $(".admintabs:first", form);
var appendwidgets = $("#appendwidgets", form);
var woptions = $("#woptions-holder", tabs);
var sidebars = $("#adminview-sidebars", tabs);
      var ul = $(".adminview-sidebar ul", sidebars);
        var disabled = [];
var custom = $("#checkbox-customsidebar", form);

//checkbox hasnt in default view
if (custom.length) {
        var checked = custom.attr("checked");
if (!checked) disabled = [0];
        var disableajax = $("#checkbox-disableajax", form).prop("disabled", checked ? "disabled" : false);
custom.click(function() {    
      var checked = $(this).prop("checked");
disableajax.prop("disabled", checked ? "disabled" : false);
tabs.tabs( "option", "disabled", checked  ? [] : [0]);
    });
      }
      
      tabs.tabs({
        disabled: disabled,
        active: disabled.length ? 1 : 0,
        beforeLoad: litepubl.uibefore
      });

sidebars.on("click.widget", "li", function() {
var id = $(this).data('idwidget');
$(".woptions", woptions).addClass("hidden");
$("#woptions-" + id, woptions).removeClass("hidden");
return false;
});
    
ul.sortable({
        connectWith: ul,
                receive: function(event, ui) {
          if ($(ui.sender).attr("id") == "append_widgets") {
            var id = $(ui.item).data("idwidget");
            if ($("li[data-idwidget='" + id + "']", this).length) {
              $(ui.sender).sortable('cancel');
            }
          }
        }
      });

    $("#append_widgets", form).sortable({
      //helper: "clone",
      connectWith: ul
    });
    
    form.submit(function() {
ul.each(function() {
        var idwidgets = [];
$("li", this).each(function() {
idwidgets.push($(this).data("idwidget"));
        });
        $("#hidden-sidebar" + $(this).data("index")).val(widgets.join(","));
});
    });
    
    woptions.on("click.delete", "[name^='delete']", function() {
      var holder = $(this).closest(".woptions");
ul.find("[data-idwidget=" + holder.data("idwidget") + "]:first").remove();
holder.remove();
return false;
    });
    
woptions.on("click.options", "input[id^='ajax']", function() {
var holder = $(this).closest(".woptions");
      if (holder.data("inline") == "enabled") {
$("[name='inline" + holder.data("idwidget") + "']", holder).prop("disabled", $(this).prop("checked") ? false : "disabled");
      }
    });
   
  }
  
});

  $(document).ready(function() {
        try {
      litepubl.adminview = new litepubl.Adminview();
  } catch(e) {erralert(e);}
  });
}(jQuery, document, window));