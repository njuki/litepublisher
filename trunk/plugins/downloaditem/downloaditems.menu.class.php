<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tticketsmenu extends tmenu {
  
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  protected function create() {
    parent::create();
    $this->data['type'] = 'tickets';
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
      $cachefile = litepublisher::$paths->cache . $this->type . '.tickets.php';
      if (file_exists($cachefile)) {
        $result .= file_get_contents($cachefile);
      } else {
        $s = $this->getall();
        file_put_contents($cachefile, $s);
        $result .= $s;
      }
    } else {
      $perpage = litepublisher::$options->perpage;
      $theme = ttheme::instance();
      $tickets = ttickets::instance();
      $tt = litepublisher::$db->prefix . $tickets->childtable;
      $pt = litepublisher::$db->posts;
      $where = $this->type == 'tickets' ? '' : " and $tt.type = '$this->type'";
      $count = $tickets->getchildscount($where);
      $from = ($page - 1) * $perpage;
      if ($from <= $count)  {
        $items = $tickets->select("$pt.status = 'published' $where", " order by $pt.posted desc, $tt.type, $tt.state, $tt.prio, $tt.votes desc limit $from, $perpage");
        $result = $theme->getposts($items, false);
      }
      $result .=$theme->getpages($this->url, $page + 1, ceil($count / $perpage) + 1);
    }
    return $result;
  }
  
  private function getall() {
    $result = '';
    $tickets = ttickets::instance();
    $db = litepublisher::$db;
    $tt = $db->prefix . $tickets->childtable;
    $pt = $db->posts;
    $where = $this->type == 'tickets' ? '' : " and $tt.type = '$this->type'";
    
    $items = $db->res2assoc($db->query("select $pt.id, $pt.idurl, $pt.title,
    $db->urlmap.url as url, $tt.type, $tt.state, $tt.votes
    from $pt, $db->urlmap, $tt
    where $pt.id = $tt.id and $db->urlmap.id  = $pt.idurl  and $pt.status = 'published' $where
    order by $pt.posted desc, $tt.votes, $tt.type, $tt.state, $tt.prio"));
    
    if (count($items) == 0) return '';
    $url = litepublisher::$site->url;
    $index = $this->type == 'tickets' ? 'type' : 'state';
    tticket::checklang();
    $local = tlocal::$data['ticket'];
    foreach ($items as $item) {
      $result .= sprintf('<li>%4$s: <a href="%1$s%2$s" title="%3$s">%3$s</a></li>', $url, $item['url'], $item['title'], $local[$item[$index]]);
    }
    
    
    $result = sprintf('<ul>%s</ul>', $result);
    
    $theme = ttheme::instance();
    $result .=$theme->getpages($this->url, 1, ceil(count($items)/ litepublisher::$options->perpage) + 1);
    return $result;
  }
  
}//class
?>