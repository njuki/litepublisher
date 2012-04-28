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
  
  public function auth() {
    if (!litepublisher::$options->ingroup('moderator')) $this->error('Not enough rights');
  }
  
  public function comment_delete(array $args) {
    $this->auth();
    $id = (int) $args['id'];
    return tcomments::i()->delete($id);
  }
  
  public function comment_setstatus($args) {
    $this->auth();
return tcomments::i()->setstatus((int) $args['id'], $args['status']);
  }
  
  public function comment_edit(array $args) {
    $this->auth();
$content = trim($args['content']);
if (empty($content)) return false;
return tcomments::i()->edit((int) $args['id'], $content);
  }
  
}//class