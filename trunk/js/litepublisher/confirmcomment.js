/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window) {
  window.litepubl.class_confirmcomment = Class.extend({
    
    init: function(opt) {
      this.options= $.extend({
        confirmcomment: true,
        comuser: false,
        form: "#commentform",
        editor: "#comment"
      }, ltoptions.theme.comments, opt);
      
      var form = $(this.options.form);
      //ctrl+enter
      this.get("content").off("keydown.confirmcomment").on("keydown.confirmcomment", function (e) {
        if (e.ctrlKey && ((e.keyCode == 13) || (e.keyCode == 10))) {
          form.submit();
        }
      });
      
      var self = this;
      form.off("submit.confirmcomment").on("submit.confirmcomment", function() {
        return self.submit();
      });
    },
    
    get: function(name) {
      if (name == 'content') return $("textarea[name='content']", this.options.form);
      return $("input[name='" + name + "']", this.options.form);
    },
    
    error: function(mesg) {
      return $.messagebox(lang.dialog.error, mesg);
    },
    
    error_field: function(field, mesg) {
      var self = this;
      this.error(mesg).close = function() {
        self.get(field).focus();
      };
    },
    
    empty: function(name) {
      var s = this.get(name).val();
      return $.trim(s) == "";
    },
    
    validemail: function() {
      var s = $.trim(this.get("email").val());
      if (s == "") return false;
    var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
      return filter.test(s);
    },
    
    validate: function() {
      if ("" == $.trim(this.get("content").val())) {
        this.error_field("content", lang.comment.emptycontent);
        return false;
      }
      if (!this.options.comuser) return true;
      
      if (this.empty("name")) {
        this.error_field("name", lang.comment.emptyname);
      } else if (!this.validemail()) {
        this.error_field("email", lang.comment.invalidemail);
      } else {
        return true;
      }
      
      return false;
    },
    
    send: function() {
    var values = {method: "comment_add"};
      var inputs = $(":input", this.options.form);
      inputs.each(function() {
        var self = $(this);
        values[self.attr("name")] = self.val();
        self.attr("disabled", "disabled");
      });
      
      var self = this;
      $.litejsonpost(values, function (resp) {
        try {
          switch (resp.code) {
            case 'confirm':
            self.confirm(resp.confirmid);
            break;
            
            case 'success':
            self.success(resp);
            break;
            
            default: //error
            self.error(resp.msg);
            break;
          }
      } catch(e) { form.error(e.message); }
      })
      .fail( function(jq, textStatus, errorThrown) {
        self.error(jq.responseText);
      })
      .always(function() {
        inputs.removeAttr("disabled");
      });
    },
    
    confirm: function(confirmid) {
      var self = this;
      $.confirmbox(lang.dialog.confirm, lang.comment.checkspam , lang.comment.robot, lang.comment.human, function(index) {
        if (index !=1) return;
      $.litejsonpost({method: "comment_confirm", confirmid: confirmid}, self.success)
        .fail( function(jq, textStatus, errorThrown) {
          self.error(jq.responseText);
        });
      });
    },
    
    success: function(data) {
      if ("cookies" in data) {
        for (var name in data.cookies) {
          set_cookie(name, data.cookies[name]);
        }
      }
      window.location = data.posturl;
    },
    
    submit: function() {
      try {
        if (!this.validate()) return false;
        if (this.options.confirmcomment) {
          this.send();
          return false;
        }
    } catch(e) {erralert(e);}
    }
    
  });
  
  $(document).ready(function() {
    litepubl.confirmcomment = new litepubl.class_confirmcomment();
  });
  
}(jQuery, document, window));