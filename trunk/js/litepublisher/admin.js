/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window, litepubl) {
  $(document).ready(function() {
    $(".checkall").click(function() {
      $(this).closest("form").find("input[type='checkbox']").prop("checked", true);
      $(this).prop("checked", false);
    });
    
    $(".invertcheck").click(function() {
      $(this).closest("form").find("input[type=checkbox]").each(function() {
        $(this).prop("checked", ! $(this).prop("checked"));
      });
      $(this).prop("checked", false);
      return false;
    });
    
    //like collapse.js
    $(".togglelink").on("click.toggle", function() {
      var link = $(this);
      var target = link.attr("data-target");
      if (target) $(target).slideToggle().removeClass("hidden");
      // second target
      target = link.attr("data-second");
      if (target) $(target).slideToggle().removeClass("hidden");
      return false;
    });
    
    // switcher template see in lib/admin.files.class.php
    var switcher = $('#files-source');
    if (switcher.length) {
      $('#text-downloadurl').parent().hide();
      switcher.click(function() {
        var mode = $('#hidden-uploadmode');
        if (mode.val() == 'file') {
          mode.val('url');
        } else {
          mode.val('url');
        }
        
        $('#file-filename').parent().toggle();
        $('#text-downloadurl').parent().toggle();
        return false;
      });
    }
    
    $("a.confirm-delete-link").on("click.confirm", function() {
      var url = $(this).attr("href");
      $.confirmdelete(function() {
        $('<form action="' + url + '&confirm=1" method="post"><input type="submit" name="submitconfirm" /></form>')
        .appendTo("body").submit();
      });
      return false;
    });
    
  });
  
  litepubl.uibefore = function( event, ui) {
    if ( ui.tab.data( "loaded" ) ) {
      event.preventDefault();
      return;
    }
    
    ui.jqXHR.success(function() {
      ui.tab.data( "loaded", true );
    });
  };
  
}(jQuery, document, window, litepubl));