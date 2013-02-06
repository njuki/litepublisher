/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window) {
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
}(jQuery, document, window));