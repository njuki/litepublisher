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
$(document).ready(function() {
$('<link rel="stylesheet" type="text/css" href="'+ltoptions.files +'/js/jjquery/jquery-ui-1.8.6.custom.css" />') .appendTo("head");
          $.getScript(ltoptions.files + '/js/jquery/jquery.ui.core-tabs.min.js', function() {
  $(sel).tabs({ cache: true });
if (typeof callback=== "function") callback();
});
});
}