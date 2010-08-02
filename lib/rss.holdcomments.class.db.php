<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class trssholdcomments extends tevents {
  public $url;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'rss.holdcomments';
    $this->url = '/rss/holdcomments.xml';
    $this->data['idurl'] = 0;
    $this->data['key'] = '';
    $this->data['count'] = 20;
    $this->data['template'] = '';
  }
  
  public function setkey($key) {
    if ($this->key != $key) {
      if ($key == '') {
        litepublisher::$classes->commentmanager->unsubscribeclass($self);
      } else {
        litepublisher::$classes->commentmanager->changed = $this->commentschanged;
      }
      $this->data['key'] = $key;
      $this->save();
    }
  }
  
  public function commentschanged($idpost) {
    litepublisher::$urlmap->setexpired($this->idurl);
  }
  
  protected function getrssurl() {
    return $this->url . litepublisher::$options->q . 'key=' . urlencode($this->key);
  }
  
  public function request($arg) {
    if (isset($_GET['key']) && ($this->key != '') && ($this->key == $_GET['key'])) {
      $result = turlmap::xmlheader();
      $rss = trss::instance();
      $rss->domrss = new Tdomrss;
      $this->dogetholdcomments($rss);
      $result .= $rss->domrss->GetStripedXML();
      return $result;
    }
    return 404;
  }
  
  private function dogetholdcomments($rss) {
    $rss->domrss->CreateRoot(litepublisher::$options->url . $this->rssurl, tlocal::$data['comment']['onrecent'] . ' '. litepublisher::$options->name);
    $manager = tcommentmanager::instance();
    $recent = $manager->getrecent($this->count, 'hold');
    var_dump($recent);
    $title = tlocal::$data['comment']['onpost'] . ' ';
    $a = array();
    $comment = new tarray2prop($a);
    ttheme::$vars['comment'] = $comment;
    $theme = ttheme::instance();
    $tml = $this->template;
    if ($tml == '') {
      $html = THtmlResource ::instance();
      $html->section = 'comments';
      $tml = $html->rsstemplate;
    }
    $tml = str_replace('$adminurl', '/admin/comments/'. litepublisher::$options->q . 'id=$comment.id&action=', $tml);
    tlocal::load('admin');
    $lang = tlocal::instance('comments');
    foreach ($recent  as $item) {
      $comment->array = $item;
      $comment->content = $theme->parse($tml);
      $rss->AddRSSComment($comment, $title . $comment->title);
    }
  }
  
}//class
?>