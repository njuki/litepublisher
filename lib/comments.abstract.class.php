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
$this->data['recentcount'] =  7;
$this->data['SendNotification'] =  true;
  }

  public function add($postid, $name, $email, $url, $content) {
    $users = tcomusers ::instance();
    $userid = $users->add($name, $email, $url);
    return $this->addcomment($postid, $userid, $content);
  }
  
 protected function doadded($id, $pid) {
    $this->dochanged($pid);
    $this->sendmail($id);
    $this->added($id);
  }
  
  public function dochanged($postid) {
$widgets = twidgets::instance();
$widgets->setexpired('tcommentswidget'); 
    
    $post = tpost::instance($postid);
    $urlmap = turlmap::instance();
    $urlmap->setexpired($post->idurl);
    
    $this->changed($postid);
  }
  
  public function sendmail($id) {
    global $options, $comment;
    if (!$this->SendNotification) return;
    $comment = $this->getcomment($id);
    $html = THtmlResource::instance();
    $html->section = 'comments';
$args = targs::instance();
$args->adminurl = $options->url . '/admin/comments/'. $options->q . "id=$id&&action";
$subject = $html->subject();
$body = $html->body($args);
    tmailer::sendmail($options->name, $options->fromemail,
    'admin', $options->email,  $subject, $body);
  }
  
}//class

?>