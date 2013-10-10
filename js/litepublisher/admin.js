/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
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

//upload form
$('#text-downloadurl').parent().hide();
$('#files-source').click(function() {
var mode = $('#uploadmode');
if (mode.val() == 'upload') {
mode.val('download');
 } else {
mode.val('upload');
}
$('#uploadedit').toggle();
$('#text-downloadurl').parent().toggle();
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
}(jQuery, document, window));