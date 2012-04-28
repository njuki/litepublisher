(function( $ ){
  $.fn.moderatebuttons= function() {
    return this.on("click", function() {
      var self = $(this);
      var action = self.data("moderate");
      var id = self.parent().data("idcomment");
      $.moderate_comment(id, action);
      return false;
    });
  };
  
  $.move_comment = function(id, status) {
    var item =$("#comment-" + id);
    var parent = status == "hold" ? $(ltoptions.theme.holdcommentlist) : $(ltoptions.theme.commentlist);
    parent.append(item);
  };
  
  $.moderate_comment = function (id, status) {
    var idcomment = "#comment-" + id;
    switch (status) {
      case "delete":
      if (!confirm(lang.comments.confirmdelete)) return;
    $.litejson({method: "comment_delete", id: id}, lang.comments.notdeleted,
      function(r){
        $(idcomment).remove();
      });
      break;
      
      case "hold":
      case "approved":
    $.litejson({method: "comment_setstatus", id: id, status: status}, lang.comments.notmoderated,
      function(r) {
try {
if (r == false) {
alert('ho');
} else {
        $.move_comment(id, status);
}
} catch(e) { alert('error ' + e.message); }
      })
      .error( function(jq, textStatus, errorThrown) {
        alert(jq.responseText);
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
  
  $(document).ready(function() {
    $(".moderationbuttons input:button").moderatebuttons();
$.load_script(ltoptions.files + "/js/plugins/tojson.min.js", function() {
alert('json');
});
  });
})( jQuery );