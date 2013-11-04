/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window) {
  litepubl.Calendar = Class.extend({
    buttonclass: ".calendar-button",
    ui_datepicker: false,
    dialogopened: false,
    
    init: function() {
      this.on(this.buttonclass);
    },
    
    on: function(buttons) {
      var self = this;
      $(buttons).off("click.calendar").on("click.calendar", function() {
        var edit = $(this).parent().find('input:first');
        self.open(edit);
        return false;
      });
    },
    
    load: function(callback) {
      if (this.ui_datepicker) return this.ui_datepicker.done(callback);
      
      var self = this;
      this.ui_datepicker= $.load_script(ltoptions.files + '/js/jquery/ui-' + $.ui.version + '/jquery.ui.datepicker.min.js', function() {
        if (ltoptions.lang == 'en') return callback();
        self.ui_datepicker= $.load_script(ltoptions.files + '/js/jquery/ui-' + $.ui.version + '/jquery.ui.datepicker-' + ltoptions.lang + '.min.js', callback);
      });
    },
    
    open: function (edit) {
      if (this.dialogopened) return;
      this.dialogopened = true;
      var self = this;
      this.load(function() {
        var cur = edit.val();
        $.litedialog({
          title: lang.admin.calendar,
          html: '<div  style="width:290px;height:200px;display:block;overflow:hidden;"><div id="popup-calendar"></div></div>',
          width: 300,
          close: function() {
            self.dialogopened = false;
          },
          
          open: function() {
            $("#popup-calendar").datepicker({
              altField: edit,
              altFormat: "dd.mm.yy",
              dateFormat: "dd.mm.yy",
              defaultDate: cur,
              changeYear: true
            });
          },
          
          buttons: [{
            title: lang.dialog.close,
            click: $.closedialog
          }]
        });
      });
      
    }
    
  });//class
  
  $(document).ready(function() {
    try {
      litepubl.calendar = new litepubl.Calendar();
  } catch(e) {erralert(e);}
  });
}(jQuery, document, window));