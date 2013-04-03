/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, litepubl, window) {
  litepubl.Uploader = Class.extend({
    handler: false,
    postdata: false,
    random: 0,
    url: "",
    maxsize: 100,
    mime: false, // regexp for html as 'image/*' to only accept images
    types: "*.*", // for flash uploader
    holder: false,
    progressbar: false,
    htmlprogress: '<div id="progressbar"></div>',
    idprogress: "#progressbar",
    
    init: function(options) {
      options = $.extend({
        url: (ltoptions.uploadurl == undefined ? ltoptions.url: ltoptions.uploadurl) + '/admin/jsonserver.php',
        holder: "#uploader",
        maxsize: 100,
        mime: false,
        types: "*.*"
      }, options);
      
      $.extend(this, options);
      this.holder = $(options.holder);
      this.random = 	$.now();
      
      this.items = new Array();
      
      var cookie = $.cookie("litepubl_user");
      if (!cookie) cookie = $.cookie("admin");
      
      this.postdata = {
        litepubl_user: cookie,
        litepubl_user_id: $.cookie("litepubl_user_id"),
        method: "files_upload"
      };
      
      if ("FileReader" in window) {
        this.handler =  new litepubl.HTMLUploader(this);
      } else {
        this.handler = new litepubl.FlashUploader (this);
      }
      
      this.progressbar = this.holder.append(this.htmlprogress).find(this.idprogress);
    },
    
    geturl: function() {
      return this.url + '?_=' + this.random++;
    },
    
    setpercent: function(percent) {
    this.progressbar.progressbar({value: percent});
    },
    
    setprogress: function(current, total) {
      this.setpercent(Math.ceil((current / total) * 100));
    },
    
    hideprogress: function() {
      this.progressbar.progressbar( "destroy" );
    },
    
    error: function(mesg) {
      $.messagebox(lang.dialog.error, mesg);
    },
    
    uploaded: function(resp) {
      try {
        if (typeof resp == "string") resp = $.parseJSON(resp);
        this.items.push(resp);
        $(this).trigger({
          type: "onupload",
          resp: resp
        });
    } catch(e) {erralert(e);}
    },
    
    addparam: function(name, value) {
      if ("addparam" in this.handler) {
        this.handler.addparam(name, value);
      } else {
        this.postdata[name] = value;
      }
    },
    
    addparams: function() {
      var perm = $("#combo-idperm_upload", this.holder.parent());
      if (perm.length) this.addparam("idperm", perm.val());
    },
    
    before: function() {
      this.addparams();
      $(this).trigger({
        type: "onbefore",
        uploader: this
      });
    },
    
    complete: function() {
      this.hideprogress();
      $(this).trigger({
        type: "oncomplete",
        uploader: this,
        items: this.items
      });
      this.items.length = 0;
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
    }
    
  });
}(jQuery, litepubl, window));