<?php

class TPost extends TItem {
 private $fComments;
 
 public function GetBaseName() {
  return 'posts' . DIRECTORY_SEPARATOR . $this->id . DIRECTORY_SEPARATOR . 'index';
 }
 
 public static function &Instance($id = 0) {
  return parent::Instance(__class__, $id);
 }
 
 protected function CreateData() {
  global $Options;
  $this->Data= array(
  'author' => 0, //reserved, not used
  'date' => 0,
  'modified' => 0,
  'title' => '',
  'url' => '',
  'content' => '',
  'excerpt' => '',
  'moretitle' => '',
  'rss' => '',
  'rawcontent' => '',
  'description' => '',
  'categories' => array(0),
  'tags' => array(),
  'status' => 'published',
  'commentsenabled' => $Options->commentsenabled,
  'pingenabled' => $Options->pingenabled,
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
  //return date('d.m.Y H:i', $this->date);
  return TLocal::date($this->date);
 }
 
 public function Getmorelink() {
  global $Options;
  if ($this->moretitle == '') return '';
  return  "<a href=\"$Options->url$this->url#more-$this->id\" class=\"more-link\">$this->moretitle</a>";
 }
 
 public function Getkeywords() {
  return $this->Gettagnames();
 }
 
 //xmlrpc
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
 
 public function GetTemplateContent() {
  global $Template;
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
  if (($Urlmap->pagenumber != 1) && $this->haspages) {
   if (isset($this->Data['pages'][$Urlmap->pagenumber - 1])) {
    $result .= $this->Data['pages'][$Urlmap->pagenumber - 1];
   } else {
    $lang = &TLocal::Instance();
    $result .= $lang->notfound;
   }
  } else {
   $result .= $this->Data['content'];
  }
  $result .= $Template->AfterPostContent($this->id);
  return $result;
 }
 
 public function Setcontent($s) {
  if ($s <> $this->rawcontent) {
   $this->rawcontent = $s;
   $ContentFilter = &TContentFilter::Instance();
   $ContentFilter->SetPostContent($this,$s);
  }
 }
 
 public function Getoutputcontent() {
  return $this->Data['content'];
 }
 
 public function SetOutputContent($s) {
  $this->Data['content'] = $s;
 }
 
 public function SetData($data) {
  foreach ($data as $key => $value) {
   if (key_exists($key, $this->Data)) $this->Data[$key] = $value;
  }
 }
 
 
 public function Gethaspages() {
  return isset($this->Data['pages']) && (count($this->Data['pages']) > 0);
 }
 
 public function Getpagescount() {
  return count($this->Data['pages']);
 }
 
}//class

?>