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
    global $post, $Template, $Urlmap, $Options;
    $comments = &$post->comments;
    if (($comments->count == 0) && !$post->commentsenabled) return '';
    if ($post->haspages && ($post->commentpages < $Urlmap->pagenumber)) return $this->GetCommentsCountLink('');
    $lang = &TLocal::Instance();
    $lang->section = 'comment';
    
    $result = '';
    $comment = &new TComment($comments);
    $items = &$comments->GetApproved();
    $count = $this->GetCommentCountStr(count($items));
    $items = array_slice($items, ($Urlmap->pagenumber - 1) * $Options->commentsperpage, $Options->commentsperpage, true);
    if (count($items)  > 0) {
      eval('$result .= "'. $this->commentsini['count'] . '\n";');
      $hold = '';
      $list = '';
      $comtempl = $this->commentsini['comment'];
      foreach  ($items as $id => $date) {
        $comment->id = $id;
        eval('$list .= "'. $comtempl . '\n"; ');
      }
      $result .= sprintf($this->commentsini['list'], $list);
      $result .= "\n";
    }
    
    if ($Urlmap->pagenumber == 1) {
      $items = &$comments->GetApproved('pingback');
      if (count($items) > 0) {
        eval('$result .= "'. $this->commentsini['pingbackhead'] . '\n";');
        $list = '';
        $comtempl = $this->commentsini['pingback'];
        foreach  ($items as $id => $date) {
          $comment->id = $id;
          eval('$list .= "'. $comtempl  . '\n"; ');
        }
        $result .= sprintf($this->commentsini['list'] , $list);
        $result .= "\n";
      }
    }
    if ($post->commentsenabled) {
      $result .=  "<?php  echo TCommentForm::PrintForm($post->id); ?>\n";
    } else {
      $result .= $this->commentsini['closed'];
    }
    return $result;
  }
  
  public function GetHoldList(&$items, &$comment) {
    $lang = &TLocal::Instance();
    $lang->section = 'comment';
    $result = '';
    if (count($items) > 0) {
      $hold = $lang->hold;
      $list = '';
      foreach  ($items as $id => $date) {
        $comment->id = $id;
        eval('$list .= "'. $this->commentsini['comment'] . '\n"; ');
      }
      eval('$result .= "'. $this->commentsini['list'] . '\n"; ');
    }
    return $result;
  }
  
  public function GenerateCommentForm() {
    global $Options;
    $CommentForm = &TCommentForm::Instance();
    $lang = &TLocal::Instance();
    $lang->section = 'comment';
    eval('$result = "'. $this->commentsini['formheader'] . '\n";');
    $result .= "\n<form action=\"$Options->url$CommentForm->url\" method=\"post\" id=\"commentform\">\n";
    
    $tabindex = 1;
    $TemplateField = $this->commentsini['field'];
    foreach ($CommentForm->Fields as $field => $type) {
    $value = "{\$values['$field']}";
      $label = $lang->$field;
      if ($type == 'checkbox') {
        eval('$result .= "'. $this->commentsini['checkbox'] . '\n";');
      } else {
        eval('$result .= "'. $TemplateField . '\n";');
      }
      
      $tabindex++;
    }
    
    eval('$result .= "'. $this->commentsini['content'] .'\n"; ');
    $tabindex++;
    
    $TemplateField = '<input type=\"hidden\" name=\"$field\" value=\"$value\" />';
    foreach ($CommentForm->Hidden as $field => $default) {
    $value = "{\$values['$field']}";
      eval("\$result .= \"$TemplateField\n\";");
    }
    
    eval('$result .= "'. $this->commentsini['button'] . '"; ');
    $result .= "\n</form>\n";
    eval('$result .= "'. $this->commentsini['formfooter'] . '"; ');
    return $result;
  }
  
} //class
?>