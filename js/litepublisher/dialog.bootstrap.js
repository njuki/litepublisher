/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( $, window, document){
  'use strict';
  
  $.BootstrapDialog = Class.extend({
    tml: '<div class="modal fade" id="dialog-%%id%%" tabindex="-1" role="dialog" aria-hidden="true">' +
    '<div class="modal-dialog center-block%%sizeclass%%"><div class="modal-content">' +
    '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
    '<h4 class="modal-title">%%title%%</h4></div>' +
    '<div class="modal-body">%%body%%</div>' +
    '<div class="modal-footer">%%buttons%%</div>' +
    '</div></div></div>',
    
    button: '<button type="button" class="btn btn-default" id="button-%%id%%-%%index%%" data-index="%%index%%">%%title%%</button>',
    dialog: false,
    styles: false,
    tmlstyle:'<style type="text/css">' +
    '.%%classname%%{' +
      '%%prop%%:%%value%%px;' +
    '}</style>',
    
    init: function() {
      this.styles = [];
      var self = this;
      this.options = {
        title: "",
        html: "",
        width: false,
        height: false,
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
    
    close: function(callback) {
      if (this.dialog) {
        if ($.isFunction(callback)) {
          this.dialog.on("hidden.bs.modal", function() {
            setTimeout(function() {
              callback();
            }, 20);
          });
        }
        
        this.dialog.modal("hide");
        
        this.dialog = false;
      }
    },
    
    open: function(o) {
      if (this.dialog) return alert('Dialog already opened');
      var id = litepubl.guid++;
    var options = $.extend({}, this.options, o);
      
      var buttons = '';
      for (var i =0, l= options.buttons.length;  i < l; i++) {
        buttons += $.simpletml(this.button, {
          index: i,
          id: id,
          title:  options.buttons[i].title
        });
      }
      
      //single button change class to "btn-primary"
      if (l == 1) buttons = buttons.replace(/%%btn-default%%/g, "btn-primary");
      
      var sizeclass = '';
    for (var prop in {width: 0, height: 0}) {
        if (options[prop]) {
          var classname = 'dialog-' + prop + '-' + options[prop];
          sizeclass = sizeclass + ' ' + classname;
          if ($.inArray(classname, this.styles) == -1) {
            this.styles.push(classname);
            $('head:first').append($.simpletml(this.tmlstyle, {
              classname: classname,
              prop: prop,
              //add 50 px to width for padding and margin by default
              value: options[prop] + (prop == "width" ? 50 : 0)
            }));
          }
        }
      }
      
      var html = $.simpletml(this.tml, {
        id: id,
        title: options.title,
        sizeclass: sizeclass,
        body: options.html,
        buttons: buttons
      });
      
      var dialog = this.dialog = $(html).appendTo("body");
      //assign events to buttons
      for (var i =0, l= options.buttons.length;  i < l; i++) {
        this.getbutton(i).on("click.dialog", options.buttons[i].click);
      }
      
      dialog.on("shown.bs.modal", function() {
        if ($.isFunction(options.open)) options.open(dialog);
        if ("tooltip" in $.fn) {
          $(".tooltip-toggle", dialog).tooltip({
            container: 'body',
            placement: 'auto top'
          });
        }
      });
      
      dialog.modal();
      var self = this;
      dialog.on("hidden.bs.modal", function() {
        if ($.isFunction(options.close)) options.close(dialog);
        self.dialog = false;
        dialog.remove();
      });
      
      return dialog;
    },
    
    getbutton: function(index) {
      if (!this.dialog) return false;
      var footer = $(".modal-footer:first", this.dialog);
      return $("button[data-index=" + index + "]", footer);
    }
    
  });
  
  ready2(function() {
    if ("modal" in $.fn) $.bootstrapDialog = new $.BootstrapDialog();
  });
  
})( jQuery, window, document );