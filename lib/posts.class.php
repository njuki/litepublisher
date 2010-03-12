<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tposts extends titems {
  public $itemcoclasses;
  public $archives;
  public $rawtable;
  
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
    $this->rawtable = 'rawposts';
    $this->basename = 'posts'  . DIRECTORY_SEPARATOR  . 'index';
    $this->addevents('edited', 'changed', 'singlecron', 'beforecontent', 'aftercontent');
    $this->data['recentcount'] = 10;
    $this->data['archivescount'] = 0;
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
    //исключить из загрузки загруженные посты
    $class = litepublisher::$classes->classes['post'];
    if (isset(titem::$instances[$class])) {
      $items = array_diff($items, array_keys(titem::$instances[$class]));
    }
    if (count($items) == 0) return;
    $list = implode(',', $items);
    return $this->select("$this->thistable.id in ($list)", '');
  }
  
  public function transformres($res) {
    $result = array();
    $t = new tposttransform();
    while ($a = litepublisher::$db->fetchassoc($res)) {
      $t->post = tpost::instance();
      $t->setassoc($a);
      $result[] = $t->post->id;
    }
    return $result;
  }
  
  public function select($where, $limit) {
    $db = litepublisher::$db;
    $res = $db->query("select $db->posts.*, $db->urlmap.url as url  from $db->posts, $db->urlmap
    where $where and  $db->urlmap.id  = $db->posts.idurl $limit");
    
    return $this->transformres($res);
  }
  
  public function getcount() {
    if (dbversion) {
      return $this->db->getcount("status<> 'deleted'");
    } else {
      return count($this->items);
    }
  }
  
  public function getwidgetcontent($id, $sitebar) {
    $list = $this->getrecent($this->recentcount);
    $theme = ttheme::instance();
    return $theme->getpostswidgetcontent($list, $sitebar, '');
  }
  
  public function add(tpost $post) {    if ($post->posted == 0) $post->posted = time();
    if ($post->icon == 0) {
      $icons = ticons::instance();
      $post->icon = $icons->getid('post');
    }
    
    $post->modified = time();
    $post->pagescount = count($post->pages);
    $post->title = tcontentfilter::escape($post->title);
    $linkgen = tlinkgenerator::instance();
    $post->url = $linkgen->addurl($post, 'post');
    $urlmap = turlmap::instance();
    if (dbversion) {
      $post->addtodb();
      $post->idurl = $urlmap->add($post->url, get_class($post), (int) $post->id);
      $post->db->setvalue($post->id, 'idurl', $post->idurl);
    } else {
      $post->id = ++$this->autoid;
      $dir =litepublisher::$paths->data . 'posts' . DIRECTORY_SEPARATOR  . $post->id;
      @mkdir($dir, 0777);
      @chmod($dir, 0777);
      $post->idurl = $urlmap->Add($post->url, get_class($post), $post->id);
    }
    $this->lock();
    $this->updated($post);
    $this->cointerface('add', $post);
    $post->save();
    $this->unlock();
    $this->added($post->id);
    $this->changed();
    $urlmap->clearcache();
    return $post->id;
  }
  
  public function edit(tpost $post) {
    $post->title = tcontentfilter::quote(trim(strip_tags($post->title)));
    $linkgen = tlinkgenerator::instance();
    $linkgen->editurl($post, 'post');
    $post->modified = time();
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
      //$this->deletedeleted();
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
  
  public function deletedeleted() {
    $deleted = $this->db->idselect("status = 'deleted'");
    if (count($deleted) == 0) return;
    $deleted = implode(',', $deleted);
    $db = litepublisher::$db;
    $db->exec("delete from $db->urlmap where id in
    (select idurl from $this->thistable where id in ($deleted))");
    
    $this->getdb($this->rawtable)->delete("id in ($deleted)");
    $this->getdb('pages')->delete("id in ($deleted)");
    
    $db->exec("delete from $db->postsmeta where id in ($deleted)");
    $this->db->delete("id in ($deleted)");
    $this->cointerface('deletedeleted', $deleted);
  }
  
  public function updated(tpost $post) {
    if (($post->status == 'published') && ($post->posted > time())) {
      $post->status = 'future';
      if ($this->dbversion) $post->db->setvalue($post->id, 'status', 'future');
    }
    $this->PublishFuture();
    if (!$this->dbversion) {
      $this->items[$post->id] = array(
      'posted' => $post->posted
      );
      if   ($post->status != 'published') $this->items[$post->id]['status'] = $post->status;
      if   ($post->author > 1) $this->items[$post->id]['author'] = $post->author;
    }
    $this->UpdateArchives();
    $Cron = tcron::instance();
    $Cron->add('single', get_class($this), 'dosinglecron', $post->id);
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
    if (dbversion) {
      $list = implode(', ', $items);
      return $this->db->idselect("status = 'published' and id in ($list)");
    } else {
      return array_intersect($items, array_keys($this->archives));
    }
  }
  
  public function sortbyposted(array $items) {
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
  
  
  //fix call reference
  public function beforecontent($id, &$result) {
    $this->callevent('beforecontent', array($id, &$result));
  }
  
  public function aftercontent($id, &$result) {
    $this->callevent('aftercontent', array($id, &$result));
  }
  
}//class

?>