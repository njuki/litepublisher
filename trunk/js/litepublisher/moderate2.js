(function( $ ){
  $.moderate = function(options) {
if ("options" in this) $(this.options.approved +", " + this.options.hold).off("click.moderate");

		this.options = $.extend({
comment: "#comment-",
approved: "#commentlist",
hold: "#holdcommentlist",
createhold: '<ol class="commentlist" id="holdcommentlist" start="1"></ol>',
buttons:".moderationbuttons input:button",
button: '<input type="button" value="%%title%%" />',
idbutton = "",
editor: "#comment"
}, ltoptions.theme.comments, options);

this.click = function() {
      var self = $(this);
      var action = self.data("moderate");
      var id = self.parent().data("idcomment");
      $.moderate.setstatus(id, action);
      return false;
  };

//init
    $(this.options.approved +", " + this.options.hold).on("click.moderate", $this.options.buttons, this.click);

this.move= function(id, status) {
var options = $.moderate.options;
    var item =$(options.comment  + id);
//create hold list if it isn't exists
if (status == "hold") {
     var parent = $(options.hold);
if (parent.length == 0) {
parent = $(options.createhold);
$(options.approved).after(parent);
}
} else {
    var parent =  $(options.approved);
}

    parent.append(item);
  };

this.error= function(mesg) {
//todo: replace to ui dialog
alert(mesg);
};
  
  this.setstatus= function (id, status) {
var options = $.moderate.options;
    var idcomment = options.comment + id;
    switch (status) {
      case "delete":
      if (!confirm(lang.comments.confirmdelete)) return;
var mesg = lang.comments.notdeleted;
    $.litejson({method: "comment_delete", id: id}, mesg,
      function(r){
if (r == false) return $.moderate.error(mesg);
        $(idcomment).remove();
      });
      break;
      
      case "hold":
      case "approved":
var mesg = lang.comments.notmoderated;
    $.litejson({method: "comment_setstatus", id: id, status: status}, mesg,
      function(r) {
try {
if (r == false) return $.moderate.error(mesg);
        $.moderate.move(id, status);
} catch(e) { alert('error ' + e.message); }
      });
      break;
      
      case "edit":
    $.litejson({method: "comment_get", id: id}, lang.comments.errorrecieved,
      function(resp){
        try {
          var area = $("#comment");
          area.data("idcomment", id);
          area.data("savedtext", area.val());
          area.val(resp.rawcontent);
          $("#commentform").one("submit", function() {
            var area = $("#comment");
var content = $.trim(area.val());
if (content == "") return alert("empty content");
          $.litejson({method: "comment_edit", id:area.data("idcomment"), content: area.val()},
            lang.comments.notedited, function(result){
              area.val(area.data("savedtext"));
              $("commentcontent-" + result.id).html(result.content);
            });
            return false;
          });
        } catch (e) {
          alert(e.message);
        }
      });
      break;
      
      default:
      alert("Unknown status " + status);
    }
  };

this.create_buttons = function() {
var options = this.options;
var approve = options.button.replace('%%title%%', lang.comments.approve);
var hold = options.button.replace('%%title%%', lang.comments.hold);
var delete = options.button.replace('%%title%%', lang.comments.delete);
var edit = options.button.replace('%%title%%', lang.comments.edit);

    $(options.buttons, options.approved +", " + options.hold).each(function() {
var self = $(this);
var id = self.data("idcomment");
if (options.ismoder) {
$(approve).appendTo(self).data("idcomment", id).data("moder", "approve").click(function() {
});
} else {
if (options.canedit)
if (options.candelete)
}
});
};
};
  
  $(document).ready(function() {
    $.moderate(ltoptions.);

$.load_script(ltoptions.files + "/js/plugins/tojson.min.js", function() {
alert('json');
});
  });

})( jQuery );