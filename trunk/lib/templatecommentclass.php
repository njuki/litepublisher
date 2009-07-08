<?php

class TTemplateComment extends TEventClass {
 public $commentsini;
 
 protected function CreateData() {
  global $Urlmap;
  parent::CreateData();
  $this->basename = 'templatecomment' . ($Urlmap->Ispda ? '.pda'  : '');
  $this->AddDataMap('commentsini', array());
 }
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 public function ThemeChanged() {
  global $Template;
  $this->commentsini     = parse_ini_file($Template->path . 'comments.ini');
  foreach ($this->commentsini  as $name => $value) {
   $this->commentsini [$name] = str_replace("'", '\"', $value);
  }
  
  $this->Save();
 }
 
 public function Load() {
  parent::Load();
  if (count($this->commentsini ) == 0) {
   $Template = &TTemplate::Instance();
   $this->ThemeChanged();
  }
 }
 
 public function GetCommentCountStr($count) {
  switch($count) {
   case 0: return TLocal::$data['comment'][0];
   case 1: return TLocal::$data['comment'][1];
   default: return sprintf(TLocal::$data['comment'][2], $count);
  }
 }
 
 public function GetCommentsCountLink($tagname) {
  global $post, $Options;
  $comments = &$post->comments;
  $CountStr = $this->GetCommentCountStr($comments->GetCountApproved());
  $url = $post->haspages ? rtrim($post->url, '/') . "/page/$post->pagescount/" : $post->url;
  return "<a href=\"$Options->url$url#comments\">$CountStr</a>";
 }
 
 public function GetComments($tagname) {
  global $post, $Template, $Urlmap;
  $comments = &$post->comments;
  if (($comments->count == 0) && !$post->commentsenabled) return '';
  if ($post->haspages && ($Urlmap->pagenumber != $post->pagescount)) return $this->GetCommentsCountLink('');
  $lang = &TLocal::Instance();
  $lang->section = 'comment';
  
  $Result = '';
  $comment = &new TComment($comments);
  $items = &$comments->GetApproved();
  if (count($items)  > 0) {
   $count = $this->GetCommentCountStr(count($items));
   eval('$Result .= "'. $this->commentsini['count'] . '\n";');
   $hold = '';
   $list = '';
   $comtempl = $this->commentsini['comment'];
   foreach  ($items as $id => $date) {
    $comment->id = $id;
    eval('$list .= "'. $comtempl . '\n"; ');
   }
   eval('$Result .= "'. $this->commentsini['list'] . '\n"; ');
  }
  
  $items = &$comments->GetApproved('pingback');
  if (count($items) > 0) {
   eval('$Result .= "'. $this->commentsini['pingbackhead'] . '\n";');
   $list = '';
   $comtempl = $this->commentsini['pingback'];
   foreach  ($items as $id => $date) {
    $comment->id = $id;
    eval('$list .= "'. $comtempl  . '\n"; ');
   }
   eval('$Result .= "'. $this->commentsini['list'] . '\n"; ');
  }
  if ($post->commentsenabled) {
   $Result .=  "<?php  echo TCommentForm::PrintForm($post->id); ?>\n";
  } else {
   $Result .= $this->commentsini['closed'];
  }
  return $Result;
 }
 
 public function GetHoldList(&$items, &$comment) {
  $lang = &TLocal::Instance();
  $lang->section = 'comment';
  $Result = '';
  if (count($items) > 0) {
   $hold = $lang->hold;
   $list = '';
   foreach  ($items as $id => $date) {
    $comment->id = $id;
    eval('$list .= "'. $this->commentsini['comment'] . '\n"; ');
   }
   eval('$Result .= "'. $this->commentsini['list'] . '\n"; ');
  }
  return $Result;
 }
 
 public function GenerateCommentForm() {
  global $Options;
  $CommentForm = &TCommentForm::Instance();
  $lang = &TLocal::Instance();
  $lang->section = 'comment';
  eval('$Result = "'. $this->commentsini['formhead'] . '"; ');
  $Result .= "\n<form action=\"$Options->url$CommentForm->url\" method=\"post\" id=\"commentform\">\n";
  
  $tabindex = 1;
  $TemplateField = $this->commentsini['field'];
  foreach ($CommentForm->Fields as $field => $type) {
  $value = "{\$values['$field']}";
   $label = $lang->$field;
   if ($type == 'checkbox') {
    eval('$Result .= "'. $this->commentsini['checkbox'] . '\n";');
   } else {
    eval('$Result .= "'. $TemplateField . '\n";');
   }
   
   $tabindex++;
  }
  
  eval('$Result .= "'. $this->commentsini['content'] .'\n"; ');
  $tabindex++;
  
  $TemplateField = '<input type=\"hidden\" name=\"$field\" value=\"$value\" />';
  foreach ($CommentForm->Hidden as $field => $default) {
  $value = "{\$values['$field']}";
   eval("\$Result .= \"$TemplateField\n\";");
  }
  
  eval('$Result .= "'. $this->commentsini['button'] . '"; ');
  $Result .= "\n</form>\n";
  return $Result;
 }
 
} //class
?>