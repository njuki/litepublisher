<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tspamfilter extends tevents {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'spamfilter';
    $this->addevents('is_spamer', 'onstatus');
  }
  
  public function createstatus($idpost, $idauthor, $content, $ip) {
    $status = $this->onstatus($idpost, $idauthor, $content, $ip);
    if (false ===  $status) return false;
    if ($status == 'spam') return false;
    if (($status == 'hold') || ($status == 'approved')) return $status;
    if (!litepublisher::$options->filtercommentstatus) return litepublisher::$options->DefaultCommentStatus;
    if (litepublisher::$options->DefaultCommentStatus == 'approved') return 'approved';
    $manager = tcommentmanager::i();
    if ($manager->trusted($idauthor)) return  'approved';
    return 'hold';
  }
  
  public function canadd($idauthor) {
    if ($this->is_spamer($idauthor)) return false;
    return true;
  }
  
  public function checkduplicate($idpost, $content) {
    $comments = tcomments::i($idpost);
    $content = trim($content);
    if (dbversion) {
      $hash = basemd5($content);
      return $comments->raw->findid("hash = '$hash'");
    } else {
      return $comments->raw->IndexOf('content', $content);
    }
  }
  
}//class

?>