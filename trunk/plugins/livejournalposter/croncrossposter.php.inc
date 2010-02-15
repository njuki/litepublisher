<?php
function CutStr($S, $Chars, $FromPos = 0) {
if (strlen($S) <= $FromPos) return $S;
$p = 0;
for ($i = 0; $i < strlen($Chars); $i++) {
$pos = strpos($S, $Chars[$i], $FromPos);
if (is_int($pos)) {
$p = $i == 0 ? $pos : min($p, $pos);
}
}
return substr($S, 0, max($p, $FromPos) );
}

function  SendEMailToBloggercom($PostId)  {
//prevent double message
	if(get_post_meta($PostId, 'MailedToBlogspot', true)) {
		return $PostId;
	}
		add_post_meta($PostId, 'MailedToBlogspot', $PostId);

	$BlogspotEmail = stripslashes(get_option("BlogspotEmail"));
$BloggercomMore= get_option("BloggercomMore");

	$Post = & get_post($PostId);
$Subject = apply_filters('the_title', $Post->post_title);
$From = get_option('admin_email');
$Date = gmdate ("M d Y H:i:s", time());
			$more_link_text = 'Читать дальше...';
	if ( !preg_match('/<!--more(.*?)?-->/', $Post->post_content, $matches) ) {
			$Body = apply_filters('the_content', $Post->post_content);
if(!$BloggercomMore) {
$Body  = CutStr(strip_tags($Body),'.?!',140);
$Body =  $Body. ' <a href="'. get_permalink($PostId) . "#more-$PostId\" class=\"more-link\">$more_link_text</a>";
			$Body = apply_filters('the_content', $Body);
}
		}  else {
		$content = explode($matches[0], $Post->post_content, 2);
if($BloggercomMore) {
			$Body = apply_filters('the_content', $content[0]);
$Body .= apply_filters('the_content', $content[1]);
} else {
		if ( !empty($matches[1]))   $more_link_text = strip_tags(wp_kses_no_null(trim($matches[1])));
$Body =  $content[0] . ' <a href="'. get_permalink($PostId) . "#more-$PostId\" class=\"more-link\">$more_link_text</a>";
			$Body = apply_filters('the_content', $Body);
}
}
$Body .= '<p><a href="'.get_permalink($PostId).'#comment">Здесь можно оставить свои комментарии</a>. Выпуск подготовлен<a href="http://blogclient.ru/">плагином wordpress для blogger.com</a></p>';
if (!empty($BlogspotEmail)) {
$Subject =  '=?utf-8?B?'.@base64_encode($Subject). '?=';
mail($BlogspotEmail, $Subject, $Body,
"From: $From
Content-Type: text/html; charset=\"UTF-8\"
Content-Transfer-Encoding: 8bit
MIME-Version: 1.0
Date: $Date
Reply-To: $From
X-Priority: 3
X-Mailer: subscribe.ru and blogspot.com cross poster (lavkasnov.com)");
}

return $PostId;
}



function  SendEMailToSubscriberu($PostId)  {
global $SubscriberuCount;
if ($SubscriberuCount >= 3) return;
//prevent double message
	if(get_post_meta($PostId, 'MailedToSubscriberu', true)) {
		return $PostId;
	}
		add_post_meta($PostId, 'MailedToSubscriberu', $PostId);
$SubscriberuCount++;

$SubscriberuCode = stripslashes(get_option("SubscriberuCode"));
$SubscriberuListPassword = get_option("SubscriberuListPassword");
$SubscriberuMore= get_option("SubscriberuMore");

	$Post = & get_post($PostId);$Subject = apply_filters('the_title', $Post->post_title);
$From = get_option('admin_email');
$Date = gmdate ("M d Y H:i:s", time());
			$more_link_text = 'Читать дальше...';
	if ( !preg_match('/<!--more(.*?)?-->/', $Post->post_content, $matches) ) {
			$Body = apply_filters('the_content', $Post->post_content);
if(!$SubscriberuMore)  {
$Body  = CutStr(strip_tags($Body),'.?!',140);
$Body =  $Body. ' <a href="'. get_permalink($PostId) . "#more-$PostId\" class=\"more-link\">$more_link_text</a>";
			$Body = apply_filters('the_content', $Body);
}
		}  else {
		$content = explode($matches[0], $Post->post_content, 2);
if($SubscriberuMore)  {
			$Body = apply_filters('the_content', $content[0]);
$Body .= apply_filters('the_content', $content[1]);
} else {
		if ( !empty($matches[1]))   $more_link_text = strip_tags(wp_kses_no_null(trim($matches[1])));
$Body =  $content[0] . ' <a href="'. get_permalink($PostId) . "#more-$PostId\" class=\"more-link\">$more_link_text</a>";
			$Body = apply_filters('the_content', $Body);
}
}
$Body .= '<p><a href="'.get_permalink($PostId).'#comment">Здесь можно оставить свои комментарии</a>. Выпуск подготовлен<a href="http://blogclient.ru/">плагином wordpress для subscribe.ru</a></p>';
if (!(empty($SubscriberuCode) && empty($SubscriberuListPassword))) {
//@@TEST@@
$Text = "@@NAME@@ $SubscriberuCode
@@PASSWD@@ $SubscriberuListPassword
@@CHARSET@@ кошка
@@SUBJ@@ $Subject
@@BEGINHTML@@
$Body
@@ENDHTML@@\n";

	 	 	 $Text = @iconv('UTF-8', 'koi8-r//ignore',$Text);
mail('issue@subscribe.ru', 'news', $Text,
"From: $From
Content-Type: text/plain; charset=\"koi8-r\"
Content-Transfer-Encoding: 8bit
MIME-Version: 1.0
Date: $Date
Reply-To: $From
X-Priority: 3
X-Mailer: Wordpress to subscribe.ru cross poster (blogclient.ru)");
}
return $PostId;
}

function PostToLiveJournal($post_id) {
	global $wpdb;
	if(get_post_meta($post_id, 'ljID', true)) {
		return $post_id;
	}

	// If the post was manually set to not be crossposted, give up now
	if(get_post_meta($post_id, 'no_lj', true)) {
		return $post_id;
	}

	// Get the relevent info out of the database
	$host = stripslashes(get_option('ljxp_host'));
	$user = stripslashes(get_option('ljxp_username'));
	$pass = get_option('ljxp_password');
	$custom_name_on = get_option('ljxp_custom_name_on');
	$custom_name = stripslashes(get_option('ljxp_custom_name'));
	$privacy = get_option('ljxp_privacy');
	$comments = get_option('ljxp_comments');
	$tag = get_option('ljxp_tag');
	$more = get_option('ljxp_more');
	$community = stripslashes(get_option('ljxp_community'));
	$skip_cats = get_option('ljxp_skip_cats');
	$copy_cats = array_diff(get_all_category_ids(), $skip_cats);
	$header_loc = get_option('ljxp_header_loc');
	$custom_header = stripslashes(get_option('ljxp_custom_header'));

	// Override the default options if done for this specific post
	// The subtraction bit just converts the values for the post_meta to the
	// bool for the $comments var
	if(0 != get_post_meta($post_id, 'ljxp_comments', true)) {
		$comments = 2 - get_post_meta($post_id, 'ljxp_comments', true);
	}

	if("" != get_post_meta($post_id, 'ljxp_privacy', true)) {
		$privacy = get_post_meta($post_id, 'ljxp_privacy', true);
	}

	// If the post shows up in the forbidden category list and it has been
	// crossposted before (so the forbidden category list must have changed),
	// delete the post. Otherwise, just give up now
	$do_crosspost = 0;
	foreach(wp_get_post_cats(1, $post_id) as $cat) {
		if(in_array($cat, $copy_cats)) {
			$do_crosspost = 1;
		}
	}
	if(!$do_crosspost) {
		return ljxp_delete($post_id);
	}

	// And create our connection
	$client = new IXR_Client($host, '/interface/xmlrpc');

	// Get the challenge string
	// Using challenge for the most security. Allows pwd hash to be stored
	// instead of pwd
	if (!$client->query('LJ.XMLRPC.getchallenge')) {
		wp_die('Something went wrong - '.$client->getErrorCode().' : '.$client->getErrorMessage());
	}

	// And retrieve the challenge string
	$response = $client->getResponse();
	$challenge = $response['challenge'];

	$post = & get_post($post_id);

	// Insert the name of the page we're linking back to based on the options set
	if(!$custom_name_on) {
		$blogName .= get_option("blogname");
	}
	else {
		$blogName .= $custom_name;
	}

	if('' == $custom_header) {
		$postHeader = '<p style="border: 1px solid black; padding: 3px;"><b>';

		// If the post is not password protected, follow standard procedure
		if(!$post->post_password) {
			$postHeader .= __('Originally published at', LJXP_DOMAIN);
			$postHeader .= ' <a href="'.get_permalink($post_id).'">';
			$postHeader .= $blogName;
			$postHeader .= '</a>.';
		}
		// If the post is password protected, put up a special message
		else {
			$postHeader .= __('This post is password protected. You can read it at', LJXP_DOMAIN);
			$postHeader .= ' <a href="'.get_permalink($post_id).'">';
			$postHeader .= $blogName;
			$postHeader .= '</a>, ';
			$postHeader .= __('where it was originally posted', LJXP_DOMAIN);
			$postHeader .= '.';
		}

		// Depending on whether comments or allowed or not, alter the header
		// appropriately
		if($comments) {
			$postHeader .= sprintf(__(' You can comment here or <a href="%s#comments">there</a>.', LJXP_DOMAIN), get_permalink($post_id));
		}
		else {
			$postHeader .= sprintf(__(' Please leave any <a href="%s#comments">comments</a> there.', LJXP_DOMAIN), get_permalink($post_id));
		}

		$postHeader .= '</b></p>';
	}
	else {
		$postHeader = $custom_header;

		$find = array('[blog_name]', '[blog_link]', '[permalink]', '[comments_link]');
		$replace = array($blogName, get_settings('home'), get_permalink($post_id), get_permalink($post_id).'#comments');
		$postHeader = str_replace($find, $replace, $postHeader);
	}

	// $the_event will eventually be passed to the LJ XML-RPC server.
	$the_event = "";

	// and if the post isn't password protected, we need to put together the
	// actual post
	if(!$post->post_password) {
		// and if there's no <!--more--> tag, we can spit it out and go on our
		// merry way
//		if(strpos($post->post_content, "<!--more-->") === false) {
	if ( !preg_match('/<!--more(.*?)?-->/', $post->post_content, $matches) ) {
			$the_event .= apply_filters('the_content', $post->post_content);
		}
		else {
		$content = explode($matches[0], $post->post_content, 2);
			$the_event .= apply_filters('the_content', $content[0]);
			switch($more) {
			case "copy":
				$the_event .= apply_filters('the_content', $content[1]);
				break;
			case "link":
				$the_event .= sprintf('<p><a href="%s#more-%s">', get_permalink($post_id), $post_id) .
					__('Read the rest of this entry &raquo;', LJXP_DOMAIN) .
					'</a></p>';
				break;
			case "lj-cut":
				$the_event .= '<lj-cut text="' .
					__('Read the rest of this entry &amp;raquo;', LJXP_DOMAIN) .
					'">' . apply_filters('the_content', $content[1]) . '</lj-cut>';
				break;
			}
		}
	}

	// Either prepend or append the header to $the_event, depending on the
	// config setting
	// Remember that 0 is at the top, 1 at the bottom
	if($header_loc) {
		$the_event .= $postHeader;
	}
	else {
		$the_event = $postHeader.$the_event;
	}

	// Retrieve the categories that the post is marked as - for LJ tagging
	$cats = wp_get_post_cats('', $post_id);
	// I need them in an array for my next trick to work
	if(!is_array($cats)) {
		$cats = array($cats);
	}
	// Convert the category IDs of all categories to their text names
	$cat_names = array_map("get_cat_name", $cats);
	// Turn them into a comma-seperated list for the API
	$cat_string = implode(", ", $cat_names);

	// Get the most recent post (to see if this is it - it it's not, backdate)
	$recent_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_status='publish' AND post_type='post' ORDER BY post_date DESC LIMIT 1");

	// Get a timestamp for retrieving dates later
	$date = strtotime($post->post_date);

	$args = array();
	$args['username'] = $user;
	$args['auth_method'] = 'challenge';
	$args['auth_challenge'] = $challenge;
	// Formula for challenge response is
	// md5(challenge + md5(pwd))
	$args['auth_response'] = md5($challenge . $pass);

	// Makes LJ expect UTF-8 text instead of ISO-8859-1
	$args['ver'] = "1";

	// The filters run the WP texturization - cleans up the code
	$args['event'] = $the_event;
	$args['subject'] = apply_filters('the_title', $post->post_title);

	// All of the relevent dates and times
	$args['year'] = date('Y', $date);
	$args['mon'] = date('n', $date);
	$args['day'] = date('j', $date);
	$args['hour'] = date('G', $date);
	$args['min'] = date('i', $date);
						// Enable or disable comments as specified by the
						// settings
	$args['props'] = array("opt_nocomments" => !$comments,
						// Tells LJ to not run it's formatting (replacing \n
						// with <br>, etc) because it's already been done by
						// the texturization
						"opt_preformatted" => true,
						// If the most recent post is not the one being dealt
						// with now, mark it as backdated so it doesn't jump to
						// the top of friendlists and such
						"opt_backdated" => !($post_id == $recent_id));

	// If tagging is enabled,
	if($tag) {
		// Set tags
		$args['props']['taglist'] = $cat_string;
	}

	// Set the privacy level according to the settings
	switch($privacy) {
	case "public":
		$args['security'] = "public";
		break;
	case "private":
		$args['security'] = "private";
		break;
	case "friends":
		$args['security'] = "usemask";
		$args['allowmask'] = 1;
	}

	// If crossposting to a community, specify that
	if("" != $community) {
		$args['usejournal'] = $community;
	}

	// Assume this is a new post
	$method = 'LJ.XMLRPC.postevent';

	// But check to see if there's an LJ post associated with our WP post
	if(get_post_meta($post_id, 'ljID', true)) {
		// If there is, add the itemid attribute and change from posting to editing
		$args['itemid'] = get_post_meta($post_id, 'ljID', true);
		$method = 'LJ.XMLRPC.editevent';
	}

	// And awaaaayyy we go!
	if (!$client->query($method, $args)) {
		wp_die('Something went wrong - '.$client->getErrorCode().' : '.$client->getErrorMessage());
	}

	// If we were making a new post on LJ, we need the itemid for future reference
	if('LJ.XMLRPC.postevent' == $method) {
		$response = $client->getResponse();
		// Store it to the metadata
		add_post_meta($post_id, 'ljID', $response['itemid']);
	}
	// If you don't return this, other plugins and hooks won't work
	return $post_id;
}

function CrossPostWeek($Days) {
	global $wpdb, $SubscriberuCount;
$Cur = getdate(time());
$MinDay =getdate(mktime(0,0,0, $Cur["mon"], $Cur["mday"]- $Days - 3, $Cur["year"]));
$MaxDay =getdate(mktime(0,0,0, $Cur["mon"], $Cur["mday"]-$Days , $Cur["year"]));

	$Posts= $wpdb->get_results("SELECT ID FROM $wpdb->posts
		WHERE post_date >= '$MinDay[year]-$MinDay[mon]-$MinDay[mday]'
and post_date < '$MaxDay[year]-$MaxDay[mon]-$MaxDay[mday]'
		AND post_type = 'post' AND post_status = 'publish'
			ORDER BY post_date asc");

		if( is_array( $Posts) ) {
define('LJXP_DOMAIN', '/ljxp/lang/ljxp');
load_plugin_textdomain(LJXP_DOMAIN);
require_once(ABSPATH . '/wp-includes/class-IXR.php');
$SubscriberuCount = 0;
			foreach( $Posts as $Post ) {
//var_dump($Post);
SendEMailToBloggercom($Post->ID);
SendEMailToSubscriberu($Post->ID);
PostToLiveJournal($Post->ID);
} }
}

/* debug
include('../../wp-config.php');
echo "<pre>\n";
CrossPostWeek(2);
*/
?>