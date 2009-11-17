<?php

class ttemplatePosts extends tevents {
  public $ps; //postscript text
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'templateposts';
    $this->addevents('BeforePostContent', 'AfterPostContent', 'Onpostscript');
  }
  
  public function GetPostscript($tagname) {
    global $classes, $post;
    $this->ps = '';
    if (is_a($post, $classes->classes['post'])) $this->ps .= $this->GetPostFooter($post);
    $this->ps .= $this->Onpostscript($post->id);
    return $this->ps;
  }
  
  private function GetPostFooter(tpost $post) {
    global $urlmap;
    $result = '';
    if ($post->haspages) {
$theme = ttheme::instance();
$result .= $theme->getpages($post->url, $urlmap->page, $post->countpages);
}
    if ($post->commentsenabled && ($post->commentscount > 0)) {
      $lang = tlocal::instance();
      $result .= "<p><a href=\"$post->rsslink\">$lang->commentsrss</a></p>\n";
    }
    
    $result .= $this->GetPrevNextLinks($post);
    return $result;
  }
  
  public function GetPrevNextLinks(tpost $post) {
    $result = '';
    $lang = &TLocal::instance();
    if ($prevpost = $post->prev) {
      $result .= "$lang->prev <a rel=\"prev\" href=\"$prevpost->link\">$prevpost->title</a>";
    }
    
    if ($nextpost = $post->next) {
      if ($result != '') $result .= ' | ';
      $result .= "$lang->next <a rel=\"next\" href=\"$nextpost->link\">$nextpost->title</a>";
    }
    
    if ($result != '') $result = "<p>$result</p>\n";
    return $result;
  }
  
  public function getitems(array &$Items) {
$theme = ttheme::instance();
        if (count($Items) == 0) return $theme->notfound;
    $Result = '';
    foreach($Items as $id) {
      $GLOBALS['post'] = &TPost::instance($id);
      $Result .=  $theme->parse($theme->excerpt);
    }
        return $Result;
  }
  
  public function getliteitems(array &$Items) {
    global $options;
$theme = ttheme::instance();
    if (count($Items) == 0) return $theme->notfound;

    $result = '<p>'. TLocal::$data['default']['archivelist'] ." </p>\n<ul>\n";
    foreach($Items as $id) {
      $post = tpost::instance($id);
      $result .= "<li>$post->localdate <a href=\"$options->url$post->url\">$post->title</a></li>\n";
    }
    $result .= "</ul>\n";
    return $result;
  }
  
}//class

?>