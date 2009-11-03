<?php

class ttemplatecomment extends TEventClass {

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
  
  public function gettemplatecomments($idpost) {
global $paths;
$filename = $paths[cache'] . "comments-$idpost->$urlmap->page.php";
if (file_exists($filename)) {
$result = file_get_contents($filename);
} else {
$result = $this->getcomments($idpost);
file_put_contents($filename, $result);
@chmod($filename, 0666);
}

return $result;
}

  public function getcomments($idpost) {
    global $template, $urlmap, $options, $post;
$post = tpost::instance($idpost);
    if (($post->commentscount == 0) && !$post->commentsenabled) return '';
    if ($post->haspages && ($post->commentpages < $urlmap->page)) return $this->getcommentslink($post);
    $comments = $post->comments;
$args = new targs();
$theme = ttheme::instance();
    $lang = tlocal::instance('comment');
    $result = '';
    $comment = new TComment($comments);
    $from = $options->commentpages  ? ($urlmap->page - 1) * $options->commentsperpage : 0;
if (dbversion) {
$c = $comments->db->getcount("post = $post->id and status = 'approved' and pingback = false");
    $count = $this->getcount($c);
$db = $comments->db;
$items = $db->queryassoc("select $db->comments.*, $db->comusers.name, $db->comusers.email, $db->comusers.url from $db->comments, $db->comusers
where $db->comments.post = $post->id and $db->comments.status = 'approved' and $db->comments.pingback = false and $db->comusers.id = $db->comments.author
sort by $db->comments.posted asc limit $from, $options->commentsperpage");
} else {
    $items = $comments->getapproved();
    $count = $this->getcount(count($items));
    if ($options->commentpages ) {
      $items = array_slice($items, $from, $options->commentsperpage, true);
    }
}
    if (count($items)  > 0) {
$result .= $theme->comments['count'];
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
    $lang = tlocal::instance('comment');
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
    $lang = tlocal::instance('comment');
    eval('$hold = "'. $theme->comments['hold'] . '";');
    return $this->GetCommentsList($items, $comment, $hold, 0);
  }
  
} //class
?>