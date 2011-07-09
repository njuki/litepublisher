<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tajaxcommentformplugin extends tplugin {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function install() {
litepublisher::$options->autocmtform = false;
litepublisher::$urlmap->addget('/ajaxcommentform.htm', get_class($this));

    $parser = tthemeparser::instance();
    $parser->parsed = $this->themeparsed;
    ttheme::clearcache();
  }
  
  public function uninstall() {
litepublisher::$options->autocmtform = true;
turlmap::unsub($this);
    $parser = tthemeparser::instance();
    $parser->unsubscribeclass($this);
    ttheme::clearcache();
  }
  
  public function themeparsed(ttheme $theme) {
    if (strpos($theme->templates['content.post.templatecomments.form'], 'ajaxcommentform')) return;
$theme->templates['content.post.templatecomments.form'].= $this->getjs();
  }
  
  public function getjs() {
$name = basename(dirname(__file__));
$about = tplugins::getabout($name);
$lang = tlocal::instance('comments');
$ls = array(
'error_title' => $lang->error
);
$result = sprintf('<script type="text/javascript">ltoptions.commentform = %s;</script>', json_encode($ls));

$template = ttemplate::instance();
    $result .= $template->getjavascript("/plugins/$name/ajaxcommentform.js");
return $result;
  }

public function request($arg) {
$this->cache = false;
if (!empty($_GET['getuser'])) {
      $cookie = basemd5($_GET['getuser']  . litepublisher::$secret);
$idpost = (int) $_GET['idpost'];
      $comusers = tcomusers::instance($idpost);
      if ($user = $comusers->fromcookie($cookie)) {
$data = array(
'name' => $user['name'],
'email' => $user['email'],
'url' => $user['url']
);

      $subscribers = tsubscribers::instance();
      $data['subscribe'] = $subscribers->subscribed($idpost, $user['id']);

return turlmap::htmlheader(false) . json_encode($data);
} else 
return 403;
}
}
  
}//class