<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tcommentswidget extends tevents {

public static function instance() {
return getinstance(__class__);
}

protected function create() {
parent::create();
$this->basename = 'commentswidget';
}

 public function GetWidgetContent($id) {
    global $options, $classes;
    $result = '';
$theme = ttheme::instance();
$tml = $theme->getwidgetitem('comment');
$manager = $classes->commentmanager;
    $count = $manager->recentcount;
$args = targs::instance();
      $args->onrecent = tlocal::$data['comment']['onrecent'];

if (dbversion) {
$db =  $manager->db;
$res = $db->query("select 
$db->comments.*, 
$db->comusers.name as name, 
$db->posts.title as title,
$db->posts.commentscount as commentscount,
$db->urlmap.url as posturl 
from $db->comments, $db->comusers, $db->posts, $db->urlmap
where $db->comments.status = 'approved' and 
$db->comments.pingback = 'false' and
$db->comusers.id = $db->comments.author and 
$db->posts.id = $db->comments.post and 
$db->urlmap.id = $db->posts.idurl 
sort by $db->comments.posted desc limit $count");
while ($row = $res->fetch()) {
$args->posturl = $row['posturl'];
    if ($options->commentpages) {
$count = ceil($row['commentscount'] / $options->commentsperpage);
if ($count > 1) $args->posturl = rtrim($row['posturl'], '/') . "/page/$count/";
}

$args->id = $row['id'];
 $args->title = $row['title'];
$args->name = $row['name'];
        $args->content = tcontentfilter::getexcerpt($row['content'], 120);
          $result .= $theme->parsearg($tml,$args);
        }
} else {
    if ($item = end($manager->items)) {
      $users = tcomusers::instance();
      do {
        $id = key($manager->items);
        if (!isset($item['status']) && !isset($item['type']) ) {
          $count--;
          $post = tpost::instance($item['pid']);
//если свежий коммент, то на последней странице
$args->posturl =     $post->haspages ? rtrim($post->url, '/') . "/page/$post->commentpages/" : $post->url;
          $content = $post->comments->getvalue($id, 'content');
          $args->content = tcontentfilter::getexcerpt($content, 120);

$args->id = $id;
 $args->title = $post->title;
          $user = $users->getitem($item['uid']);
$args->name = $user['name'];
          $result .= $theme->parsearg($tml,$args);
        }
      } while (($count > 0) && ($item  = prev($manager->items)));
    }
    
}
    return $result;
  }
  
}//class
?>