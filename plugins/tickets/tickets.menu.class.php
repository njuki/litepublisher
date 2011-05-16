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
        @chmod($cachefile, 0666);
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
    
    $items = $db->res2assoc($db->query("select $pt.id, $pt.idurl, $pt.title, $pt.commentscount,
    $db->urlmap.url as url, $tt.type, $tt.state, $tt.votes
    from $pt, $db->urlmap, $tt
    where $pt.id = $tt.id and $db->urlmap.id  = $pt.idurl  and $pt.status = 'published' $where
    order by $pt.posted desc, $tt.votes, $tt.type, $tt.state, $tt.prio"));
    
    if (count($items) == 0) return '';
    
    $index = $this->type == 'tickets' ? 'type' : 'state';
    $theme = ttheme::instance();
    tticket::checklang();
    $langticket = tlocal::$data['ticket'];
    $args = targs::instance();
    $tml = '<tr>
    <td align="left">$state</td>
    <td align="right">$commentscount</td>
    <td align="left"><a href="$link" title="$title">$title</a></td>
    </tr>';
    
    foreach ($items as $item) {
      $args->add($item);
      $args->state = $langticket[$item[$index]];
      $result .= $theme->parsearg($tml, $args);
    }
    
    $args->tablebody = $result;
    $lang = tlocal::instance('ticket');
    $result = $theme->parsearg('<table class="classictable">
    <thead>
    <tr>
    <td align="left">$lang.state</td>
    <td align="right">$lang.comments</td>
    <td align="left">$lang.ticket</td>
    </tr>
    </thead>
    <tbody>
    $tablebody
    </tbody >
    </table>',
    $args);
    
    $result .=$theme->getpages($this->url, 1, ceil(count($items)/ litepublisher::$options->perpage) + 1);
    return $result;
  }
  
}//class
?>