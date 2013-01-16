/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

$(document).ready(function() {
  $("input[rel='checkall']").click(function() {
    $(this).closest("form").find("input[type=checkbox]").prop("checked", true);
    $(this).prop("checked", false);
  });
  
  $("input[rel='invertcheck']").click(function() {
    $(this).closest("form").find("input[type=checkbox]").each(function() {
      $(this).prop("checked", ! $(this).prop("checked"));
    });
    $(this).prop("checked", false);
  });
  
});