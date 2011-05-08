<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tposts extends titems {
  public $itemcoclasses;
  public $archives;
  public $rawtable;
  public $childtable;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public static function unsub($obj) {
    $self = self::instance();
    $self->unsubscribeclassname(get_class($obj));
  }
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->table = 'posts';
    $this->childtable = '';
    $this->rawtable = 'rawposts';
    $this->basename = 'posts'  . DIRECTORY_SEPARATOR  . 'index';
    $this->addevents('edited', 'changed', 'singlecron', 'beforecontent', 'aftercontent', 'beforeexcerpt', 'afterexcerpt', 'onselect');
    $this->data['archivescount'] = 0;
    $this->data['revision'] = 0;
    $this->data['syncmeta'] = false;
    if (!dbversion) $this->addmap('archives' , array());
    $this->addmap('itemcoclasses', array());
  }
  
  public function getitem($id) {
    if (dbversion) {
      if ($result = tpost::instance($id)) return $result;
      $this->error("Item $id not found in class ". get_class($this));
    } else {
      if (isset($this->items[$id])) return tpost::instance($id);
    }
    return $this->error("Item $id not found in class ". get_class($this));
  }
  
  public function loaditems(array $items) {
    if (!dbversion || count($items) == 0) return;
    //exclude already loaded items
    if (isset(titem::$instances['post'])) {
      $items = array_diff($items, array_keys(titem::$instances['post']));
    }
    if (count($items) == 0) return;
    $list = implode(',', $items);
    return $this->select("$this->thistable.id in ($list)", '');
  }
  
  public function setassoc(array $items) {
    if (count($items) == 0) return array();
    $result = array();
    $t = new tposttransform();
    $fileitems = array();
    foreach ($items as $a) {
      $t->post = tpost::newpost($a['class']);
      $t->setassoc($a);
      $result[] = $t->post->id;
      $f = $t->post->files;
      if (count($f) > 0) $fileitems = array_merge($fileitems, array_diff($f, $fileitems));
    }
    unset($t);
    if ($this->syncmeta)  tmetapost::loaditems($result);
    if (count($fileitems) > 0) {
      $files = tfiles::instance();
      $files->preload($fileitems);
    }
    
    $this->onselect($result);
    return $result;
  }
  
  public function select($where, $limit) {
    $db = litepublisher::$db;
    if ($this->childtable) {
      $childtable = $db->prefix . $this->childtable;
      return $this->setassoc($db->res2items($db->query("select $db->posts.*, $db->urlmap.url as url, $childtable.*
      from $db->posts, $db->urlmap, $childtable
      where $where and  $db->posts.id = $childtable.id and $db->urlmap.id  = $db->posts.idurl $limit")));
    }
    
    /*
    $items = $db->res2items($db->query("select $db->posts.*, $db->urlmap.url as url  from $db->posts, $db->urlmap
    where $where and  $db->urlmap.id  = $db->posts.idurl $limit"));
    */
    
    $items = $db->res2items($db->query(
    "select $db->posts.*, $db->urlmap.url as url  from $db->posts
    left join  $db->urlmap on $db->urlmap.id  = $db->posts.idurl
    where $where $limit"));
    
    if (count($items) == 0) return array();
    $subclasses = array();
    foreach ($items as &$item) {
      if (empty($item['class'])) $item['class'] = 'tpost';
      if ($item['class'] != 'tpost') {
        $subclasses[$item['class']][] = $item['id'];
      }
    }
    unset($item);
    
    foreach ($subclasses as $class => $list) {
      $childtable =  $db->prefix .
      call_user_func_array(array($class, 'getchildtable'), array());
      $list = implode(',', $list);
      $subitems = $db->res2items($db->query("select $childtable.*
      from $childtable where id in ($list)"));
      foreach ($subitems as $id => $subitem) {
        $items[$id] = array_merge($items[$id], $subitem);
      }
    }
    
    return $this->setassoc($items);
  }
  
  public function getcount() {
    if (dbversion) {
      return $this->db->getcount("status<> 'deleted'");
    } else {
      return count($this->items);
    }
  }
  
  public function getchildscount($where) {
    if ($this->childtable == '') return 0;
    $db = litepublisher::$db;
    $childtable = $db->prefix . $this->childtable;
    if ($res = $db->query("SELECT COUNT($db->posts.id) as count FROM $db->posts, $childtable
    where $db->posts.status <> 'deleted' and $childtable.id = $db->posts.id $where")) {
      if ($r = $db->fetchassoc($res)) return $r['count'];
    }
    return 0;
  }
  
  private function beforechange($post) {
    $post->title = trim($post->title);
    $post->modified = time();
    $post->data['revision'] = $this->revision;
    $post->data['class'] = get_class($post);
    if (($post->status == 'published') && ($post->posted > time())) {
      $post->status = 'future';
    } elseif (($post->status == 'future') && ($post->posted <= time())) {
      $post->status = 'published';
    }
  }
  
  public function add(tpost $post) {    if ($post->posted == 0) $post->posted = time();
    $this->beforechange($post);
    if (($post->icon == 0) && !litepublisher::$options->icondisabled) {
      $icons = ticons::instance();
      $post->icon = $icons->getid('post');
    }
    
    if ($post->idview == 1) {
      $views = tviews::instance();
      if (isset($views->defaults['post'])) $post->data['idview'] = $views->defaults['post'];
    }
    
    $post->pagescount = count($post->pages);
    $linkgen = tlinkgenerator::instance();
    $post->url = $linkgen->addurl($post, $post->schemalink);
    $post->title = tcontentfilter::escape($post->title);
    $urlmap = turlmap::instance();
    if (dbversion) {
      $id = $post->addtodb();
      $post->idurl = $urlmap->add($post->url, get_class($post), (int) $post->id);
      $post->db->setvalue($post->id, 'idurl', $post->idurl);
    } else {
      $post->id = ++$this->autoid;
      $dir =litepublisher::$paths->data . 'posts' . DIRECTORY_SEPARATOR  . $post->id;
      if (!is_dir($dir)) mkdir($dir, 0777);
      chmod($dir, 0777);
      $post->idurl = $urlmap->Add($post->url, get_class($post), $post->id);
    }
    $this->lock();
    $this->updated($post);
    $this->cointerface('add', $post);
    if (!$this->dbversion) $post->save();
    $this->unlock();
    $this->added($post->id);
    $this->changed();
    $urlmap->clearcache();
    return $post->id;
  }
  
  public function edit(tpost $post) {
    $this->beforechange($post);
    $linkgen = tlinkgenerator::instance();
    $linkgen->editurl($post, $post->schemalink);
    $post->title = tcontentfilter::escape($post->title);
    $this->lock();
    $this->updated($post);
    $this->cointerface('edit', $post);
    $post->save();
    $this->unlock();
    $this->edited($post->id);
    $this->changed();
    litepublisher::$urlmap->clearcache();
  }
  
  public function delete($id) {
    if (!$this->itemexists($id)) return false;
    $urlmap = turlmap::instance();
    if ($this->dbversion) {
      $idurl = $this->db->getvalue($id, 'idurl');
      $this->db->setvalue($id, 'status', 'deleted');
      if ($this->childtable) {
        $db = $this->getdb($this->childtable);
        $db->delete("id = $id");
      }
    } else {
      if ($post = tpost::instance($id)) {
        $idurl = $post->idurl;
        $post->free();
      }
      titem::deletedir(litepublisher::$paths->data . 'posts'. DIRECTORY_SEPARATOR   . $id . DIRECTORY_SEPARATOR  );
      unset($this->items[$id]);
      $urlmap->deleteitem($idurl);
    }
    
    $this->lock();
    $this->PublishFuture();
    $this->UpdateArchives();
    $this->cointerface('delete', $id);
    $this->unlock();
    $this->deleted($id);
    $this->changed();
    $urlmap->clearcache();
    return true;
  }
  
  
  public function updated(tpost $post) {
    if (!$this->dbversion) {
      $this->items[$post->id] = array(
      'posted' => $post->posted
      );
      if   ($post->status != 'published') $this->items[$post->id]['status'] = $post->status;
      if   ($post->author > 1) $this->items[$post->id]['author'] = $post->author;
    }
    $this->PublishFuture();
    $this->UpdateArchives();
    $cron = tcron::instance();
    $cron->add('single', get_class($this), 'dosinglecron', $post->id);
  }
  
  public function UpdateArchives() {
    if ($this->dbversion) {
      $this->archivescount = $this->db->getcount("status = 'published' and posted <= '" . sqldate() . "'");
    } else {
      $this->archives = array();
      foreach ($this->items as $id => $item) {
        if ((!isset($item['status']) || ($item['status'] == 'published')) &&(time() >= $item['posted'])) {
          $this->archives[$id] = $item['posted'];
        }
      }
      arsort($this->archives,  SORT_NUMERIC);
      $this->archivescount = count($this->archives);
    }
  }
  
  public function dosinglecron($id) {
    $this->PublishFuture();
    ttheme::$vars['post'] = tpost::instance($id);
    $this->singlecron($id);
  }
  
  public function hourcron() {
    $this->PublishFuture();
  }
  
  private function publish($id) {
    $post = tpost::instance($id);
    $post->status = 'published';
    $this->edit($post);
  }
  
  public function PublishFuture() {
    if ($this->dbversion) {
      if ($list = $this->db->idselect(sprintf("status = 'future' and posted <= '%s' order by posted asc", sqldate()))) {
        foreach( $list as $id) $this->publish($id);
      }
    } else {
      foreach ($this->items as $id => $item) {
        if (isset($item['status']) && ($item['status'] == 'future') && ($item['posted'] <= time())) $this->publish($id);
      }
    }
  }
  
  public function getrecent($count) {
    if (dbversion) {
      return $this->db->idselect("status = 'published'order by posted desc limit $count");
    }  else {
      return array_slice(array_keys($this->archives), 0, $count);
    }
  }
  
  public function getpage($page, $perpage, $invertorder) {
    $count = $this->archivescount;
    $from = ($page - 1) * $perpage;
    if ($from > $count)  return array();
    if (dbversion)  {
      $order = $invertorder ? 'asc' : 'desc';
      return $this->select("status = 'published'", " order by posted $order limit $from, $perpage");
    } else {
      $to = min($from + $perpage , $count);
      $result = array_keys($this->archives);
      if ($invertorder) $result =array_reverse($result);
      return array_slice($result, $from, $to - $from);
    }
  }
  
  public function GetPublishedRange($page, $perpage) {
    $count = $this->archivescount;
    $from = ($page - 1) * $perpage;
    if ($from > $count)  return array();
    if (dbversion)  {
      return $this->select("status = 'published'", " order by posted desc limit $from, $perpage");
    } else {
      $to = min($from + $perpage , $count);
      return array_slice(array_keys($this->archives), $from, $to - $from);
    }
  }
  
  public function stripdrafts(array $items) {
    if (count($items) == 0) return array();
    if (dbversion) {
      $list = implode(', ', $items);
      return $this->db->idselect("status = 'published' and id in ($list)");
    } else {
      return array_intersect($items, array_keys($this->archives));
    }
  }
  
  public function sortbyposted(array $items) {
    if (count($items) <= 1) return $items;
    if (dbversion) {
      $list = implode(', ', $items);
      return $this->db->idselect("status = 'published' and id in ($list) order by posted desc");
    }
    /* надо выбрать опубликованные посты из items, потом отсортировать */
    $result = array_intersect_key ($this->archives, array_combine($items, $items));
    arsort($result,  SORT_NUMERIC);
    return array_keys($result);
  }
  
  //coclasses
  private function cointerface($method, $arg) {
    foreach ($this->coinstances as $coinstance) {
      if ($coinstance instanceof  ipost) $coinstance->$method($arg);
    }
  }
  
  public function addrevision() {
    $this->data['revision']++;
    $this->save();
    litepublisher::$urlmap->clearcache();
  }
  
  //fix call reference
  public function beforecontent($post, &$result) {
    $this->callevent('beforecontent', array($post, &$result));
  }
  
  public function aftercontent($post, &$result) {
    $this->callevent('aftercontent', array($post, &$result));
  }
  
  public function beforeexcerpt($post, &$result) {
    $this->callevent('beforeexcerpt', array($post, &$result));
  }
  
  public function afterexcerpt($post, &$result) {
    $this->callevent('afterexcerpt', array($post, &$result));
  }
  
}//class


class tpostswidget extends twidget {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'widget.posts';
    $this->template = 'posts';
    $this->adminclass = 'tadminmaxcount';
    $this->data['maxcount'] = 10;
  }
  
  public function getdeftitle() {
    return tlocal::$data['default']['recentposts'];
  }
  
  public function getcontent($id, $sidebar) {
    $posts = tposts::instance();
    $list = $posts->getrecent($this->maxcount);
    $theme = ttheme::instance();
    return $theme->getpostswidgetcontent($list, $sidebar, '');
  }
  
}//class

?>