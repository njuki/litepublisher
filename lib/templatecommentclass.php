<?php

class TTemplateComment extends TEventClass {
  public $templ;

  public static function &Instance() {
    return GetNamedInstance('templatecomment', __class__);
  }
  
  protected function CreateData() {
    parent::CreateData();
$urlmap = TUrlmap::Instance();
    $this->basename = 'templatecomment' . ($urlmap->Ispda ? '.pda'  : '');
    $this->AddDataMap('templ', array());
  }
  
  public function ThemeChanged() {
    $this->templ = array();
    $template = TTemplate::Instance();
$s = file_get_contents($template->path . 'comments.tml');
$comments = $template->parsetml($s, 'comments', '');
$count= $template->parsetml($comments, 'count', '');
$this->templ['count'] = str_replace('"', '\"', ltrim($count));

$comment = $template->parsetml($comments, 'comment', '%1$s');
$this->templ['comments'] = $comments;
$this->templ['class1'] = $template->parsetml($comment, 'class1', '$class');
$this->templ['class2'] = $template->parsetml($comment, 'class2', '');
$this->templ['hold'] = $template->parsetml($comment, 'hold', '$hold');
$this->templ['comment'] = str_replace('"', '\"', ltrim($comment));

$pingbacks = str_replace('"', '\"', $template->parsetml($s, 'pingbacks', ''));
$this->templ['pingback'] = $template->parsetml($pingbacks, 'pingback', '%1$s');
$this->templ['pingbacks'] = $pingbacks;

$this->templ['closed'] = str_replace('"', '\"', $template->parsetml($s, 'closed', ''));
$CommentForm = TCommentForm::Instance();
$CommentForm->form = $template->parsetml($s, 'form', '');
    $this->save();
  }
  
  public function load() {
    parent::load();
    if (count($this->templ) == 0) $this->ThemeChanged();
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
    $comments = $post->comments;
    $CountStr = $this->GetCommentCountStr($comments->GetCountApproved());
    $url = $post->haspages ? rtrim($post->url, '/') . "/page/$post->pagescount/" : $post->url;
    return "<a href=\"$Options->url$url#comments\">$CountStr</a>";
  }
  
  public function GetComments($tagname) {
    global $post, $Template, $Urlmap, $Options;
    $comments = $post->comments;
    if (($comments->count == 0) && !$post->commentsenabled) return '';
    if ($post->haspages && ($post->commentpages < $Urlmap->pagenumber)) return $this->GetCommentsCountLink('');
    $lang = TLocal::Instance('comment');
        $result = '';
    $comment = &new TComment($comments);
    $items = &$comments->GetApproved();
    $count = $this->GetCommentCountStr(count($items));
    $from = 0;
    if ($Options->commentpages ) {
      $from = ($Urlmap->pagenumber - 1) * $Options->commentsperpage;
      $items = array_slice($items, $from, $Options->commentsperpage, true);
    }
    if (count($items)  > 0) {
      eval('$result .= "'. $this->templ['count'] . '";');
      $result .= $this->GetcommentsList($items, $comment, '', $from);
    }
    
    if ($Urlmap->pagenumber == 1) {
      $items = &$comments->GetApproved('pingback');
      if (count($items) > 0) {
        $list = '';
        $comtempl = $this->templ['pingback'];
        foreach  ($items as $id => $date) {
          $comment->id = $id;
          eval('$list .= "'. $comtempl  . '"; ');
        }
eval('$pingbacks = "'. $this->templ['pingbacks'] . '";');
        $result .= sprintf($pingbacks, $list);
      }
    }
    if (!$Options->commentsdisabled && $post->commentsenabled) {
      $result .=  "<?php  echo TCommentForm::PrintForm($post->id); ?>\n";
    } else {
      eval('$result .= "'. $this->templ['closed'] . '"';);
    }
    return $result;
  }
  
 private function GetCommentsList(&$items, &$comment, $hold, $from) {
    global $Options, $post, $Template;
    $lang = TLocal::Instance('comment');
        $result = '';
    $comtempl = $this->templ['comment'];
    $class1 = $this->templ['class1'];
    $class2 = $this->templ['class2'];
    $i = 1;
    foreach  ($items as $id => $date) {
      $comment->id = $id;
      $class = (++$i % 2) == 0 ? $class1 : $class2;
      eval('$result .= "'. $comtempl . '\n"; ');
    }
    
    return sprintf($this->templ['comments'], $result, $from + 1);
  }
  
  public function GetHoldList(&$items, $postid) {
    if (count($items) == 0) return '';
    $comments = TComments::Instance($postid);
    $comment = new TComment($comments);
    $lang = TLocal::Instance('comment');
eval('$hold = "'. $this->templ['hold'] . '";');
    return $this->GetCommentsList($items, $comment, $hold, 0);
  }

} //class
?>