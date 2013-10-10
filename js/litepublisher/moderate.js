/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window) {
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
      
      this.create_buttons(this.options.comments +", " + this.options.hold);
      $(".loadhold").click(this.loadhold);
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
        this.setenabled(false);
        $.confirmbox(lang.dialog.confirm, lang.comments.confirmdelete, lang.comments.yesdelete, lang.comments.nodelete, function(index) {
          if (index !=0) return self.setenabled(true);
        $.litejson({method: "comment_delete", id: id}, function(r){
            if (r == false) return self.error(lang.comments.notdeleted);
            $(idcomment).remove();
            self.setenabled(true);
          })
          .fail( function(jq, textStatus, errorThrown) {
            self.error(lang.comments.notdeleted);
            //alert(jq.responseText);
          });
        });
        break;
        
        case "hold":
        case "approved":
        this.setenabled(false);
      $.litejson({method: "comment_setstatus", id: id, status: status}, function(r) {
          try {
            if (r == false) return self.error(lang.comments.notmoderated);
            $(status == "hold" ? options.hold : options.comments).append($(options.comment  + id));
            self.setenabled(true);
        } catch(e) {erralert(e);}
        })
        .fail( function(jq, textStatus, errorThrown) {
          self.error(lang.comments.notmoderated);
          //alert(jq.responseText);
        });
        break;
        
        case "edit":
        this.setenabled(false);
      $.litejson({method: "comment_getraw", id: id}, function(resp){
          try {
            self.edit(id, resp.rawcontent);
        } catch(e) {erralert(e);}
        })
        .fail( function(jq, textStatus, errorThrown) {
          self.error(lang.comments.errorrecieved);
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
        $.litejsonpost({method: "comment_edit", id: area.data("idcomment"), content: content}, function(r){
            try {
              $(":input", form).removeAttr("disabled");
              var cc = self.options.content + r.id;
              $(cc).html(r.content);
              self.restore_submit();
              location.hash = cc.substring(1);
          } catch(e) {erralert(e);}
          })
          .fail( function(jq, textStatus, errorThrown) {
            $(":input", form).removeAttr("disabled");
            self.error(lang.comments.notedited);
            self.restore_submit();
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
    $.litejson({method: "comments_get_hold", idpost: ltoptions.idpost}, function(r) {
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
      })
      .fail( function(jq, textStatus, errorThrown) {
        self.error(lang.comments.errorrecieved);
      });
      return false;
    },
    
    create_buttons: function(where) {
      var options = this.options;
      var approved = options.button.replace('%%title%%', lang.comments.approve);
      var hold = options.button.replace('%%title%%', lang.comments.hold);
      var del = options.button.replace('%%title%%', lang.comments.del);
      var edit = options.button.replace('%%title%%', lang.comments.edit);
      var show = '<button type="button">E</button>';
      var self = this;
      var click = function() {
        if (!self.enabled) return false;
        var button = $(this);
        self.setstatus(button.data("idcomment"), button.data("moder"));
        return false;
      };
      
      var iduser = get_cookie("litepubl_user_id");
      $(options.buttons, where).each(function() {
        var container = $(this);
        var id = container.data("idcomment");
        if (options.ismoder) {
          $(approved).appendTo(container).data("idcomment", id).data("moder", "approved").click(click);
          $(hold).appendTo(container).data("idcomment", id).data("moder", "hold").click(click);
          $(del).appendTo(container).data("idcomment", id).data("moder", "delete").click(click);
          $(edit).appendTo(container).data("idcomment", id).data("moder", "edit").click(click);
          if (container.is(":hidden")) {
            $(show).insertBefore(container).one("click",  function() {
              $(this).next().show();
              $(this).remove();
              return false;
            });
          }
        } else {
          var idauthor = container.data("idauthor");
          if (idauthor == iduser) {
            if (options.canedit) $(edit).appendTo(container).data("idcomment", id).data("moder", "edit").click(click);
            if (options.candelete) $(del).appendTo(container).data("idcomment", id).data("moder", "delete").click(click);
            if ((options.canedit ||options.candelete) && container.is(":hidden")) {
              $(show).insertBefore(container).one("click",  function() {
                $(this).next().show();
                $(this).remove();
                return false;
              });
            }
          }
        }
      });
    }
    
  });//class
  
  $(document).ready(function() {
    //only logged users
    if (get_cookie("litepubl_user_id")) litepubl.moderate = new litepubl.Moderate();
  });
  
}(jQuery, document, window));