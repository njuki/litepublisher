<?php

class ttemplatecomment extends TEventClass {

  public static function instance() {
    return getinstance(__class__);
  }

public function load() {}
public function save() {}  

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
  
  public function getcomments(tpost $post) {
    global $template, $urlmap, $options;
    $comments = $post->comments;
    if (($comments->count == 0) && !$post->commentsenabled) return '';
    if ($post->haspages && ($post->commentpages < $urlmap->page)) return $this->GetCommentsCountLink('');
$theme = ttheme::instance();
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
      eval('$result .= "'. $theme->comments['count'] . '";');
      $result .= $this->GetcommentsList($items, $comment, '', $from);
    }
    
    if ($urlmap->page == 1) {
      $items = $comments->getapproved('pingback');
      if (count($items) > 0) {
        $list = '';
        $comtempl = $theme->comments['pingback'];
        foreach  ($items as $id) {
          $comment->id = $id;
          eval('$list .= "'. $comtempl  . '"; ');
        }
        $pingbacks = str_replace('%1$', '%1\$', $theme->comments['pingbacks']);
        $pingbacks = str_replace('%2$', '%2\$', $pingbacks);
        eval('$pingbacks = "'. $pingbacks . '";');
        
        $result .= sprintf($pingbacks, $list, 1);
      }
    }
    if (!$options->commentsdisabled && $post->commentsenabled) {
      $result .=  "<?php  echo tcommentform::printform($post->id); ?>\n";
    } else {
      eval('$result .= "'. $theme->comments['closed'] . '";');
    }
    return $result;
  }
  
  private function GetCommentsList(array &$items, &$comment, $hold, $from) {
    global $options, $post, $template;
$theme = ttheme::instance();
    $lang = TLocal::instance('comment');
    $result = '';
    $comtempl = $theme->comments['comment'];
    $class1 = $theme->comments['class1'];
    $class2 = $theme->comments['class2'];
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
    
    return sprintf($theme->comments['comments'], $result, $from + 1);
  }
  
  public function GetHoldList(&$items, $postid) {
    if (count($items) == 0) return '';
$theme = ttheme::instance();
    $comments = tcomments::instance($postid);
    $comment = new TComment($comments);
    $lang = TLocal::instance('comment');
    eval('$hold = "'. $theme->comments['hold'] . '";');
    return $this->GetCommentsList($items, $comment, $hold, 0);
  }
  
} //class
?>