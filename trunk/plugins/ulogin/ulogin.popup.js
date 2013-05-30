(function ($, window) {
  $(document).ready(function() {
    litepubl.uloginpopup = new litepubl.Uloginpopup();
  });
  
  litepubl.Uloginpopup= Class.extend({
script: false,
dialogopened: false,
    title: "Авторизация на сайте",
    html: '<div class="regservices">\
    <p>Если у вас нет аккаунта в этих соцсетях, то авторизуйтесь через <a href="%%SITEURL%%/admin/reguser/?backurl=">e-mail</a></p>',
    
    init: function() {
if ($.cookie('litepubl_user')) return;
      this.html = this.html.replace(/%%siteurl%%/gim, ltoptions.url);
      var self = this;
      $('a[href^="' + ltoptions.url + '/admin/"], a[href^="/admin/"]').click(function() {
var url = $(this).attr("href"));
self.load(function() {
        self.popup(url);
});
        return false;
      });
    },
    
    popup: function(url) {
if (this.dialogopened) return false;
this.dialogopened = true;
      $.prettyPhotoDialog({
        title: lang.ulogin.title,
        html: this.html.replace(/BACKURL=/gim, 'BACKURL=' + encodeURIComponent(url)),
        width: 300,
        buttons: [{
          title: lang.dialog.close,
          click: function() {
self.dialogopened = false;
$.prettyPhoto.close();
})
        }]
      });
    },

ready: function(callback) {
if (this.script) return this.script.done(callback);
return this.script = $.load_script('http://ulogin.ru/js/ulogin.js', callback);
}
    
  });
  
}(jQuery, geo, window));