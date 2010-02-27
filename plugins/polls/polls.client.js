var pollclient = {
created: false,
client: null,
cookie: '',
cookierequested: false,
oncookie: null,
type: ''
};

pollclient.create= function () {
if (this.created) return;
this.created = true;
this.client= new rpc.ServiceProxy(ltoptions.pingback, {
asynchronous: true,
protocol: 'XML-RPC',
sanitize: false,     
methods: [
'litepublisher.poll.sendvote',
'litepublisher.poll.getcookie'
]
//callbackParamName: 'callback'
}); 

this.cookie = this.get_cookie("polluser");
if (this.cookie == null) this.cookie = '';
this.getcookie();
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

pollclient.getcookie = function() {
this.create();
this.client.litepublisher.poll.getcookie( {
params:[this.cookie],
                 onSuccess:function(result) {
pollclient.setcookie(result);
},
                  onException:function(errorObj){ 
                    alert(errorObj.message);
},

onComplete:function(responseObj){ }
} );

}

pollclient.setcookie = function(cookie) {
try {
if (cookie != this.cookie) {
this.set_cookie('polluser', cookie, false);
this.cookie = cookie;
}

this.cookierequested = true;
if (this.oncookie) this.oncookie();
} catch (e) { alert(e.message); }
}

pollclient.sendvote = function (idpoll, vote) {
this.create();
this.client.litepublisher.poll.sendvote( {
params:[idpoll, vote, this.cookie],

                 onSuccess:function(result){                     
try {
var idspan = 'votes-' + idpoll + '-';
for (var i =0, n =result.length; i < n; i++) {
if (span = document.getElementById(idspan + i)) {
span.innerHTML = result[i];
}
}
} catch (e) { alert(e.message); }
},

                  onException:function(errorObj){ 
                    alert(errorObj.message);
},

onComplete:function(responseObj){ }
} );

}

pollclient.send_vote = function(idpoll, vote) {
this.create();
if (this.cookierequested) return this.sendvote(idpoll, vote);

this.oncookie = function() {
this.sendvote(idpoll, vote);
};
}

pollclient.radiovote = function(idpoll, btn) {
try {
var elems =  btn.form.elements;
	for (var i = 0, n = elems.length; i < n; i++) {
		if((elems[i].type == 'radio') && (elems[i].checked = true)) {
var vote = elems[i].value;
break;
	}
}
} catch (e) { alert(e.message); }

this.send_vote(idpoll, vote);
}