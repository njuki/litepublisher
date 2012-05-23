/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( $ ){
  $.confirmcomment = function(opt) {
    var options= $.extend({
      confirmcomment: true,
      comuser: false,
      form: "#commentform",
      editor: "#comment"
    }, ltoptions.theme.comments, opt);
    
    var form= {
      get: function(name) {
        if (name == 'content') return $(options.editor);
        return $("input[name='" + name + "']", options.form);
      },
      
      error: function(mesg) {
        return $.messagebox(lang.dialog.error, mesg);
      },
      
      error_field: function(field, mesg) {
        form.error(mesg).close = function() {
          form.get(field).focus();
        };
      },
      
      empty: function(name) {
        var s = form.get(name).val();
        return $.trim(s) == "";
      },
      
      validemail: function() {
        var s = $.trim(form.get("email").val());
        if (s == "") return false;
      var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return filter.test(s);
      },
      
      validate: function() {
        if ("" == $.trim($(options.editor).val())) {
          form.error_field("content", lang.comment.emptycontent);
          return false;
        } else if (options.comuser) {
          if (form.empty("name")) {
            form.error_field("name", lang.comment.emptyname);
            return false;
          }
          
          if (!form.validemail()) {
            form.error_field("email", lang.comment.invalidemail);
            return false;
          }
        }
        return true;
      },
      
      send: function() {
      var values = {method: "comment_add"};
        var inputs = $(":input", options.form);
        inputs.each(function() {
          var self = $(this);
          values[self.attr("name")] = self.val();
          self.attr("disabled", "disabled");
        });
        
        $.litejsontype("post", values, function (resp) {
          try {
            switch (resp.code) {
              case 'confirm':
              form.confirm(resp.confirmid);
              break;
              
              case 'success':
              form.success(resp);
              break;
              
              default: //error
              form.error(resp.msg);
              break;
            }
        } catch(e) { form.error(e.message); }
        })
        .fail( function(jq, textStatus, errorThrown) {
          form.error(jq.responseText);
        })
        .always(function() {
          inputs.removeAttr("disabled");
        });
      },
      
      confirm: function(confirmid) {
        $.confirmbox(lang.dialog.confirm, lang.comment.checkspam , lang.comment.robot, lang.comment.human, function(index) {
          if (index !=1) return;
        $.litejsontype("post", {method: "comment_confirm", confirmid: confirmid}, form.success)
          .fail( function(jq, textStatus, errorThrown) {
            form.error(jq.responseText);
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
          if (!form.validate()) return false;
          if (options.confirmcomment) {
            form.send();
            return false;
          }
      } catch(e) { alert(e.message); }
      }
      
    }; //form
    
    //init
    //ctrl+enter
    $(options.editor).off("keydown.confirmcomment").on("keydown.confirmcomment", function (e) {
      if (e.ctrlKey && ((e.keyCode == 13) || (e.keyCode == 10))) {
        $(options.form).submit();
      }
    });
    
    
    $(options.form).off("submit.confirmcomment").on("submit.confirmcomment", form.submit);
  };
  
  $(document).ready(function() {
    $.confirmcomment();
  });
  
})( jQuery );