/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

  $(document).ready(function() {
if ($(".lazybuttons").length == 0) return;

function show_lazybuttons() {
try {
var lazy = $(".lazybuttons");
		  var url = document.location;
		  var title = document.title.replace("'",'&apos;');
//plus one callback
$.plusone_callback = function(r) {
if (_gaq != undefined) {
if(r.state=='on'){
_gaq.push(['_trackEvent','google', 'plussed', title]);
}else{
_gaq.push(['_trackEvent','google', 'unplussed', title]);
}
}
};

lazy.append(
'<g:plusone size="standard" count="true" callback="$.plusone_callback" href="'+url +'"></g:plusone>');

 var js = lazyoptions.lang == 'en' ? '' : "{lang: '"+lazyoptions.lang+"'}";
//var script = $('<script src="' +document.location.protocol + '//apis.google.com/js/plusone.js'+ '">' + js + "</script>");
var script = $('<script src="https//apis.google.com/js/plusone.js'+ '">' + js + "</script>");
		    		    $('head:first').append(script);

//facebook
lazy.append('<div><iframe src="http://www.facebook.com/plugins/like.php?locale=ru_RU&href=' + encodeURIComponent(url) + 
'&amp;layout=button_count&amp;show_faces=true&amp;width=450&amp;action=like&amp;font=segoe+ui&amp;colorscheme=light" frameborder="0" </iframe></div>');

//twitter
var twituser = lazyoptions.twituser == '' ? '' :'<script type="text/javascript">tweetmeme_source = "' + lazyoptions.twituser  + '";</script>';
lazy.append('<div>' + twituser + '<script type="text/javascript" src="http://tweetmeme.com/i/scripts/button.js"></script></div>');

/*
	  		var via = lazyoptions.twituser == '' ? '' : 'data-via="'+lazyoptions.twituser +'" ';
lazy.append('<div><a href="http://twitter.com/share" class="twitter-share-button" data-url="'+url +'" ' + via+'data-text="'+title +'" data-lang="'+lazyoptions.lang +'" data-count="vertical">Tweet</a></div>');

$.load_script('http://platform.twitter.com/widgets.js', function() {
return;
function ga_track(type, value) {
if (_gaq != undefined) {
							_gaq.push(['_trackEvent', 'twitter_web_intents', type, value]);
}
}

			function clickEvent(event) {
			  if (event) {
ga_track(event.type, event.region);
}
			}       

			function tweetIntent(event) {
			  if (event) {
ga_track(event.type, event.data.tweet_id);
//ontweet
			  }
			}       

			function retweetIntent(event) {
			  if (event) {
ga_track(event.type, event.data.source_tweet_id);
			 		//onretweet
			}       
}

			function followIntent(event) {
			  if (event) {
ga_track(event.type, event.data.user_id + " (" + event.data.screen_name + ")");
//onfollow
			}       
}

			twttr.events.bind('click',    clickEvent);
			twttr.events.bind('tweet',    tweetIntent);
			twttr.events.bind('retweet',  retweetIntent);
			twttr.events.bind('favorite', tweetIntent);
			twttr.events.bind('follow',   followIntent);
});
*/
lazy.append('<div><a href="">' + lazyoptions.hide + '</a></div>').click(function() {
set_cookie("lazybuttons", "hide");
return false;
});

//$("<code></code>").appendTo("body").text(lazy.html());
} catch(e) { alert( e.message); }
}

window.setTimeout(function() {
var cookie  = get_cookie("lazybuttons");
if (cookie  == "hide") {
$('<a href="">' + lazyoptions.show + '</a>').appendTo(".lazybuttons").click(function() {
$(this).remove();
set_cookie("lazybuttons", "show");
show_lazybuttons();
return false;
});
} else {
show_lazybuttons();
}
    }, 120);
});