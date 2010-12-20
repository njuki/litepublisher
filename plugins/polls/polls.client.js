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
  for (var i = this.voted.length -1; i >= 0; i--) {
    if (idpoll == this.voted[i]) return false;
  }
  this.voted.push(idpoll);

  $.get(ltoptions.url + '/ajaxpollserver.htm',
{action: 'sendvote', cookie: this.cookie,idpoll: idpoll, vote: vote},
  function (result) {
var items = result.split(',');
        var idspan = '#votes-' + idpoll + '-';
        for (var i =0, n =items.length; i < n; i++) {
$(idspan + i).html(items[i]);
          }
        });
    }

pollclient.clickvote = function(idpoll, vote) {
  if (this.cookierequested) {
this.sendvote(idpoll, vote);
} else {
  this.cookie = this.get_cookie("polluser");
  if (this.cookie == null) this.cookie = '';
this.getcookie(function() { 
    this.sendvote(idpoll, vote);
  });
}

pollclient.radiovote = function(idpoll, btn) {
var form = $(btn).closest("form");

    var elems =  btn.form.elements;
    for (var i = 0, n = elems.length; i < n; i++) {
      if((elems[i].type == 'radio') && (elems[i].checked == true)) {
        var vote = elems[i].value;
        break;
      }
    }
} catch (e) { alert(e.message); }
  
  this.clickvote(idpoll, vote);
}

pollclient.getcookie = function(callback) {
  $.get(ltoptions.url + '/ajaxpollserver.htm',
{action: 'getcookie', cookie: this.cookie},
  function (cookie) {
    if (cookie != this.cookie) {
      this.set_cookie('polluser', cookie, false);
      this.cookie = cookie;
    }
    
    this.cookierequested = true;
    if (callback) callback();
    });
}

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
}

pollclient.set_cookie = function (name, value, expires){
  if (!expires) {
    expires = new Date();
    expires.setFullYear(expires.getFullYear() + 10);
  }
  document.cookie = name + "=" + escape(value) + "; expires=" + expires.toGMTString() +  "; path=/";
}
