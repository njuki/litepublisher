<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcodedocmenu extends tmenu {
  
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function setcontent($s) {
    $this->rawcontent = $s;
    $filter = tcontentfilter::instance();
    $this->data['content'] = $filter->filter($s);
  }
  
  public function getcontent() {
    $page = litepublisher::$urlmap->page - 1;
    if ($page == 0) {
      $result = parent::getcontent();
      $cachefile = litepublisher::$paths->cache . 'codedoc.php';
      if (file_exists($cachefile)) {
        $result .= file_get_contents($cachefile);
      } else {
        $s = $this->getall();
        file_put_contents($cachefile, $s);
        $result .= $s;
      }
    } else {
      $result = '';
      $perpage = litepublisher::$options->perpage;
      $theme = ttheme::instance();
      $count = $this->getdb('codedoc')->getcount();
      $from = ($page - 1) * $perpage;
      if ($from <= $count)  {
        $db = litepublisher::$db;
        $items = $db->res2id($db->query("select $db->posts.id from $db->posts, $db->codedoc
        where $db->posts.id = $db->codedoc.id and $db->posts.status = 'published'
        order by $db->codedoc.class, $db->posts.title, $db->posts.posted limit $from, $perpage"));
        $result .= $theme->getposts($items, false);
      }
      $result .=$theme->getpages($this->url, $page + 1, ceil($count / $perpage) + 1);
    }
    return $result;
  }
  
  private function getall() {
    $result = '';
    $db = litepublisher::$db;
    $res = $db->query("select $db->posts.id, $db->posts.idurl, $db->posts.title,
    $db->urlmap.url as url, $db->codedoc.class
    from $db->posts, $db->urlmap, $db->codedoc
    where $db->posts.id = $db->codedoc.id and $db->urlmap.id  = $db->posts.idurl  and $db->posts.status = 'published'
    order by $db->codedoc.class, $db->posts.title, $db->posts.posted");
    
    $count = 0;
    $url = litepublisher::$site->url;
    while ($item = $db->fetchassoc($res)) {
      $result .= sprintf('<li><a href="%1$s%2$s" title="%3$s">%3$s</a></li>', $url, $item['url'], $item['title']);
      $count++;
    }
    
    if ($count == 0) return '';
    $result = sprintf('<ul>%s</ul>', $result);
    
    $theme = ttheme::instance();
    $result .=$theme->getpages($this->url, 1, ceil($count/ litepublisher::$options->perpage) + 1);
    return $result;
  }
  
}//class
?>