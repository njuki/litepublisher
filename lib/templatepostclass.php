<?php

class TTemplatePost extends TEventClass {
  public $ps; //postscript text
  
  public static function &Instance() {
    return GetNamedInstance('templatepost', __class__);
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
    global $Options, $Urlmap;
    $result = '';
    $lang = &TLocal::Instance();
    //pages
    if ($post->haspages) $result .= $this->PrintNaviPages($post->url, $Urlmap->pagenumber, $post->pagescount);
    if ($post->commentsenabled && ($post->comments->count > 0)) {
      $result .= "<p><a href=\"$Options->url/comments/$post->id/\">$lang->commentsrss</a></p>\n";
    }
    
    //prev and next post
    $links = '';
    $posts = &TPosts::Instance();
    $keys = array_keys($posts->archives);
    $i = array_search($post->id, $keys);
    if ($i < count($keys) -1) {
      $prevpost = &TPost::Instance($keys[$i + 1]);
      $links .= "$lang->prev <a href=\"$Options->url$prevpost->url\">$prevpost->title</a>";
    }
    
    if ($i > 0) {
      $nextpost = &TPost::Instance($keys[$i - 1]);
      if ($links != '') $links .= ' | ';
      $links .= "$lang->next <a href=\"$Options->url$nextpost->url\">$nextpost->title</a>";
    }
    
    if ($links != '') $result .= "<p>$links</p>\n";
    
    return $result;
  }
  
  public function PrintPosts(&$Items) {
    $Template = TTemplate::Instance();
    
    if (count($Items) == 0) {
      $lang = &TLocal::Instance();
      return 		"<h2 class=\"center\">$lang->notfound </h2>\n<p class=\"center\">$lang->nocontent</p>";
    }
    
    $Result = '';
    foreach($Items as $id) {
      $GLOBALS['post'] = &TPost::Instance($id);
      $Result .=  $Template->ParseFile('postexcerpt.tml');
    }
    
    return $Result;
  }
  
  public function LitePrintPosts(&$Items) {
    global $Options;
    if (count($Items) == 0) {
      $lang = &TLocal::Instance();
      return 		"<h2 class=\"center\">$lang->notfound </h2>\n<p class=\"center\">$lang->nocontent</p>";
    }
    
    $result = '<p>'. TLocal::$data['default']['archivelist'] ." </p>\n<ul>\n";
    foreach($Items as $id) {
      $post = TPost::Instance($id);
      $result .= "<li>$post->localdate <a href=\"$Options->url$post->url\">$post->title</a></li>\n";
    }
    $result .= "</ul>\n";
    return $result;
  }
  
  public function PrintNaviPages($url, $page, $count) {
    global  $Options;
    if (!(($count > 1) && ($page >=1) && ($page <= $count)))  return '';
    $Template = TTemplate::Instance();
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