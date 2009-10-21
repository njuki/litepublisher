<?php

class TCommentsWidget extends TEventClass {

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
$manager = $classes->commentmanager;
    $template = ttemplate::instance();

    $templ = isset($template->theme['widget']['recentcomment']) ? $template->theme['widget']['recentcomment'] :
    '<li><strong><a href="%1$s#comment-%2$s" title="%6$s %3$s">%4$s</a></strong>: %5$s...</li>';
    
    $count = $manager->options->recentcount;
      $onrecent = TLocal::$data['comment']['onrecent'];

if (dbversion) {
$db =  $manager->db;
$res = $db->query("select 
$db->comments.*, 
$db->comusers.name as name, 
$db->posts.title as title, 
$db->urlmap.url as posturl 
from $db->comments, $db->comusers, $db->posts, $db->urlmap
where $db->comments.status = 'approved' and 
$db->comments.pingback = 'false' and
$db->comusers.id = $db->comments.author and 
$db->posts.id = $db->comments.post and 
$db->urlmap.class = 'tposts' and $db->urlmap.arg = $db->comments.post
sort by $db->comments.posted desc limit ".$manager->options->recentcount); 
while ($row = $res->fetch()) {
         $content = TContentFilter::GetExcerpt($row['content'], 120);
          $result .= sprintf($templ, $options->url . $posturl, $row['id], $row['title'], $row['name'], $content, $onrecent);
        }
} else {
    if ($item = end($manager->items)) {
      $users = TCommentUsers::instance();
      do {
        $id = key($manager->items);
        if (!isset($item['status']) && !isset($item['type']) ) {
          $count--;
          $post = tpost::instance($item['pid']);
          $content = $post->comments->getvalue($id, 'content');
          $content = TContentFilter::GetExcerpt($content, 120);
          $user = $users->getitem($item['uid']);
          $result .= sprintf($templ, $options->url . $post->url, $id,$post->title, $user['name'], $content, $onrecent);
        }
      } while (($count > 0) && ($item  = prev($manager->items)));
    }
    
}
    return $result;
  }
  

}//class
?>