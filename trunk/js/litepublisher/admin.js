/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

$(document).ready(function() {
  $("input[rel='checkall']").click(function() {
    $(this).closest("form").find("input:checkbox").attr("checked", true);
    $(this).attr("checked", false);
  });
  
  $("input[rel='invertcheck']").click(function() {
    $(this).closest("form").find("input:checkbox").each(function() {
      $(this).attr("checked", ! $(this).attr("checked"));
    });
    $(this).attr("checked", false);
  });
  
});

function doinittabs(sel, fn) {
$(sel).tabs({ cache: true });
  if ($.isFunction(fn)) fn();
}

function inittabs(sel, fn) {
  switch($._tabsready) {
    case 'loaded':
    doinittabs(sel, fn);
    break;
    
    case 'loading':
    $._tabslist.push({
      sel: sel,
      fn: fn
    });
    break;
    
    default:
    $._tabslist = [];
    $._tabsready = 'loading';
    $('<link rel="stylesheet" type="text/css" href="'+ ltoptions.files + '/js/jquery/ui-1.8.10/redmond/jquery-ui-1.8.10.custom.css" />').appendTo("head");
    $.getScript(ltoptions.files + '/js/jquery/ui-1.8.10/jquery-ui-1.8.10.custom.min.js', function() {
      $(document).ready(function() {
        $._tabsready = 'loaded';
        doinittabs(sel, fn);
        $($._tabslist).each(function(index, value) {
          doinittabs(value.sel, value.fn);
        });
        $.tabslist = null;
      });
    });
  }
}