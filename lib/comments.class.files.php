<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tcomments extends titems {
  public $pid;
private $rawitems;
private $holtitems;
  private static $instances;
  
  public static function instance($pid) {
global $classes;
    if (!isset(self::$instances)) self::$instances = array();
    if (isset(self::$instances[$pid]))       return self::$instances[$pid];
$self = $classes->newinstance(__class__);
      self::$instances[$pid]  = $self;
      $self->pid = $pid;
      $self->load();
return $self;
  }
  
  public static function getcomment($pid, $id) {
    $self = self::instance($pid);
    $result = new tcomment($self);
    $result->id = $id;
    return $result;
  }

  public function getbasename() {
    return 'posts'.  DIRECTORY_SEPARATOR . $this->pid . DIRECTORY_SEPARATOR . 'comments';
  }

public function getraw() {
if (!isset($this->rawitems)) {
$this->rawitems = new trawcomments($this);
}
return $this->rawitems;
}

public function gethold() {
if (!isset($this->holditems)) {
$this->holditems = new tholdcomments($this);
}
return $this->holditems;
}

public function getapprovedcount() {
return count($this->items);
}

  public function add($author, $content, $status) {
    $filter = TContentFilter::instance();
$item  = array(
    'author' => $author,
    'posted' => time(),
    'content' => $filter->GetCommentContent($content)
    );

if ($status == 'approved') {
    $this->items[++$this->autoid] = $item;
} else {
$this->hold->items[++$this->autoid] =  $item;
$this->hold->save();
}
    $this->save();

    $ip = preg_replace( '/[^0-9., ]/', '',$_SERVER['REMOTE_ADDR']);
$this->raw->add($this->autoid, $content, $ip);
$this->added($this->autoid);
    return $this->autoid;
  }

public function delete($id) {
if (!isset($this->items[$id])) return false;
$author = $this->items[$id]['author'];
unset($this->items[$id]);
$this->save();
$this->raw->delete($id);

$this->deleteauthor($author);
$this->deleted($id);
return true;
}

public function deleteauthor($author) {
    foreach ($this->items as $id => $item) {
      if ($author == $item['author'])  return;
    }

    foreach ($this->hold->items as $id => $item) {
      if ($author == $item['author'])  return;
    }

//автора не нашли
$comusers = tcomusers::instance($this->pid);
$comusers->delete($author);
}

  public function hold($id) {
if (isset($this->itms[$id])) {
$item = $this->items[$id];
unset($this->items[$id]);
    $this->save();

$this->hold->items[$id] = $item;
$this->hold->save();
  }
}

public function approve($id) {
if (isset($this->hold->items[$id])) {
$this->items[$id] = $this->hold->items[$id];
$this->hold->delete($id);
}
}
/*
  public function sort() }
        $Result[$id] = $item['posted'];
    asort($Result);
    return  array_keys($Result);
  }
*/

public function getholdcontent($idauthor) {
return $this->hold->getcontent($idauthor);
}

public function getcontent() {
    global $options, $urlmap, $comment;
    $result = '';
$from = 0;
$items = array_keys($this->items);
if (__class__ == get_class($this)) {
    if ($options->commentpages ) {
$from = ($urlmap->page - 1) * $options->commentsperpage;
      $items = array_slice($items, $from, $options->commentsperpage, true);
}
}

if (count($items) == 0) return '';

$args = targs::instance();
$args->from = $from;
    $comment = new TComment($this);
if ('tholdcomments' == get_class($this)) $comment->status = 'hold';
    $lang = tlocal::instance('comment');
$theme = ttheme::instance();
$tml = $theme->content->post->templatecomments->comments->comment;
    $i = 1;
foreach ($items as $id) {
//разрулить в одном месте одобренные и задержанные комменты
if (__class__ != get_class($this)) {
//значит задержанные
if ($this->idauthor != $this->items[$id]['author']) continue;
}
      $comment->id = $id;
      $args->class = (++$i % 2) == 0 ? $tml->class1 : $tml->class2;
$result .= $theme->parsearg($tml, $args);
    }
    return sprintf($theme->content->post->templatecomments->comments, $result, $from + 1);    
}

}//class

