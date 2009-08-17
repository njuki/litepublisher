<?php

class TTemplatePost extends TEventClass {
  public $ps; //postscript text
  
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  protected function CreateData() {
    parent::CreateData();
    $this->basename = 'templatepost';
    $this->AddEvents('BeforePostContent', 'AfterPostContent', 'Onpostscript');
  }
  
  public function GetPostscript($tagname) {
    global $Options, $Urlmap, $post;
    $lang = &TLocal::Instance();
    $this->ps = '';
    if (is_a($post, 'TPost')) {
      //pages
      if ($post->haspages) {
        $this->ps .= $this->PrintNaviPages($post->url, $Urlmap->pagenumber, $post->pagescount);
      }
      if ($post->commentsenabled && ($post->comments->count > 0)) {
        $this->ps .= "<p><a href=\"$Options->url/comments/$post->id/\">$lang->commentsrss</a></p>\n";
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
      
      if ($links != '') $this->ps .= "<p>$links</p>\n";
    }
    $this->ps .= $this->Onpostscript($post->id);
    return $this->ps;
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
    $result = '<p>';
    $result .= $page== 1 ? '1' : "<a href=\"$Options->url$url\">1</a>";
    $url = rtrim($url, '/');
    for ($i = 2; $i <= $count; $i++) {
      if ($i != $page) {
        $result .= "|<a href=\"$Options->url$url/page/$i/\">$i</a>";
      } else {
        $result .= "|$i";
      }
    }
    
    $result .= "</p>\n";
    return $result;
  }
  
}//class

?>