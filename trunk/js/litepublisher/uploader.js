(function ($, litepubl, window) {
  litepubl.Uploader = Class.extend({
handler: false,
postdata: false,
url: "",
    progressbar: "#progressbar",
    maxsize: "100",
    types: "*.*",

    //events
    onbefore: $.noop,
    onupload: $.noop,
    oncomplete: $.noop,
    
    init: function() {
      this.items = new Array();
      this.url = ltoptions.uploadurl == undefined ? ltoptions.url: ltoptions.uploadurl;
      var cookie = $.cookie("litepubl_user");
      if (!cookie) cookie = $.cookie("admin");

        this.postdata = {
          litepubl_user: cookie,
          litepubl_user_id: $.cookie("litepubl_user_id"),
          method: "files_upload"
        };

this.init_handler();
if ("FileReader" in window) {
this.handler =  new litepubl.HTMLUploader(this);
} else {
this.handler = new litepubl.FlashUploader(this);
}
},

setpercent: function(percent) {
        $(this.progressbar).progressbar({value: percent});
},

setprogress: function(current, total) {
this.setprogress(Math.ceil((current / total) * 100));
},

hideprogress: function() {
              $(this.progressbar).progressbar( "destroy" );
},

error: function(mesg) {
          $.messagebox(lang.dialog.error, mesg);
},


uploaded: function(filename, resp) {
            this.items.push(resp);
$(this).trigger({
type: "upload",
filename: filename,
resp: resp
});
},
    
//events
    onbefore: function(fn) {
$(this).bind("onbefore", fn);
},

    onupload: function(fn) {
$(this).bind("onupload", fn);
},

    oncomplete: function(fn) {
$(this).bind("oncomplete", fn);
},

    addparam: function(name, value) {
this.postdata[name] = value;
},

addperm: function() {
      var perm = $("#combo-idperm_upload");
      if (perm.length) this.addparam("idperm", perm.val());
},

    before: function() {
this.addperm();
      $(this).trigger({
type: "onbefore",
uploader: this
});
    },
    
    complete: function() {
      $(this).trigger({
type: "oncomplete",
uploader: this,
files: this.items
});
      this.items.length = 0;
    }
    
  });
}(jQuery, litepubl, window));