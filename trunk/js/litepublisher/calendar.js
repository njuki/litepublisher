/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window) {
  litepubl.Calendar = Class.extend({
    holderclass: ".calendar",
    ui_datepicker: false,
    dialogopened: false,
    
    init: function() {
      this.on(this.holderclass);
    },
    
    on: function(holders) {
var self = this;
holders.each(function() {
var inputs = $("input", this);
        var date = inputs.eq(0).addClass("date-edit");
        var time = inputs.eq(1).addClass("time-edit");
      $("button:first", this).data("date", date).off("click.calendar").on("click.calendar", function() {
        self.open($(this).data("date"));
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

datepicker: function(holder, edit) {
            $(holder).datepicker({
              altField: edit,
              altFormat: "dd.mm.yy",
              dateFormat: "dd.mm.yy",
              defaultDate: edit.val(),
              changeYear: true
            });
},
    
    open: function (edit) {
      if (this.dialogopened) return;
      this.dialogopened = true;
      var self = this;
      this.load(function() {
        $.litedialog({
          title: lang.admin.calendar,
          html: '<div  style="width:290px;height:200px;display:block;overflow:hidden;"><div id="popup-calendar"></div></div>',
          width: 300,
          close: function() {
            self.dialogopened = false;
          },
          
          open: function() {
            self.datepicker("#popup-calendar", edit);
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