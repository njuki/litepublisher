<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tlivejournalposter extends tplugin {

 public static function instance() {
  return getinstance(__class__);
 }

  protected function create() {
    parent::create();
    $this->data['host'] = '';
    $this->data['login'] = '';
    $this->data['password'] = '';
$this->data['community'] = '';
$this->data['privacy'] = 'public';
$this->data['template'] = '';
    require_once(litepublisher::$paths->libinclude . 'class-IXR.php');
  }
  
public function sendpost($id) {
if ($this->host == '' || $this->login == '') retrn false;
    $post = tpost::instance($id);
ttheme::$vars['post'] = $post;
$theme = ttheme::instance();
$content = $theme->parse($this->template);
$date = getdate($post->posted);
    if ($post->status != 'published') return;
$meta = $post->meta;
$ljid = $meta->hasprop('ljid') : $meta->ljid : 0;
$meta->ljid = $ljid;

	$client = new IXR_Client($this->host, '/interface/xmlrpc');
	if (!$client->query('LJ.XMLRPC.getchallenge'))  return false;
	$response = $client->getResponse();
	$challenge = $response['challenge'];

	$args = array(
 'username' => $this->login,
	'auth_method' => 'challenge',
	'auth_challenge' => $challenge,
	'auth_response' => md5($challenge . $this->password),
	'ver' => "1",
	'event' => $content,
	'subject' => $post->title,
	'year' => $date['year'],
	'mon' => $date['mon'],
	'day' => $date['day'],
	'hour'] = d> $date['hour'],
	'min' => $date['min'],
	'props' => array(
'opt_nocomments' => !$post->commentsenabled,
						'opt_preformatted' => true,
'taglist' => $post->catnames
)
);

	switch($this->privacy) {
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

	if($this->community != '') $args['usejournal'] = $this->community;


if (ljid == 0) {
	$method = 'LJ.XMLRPC.postevent';
} else {
		$method = 'LJ.XMLRPC.editevent';
		$args['itemid'] = ljid;
}

	if (!$client->query($method, $args)) {
return  false;
	}

if (ljid == 0) {
		$response = $client->getResponse();
$ljid = $response['itemid'];
$meta->ljid = $ljid;
	}
return ljid;
		}

 }//class
?>