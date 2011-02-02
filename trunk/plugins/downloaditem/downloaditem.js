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
var args  = 'itemtype=' + type + '&url=' +encodeURIComponent(url);
var q = ltoptions.download_site.indexOf('?') ? '&' : '?';
return ltoptions.download_site + '/admin/service/upload/' + q + args;
}

function download_item_clicked() {
var url = $(this).data("url");
var type = $(this).attr("rel");
$("#form_download_site").show();
if (ltoptions.download_site = $.trim(prompt(lang.downloaditem.urlprompt, "http://"))) {
set_cookie('download_site', ltoptions.download_site);
window.location= get_download_item(url, type);
}
return false;
}

//init
$(document).ready(function() {
if (ltoptions.download_site = get_download_url()) {
$("#text_download_site").val(ltoptions.download_site);
$("#form_download_site").hide();
} else {

}

$("a[rel='theme'], a[rel='plugin']").each(function() {
var url = $(this).attr("href");
$(this).data("url", url);
if (ltoptions.download_site == '') {
$(this).click(download_item_clicked);
} else {
var type = $(this).attr("rel");
$(this).attr("href", get_download_item(url, type));
}
});

$("#download_site_form").submit(function() {
ltoptions.download_site = $("#text_download_site").val();
return false;
});
});