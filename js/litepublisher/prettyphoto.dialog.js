/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( $ ){
  $.messagebox = function(title, mesg) {
    return $.prettyPhotoDialog({
      title: title,
      html: "<p>" + mesg + "</p>",
      width: 200
    });
  };
  
  $.confirmbox= function(title, mesg, button_title1, button_title2, callback) {
    return $.prettyPhotoDialog({
      title: title,
      html: "<p>" + mesg + "</p>",
      //width: 200,
      buttons: [
      {
        title: button_title1,
        click: function() {
          var index = $(this).data("index");
          $.prettyPhoto.close();
          callback(index);
        }
      },
      
      {
        title: button_title2,
        click: function() {
          var index = $(this).data("index");
          $.prettyPhoto.close();
          callback(index);
        }
      }
      ]
    });
  };
  
  $.confirmdelete= function(callback) {
    $.confirmbox(lang.dialog.confirm, lang.dialog.confirmdelete, lang.dialog.yes, lang.dialog.no, function(index) {
      if (index == 0) callback();
    });
  };
  
  $.fn.prettyPhotoDialog = function(buttons) {
    $.prettyPhotoDialog({
      title: $(this).attr("title"),
      html: $(this).html(),
      buttons: buttons
    });
    return this;
  };
  
  $.prettyPhotoDialog = function(o) {
    var options = $.extend({
      title: "",
      html: "",
      width: 300,
    open: function() {},
    close: function() {},
      buttons: [
      {
        title: "Ok",
        click: function() {
          $.prettyPhoto.close();
        }
      }
      ]
    }, o);
    
var button = '<button type="button" class="pp_dialog_btn_{index}">{title}</button>';
    var buttons = '';
    for (var i =0, l= options.buttons.length;  i < l; i++) {
  buttons += button.replace(/{index}/g, i).replace(/{title}/g, options.buttons[i].title);
    }
    
    var id = "pp_dialog_id_" + (Math.random() + '').replace('.', '');
    var div = $('<div id="' + id + '"></div>').appendTo("body").hide();
    div.html('<div class="pp_dialog_title">' +
    '<h3>' + options.title + '</h3></div>' +
    options.html +
    '<div class="pp_dialog_buttons">' + buttons + '</div>')
    
    var tmp = $('<div></div>').appendTo('body').hide();
    var a = $("<a title=''></a>").appendTo(tmp);
    a.attr("href", "#" +id);
    
    a.prettyPhoto({
      default_width: options.width,
      opacity: 0.60, /* Value between 0 and 1 */
      modal: true, /* If set to true, only the close button will close the window */
      deeplinking: false, /* Allow prettyPhoto to update the url to enable deeplinking. */
      keyboard_shortcuts: false, /* Set to false if you open forms inside prettyPhoto */
      show_title: false, /* true/false */
      social_tools: false,
      //hideflash: true,
      
      changepicturecallback: function(){
        div.remove();
        $(".pp_close").remove();
        for (var i =0, l= options.buttons.length;  i < l; i++) {
          $(".pp_dialog_btn_" + i).data("index", i).click(options.buttons[i].click);
        }
        $(".pp_dialog_btn_0").focus();
        if ($.isFunction(options.open)) options.open($(".pp_inline"));
      },
      
      /* Called when prettyPhoto is closed */
      callback: function(){
        $(document).off('keydown.onEscape');
        if ($.isFunction(options.close)) options.close();
      }
    });
    
    a.click();
    tmp.remove();
    
    $.onEscape($.prettyPhoto.close);
    return options;
  };
  
})( jQuery );