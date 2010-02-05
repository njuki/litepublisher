<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tspamfilter extends tevents {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'spamfilter';
  }
  
  public function createstatus($idauthor, $content) {
    global $options;
    if ($options->DefaultCommentStatus == 'approved') return 'approved';
    $manager = tcommentmanager::instance();
    if ($manager->trusted($idauthor)) return  'approved';
    return 'hold';
  }
  
  public function canadd($idauthor) {
    return true;
  }
  
  public function checkduplicate($idpost, $content) {
    $comments = tcomments::instance($idpost);
    $content = trim($content);
    if (dbversion) {
      $hash = md5($content);
      return $comments->raw->findid("hash = '$hash'");
    } else {
      return $comments->raw->IndexOf('content', $content) > 0;
    }
  }
  
}//class

?>