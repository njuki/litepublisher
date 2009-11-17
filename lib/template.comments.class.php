<?php

class ttemplatecomments extends tevents {

  public static function instance() {
    return getinstance(__class__);
  }

public function load() {}
public function save() {}  

  public function getcount($count) {
$l = &tlocal::$data['comment'];
    switch($count) {
      case 0: return $l[0];
      case 1: return $l[1];
      default: return sprintf($l[2], $count);
    }
  }
  
  public function getcommentslink(tpost $post) {
    global $options;
    $comments = $post->comments;
    $count = $this->getcount($comments->GetCountApproved());
    $url = $post->haspages ? rtrim($post->url, '/') . "/page/$post->countpages/" : $post->url;
    return "<a href=\"$options->url$url#comments\">$count</a>";
  }
  
  public function getcomments($idpost) {
    global $options;
    $result = '';
$urlmap = turlmap::instance();
$idpost = (int) $idpost;
$post = tpost::instance($idpost);
    if (($post->commentscount == 0) && !$post->commentsenabled) return '';
    if ($post->haspages && ($post->commentpages < $urlmap->page)) return $this->getcommentslink($post);

$args = new targs();
$theme = ttheme::instance();
    $lang = tlocal::instance('comment');
    $comments = tcomments::instance($idpost);
    $from = $options->commentpages  ? ($urlmap->page - 1) * $options->commentsperpage : 0;
if (dbversion) {
$c = $comments->db->getcount("post = $idpost and status = 'approved' and pingback = 'false'");
    $count = $this->getcount($c);
$db = $comments->db;
$items = $comments->getitems("$db->comments.post = $idpost and $db->comments.status = 'approved' and $db->comments.pingback = 'false' and $db->comusers.id = $db->comments.author", $from, $options->commentsperpage);
} else {
    $items = $comments->getapproved();
    $count = $this->getcount(count($items));
    if ($options->commentpages ) {
      $items = array_slice($items, $from, $options->commentsperpage, true);
    }
}

    if (count($items)  > 0) {
$result .= $theme->parsearg($theme->comments['count'], $args);
      $result .= $this->getlist($items, $idpost, '', $from);
    }
    
    if ($urlmap->page == 1)  $result .= $this->getpingbacks($idpost);
    if (!$options->commentsdisabled && $post->commentsenabled) {
      $result .=  "<?php  echo tcommentform::printform($idpost); ?>\n";
    } else {
$result .= $theme->parse($theme->comments['closed']);
    }
    return $result;
  }

private function getpingbacks($idpost) {
global $comment;
    $comments = tcomments::instance($postid);
if (dbversion) {
$db = $comments->db;
$items = $db->queryassoc("select $db->comments.*, $db->comusers.name, $db->comusers.email, $db->comusers.url from $db->comments, $db->comusers
where $db->comments.post = $idpost and $db->comments.status = 'approved' and $db->comments.pingback = 'true' and $db->comusers.id = $db->comments.author
sort by $db->comments.posted asc");
} else {
      $items = $comments->getapproved('pingback');
}

      if (count($items) == 0) return '';

    $result = '';
    $comment = new TComment($comments);
    $lang = tlocal::instance('comment');
$theme = ttheme::instance();
    $comtempl = $theme->comments['pingback'];
    foreach  ($items as $iddata) {
//трюк: в бд items это комменты целиком, а в файлах только id
if (dbversion)  {
      $comment->data = $iddata;
} else {
      $comment->id = $iddata;
}
$result .= $theme->parse($comtempl);
    }
    
    return sprintf($theme->comments['pingbacks'], $result);
}

  private function getlist(array &$items, $idpost, $hold, $from) {
    global $comment, $post;
    $result = '';
$post = tpost::instance($idpost);
$args = new targs();
$args->hold = $hold;
$args->from = $from;
    $comments = tcomments::instance($postid);
    $comment = new TComment($comments);
    $lang = tlocal::instance('comment');
$theme = ttheme::instance();
    $comtempl = $theme->comments['comment'];
    $class1 = $theme->comments['class1'];
    $class2 = $theme->comments['class2'];
    $i = 1;
    foreach  ($items as $iddata) {
//трюк: в бд items это комменты целиком, а в файлах только id
if (dbversion)  {
      $comment->data = $iddata;
} else {
      $comment->id = $iddata;
}
      $args->class = (++$i % 2) == 0 ? $class1 : $class2;
$result .= $theme->parsearg($comtempl, $args);
    }
    
    return sprintf($theme->comments['comments'], $result, $from + 1);
  }
  
  public function gethold(array &$items, $idpost) {
    if (count($items) == 0) return '';
$theme = ttheme::instance();
    $lang = tlocal::instance('comment');
$hold = $theme->parse($thme->comments['hold']);
    return $this->getlist($items, $idpost, $hold, 0);
  }
  
} //class
?>