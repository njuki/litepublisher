function get_cookie(name) {
  if (document.cookie && document.cookie != '') {
    var cookies = document.cookie.split(';');
    for (var i = 0; i < cookies.length; i++) {
      var cookie = jQuery.trim(cookies[i]);
      if (cookie.substring(0, name.length + 1) == (name + '=')) {
        return decodeURIComponent(cookie.substring(name.length + 1));
      }
    }
  }
  return '';
}

function set_cookie (name, value, expires){
  if (!expires) {
    expires = new Date();
    expires.setFullYear(expires.getFullYear() + 10);
  }
  document.cookie = name + "=" + encodeURIComponent(value) + "; expires=" + expires.toGMTString() +  "; path=/";
}

function get_get(name) {
    var q = window.location.search.substring(1);
    var vars = q.split('&');
    for (var i=0; i<vars.length; i++) {
        var pair = vars[i].split('=');
        if (name == pair[0]) return decodeURIComponent(pair[1]);
    }
return false;
}

function get_download_site() {
var result = '';
if (result = get_get('site')) {
set_cookie('download_site', result);
} else {
result = get_cookie('download_site');
}
return result;
}

function get_download_item(url, type) {
alert(url);
var args  = 'itemtype=' + type + '&url=' +encodeURIComponent(url);
var q = ltoptions.download_site.indexOf('?')== -1  ? '?' : '&';
return ltoptions.download_site + '/admin/service/upload/' + q + args;
}

function siteurl_dialog(fn) {
  switch($._ui_dialog) {
    case 'loaded':
$("#siteurl_dialog").dialog( "open" );
    break;
    
    case 'loading':
//ignore
    break;
    
    default:
    $._ui_dialog = 'loading';
var dir = ltoptions.files + '/plugins/downloaditem/resource/';
    $('<link rel="stylesheet" type="text/css" href="'+ dir + 'jquery-ui-dialog-1.8.9.css" />').appendTo("head");
    $.getScript(dir + "jquery-ui-dialog-1.8.9.min.js", function() {
$("#siteurl_dialog").dialog( {
autoOpen: false,
modal: true,
buttons: [
{
        text: "Ok",
        click: function() {
 $(this).dialog("close"); 
var url = $.trim($("#text_download_site").val());
set_cookie('download_site', url);
update_siteurl(url);
if ($.isFunction(fn)) fn();
}
    },
{
        text: "Cancel",
        click: function() { $(this).dialog("close"); }
    }
]
} );

        $._ui_dialog = 'loaded';
$("#siteurl_dialog").dialog( "open" );
    });
  }
}

function download_item_clicked() {
var url = $(this).data("url");
var type = $(this).attr("rel");
if (ltoptions.download_site == '') {
siteurl_dialog(function() {
window.location= get_download_item(url, type);
});
}
return false;
}

function update_siteurl(url) {
ltoptions.download_site =url;
$("#text_download_site").val(url);
var link = $("#yoursite");
link.attr("href", url);
link.attr("title", url);
link.text(url);

$("a[rel='theme'], a[rel='plugin']").each(function() {
if (url == '') {
$(this).click(download_item_clicked);
} else {
$(this).unbind("click");
var type = $(this).attr("rel");
var fileurl = $(this).data("url");
if (fileurl == undefined) {
fileurl = $(this).attr("href");
$(this).data("url", fileurl);
}
$(this).attr("href", get_download_item(fileurl, type));
}
});

}

function init_download_items() {
try {
ltoptions.download_site = '';
if (url = get_download_site()) {
update_siteurl(url);
}

$("#change_url").click(function() {
siteurl_dialog();
return false;
});

$("a[rel='theme'], a[rel='plugin']").each(function() {
var url = $(this).attr("href");
alert('url= ' + url);
$(this).data("url", url);
if (ltoptions.download_site == '') {
$(this).click(download_item_clicked);
} else {
var type = $(this).attr("rel");
$(this).attr("href", get_download_item(url, type));
alert(get_download_item(url, type));
}
});

} catch(e) { alert('ex' + e.message); }
}

$(document).ready(init_download_items);