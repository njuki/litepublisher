<?php

class TTemplateComment extends TEventClass {
  public $templ;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'templatecomment';
    $this->AddDataMap('templ', array());
  }
  
  public function ThemeChanged() {
    $this->templ = array();
    $template = TTemplate::instance();
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
    $CommentForm = TCommentForm::instance();
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
    global $post, $options;
    $comments = $post->comments;
    $CountStr = $this->GetCommentCountStr($comments->GetCountApproved());
    $url = $post->haspages ? rtrim($post->url, '/') . "/page/$post->countpages/" : $post->url;
    return "<a href=\"$options->url$url#comments\">$CountStr</a>";
  }
  
  public function GetComments($tagname) {
    global $post, $template, $urlmap, $options;
    $comments = $post->comments;
    if (($comments->count == 0) && !$post->commentsenabled) return '';
    if ($post->haspages && ($post->commentpages < $urlmap->page)) return $this->GetCommentsCountLink('');
    $lang = tlocal::instance('comment');
    $result = '';
    $comment = new TComment($comments);
    $from = $options->commentpages  ? ($urlmap->page - 1) * $options->commentsperpage : 0;
if (dbversion) {
$c = $comments->db->getcount("post = $post->id and status = 'approved' and pingback = false");
    $count = $this->GetCommentCountStr($c);
$db = $comments->db;
$items = $db->queryassoc("select $db->comments.*, $db->comusers.name, $db->comusers.email, $db->comusers.url from $db->comments, $db->comusers
where $db->comments.post = $post->id and $db->comments.status = 'approved' and $db->comments.pingback = false and $db->comusers.id = $db->comments.author
sort by $db->comments.posted asc limit $from, $options->commentsperpage");
} else {
    $items = $comments->getapproved();
    $count = $this->GetCommentCountStr(count($items));
    if ($options->commentpages ) {
      $items = array_slice($items, $from, $options->commentsperpage, true);
    }
}
    if (count($items)  > 0) {
      eval('$result .= "'. $this->templ['count'] . '";');
      $result .= $this->GetcommentsList($items, $comment, '', $from);
    }
    
    if ($urlmap->page == 1) {
      $items = $comments->getapproved('pingback');
      if (count($items) > 0) {
        $list = '';
        $comtempl = $this->templ['pingback'];
        foreach  ($items as $id) {
          $comment->id = $id;
          eval('$list .= "'. $comtempl  . '"; ');
        }
        $pingbacks = str_replace('%1$', '%1\$', $this->templ['pingbacks']);
        $pingbacks = str_replace('%2$', '%2\$', $pingbacks);
        eval('$pingbacks = "'. $pingbacks . '";');
        
        $result .= sprintf($pingbacks, $list, 1);
      }
    }
    if (!$options->commentsdisabled && $post->commentsenabled) {
      $result .=  "<?php  echo TCommentForm::PrintForm($post->id); ?>\n";
    } else {
      eval('$result .= "'. $this->templ['closed'] . '";');
    }
    return $result;
  }
  
  private function GetCommentsList(array &$items, &$comment, $hold, $from) {
    global $options, $post, $template;
    $lang = TLocal::instance('comment');
    $result = '';
    $comtempl = $this->templ['comment'];
    $class1 = $this->templ['class1'];
    $class2 = $this->templ['class2'];
    $i = 1;

    foreach  ($items as $id) {
if (dbversion)  {
      $comment->data = $id;
} else {
      $comment->id = $id;
}
      $class = (++$i % 2) == 0 ? $class1 : $class2;
      eval('$result .= "'. $comtempl . '\n"; ');
    }
    
    return sprintf($this->templ['comments'], $result, $from + 1);
  }
  
  public function GetHoldList(&$items, $postid) {
    if (count($items) == 0) return '';
    $comments = tcomments::instance($postid);
    $comment = new TComment($comments);
    $lang = TLocal::instance('comment');
    eval('$hold = "'. $this->templ['hold'] . '";');
    return $this->GetCommentsList($items, $comment, $hold, 0);
  }
  
} //class
?>