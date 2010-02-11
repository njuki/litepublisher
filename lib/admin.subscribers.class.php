<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminsubscribers extends tadminmenu {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'subscribers';
  }
  
public function auth() { }
  
  public function getcontent() {
    $html= $this->html;
    $args = targs::instance();
    $comusers = tcomusers::instance();
    if (!($user = $comusers->fromcookie($_GET['userid']))) return $this->notfount();
    $subscribers=  tsubscribers::instance();
    $items = $subscribers->getposts($user['id']);
    if (count($items) == 0) return $html->h2->nosubscribtions;
    $args->email = $user['email'];
    $result .=$html->formhead($args);
    foreach ($items as $postid) {
      $post = tpost::instance($postid);
      ttheme::$vars['post'] = $post;
      if ($post->status != 'published') continue;
      $result .= $html->formitem($args);
    }
    $result .= $html->formfooter();
    return $this->FixCheckall($result);
  }
  
  public function processform() {
    $comusers = tcomusers::instance();
    if (!($user = $comusers->fromcookie($_GET['userid']))) return '';
    $subscribers = tsubscribers::instance();
    $subscribers->lock();
    foreach ($_POST as $name => $value) {
      if (substr($name, 0, 7) == 'postid-') {
        $subscribers->delete($value, $user['id']);
      }
    }
    $subscribers->unlock();
    
    return $this->html->h2->unsubscribed;
  }
  
}//class

?>