/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window) {
  'use strict';
  
  litepubl.Moderate = Class.extend({
    enabled : true,
    
    init: function(opt) {
      this.options= $.extend({
        comments: "#commentlist",
        hold: "#holdcommentlist",
        comment: "#comment-",
        content: "#commentcontent-",
        buttons:".moderationbuttons",
        button: '<button type="button" class="button"><span>%%title%%</span></button>',
        form: "#commentform"
      }, ltoptions.theme.comments, opt);
      
      this.onbuttons = $.Callbacks();
      $(".loadhold").click(this.loadhold);
      
      var self = this;
      window.setTimeout(function() {
        self.create_buttons(self.options.comments +", " + self.options.hold);
      }, 20);
    },
    
    setenabled: function(value) {
      if (value== this.enabled) return;
      this.enabled = value;
      if(value) {
        $(this.options.buttons).show();
      } else {
        $(this.options.buttons).hide();
      }
    },
    
    getarea: function() {
      return $("textarea:first", this.options.form);
    },
    
    error: function(mesg) {
      this.setenabled(true);
      $.messagebox(lang.dialog.error, mesg);
    },
    
    setstatus: function (id, status) {
      var self = this;
      var options = this.options;
      var idcomment = options.comment + id;
      switch (status) {
        case "delete":
        case "del":
        this.setenabled(false);
        $.confirmbox(lang.dialog.confirm, lang.comments.confirmdelete, lang.comments.yesdelete, lang.comments.nodelete, function(index) {
          if (index !=0) return self.setenabled(true);
          $.jsonrpc({
            type: 'get',
            method: "comment_delete",
          params: {id: id},
            callback: function(r){
              if (r == false) return self.error(lang.comments.notdeleted);
              $(idcomment).remove();
              self.setenabled(true);
            },
            
            error: function(message, code) {
              self.error(lang.comments.notdeleted);
            }
          });
        });
        break;
        
        case "hold":
        case "approved":
        case "approve":
        this.setenabled(false);
        $.jsonrpc({
          type: 'get',
          method: "comment_setstatus",
        params:  {id: id, status: status == 'hold' ? 'hold' : 'approved'},
          callback:  function(r) {
            try {
              if (r == false) return self.error(lang.comments.notmoderated);
              $(status == "hold" ? options.hold : options.comments).append($(options.comment  + id));
              self.setenabled(true);
          } catch(e) {erralert(e);}
          },
          
          error: function(message, code) {
            self.error(lang.comments.notmoderated);
          }
        });
        break;
        
        case "edit":
        this.setenabled(false);
        $.jsonrpc({
          type: 'get',
          method: "comment_getraw",
        params: {id: id},
          callback: function(resp){
            try {
              self.edit(id, resp.rawcontent);
          } catch(e) {erralert(e);}
          },
          
          error: function(message, code) {
            self.error(lang.comments.errorrecieved);
          }
        });
        break;
        
        default:
        alert("Unknown status " + status);
      }
      
    },
    
    edit: function(id, rawcontent) {
      var area = this.getarea();
      area.data("idcomment", id)
      .data("savedtext", area.val())
      .val(rawcontent)
      .focus();
      
      $.onEscape(this.restore_submit);
      
      var self = this;
      var form = $(this.options.form);
      form.off("submit.confirmcomment").on("submit.moderate", function() {
        try {
          var content = $.trim(area.val());
          if (content == "") {
            self.enabled = true;
            self.error(lang.comment.emptycontent);
            self.enabled = false;
            return false;
          }
          
          $(":input", form).attr("disabled", "disabled");
          $.jsonrpc({
            method: "comment_edit",
          params: {id: area.data("idcomment"), content: content},
            callback: function(r){
              try {
                $(":input", form).removeAttr("disabled");
                var cc = self.options.content + r.id;
                $(cc).html(r.content);
                self.restore_submit();
                location.hash = cc.substring(1);
            } catch(e) {erralert(e);}
            },
            
            error: function(message, code) {
              $(":input", form).removeAttr("disabled");
              self.error(lang.comments.notedited);
              self.restore_submit();
            }
          });
          
      } catch(e) {erralert(e);}
        return false;
      });
    },
    
    restore_submit: function() {
      var area = this.getarea();
      area.val(area.data("savedtext"));
      this.setenabled(true);
      $(this.options.form).off("submit.moderate").on("submit.confirmcomment", function() {
        if ("confirmcomment" in litepubl) return litepubl.confirmcomment.submit();
      });
    },
    
    loadhold: function() {
      var self = this;
      var options = this.options;
      $(".loadhold").parent().remove();
      $.jsonrpc({
        type: 'get',
        method: "comments_get_hold",
      params: {idpost: ltoptions.idpost},
        callback: function(r) {
          try {
            if (options.ismoder) {
              var approved = $(options.comments);
              var hold = $(options.hold);
              while (approved.next()[0] != hold[0]) approved.next().remove();
              hold.remove();
            }
            $(options.comments).after(r.items);
            self.create_buttons(options.hold);
        } catch(e) {erralert(e);}
        },
        
        error:  function(message, code) {
          self.error(lang.comments.errorrecieved);
        }
      });
      return false;
    },
    
    create_buttons: function(where) {
      var options = this.options;
      var buttons = {
        approve: '',
        hold: '',
        del: '',
        edit: ''
      };
      
      for (var name in buttons) {
        buttons[name] = $.simpletml(options.button, {
          title: lang.comments[name],
          name: name
        });
      }
      
      var showbutton  = $.simpletml(options.button, {
        title: 'E',
        name: 'show'
      });
      
      var self = this;
      var click = function() {
        if (!self.enabled) return false;
        var button = $(this);
        self.setstatus(button.data("idcomment"), button.data("moder"));
        return false;
      };
      
      var iduser = litepubl.getuser().id;
      $(options.buttons, where).each(function() {
        var container = $(this);
        var id = container.data("idcomment");
        if (options.ismoder) {
          for (var name in buttons) {
            $(buttons[name]).appendTo(container).data("idcomment", id).data("moder", name).click(click);
          }
          
          if (container.is(":hidden")) self.addswitcher(container, showbutton);
        } else {
          var idauthor = parseInt(container.data("idauthor"));
          if (idauthor == iduser) {
            if (options.canedit) $(buttons.edit).appendTo(container).data("idcomment", id).data("moder", "edit").click(click);
            if (options.candelete) $(buttons.del).appendTo(container).data("idcomment", id).data("moder", "delete").click(click);
            if ((options.canedit ||options.candelete) && container.is(":hidden")) self.addswitcher(container, showbutton);
          }
        }
        
        self.onbuttons.fire(container);
      });
    },
    
    addswitcher: function(container, button) {
      $(button).insertBefore(container).one("click mouseenter",  function() {
        $(this).next().show();
        $(this).remove();
        return false;
      });
    }
    
  });//class
  
  $(document).ready(function() {
    //only logged users
    if (litepubl.getuser().id) classes.create({moderate: litepubl.Moderate});
  });
  
}(jQuery, document, window));