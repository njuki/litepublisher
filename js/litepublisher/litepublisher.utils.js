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
  }
});

function load_ui(fn) {
if ($.uiscript == undefined) {
var dir = ltoptions.files + '/js/jquery/ui-' + ltoptions.jqueryui_version;
$('<link rel="stylesheet" type="text/css" href="'+ dir + '/redmond/jquery-ui-' + ltoptions.jqueryui_version + '