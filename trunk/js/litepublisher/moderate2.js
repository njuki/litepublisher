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
editor: "#comment"
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
$.messagebox(lang.comments.error, mesg);
},

  setstatus: function (id, status) {
var options = moderate.options;
    var idcomment = options.comment + id;
    switch (status) {
      case "delete":
$.confirmbox(lang.comments.confirm, lang.comments.confirmdelete, lang.comments.yesdelete, lang.comments.nodelete, function(index) {
if (index !=0) return;
    $.litejson({method: "comment_delete", id: id}, function(r){
if (r == false) return moderate.error(lang.comments.notdeleted);
        $(idcomment).remove();
moderate.setenabled(true);
      })
      .error( function(jq, textStatus, errorThrown) {
moderate.error(lang.comments.notdeleted);
        //alert(jq.responseText);
});
});
      break;
      
      case "hold":
      case "approved":
    $.litejson({method: "comment_setstatus", id: id, status: status}, function(r) {
try {
if (r == false) return moderate.error(lang.comments.notmoderated);
    $(status == "hold" ? options.hold : options.comments).append($(options.comment  + id));
moderate.setenabled(true);
} catch(e) { alert('error ' + e.message); }
      })
      .error( function(jq, textStatus, errorThrown) {
moderate.error(lang.comments.notmoderated);
        alert(jq.responseText);
});
      break;
      
      case "edit":
    $.litejson({method: "comment_getraw", id: id}, function(resp){
          var area = $(moderate.options.editor);
          area.data("idcomment", id);
          area.data("savedtext", area.val());
          area.val(resp.rawcontent);
area.focus();
          $("#commentform").one("submit", function() {
          var area = $(moderate.options.editor);
var content = $.trim(area.val());
if (content == "") return moderate.error(lang.comment.emptycontent);
          $.litejson({method: "comment_edit", id:area.data("idcomment"), content: content},
            function(r){
              area.val(area.data("savedtext"));
var cc = moderate.options.content + r.id;
              $(cc).html(r.content);
location.hash = cc.substring(1);
//} catch (e) { alert(e.message); }
            })
      .error( function(jq, textStatus, errorThrown) {
moderate.error(lang.comments.notedited);
});
        //} catch (e) { alert(e.message); }
            return false;
          });
      })
      .error( function(jq, textStatus, errorThrown) {
moderate.error(lang.comments.errorrecieved);
});
      break;
      
      default:
      alert("Unknown status " + status);
    }
  },

get_hold: function() {
//$().remove;
$.litejson({method: "comments_get_hold", idpost: ltoptions.idpost}, function(r) {
var options = moderate.options;
if (options.ismoder) {

$(options.hold).remove();
}
$(options.comments).after(r.items);
moderate.create_buttons(options.hold);
})
      .error( function(jq, textStatus, errorThrown) {
moderate.error(lang.comments.errorrecieved);
});
},

create_buttons: function(where) {
var options = moderate.options;
var approve = options.button.replace('%%title%%', lang.comments.approve);
var hold = options.button.replace('%%title%%', lang.comments.hold);
var del = options.button.replace('%%title%%', lang.comments.del);
var edit = options.button.replace('%%title%%', lang.comments.edit);

var moderclick = moderate.click;
var iduser = get_cookie("litepubl_user_id");

    $(options.buttons, where).each(function() {
var self = $(this);
var id = self.data("idcomment");
if (options.ismoder) {
$(approve).appendTo(self).data("idcomment", id).data("moder", "approve").click(moderclick);
$(hold).appendTo(self).data("idcomment", id).data("moder", "hold").click(moderclick);
$(del).appendTo(self).data("idcomment", id).data("moder", "delete").click(moderclick);
$(edit).appendTo(self).data("idcomment", id).data("moder", "edit").click(moderclick);
} else {
var idauthor = self.data("idauthor");
if (idauthor == iduser) {
if (options.canedit) $(edit).appendTo(self).data("idcomment", id).data("moder", "edit").click(moderclick);
if (options.candelete) $(del).appendTo(self).data("idcomment", id).data("moder", "delete").click(moderclick);
}
}
});
}
};

moderate.options = $.extend(moderate.options, ltoptions.theme.comments, options);
moderate.create_buttons(moderate.options.comments +", " + moderate.options.hold);
return this;  
};

  $(document).ready(function() {
$.load_script(ltoptions.files + "/js/plugins/tojson.min.js", function() {
//alert($.toJSON (lang));
    $.moderate();
});
  });

})( jQuery );