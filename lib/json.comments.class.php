<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tjsoncomments extends tevents {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function auth($id, $action) {
if (!litepublisher::$options->user) return false;
if (litepublisher::$options->ingroup('moderator')) return true;
$comments = tcomments();
if (!$comments->itemexists($id)) return false;
$cm = tcommentmanager::i();
switch ($action) {
case 'edit':
if (!$cm->canedit) return false;
if ('closed' == litepublisher::$db->getval('posts', $comments->getvalue($id, 'post'), 'comstatus')) return false;
return $comments->getvalue($id, 'author') == litepublisher::$options->user;

case 'delete':
if (!$cm->candelete) return false;
if ('closed' == litepublisher::$db->getval('posts', $comments->getvalue($id, 'post'), 'comstatus')) return false;
return $comments->getvalue($id, 'author') == litepublisher::$options->user;
}
return false;
  }

public function forbidden() {
$this->error('Forbidden', 403);
}
  
  public function comment_delete(array $args) {
    $id = (int) $args['id'];
if (!$this->auth($id, 'delete')) return $this->forbidden();
    return tcomments::i()->delete($id);
  }
  
  public function comment_setstatus($args) {
    $id = (int) $args['id'];
if (!$this->auth($id, 'status')) return $this->forbidden();
return tcomments::i()->setstatus(($id, $args['status']);
  }
  
  public function comment_edit(array $args) {
    $id = (int) $args['id'];
if (!$this->auth($id, 'edit')) return $this->forbidden();
$content = trim($args['content']);
if (empty($content)) return false;
return tcomments::i()->edit(($id, $content);
  }
  
}//class