<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminsubscribers extends tadminform {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->section = 'subscribers';
  }
  
  public function getcontent() {
    $html= $this->html;
    $args = targs::instance();
    $comusers = tcomusers::instance();
    if (!($user = $comusers->fromcookie($_GET['userid']))) return $html->h2->nosubscribtions  ;
    $subscribers=  tsubscribers::instance();
    $items = $subscribers->getposts($user['id']);
    if (count($items) == 0) return $html->h2->nosubscribtions;
    $args->email = $user['email'];
    $result =$html->formhead($args);
    foreach ($items as $postid) {
      $post = tpost::instance($postid);
      ttheme::$vars['post'] = $post;
      if ($post->status != 'published') continue;
      $args->postid = $postid;
      $result .= $html->formitem($args);
    }
    $result .= $html->formfooter();
    return $html->fixquote($result);
  }
  
  public function processform() {
    $comusers = tcomusers::instance();
    if (!($user = $comusers->fromcookie($_GET['userid']))) return '';
    $subscribers = tsubscribers::instance();
    $subscribers->lock();
    foreach ($_POST as $name => $value) {
      if (strbegin($name, 'postid-')) {
        $subscribers->remove($value, $user['id']);
      }
    }
    $subscribers->unlock();
    
    return $this->html->h2->unsubscribed;
  }
  
}//class

?>