<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tcommentswidget extends titems {

public static function instance() {
return getinstance(__class__);
}

protected function create() {
parent::create();
$this->basename = 'commentswidget';
$this->data['recentcount'] =  7;
}

public function setrecentcount($value) {
if ($value != $this->recentcount) {
$this->data['recentcount'] = $recentcount;
$this->save();
}
}

 public function getwidgetcontent($id, $sitebar) {
    global $options, $db;
    $result = '';
$theme = ttheme::instance();
$tml = $theme->getwidgetitem('comments', $sitebar);
$args = targs::instance();
      $args->onrecent = tlocal::$data['comment']['onrecent'];

if (dbversion) {
$res = $db->query("select $db->comments.*, 
$db->comusers.name as name, 
 $db->posts.title as title, $db->posts.commentscount as commentscount,
$db->urlmap.url as posturl 
from $db->comments, $db->comusers, $db->posts, $db->urlmap
where $db->comments.status = 'approved' and 
$db->comusers.id = $db->comments.author and 
$db->posts.id = $db->comments.post and 
$db->urlmap.id = $db->posts.idurl 
order by $db->comments.posted desc limit $this->recentcount");

$res->setFetchMode (PDO::FETCH_ASSOC);
foreach ($res as $item) {
$args->add($item);
    if ($options->commentpages) {
$page = ceil($item['commentscount'] / $options->commentsperpage);
if ($page > 1) $args->posturl = rtrim($item['posturl'], '/') . "/page/$page/";
}

        $args->content = tcontentfilter::getexcerpt($item['content'], 120);
          $result .= $theme->parsearg($tml,$args);
        }
} else {
foreach ($this->items as $item) {
$args->add($item);
        $args->content = tcontentfilter::getexcerpt($item['content'], 120);
          $result .= $theme->parsearg($tml,$args);
        }
}
    return $result;
  }

public function changed($id, $idpost) {
$std = tstdwidgets::instance();
$std->expire('comments');

if (!dbversion) {
$comments = tcomments::instance($idpost);
if (!$comments->itemexists($id)) }
//удалить если существует
foreach ($this->items as $i => $item) {
if ($id == $item['id']) {
array_splice($this->items, $i, 1);
$this->save();
return;
}
}
} else {
//добавить комментарий в список и удалить старый
$item = $comments->items[$id];
$item['id'] = $id;
$item['idpost'] = $idpost;
          $post = tpost::instance($idpost);
//если свежий коммент, то на последней странице
$item[['posturl'] =     $post->url;
    if ($options->commentpages) {
$page = ceil($comments->count / $options->commentsperpage);
if ($page > 1) $item['posturl'] = rtrim($item['posturl'], '/') . "/page/$page/";
}

$comusers = tcomusers::instance($idpost);
$author = $comusers->items[$item['author']];
$item['name'] = $author['name'];
$item['email'] = $author['email'];
$item['url'] = $author['url'];

if (count($this->items) == $this->recentcount) array_pop($this->items);
array_unshift($this->items, $item);
$this->save();
}
}
  
}//class
?>