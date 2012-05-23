/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( $ ){
  $.moderate = function(options) {
    var moderate = {
      enabled : true,
      options: {
        comments: "#commentlist",
        hold: "#holdcommentlist",
        comment: "#comment-",
        content: "#commentcontent-",
        buttons:".moderationbuttons",
        button: '<button type="button">%%title%%</button>',
        form: "#commentform",
        editor: "#comment"
      },
      
      setenabled: function(value) {
        if (value== this.enabled) return;
        this.enabled = value;
        if(value) {
          $(moderate.options.buttons).show();
        } else {
          $(moderate.options.buttons).hide();
        }
      },
      
      click: function() {
        if (!moderate.enabled) return false;
        try {
          var self = $(this);
          var action = self.data("moder");
          var id = self.data("idcomment");
          moderate.setstatus(id, action);
      } catch(e) { alert('error ' + e.message); }
        return false;
      },
      
      error: function(mesg) {
        moderate.setenabled(true);
        $.messagebox(lang.dialog.error, mesg);
      },
      
      setstatus: function (id, status) {
        var options = moderate.options;
        var idcomment = options.comment + id;
        switch (status) {
          case "delete":
          moderate.setenabled(false);
          $.confirmbox(lang.dialog.confirm, lang.comments.confirmdelete, lang.comments.yesdelete, lang.comments.nodelete, function(index) {
            if (index !=0) return moderate.setenabled(true);
          $.litejson({method: "comment_delete", id: id}, function(r){
              if (r == false) return moderate.error(lang.comments.notdeleted);
              $(idcomment).remove();
              moderate.setenabled(true);
            })
            .fail( function(jq, textStatus, errorThrown) {
              moderate.error(lang.comments.notdeleted);
              //alert(jq.responseText);
            });
          });
          break;
          
          case "hold":
          case "approved":
          moderate.setenabled(false);
        $.litejson({method: "comment_setstatus", id: id, status: status}, function(r) {
            try {
              if (r == false) return moderate.error(lang.comments.notmoderated);
              $(status == "hold" ? options.hold : options.comments).append($(options.comment  + id));
              moderate.setenabled(true);
          } catch(e) { alert('error ' + e.message); }
          })
          .fail( function(jq, textStatus, errorThrown) {
            moderate.error(lang.comments.notmoderated);
            //alert(jq.responseText);
          });
          break;
          
          case "edit":
          moderate.setenabled(false);
        $.litejson({method: "comment_getraw", id: id}, function(resp){
            var area = $(moderate.options.editor);
            area.data("idcomment", id);
            area.data("savedtext", area.val());
            area.val(resp.rawcontent);
            area.focus();
            
            $.onEscape(moderate.restore_submit);
            var form = $(options.form);
            form.off("submit.confirmcomment").on("submit.moderate", function() {
              try {
                var area = $(options.editor);
                var content = $.trim(area.val());
                if (content == "") {
                  moderate.enabled = true;
                  moderate.error(lang.comment.emptycontent);
                  moderate.enabled = false;
                  return false;
                }
                $(":input", form).attr("disabled", "disabled");
                
              $.litejsontype("post", {method: "comment_edit", id: area.data("idcomment"), content: content}, function(r){
                  try {
                    $(":input", form).removeAttr("disabled");
                    var cc = options.content + r.id;
                    $(cc).html(r.content);
                    moderate.restore_submit();
                    location.hash = cc.substring(1);
                } catch (e) { alert(e.message); }
                })
                .fail( function(jq, textStatus, errorThrown) {
                  $(":input", form).removeAttr("disabled");
                  moderate.error(lang.comments.notedited);
                  moderate.restore_submit();
                });
                
            } catch (e) { alert(e.message); }
              return false;
            });
          })
          .fail( function(jq, textStatus, errorThrown) {
            moderate.error(lang.comments.errorrecieved);
          });
          break;
          
          default:
          alert("Unknown status " + status);
        }
      },
      
      restore_submit: function() {
        var area = $(moderate.options.editor);
        area.val(area.data("savedtext"));
        $(moderate.options.form).off("submit.moderate");
        moderate.setenabled(true);
        if ("confirmcomment" in $) {
          $.confirmcomment();
        }
      },
      
      loadhold: function() {
        $(".loadhold").parent().remove();
      $.litejson({method: "comments_get_hold", idpost: ltoptions.idpost}, function(r) {
          try {
            var options = moderate.options;
            if (options.ismoder) {
              var approved = $(options.comments);
              var hold = $(options.hold);
              while (approved.next()[0] != hold[0]) approved.next().remove();
              hold.remove();
            }
            $(options.comments).after(r.items);
            moderate.create_buttons(options.hold);
        } catch(e) { alert('error ' + e.message); }
        })
        .fail( function(jq, textStatus, errorThrown) {
          moderate.error(lang.comments.errorrecieved);
        });
        return false;
      },
      
      create_buttons: function(where) {
        var options = moderate.options;
        var approved = options.button.replace('%%title%%', lang.comments.approve);
        var hold = options.button.replace('%%title%%', lang.comments.hold);
        var del = options.button.replace('%%title%%', lang.comments.del);
        var edit = options.button.replace('%%title%%', lang.comments.edit);
        var show = '<button type="button">E</button>';
        
        var moderclick = moderate.click;
        var iduser = get_cookie("litepubl_user_id");
        
        $(options.buttons, where).each(function() {
          var self = $(this);
          var id = self.data("idcomment");
          if (options.ismoder) {
            $(approved).appendTo(self).data("idcomment", id).data("moder", "approved").click(moderclick);
            $(hold).appendTo(self).data("idcomment", id).data("moder", "hold").click(moderclick);
            $(del).appendTo(self).data("idcomment", id).data("moder", "delete").click(moderclick);
            $(edit).appendTo(self).data("idcomment", id).data("moder", "edit").click(moderclick);
            if (self.is(":hidden")) {
              $(show).insertBefore(self).one("click",  function() {
                $(this).next().show();
                $(this).remove();
                return false;
              });
            }
          } else {
            var idauthor = self.data("idauthor");
            if (idauthor == iduser) {
              if (options.canedit) $(edit).appendTo(self).data("idcomment", id).data("moder", "edit").click(moderclick);
              if (options.candelete) $(del).appendTo(self).data("idcomment", id).data("moder", "delete").click(moderclick);
              if ((options.canedit ||options.candelete) && self.is(":hidden")) {
                $(show).insertBefore(self).one("click",  function() {
                  $(this).next().show();
                  $(this).remove();
                  return false;
                });
              }
            }
          }
        });
      }
    };
    
    moderate.options = $.extend(moderate.options, ltoptions.theme.comments, options);
    moderate.create_buttons(moderate.options.comments +", " + moderate.options.hold);
    $(".loadhold").click(moderate.loadhold);
    return this;
  };
  
  $(document).ready(function() {
    //only logged users
    if (get_cookie("litepubl_user_id")) $.moderate();
  });
  
})( jQuery );