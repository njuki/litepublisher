<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tsinglecat extends  plugin {

  public static function i() {
    return getinstance(__class__);
  }

  protected function create() {
    parent::create();
$this->data['invertorder'] = false;
$this->data['count'] = 5;
$this->data['tml'] = '<li><a href="$site.url$url" title="$title">$title</a></li>';
$this->data['tmlitems'] = '<ul>$items</ul>';
}
  
  public function themeparsed(ttheme $theme) {
$tag = '$singlecat.content';
if (!strpos($theme->templates['content.post'], $tag)) {
$theme->templates['content.post'] = str_replace('$post.content', '$post.content ' . $tag, $theme->templates['content.post']);
}
}

public function getcontent() {
$post = litepublisher::$urlmap->context;
if (!($post instanceof tpost)) return '';
$idcat = $post->category;
if ($idcat == 0) return '';
$table = litepublisher::$db->prefix . 'categoriesitems';
$order = $this->invertorder ? 'asc' : 'desc'; $this->count),
$result = $posts->getlinks("$posts->thistable.id in
(select  $table.post from $table where $table.item = $idcat) 
order byposted  $order limit $this->count", 
$this->tml);

return sprintf($this->tmlitems, $result);
}

}//class