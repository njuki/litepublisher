$(document).ready(function() {
  $("input[rel=\'checkall\']").click(function() {
    $(this).closest("form").find("input:checkbox").attr("checked", true);
    $(this).attr("checked", false);
  });
  
  $("input[rel=\'invertcheck\']").click(function() {
    $(this).closest("form").find("input:checkbox").each(function() {
      $(this).attr("checked", ! $(this).attr("checked"));
    });
    $(this).attr("checked", false);
  });
  
});

function inittabs(sel, callback) {

    $('<link rel="stylesheet" type="text/css" href="'+ ltoptions.files + '/js/jquery/ui-1.8.9/redmond/jquery-ui-1.8.9.custom.css" />').appendTo("head");
  $(document).ready(function() {
    $.getScript(ltoptions.files + '/js/jquery/ui-1.8.9/jquery-ui-1.8.9.custom.min.js', function() {
    $(sel).tabs({ cache: true });
      if (typeof callback=== "function") callback();
    });
  });
}