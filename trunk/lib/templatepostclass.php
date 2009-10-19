<?php

class TTemplatePost extends TEventClass {
  public $ps; //postscript text
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function CreateData() {
    parent::CreateData();
    $this->basename = 'templatepost';
    $this->AddEvents('BeforePostContent', 'AfterPostContent', 'Onpostscript');
  }
  
  public function GetPostscript($tagname) {
    global $classes, $post;
    $this->ps = '';
    if (is_a($post, $classes->classes['post'])) $this->ps .= $this->GetPostFooter($post);
    $this->ps .= $this->Onpostscript($post->id);
    return $this->ps;
  }
  
  private function GetPostFooter(&$post) {
    global $Urlmap;
    $result = '';
    if ($post->haspages) $result .= $this->PrintNaviPages($post->url, $Urlmap->pagenumber, $post->countpages);
    if ($post->commentsenabled && ($post->commentscount > 0)) {
      $lang = tlocal::instance();
      $result .= "<p><a href=\"$post->rsslink\">$lang->commentsrss</a></p>\n";
    }
    
    $result .= $this->GetPrevNextLinks($post);
    return $result;
  }
  
  public function GetPrevNextLinks(&$post) {
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
  
  public function PrintPosts(&$Items) {
    $Template = TTemplate::instance();
    
    if (count($Items) == 0) {
      $lang = &TLocal::instance();
      return 		"<h2 class=\"center\">$lang->notfound </h2>\n<p class=\"center\">$lang->nocontent</p>";
    }
    
    $Result = '';
    foreach($Items as $id) {
      $GLOBALS['post'] = &TPost::instance($id);
      $Result .=  $Template->ParseFile('postexcerpt.tml');
    }
    
    return $Result;
  }
  
  public function LitePrintPosts(&$Items) {
    global $Options;
    if (count($Items) == 0) {
      $lang = &TLocal::instance();
      return 		"<h2 class=\"center\">$lang->notfound </h2>\n<p class=\"center\">$lang->nocontent</p>";
    }
    
    $result = '<p>'. TLocal::$data['default']['archivelist'] ." </p>\n<ul>\n";
    foreach($Items as $id) {
      $post = TPost::instance($id);
      $result .= "<li>$post->localdate <a href=\"$Options->url$post->url\">$post->title</a></li>\n";
    }
    $result .= "</ul>\n";
    return $result;
  }
  
  public function PrintNaviPages($url, $page, $count) {
    global  $Options;
    if (!(($count > 1) && ($page >=1) && ($page <= $count)))  return '';
    $Template = TTemplate::instance();
    //подготовка шаблонов ссылок
    $navi =isset($Template->theme['navilinks']['navi']) ? $Template->theme['navilinks']['navi'] : '<p>%s</p>';
    $link =isset($Template->theme['navilinks']['link']) ? $Template->theme['navilinks']['link'] : '<a href="%1$s">%2$s</a>';
    $current= isset($Template->theme['navilinks']['current']) ? $Template->theme['navilinks']['current'] : '%2$s';
    $separator =isset($Template->theme['navilinks']['separator']) ? $Template->theme['navilinks']['separator'] : ' | ';
    
    $suburl = rtrim($url, '/');
    $a = array();
    for ($i = 1; $i <= $count; $i++) {
      $pageurl = $i == 1 ? $Options->url . $url : "$Options->url$suburl/page/$i/";
      $a[] = sprintf($i == $page ? $current : $link, $pageurl, $i);
    }
    
    $result = implode($separator, $a);
    $result = sprintf($navi, $result);
    return $result;
  }
  
}//class

?>