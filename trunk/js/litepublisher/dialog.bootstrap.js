/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( $, window, document){
  $.BootstrapDialog = Class.extend({
  tml: '<div class="modal fade" id="dialog-%%id%%" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">' +
    '<div class="modal-dialog"><div class="modal-content">' +
        '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
          '<h4 class="modal-title">%%title%%</h4></div>' +
        '<div class="modal-body">%%body%%</div>' +
        '<div class="modal-footer">%%buttons%%</div>' +
      '</div></div></div>',

button: '<button type="button" class="btn btn-default" id="%%id%%">%%title%%</button>',
//singlebutton: '<button type="button" class="btn btn-primary" data-dismiss="modal" id="%%id%%">%%title%%</button>',
                    
  init: function() {
        this.id = 	$.now();
        this.stack = [];
var self = this;
    this.options = {
      title: "",
      html: "",
      width: 300,
      open: $.noop,
      close: $.noop,
      buttons: [
      {
        title: "Ok",
        click: function() {
          self.close();
        }
      }
      ]
    };
    
    $.closedialog = $.proxy(this.close, this);
    $.litedialog = $.proxy(this.open, this);
          },
          
          close: function() {
          if (!this.stack.length) return false;
          
var dialog = this.stack.pop();
dialog.modal("hide");
          },
          
          remove: function(dialog) {
          var stack = this.stack;
          for (var i = stack.length -1; i >= 0; i--) {
          if (dialog == stack[i]) {
          stack.splice(i, 1);
          dialog.remove();
          return;
          }
          }
          },
  
  open: function(o) {
  var id = this.id++;
    var options = $.extend({}, this.options, o);
    
    var buttons = '';
    var idbutton = "button-" + id + "-";
    for (var i =0, l= options.buttons.length;  i < l; i++) {
      buttons += this.button.replace(/%%id%%/g, idbutton + i).replace(/%%title%%/g, options.buttons[i].title);
    }
    
    //single button change class to "btn-primary"
    if (l == 1) buttons = buttons.replace(/%%btn-default%%/g, "btn-primary");
    
    var html = $.simpletml(this.tml, {
    id: id,
title: options.title,
    body: options.html,
buttons: buttons
});    

        var dialog = $(html).appendTo("body");
        //assign events to buttons
            for (var i =0, l= options.buttons.length;  i < l; i++) {
            $("#" + idbutton +i, dialog).data("index", i).on("click.dialog", options.buttons[i].click);
            }
            
        this.stack.push(dialog);
        dialog.modal().on("shown.bs.modal", function() {
                if ($.isFunction(options.open)) options.open($(".pp_inline"));
                });

var self = this;
      dialog.on("hidden.bs.modal", function() {
        if ($.isFunction(options.close)) options.close();
        self.remove($(this));
        });

    return options;
  }
  
  });
  
    $(document).ready(function() {
        window.setTimeout(function() {
  if ("modal" in $.fn) $.bootstrapDialog = new $.BootstrapDialog();
      }, 40);
  });

})( jQuery, window, document );