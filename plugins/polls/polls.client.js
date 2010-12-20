/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

var pollclient = {
  cookierequested: false,
  cookie: '',
  voted : []
};

pollclient.sendvote = function (idpoll, vote) {
  $.get(ltoptions.url + '/ajaxpollserver.htm',
{action: 'sendvote', cookie: this.cookie,idpoll: idpoll, vote: vote},
  function (result) {
    var items = result.split(',');
    var idspan = '#votes-' + idpoll + '-';
    for (var i =0, n =items.length; i < n; i++) {
      $(idspan + i).html(items[i]);
    }
  });
};

pollclient.clickvote = function(idpoll, vote) {
  for (var i = this.voted.length -1; i >= 0; i--) {
    if (idpoll == this.voted[i]) {
      return false;
    }
  }
  this.voted.push(idpoll);
  
  if (this.cookierequested) {
    this.sendvote(idpoll, vote);
  } else {
    this.cookie = this.get_cookie("polluser");
    if (this.cookie == null) this.cookie = '';
    this.getcookie(function() {
      pollclient.sendvote(idpoll, vote);
    });
  }
};

pollclient.radiovote = function(idpoll, btn) {
  $(btn).closest("form").find("radio:checked").each(function() {
    var vote = $(this).val();
  });
};

pollclient.getcookie = function(callback) {
  $.get(ltoptions.url + '/ajaxpollserver.htm',
{action: 'getcookie', cookie: this.cookie},
  function (cookie) {
    if (cookie != pollclient.cookie) {
      pollclient.set_cookie('polluser', cookie, false);
      pollclient.cookie = cookie;
    }
    
    pollclient.cookierequested = true;
    if (callback) callback()
  });
};

pollclient.get_cookie= function(name) {
  var cookie = " " + document.cookie;
  var search = " " + name + "=";
  var setStr = null;
  var offset = 0;
  var end = 0;
  if (cookie.length > 0) {
    offset = cookie.indexOf(search);
    if (offset != -1) {
      offset += search.length;
      end = cookie.indexOf(";", offset)
      if (end == -1) {
        end = cookie.length;
      }
      setStr = unescape(cookie.substring(offset, end));
    }
  }
  return(setStr);
};

pollclient.set_cookie = function (name, value, expires){
  if (!expires) {
    expires = new Date();
    expires.setFullYear(expires.getFullYear() + 10);
  }
  document.cookie = name + "=" + escape(value) + "; expires=" + expires.toGMTString() +  "; path=/";
};