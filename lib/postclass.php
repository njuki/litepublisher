<?php

class TPost extends TItem {
  private $fComments;
  private $dateformater;
  
  public function GetBaseName() {
    return 'posts' . DIRECTORY_SEPARATOR . $this->id . DIRECTORY_SEPARATOR . 'index';
  }
  
  public static function &Instance($id = 0) {
    global $classes;
    $class = !empty($classes->classes['post']) ? $classes->classes['post'] : __class__;
    return parent::Instance($class, $id);
  }
  
  protected function CreateData() {
    global $Options;
    $this->Data= array(
    'parent' => 0,
    'author' => 0, //reserved, not used
    'date' => 0,
    'modified' => 0,
    'url' => '',
    'title' => '',
    'filtered' => '',
    'excerpt' => '',
    'rss' => '',
    'rawcontent' => '',
    'description' => '',
    'moretitle' => '',
    'categories' => array(0),
    'tags' => array(),
    'status' => 'published',
    'commentsenabled' => $Options->commentsenabled,
    'pingenabled' => $Options->pingenabled,
    'rssenabled' => true,
    'password' => '',
    'theme' => '',
    'pages' => array()
    );
  }
  
  public function &Getcomments() {
    if (!isset($this->fComments) ) {
      $this->fComments = &TComments::Instance($this->id);
    }
    return $this->fComments;
  }
  
  public function Getlink() {
    global $Options;
    return $Options->url . $this->url;
  }
  
  public function Setlink($link) {
    global $Options;
    if ($UrlArray = parse_url($link)) {
      $url = $UrlArray['path'];
      if (!empty($UrlArray['query'])) $url .= '?' . $UrlArray['query'];
      $this->url = $url;
    }
  }
  
  public function Getpubdate() {
    return date('r', $this->date);
  }
  
  public function Setpubdate($date) {
    $this->Data['date'] = strtotime($date);
  }
  
  //template
  public function Getcategorieslinks($divider = ', ') {
    $Categories = &TCategories::Instance();
    $Items= array();
    foreach ($this->Data['categories'] as  $id) {
      $Items[] = $Categories->GetLink($id);
    }
    return implode($divider, $Items);
  }
  
  public function Gettagslinks($divider = ', ') {
    $Tags= &TTags::Instance();
    $Items= array();
    foreach ($this->Data['tags'] as $id) {
      $Items[] = $Tags->GetLink($id);
    }
    return implode($divider, $Items);
  }
  
  public function Getlocaldate() {
    return TLocal::date($this->date);
  }
  
  public function Getdateformat() {
    if (!isset($this->dateformater)) $this->dateformater = new TDate($this->date);
    $this->dateformater->date = $this->date;
    return $this->dateformater;
  }
  
  public function Getmorelink() {
    global $Options;
    if ($this->moretitle == '') return '';
    return  "<a href=\"$Options->url$this->url#more-$this->id\" class=\"more-link\">$this->moretitle</a>";
  }
  
  public function Getkeywords() {
    return $this->Gettagnames();
  }
  
  
  public function Gettagnames() {
    if (count($this->tags) == 0) return '';
    $Tags = &TTags::Instance();
    return implode(', ', $Tags->GetNames($this->tags));
  }
  
  public function Settagnames($names) {
    $Tags = &TTags::Instance();
    $this->tags=  $Tags->CreateNames($names);
  }
  
  public function Getcatnames() {
    if (count($this->categories) == 0)  return array();
    $Categories = &TCategories::Instance();
    return $Categories->GetNames($this->categories);
  }
  
  public function Setcatnames($names) {
    $Categories = &TCategories::Instance();
    $this->categories = $Categories->CreateNames($names);
    if (count($this->categories ) == 0) $this->categories [] = $Categories->defaultid;
  }
  
  public function Request($id) {
    parent::Request($id);
    if ($this->status != 'published') return 404;
  }
  
  public function GetTemplateContent() {
    $Template = TTemplate::Instance();
    
    $GLOBALS['post'] = &$this;
    $tml = 'post.tml';
    if ($this->theme <> '') {
      if (@file_exists($Template->path . $this->theme)) $tml = $this->theme;
    }
    return $Template->ParseFile($tml);
  }
  
  public function Getcontent() {
    $Template = &TTemplatePost::Instance();
    $result = $Template->BeforePostContent($this->id);
    $Urlmap = &TUrlmap::Instance();
    if (($Urlmap->pagenumber == 1) && !(isset($this->Data['pages']) && (count($this->Data['pages']) > 0))) {
      $result .= $this->filtered;
    } else {
      if (isset($this->Data['pages'][$Urlmap->pagenumber - 1])) {
        $result .= $this->Data['pages'][$Urlmap->pagenumber - 1];
      } elseif ($Urlmap->pagenumber <= $this->commentpages) {
        //$result .= '';
      } else {
        $lang = &TLocal::Instance();
        $result .= $lang->notfound;
      }
    }
    $result .= $Template->AfterPostContent($this->id);
    return $result;
  }
  
  public function Setcontent($s) {
    if ($s <> $this->rawcontent) {
      $this->rawcontent = $s;
      $filter = TContentFilter::Instance();
      $filter->SetPostContent($this,$s);
    }
  }
  
  /*
  public function Getfiltered() {
    if (isset($this->Data['content']))return $this->Data['content'];
    return $this->Data['filtered'];
  }
  
  public function Setfiltered($s) {
    $this->Data['filtered'] = $s;
    if (isset($this->Data['content']))unset($this->Data['content']);
  }
  
  */
  public function SetData($data) {
    foreach ($data as $key => $value) {
      if (key_exists($key, $this->Data)) $this->Data[$key] = $value;
    }
  }
  
  public function Gethaspages() {
    return (isset($this->Data['pages']) && (count($this->Data['pages']) > 0)) || ($this->commentpages > 1);
  }
  
  public function Getpagescount() {
    return max($this->commentpages,  isset($this->Data['pages']) ? count($this->Data['pages']) : 0);
  }
  
  public function Getcommentpages() {
    global $Options;
    if (!$Options->commentpages) return 1;
    return ceil($this->comments->count / $Options->commentsperpage);
  }
  
  //db
  public function Getdbdate() {
    return date('Y-m-d H:i:s', $this->date);
  }
  
  public function Setdbdate($date) {
    $this->Data['date'] = strtotime($date);
  }
  
  public function Getdbmodified() {
    return date('Y-m-d H:i:s', $this->modified);
  }
  
  public function Setdbmodified($date) {
    $this->Data['modified'] = strtotime($date);
  }
  
  protected function db2data($res) {
    //$this->dbdate = $res->dbdate
  }
  
}//class

?>