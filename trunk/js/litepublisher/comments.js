/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window) {
  window.litepubl.Commentquote = Class.extend({
    
    init: function(opt) {
      this.options= $.extend({
        comments: "#commentlist",
        form: "#commentform"
      }, ltoptions.theme.comments, opt);
      
      var self = this;
      var list = $(this.options.comments);
      //backward support
      $("*[onclick^='replycomment'], *[onclick^='quotecomment']", list).each(function() {
        var link = $(this);
        var values = /^\s*(\w*)\s*\(\s*(\d*)\s*,\s*'(.*)'\s*\)/.exec(link.attr("onclick"));
        link.attr("onclick", false);
        link.data("idcomment", values[2]);
        link.data("authorname", values[3]);
        link.addClass(values[1] == "replycomment" ? "replycomment" : "quotecomment");
      });
      
      list.off("click.quotecomment").on("click.quotecomment", ".replycomment, .quotecomment", function() {
        var cmt = $(this);
        if (cmt.hasClass("replycomment")) {
          self.reply(cmt.data("idcomment"), cmt.data("authorname"));
        } else if (cmt.hasClass("quotecomment")) {
          self.quote(cmt.data("idcomment"), cmt.data("authorname"));
        }
        return false;
      });
    },
    
    getarea: function() {
      return $("textarea[name='content']", this.options.form);
    },
    
    getquoted: function( authorname, content) {
      if (content == '') {
        return lang.comment.to + " [b]" + authorname + "[/b]: ";
      } else {
        return "[b]" + authorname + "[/b] " + lang.comment.says + ":\n[quote]" + content + "[/quote]\n";
      }
    },
    
    quote: function(id, authorname) {
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
      var area =   this.getarea();
      area.val(area.val() + this.getquoted(authorname, sel)).focus();
    },
    
    reply: function(id, authorname) {
      var area = this.getarea();
      area.val(area.val() + this.getquoted(authorname, ''));
    }
  });
  
  $(document).ready(function() {
    litepubl.commentquote = new litepubl.Commentquote();
  });
}(jQuery, document, window));