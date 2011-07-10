/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

$.extend({
  load_script: function( url, callback ) {
    return $.ajax({
      type: 'get',
      url: url,
      data: undefined,
      success: callback,
      dataType: "script",
      cache: true
    });
  },
  
  uiscript: false,
  load_ui: function(fn) {
    if (!$.uiscript) {
      var dir = ltoptions.files + '/js/jquery/ui-' + ltoptions.jqueryui_version;
      $('<link rel="stylesheet" type="text/css" href="' + dir + '/redmond/jquery-ui-' + ltoptions.jqueryui_version + '.custom.css" />').appendTo("head:first");
      $.uiscript = $.load_script(dir + '/jquery-ui-' + ltoptions.jqueryui_version + '.custom.min.js');
    }
    if ($.isFunction(fn)) $.uiscript.done(fn);
  }
});