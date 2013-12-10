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
  
  litepubl.is_adminpanel = function() {
    if (litepubl.adminpanel !== false) return litepubl.adminpanel;
    litepubl.adminpanel = 0;
    var url = location.href.toLowerCase().substring(10);
    var path = url.split('/');
    if ((path.length <= 2) || (path[1] != 'admin') || (path[2] == '')) return false;
    return litepubl.adminpanel = (/^(cabinet|login|logout|password)$/.test(path[2]) ? 0 : 1);
  };
}(jQuery, document, window, litepubl));