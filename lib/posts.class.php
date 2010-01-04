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
    parent::create();
    $this->dbversion = dbversion;
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
      if ($res = $this->db->select("id = $id")) {
        if ($result = tpost::instance($id)) return $result;
      }
      $this->error("Item $id not found in class ". get_class($this));
    } else {
      if (isset($this->items[$id])) return tpost::instance($id);
    }
    return $this->error("Item $id not found in class ". get_class($this));
  }
  
  public function loaditems(array $items) {
    global $classes;
    if (!dbversion) return;
    //исключить из загрузки загруженные посты
    $class = $classes->classes['post'];
    if (isset(titem::$instances[$class])) {
      $items = array_diff($items, array_keys(titem::$instances[$class]));
    }
    if (count($items) == 0) return;
    $list = implode(',', $items);
    return $this->select("$this->thistable.id in ($list)", '');
  }
  
  private function transformres($res) {
    $result = array();
    $t = new tposttransform();
    $res->setFetchMode (PDO::FETCH_INTO , $t);
    do {
      if (isset($t->post)) $result[] = $t->post->id;
      $t->post = tpost::instance();
    } while ($res->fetch());
    return $result;
  }
  
  public function select($where, $limit) {
    global $db;
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
    global $options, $post;
    $theme = ttheme::instance();
    $tml = $theme->getwidgetitem('posts', $sitebar);
    
    $result = '';
    $list = $this->getrecent($this->recentcount);
    foreach ($list as $id) {
      $post = tpost::instance($id);
      $result .= $theme->parse($tml);
    }
    $result = str_replace("'", '"', $result);
    return $result;
  }
  
  public function add(tpost $post) {    if ($post->posted == 0) $post->posted = time();
    $icons = ticons::instance();
    $post->icon = $icons->getid('post');
    
    $post->modified = time();
    $post->pagescount = count($post->pages);
    $post->title = tcontentfilter::escape($post->title);
    $linkgen = tlinkgenerator::instance();
    if ($post->url == '' ) {
      $post->url = $linkgen->createlink($post, 'post', true);
    } else {
      $title = $post->title;
      $post->title = trim($post->url, '/');
      $post->url = $linkgen ->createlink($post, 'post', true);
      $Post->title = $title;
    }
    
    $urlmap = turlmap::instance();
    if (dbversion) {
      $post->id = tposttransform ::add($post);
      $post->idurl = $urlmap->add($post->url, get_class($post), $post->id);
      $post->db->setvalue($post->id, 'idurl', $post->idurl);
      
    } else {
      global $paths;
      $post->id = ++$this->autoid;
      $dir =$paths['data'] . 'posts' . DIRECTORY_SEPARATOR  . $post->id;
      @mkdir($dir, 0777);
      @chmod($dir, 0777);
      $post->idurl = $urlmap->Add($post->url, get_class($post), $post->id);
    }
    $this->lock();
    $this->updated($post);
    $post->save();
    $this->unlock();
    $this->added($post->id);
    $this->changed();
    $urlmap->clearcache();
    return $post->id;
  }
  
  public function edit(tpost $post) {
    $post->title = tcontentfilter::quote(trim(strip_tags($post->title)));
    $urlmap = turlmap::instance();
    $oldurl = $urlmap->getidurl($post->idurl);
    if ($oldurl != $post->url) {
      $linkgen = tlinkgenerator::instance();
      if ($post->url == '') {
        $post->url = $linkgen->createlink($post, 'post', false);
      } else {
        $title = $post->title;
        $post->title = trim($post->url, '/');
        $post->url = $linkgen->Create($post, 'post', false);
        $post->title = $title;
      }
    }
    
    if ($oldurl != $post->url) {
      //check unique url
      if (($idurl = $urlmap->idfind($post->url)) && ($idurl != $post->idurl)) {
        $post->url = $linkgen->MakeUnique($post->url);
      }
      $urlmap->setidurl($post->idurl, $post->url);
      $urlmap->addredir($oldurl, $post->url);
    }
    
    $post->modified = time();
    $this->lock();
    $this->updated($post);
    $post->save();
    $this->unlock();
    $this->edited($post->id);
    $this->changed();
    $urlmap->clearcache();
  }
  
  public function delete($id) {
    global $classes, $paths;
    if (!$this->itemexists($id)) return false;
    $urlmap = turlmap::instance();
    if ($this->dbversion) {
      $idurl = $this->db->getvalue($id, 'idurl');
      $this->db->setvalue($id, 'status', 'deleted');
      /* will be deleted indbmanager optimize
      $this->db->iddelete($id);
      $this->getdb('pages')->delete("post = $id");
      $this->getdb($this->rawtable)->iddelete($id);
      */
    } else {
      $post = tpost::instance($id);
      $idurl = $post->idurl;
      $post->free();
      TItem::DeleteItemDir($paths['data']. 'posts'. DIRECTORY_SEPARATOR   . $id . DIRECTORY_SEPARATOR  );
      unset($this->items[$id]);
      $urmap->deleteitem($idurl);
    }
    
    $this->lock();
    $this->PublishFuture();
    $this->UpdateArchives();
    $this->unlock();
    $this->deleted($id);
    $this->changed();
    $urlmap->clearcache();
    return true;
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
    }
    $this->UpdateArchives();
    $Cron = tcron::instance();
    $Cron->add('single', get_class($this), 'dosinglecron', $post->id);
  }
  
  public function UpdateArchives() {
    if ($this->dbversion) {
      $this->archivescount = $this->db->getcount("status = 'published' and posted <= now()");
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
    $GLOBALS['post'] = tpost::instance($id);
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
      if ($list = $this->db->idselect("status = 'future' and posted <= now() order by posted asc")) {
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
  
  public function StripDrafts(array $items) {
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
    
    $result = array_intersect_key ($this->archives, $items);
    arsort($result,  SORT_NUMERIC);
    return array_keys($result);
  }
  
}//class

?>