class tholdcomments extends tcomments {
public $owner;
public $idauthor;

  public static function instance($pid) {
$owner = tcomments::instance($pid);
return $owner->hold;
}

public function __construct($owner) {
$this->owner = $owner;
parent::__construct();
}

  public function getbasename() {
    return $this->owner->getbasename() . '.hold';
  }

public function delete($id) {
if (!isset($this->items[$id])) return false;
$author= $this->items[$id]['author'];
unset($this->items[$id]);
$this->save();
$this->owner->raw->delete($id);
$this->owner->deleteauthor($author);
$this->deleted($id);
}

public function getcontent($idauthor) {
$this->idauthor = $idauthor;
return parent::getcontent();
}

  }//class

class trawcomments extends titems {
public $owner;

public function getbasename() {
    return 'posts'.  DIRECTORY_SEPARATOR . $this->owner->pid . DIRECTORY_SEPARATOR . 'comments.raw';
}

public function __construct($owner) {
$this->owner = $owner;
parent::__construct();
}

public function add($id, $content, $ip) {
$this->items[$id] = array(
'content' => $content,
'ip' => $ip
);
$this->save();
}

}//class

//wrapper for simple acces to single comment
class TComment {
  public $id;
  public $owner;
public $status;
  
  public function __construct($owner = null) {
    $this->owner = $owner;
$this->status = 'approved';
  }
  
  public function __get($name) {
    if (method_exists($this,$get = "get$name")) {
      return  $this->$get();
    }
    return $this->owner->items[$this->id][$name];
  }
  
  public function __set($name, $value) {
    if ($name == 'content') {
      $this->setcontent($value);
    } else {
      $this->owner->items[$this->id][$name] = $value;
    }
  }
  
  public function save() {
    $this->owner->save();
  }

  private function setcontent($value) {
      $filter = TContentFilter::instance();
      $this->owner->items[$this->id]['content'] = $filter->GetCommentContent($value);
      $this->save();
      $this->owner->raw->items[$this->id]['content'] =  $value;
$this->owner->raw->save();
    }
  
private function getauthoritem() {
    $comusers = tcomusers::instance($this->owner->pid);
    return  $comusers->getitem($this->author);
  }
  
  public function getname() {
    return $this->authoritem['name'];
  }

    public function getemail() {
    return $this->authoritem['email'];
  }
  
  public function getwebsite() {
    return $this->authoritem['url'];
}

 public function getauthorlink() {
global $options;
$idpost = $this->owner->pid;
$comusers = tcomusers::instance($idpost);
$item = $comusers->getitem($this->author);
$name = $item['email'];
$url = $item['url'];
$manager = tcommentmanager::instance();
    if ($manager->hidelink || empty($url)) return $name;
    $rel = $manager->nofollow ? 'rel="nofollow noindex"' : '';
    if ($manager->redir) {
      return "<a $rel href=\"$options->url/comusers.htm{$options->q}id=$this->author&post=$idpost\">$name</a>";
    } else {
      return "<a $rel href=\"$url\">$name</a>";
    }
}

  public function getdate() {
$theme = ttheme::instance();
    return TLocal::date($this->posted, $theme->comment->dateformat);
  }
  
  public function getlocalstatus() {
    return tlocal::$data['commentstatus'][$this->status];
  }
  
  public function  gettime() {
    return date('H:i', $this->posted);
  }
  
  public function geturl() {
    $post = tpost::instance($this->owner->pid);
    return "$post->link#comment-$this->id";
  }
  
  public function getposttitle() {
    $post = tpost::instance($this->owner->pid);
    return $post->title;
  }

public function getrawcontent() {
return $this->owner->raw->items[$this->id]['content'];
}

public function getip() {
return $this->owner->raw->items[$this->id]['ip'];
}

}//class

?>