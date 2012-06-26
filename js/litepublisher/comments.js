/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function getquotedcontent( authorname, content) {
  if (content == '') {
    return "2 [b]" + authorname + "[/b]: ";
  } else {
    return "[b]" + authorname + "[/b] " + lang.comment.says + ":\n[quote]" + content + "[/quote]\n";
  }
}

function quotecomment(id, authorname) {
  if (window.getSelection) {
    var sel = window.getSelection();
  } else if (document.getSelection) {
    var sel = document.getSelection();
  } else if (document.selection) {
    var sel = document.selection.createRange().text;
  } else {
    var sel = '';
  }
  
  if (sel == '') sel = $("#commentcontent-" + id).text();
  var area =   $("#comment");
  area.val(area.val() + getquotedcontent(authorname, sel)).focus();
}

function replycomment(id, authorname) {
  var area = $("#comment");
  area.val(area.val() + getquotedcontent(authorname, ''));
}

$(document).ready(function() {
  if (("theme" in ltoptions) && ("comments" in ltoptions.theme) && ("comments" in ltoptions.theme.comments)) {
    var comlist = $(ltoptions.theme.comments.comments);
  } else {
    var comlist = $("#commentlist");
  }
  
  comlist.on("click", ".replycomment, .quotecomment", function() {
    var self= $(this);
    if (self.hasClass("replycomment")) {
      replycomment(self.data("idcomment"), self.data("authorname"));
    } else if (self.hasClass("quotecomment")) {
      quotecomment(self.data("idcomment"), self.data("authorname"));
    }
    return false;
  });
});