<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

interface icommentmanager {
public function addcomment($postid, $author, $content);
public function addpingback($pid, $url, $title);
public function getcomment($id);
public function delete($id);
public function postdeleted($postid);
public function setstatus($id, $value);
}

class TAbstractCommentManager extends titems {

  protected function create() {
    parent::create();
    $this->basename = 'commentmanager';
    $this->addevents('edited', 'changed', 'approved');
  }

  public function add($postid, $name, $email, $url, $content) {
    $users = tcomusers ::instance();
    $userid = $users->add($name, $email, $url);
    return $this->addcomment($postid, $userid, $content);
  }
  
 protected function doadded($id) {
    $this->dochanged($this->items[$id]['pid']);
    $this->CommentAdded($id);
    $this->Added($id);
  }
  
  public function dochanged($postid) {
$widgets = twidgets::instance();
$widgets->setexpired('tcommentswidget'); 
    
    $post = tpost::instance($postid);
    $urlmap = turlmap::instance();
    $urlmap->setexpired($post->idurl);
    
    $this->changed($postid);
  }
  
  public function CommentAdded($id) {
    global $options;
    if (!$this->options->SendNotification) return;
    $comment = $this->getcomment($id);
    $html = THtmlResource::instance();
    $html->section = 'moderator';
    $lang = tlocal::instance();
    eval('$subject = "' . $html->subject . '";');
    eval('$body = "'. $html->body . '";');
    tmailer::sendmail($options->name, $options->fromemail,
    'admin', $options->email,  $subject, $body);
  }
  
}//class

?>