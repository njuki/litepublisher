/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window) {
  litepubl.Fileman = Class.extend({
  items: {},
    curr: [],
    indialog: false,
    
    init: function(holder) {
      try {
        this.init_templates();
var self = this;
        $(holder).html(this.tml.tabs);
        var tabs = $("#posteditor-files-tabs");
        tabs.tabs({
          cache: true,
          select: function(event, ui) {
            if ("empty" == $(ui.panel).data("files")) {
              self.loadpage(ui.panel, $(ui.panel).data("page"));
            }
          }
        });

        this.load_current_files();
/*
        ltoptions.swfu = createswfu(function(file, serverData) {
self.uploaded(file, serverData);
});
*/
litepubl.uploader = new litepubl.Uploader();
litepubl.uploader.onupload = function(file, r) {
self.uploaded(file, r);
};
        
        $('form:first').submit(function() {
          $("input[name='files']").val(self.curr.join(','));
        });
    } catch(e) { alert('error ' + e.message); }
    },
    
    init_templates: function() {
this.tml = litepubl.tml.fileman;
$.replacetml(litepubl.tml.fileman, {
        lang: lang.posteditor,
        iconurl:  ltoptions.files + "/js/litepublisher/icons/"
      });
    },
    
    load_current_files: function() {
var self = this;
    $.litejson({method: "files_getpost", idpost: ltoptions.idpost}, function (r) {
        try {
          self.set_uploaded(r);
      } catch(e) { alert('error ' + e.message); }
      })
      .fail( function(jq, textStatus, errorThrown) {
        $.messagebox(lang.dialog.error, jq.responseText);
        //alert(jq.responseText);
      });
    },
    
    set_uploaded: function(r) {
      if ("fileperm" in r) {
        $("#posteditor-fileperms").html(r.fileperm);
      }
      
      this.set_tabs_count(r.count);
      for (var id in r.files) {
        this.curr.push(id);
        this.items[id] = r.files[id];
      }
      
      this.setpage("#current-files", r.files);
      //to assign events
    this.setpage("#new-files", {});
    },
    
    set_tabs_count: function(count) {
      if (count < 1) return;
      var tabs = $("#posteditor-files-tabs");
      for (var i =1; i <= count; i++) {
    $(this.tml.tab.replace('{{index}}', i)).appendTo(tabs).data("page", i).data("files", "empty");
        tabs.tabs( "add" , "#filepage-" + i, i);
      }
    },
    
    setpage: function(uipanel, files) {
      var panel = $(".file-items", uipanel);
      for (var id in files) {
        if (parseInt(files[id]['parent']) != 0) continue;
        panel.append(this.get_fileitem(id));
      }
      
      this.setborders(panel);

var self = this;      
      panel.on("click.toolbar", ".file-toolbar a", function() {
        var holder = $(this).closest(".file-item");
        var idfile = holder.data("idfile");
        
        switch($(this).attr("class")) {
          case "add-toolbutton":
          self.add(idfile);
          break;
          
          case "delete-toolbutton":
          self.del(idfile, holder);
          break;
          
          case "property-toolbutton":
          self.editprops(idfile, holder);
          break;
        }
        
        return false;
      });
      
      panel.on("click.image", "a.file-image", function() {
        var link = $(this);
        $.prettyPhoto.open(link.attr("href"), link.attr("title"), $("img", link).attr("alt"));
        return false;
      });
      
    },
    
    get_fileitem: function(id) {
      var item =this.items[id];
      item.link = ltoptions.files + "/files/" + item.filename;
      type = (item["media"] in this.tml) ? item["media"] : "file";
      item.previewlink = '';
      if ((parseInt(item["preview"]) != 0) &&(item.preview in this.items)) item.previewlink = ltoptions.files + "/files/" + this.items[item["preview"]]["filename"];
      var html = $.simpletml(this.tml.item, {
        id: item["id"],
        content: $.simpletml(this.tml[type], item)
      });
      
      return $(html).data("idfile", id);
    },
    
    loadpage: function(uipanel, page) {
var self = this;
      $(uipanel).data("files", "loading");
    $.litejson({method: "files_getpage", page: page - 1}, function(r) {
        self.joinitems(r.files);
        self.setpage(uipanel, r.files);
      })
      .fail( function(jq, textStatus, errorThrown) {
        $.messagebox(lang.dialog.error, jq.responseText);
      });
    },
    
    joinitems: function(files) {
      for (var id in files) {
        this.items[id] = files[id];
      }
    },
    
    uploaded: function(file, r) {
      try {
        //var r = $.parseJSON(serverData);
        /*
 r = {
          id: int idfile,
          item: array fileitem,
          preview: array fileitem optimal
        }
*/
        
        var idfile = r.id;
        this.curr.push(idfile);
        this.items[idfile] = r.item;
        if (r.item["preview"] != 0) this.items[r.preview['id']] = r.preview;
        
        $("#current-files .file-items").append(this.get_fileitem(idfile));
        $("#new-files .file-items").append(this.get_fileitem(idfile));
    } catch(e) { alert('error ' + e.message); }
    },
    
    setborders: function(uipanel) {
      var all = $(".file-item", uipanel);
      if (all.length == 0) return;
      all.removeClass("border-left");
      var firstpos = $(".file-item:first", uipanel).position();
      all.each(function() {
        var self = $(this);
        var pos = self.position();
        if (pos.left == firstpos.left) self.addClass("border-left");
      });
    },
    
    add: function(idfile) {
      if ($.inArray(idfile, this.curr) >= 0) return;
      this.curr.push(idfile);
      
      this.setborders($("#current-files .file-items").append(this.get_fileitem(idfile)));
    },
    
    del: function(idfile, holder) {
      var i = $.inArray(idfile, this.curr);
      if (i < 0) return;
      delete this.curr[i];
      var parent = holder.parent();
      holder.remove();
      this.setborders(parent);
    },
    
    editprops: function(idfile, owner) {
      if (this.indialog) return false;
      this.indialog = true;
      var fileitem = this.items[idfile];
var self = this;
      
      $.prettyPhotoDialog({
        title: lang.posteditor.property,
        html: this.tml.fileprops,
        open: function(holder) {
          $("input[name='fileprop-title']", holder).val(fileitem.title);
          $("input[name='fileprop-description']", holder).val(fileitem.description);
          $("input[name='fileprop-keywords']", holder).val(fileitem.keywords);
        },
        
        buttons: [
        {
          title: "Ok",
          click: function() {
            var holder = $(".pp_inline");
            var title = $.trim($("input[name='fileprop-title']", holder).val());
            var description = $.trim($("input[name='fileprop-description']", holder).val());
            var keywords = $.trim($("input[name='fileprop-keywords']", holder).val());
            $.prettyPhoto.close();
            self.setprops(idfile, title, description, keywords, owner);
          }
        },
        {
          title: lang.dialog.cancel,
          click: function() {
            $.prettyPhoto.close();
            self.indialog = false;
          }
        }
        ]
      } );
    },
    
    setprops: function(idfile, title, description, keywords, holder) {
var self = this;
    $.litejsonpost({method: "files_setprops", idfile: idfile, title: title, description: description, keywords: keywords}, function(r) {
        self.items[r.item["id"]] = r.item;
        //need to update infos but we cant find all files
        holder.replaceWith(self.get_fileitem(idfile));
        self.indialog = false;
      })
      .fail( function(jq, textStatus, errorThrown) {
        self.indialog = false;
        $.messagebox(lang.dialog.error, jq.responseText);
      });
    }
    
  });//fileman
}(jQuery, document, window));