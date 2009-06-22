<?php

class TTemplateComment extends TEventClass {
 public $ThemeComments;
 
 public function GetBaseName() {
  return 'templatecomment';
 }
 
 public static function &Instance() {
  return GetInstance(__class__);
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
  return "<a href=\"$Options->url$post->url#comments\">$CountStr</a>";
 }
 
 public function CheckThemeComments() {
  if (!isset($this->ThemeComments )) {
   $Template = &TTemplate::Instance();
   $this->ThemeComments     = parse_ini_file($Template->path . 'comments.ini');
   foreach ($this->ThemeComments  as $name => $value) {
    $this->ThemeComments [$name] = str_replace("'", '\"', $value);
   }
  }
 }
 
 public function GetComments($tagname) {
  global $post, $Template;
  $comments = &$post->comments;
  if (($comments->count == 0) && !$post->commentsenabled) return '';
  $this->CheckThemeComments();
  
  $Result = '';
  $comment = &new TComment($comments);
  $items = &$comments->GetApproved();
  if (count($items)  > 0) {
   $count = $this->GetCommentCountStr(count($items));
   eval('$Result .= "'. $this->ThemeComments['count'] . '\n";');
   $hold = '';
   $list = '';
   foreach  ($items as $id => $date) {
    $comment->id = $id;
    //$class =  ($count % 2) == 0 ? 'alt' : '';
    //$class = 'alt';
    
    eval('$list .= "'. $this->ThemeComments['comment'] . '\n"; ');
   }
   eval('$Result .= "'. $this->ThemeComments['list'] . '\n"; ');
  }
  
  $items = &$comments->GetApproved('pingback');
  if (count($items) > 0) {
   eval('$Result .= "'. $this->ThemeComments['pingbackhead'] . '\n";');
   $list = '';
   foreach  ($items as $id => $date) {
    $comment->id = $id;
    eval('$list .= "'. $this->ThemeComments['pingback'] . '\n"; ');
   }
   eval('$Result .= "'. $this->ThemeComments['list'] . '\n"; ');
  }
  if ($post->commentsenabled) {
   $Result .=  "<?php  echo TCommentForm::PrintForm($post->id); ?>\n";
  } else {
   $Result .= $this->CommentTemplate['closed'];
  }
  return $Result;
 }
 
 public function GetHoldList(&$items, &$comment) {
$lang = &TLocal::Instance();  
$lang->section = 'comment';
  $this->CheckThemeComments();
  $Result = '';
  if (count($items) > 0) {
   $hold = $lang->hold;
   $list = '';
   foreach  ($items as $id => $date) {
    $comment->id = $id;
    eval('$list .= "'. $this->ThemeComments['comment'] . '\n"; ');
   }
   eval('$Result .= "'. $this->ThemeComments['list'] . '\n"; ');
  }
  return $Result;
 }
 
 public function GenerateCommentForm() {
  global $Options;
  $CommentForm = &TCommentForm::Instance();
$lang = &TLocal::Instance();    
$lang->section = 'comment';
  $this->CheckThemeComments();
  eval('$Result = "'. $this->ThemeComments['formhead'] . '"; ');
  $Result .= "\n<form action=\"$Options->url$CommentForm->url\" method=\"post\" id=\"commentform\">\n";
  
  $tabindex = 1;
  $TemplateField = $this->ThemeComments['field'];
  foreach ($CommentForm->Fields as $field => $type) {
   $value = "\$values[$field]";
   $label = $lang->$field;
   eval("\$Result .= \"$TemplateField\n\";");
   $tabindex++;
  }
  
  eval('$Result .= "'. $this->ThemeComments['content'] .'\n"; ');
  $tabindex++;
  
  $TemplateField = '<input type=\"hidden\" name=\"$field\" value=\"$value\" />';
  foreach ($CommentForm->Hidden as $field => $default) {
   $value = "\$values[$field]";
   eval("\$Result .= \"$TemplateField\n\";");
  }
  
  eval('$Result .= "'. $this->ThemeComments['button'] . '"; ');
  $Result .= "\n</form>\n";
  return $Result;
 }
 
} //class
?>