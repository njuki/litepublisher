<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tcommentmanager extends tevents {
  
  public static function instance() {
    return getinstance(__class__);
  }

  protected function create() {
    parent::create();
    $this->basename = 'commentmanager';
    $this->addevents('added', 'deleted', 'edited', 'changed', 'approved');
$this->data['sendnotification'] =  true;
$this->data['trustlevel'] = 2;
$this->data['hidelink'] = false;
$this->data['redir'] = true;
$this->data['nofollow'] = false;
  }

  public function add($idpost, $name, $email, $url, $content) {
    $comusers = dbversion ? tcomusers ::instance() : tcomusers ::instance($idpost);
    $idauthor = $comusers->add($name, $email, $url);
    return $this->addcomment($idpost, $idauthor, $content);
  }

public function addcomment($idpost, $idauthor, $content) {
global $classes;
    $status = $classes->spamfilter->createstatus($idauthor, $content);
    $comments = tcomments::instance($idpost);
$id = $comments->add($idauthor,  $content, $status);

    $this->dochanged($id, $idpost);
    $this->added($id);
    $this->sendmail($id);

return $id;
  }

  private function dochanged($id, $idpost) {
if (dbversion) {
$comments = tcomments::instance($idpost);
$count = $comments->db->getcount("post = $idpost and status = 'approved'");
$comments->getdb('posts')->setvalue($idpost, 'commentscount', $count);
//update trust
try {
$item = $comments->getitem($id);
$idauthor = $item['author'];
$comusers = tcomusers::instance($idpost);
$comusers->db->setvalue($idauthor, 'trust', $comments->db->getcount("author = $author and status = 'approved' limit 5"));
    } catch (Exception $e) {
}
}
    
    $post = tpost::instance($idpost);
$post->clearcache();
    $this->changed($id);
  }

 public function delete($id, $idpost) {
$comments = tcomments::instance($idpost);
$comments->delete($id);
$this->deleted($id);
      $this->dochanged($id, $idpost);
  }

  public function postdeleted($idpost) {
if (dbversion) {
$comments = tcomments::instance($idpost);
$comments->db->update("status = 'deleted'", "post = $idpost");
}
}

    public function setstatus($idpost, $id, $status) {
    if (!in_array($status, array('approved', 'hold', 'spam')))  return false;
$comments = Tcomments($idpost);
if (dbversion) {
$comments->db->setvalue($id, 'status', $status);
} else {
switch ($value) {
case 'hold': 
$comments->hold($id);
break;

case 'approved':
$comments->approve($id);
break;
}
}
    $this->dochanged($id, $idpost);
  }

public function checktrust($value) {
return $value >= $this->trustlevel;
}

  public function sendmail($id) {
    global $options, $comment;
    if (!$this->sendnotification) return;
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