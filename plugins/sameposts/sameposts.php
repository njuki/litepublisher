<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tsameposts extends tclasswidget {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    if (dbversion) {
      $this->table = 'sameposts';
    } else {
      $this->data['revision'] = 1;
    }

    $this->basename = 'widget.sameposts';
$this->template = 'posts';
$this->adminclass = 'tadminsameposts';
$this->cache = 'nocache';
$this->data['title'] = tlocal::$data['default']['sameposts'];
    $this->data['maxcount'] = 10;
  }
  
  public function postschanged() {
    if (dbversion) {
      $this->db->exec("truncate $this->thistable");
    } else {
      $this->revision += 1;
      $this->save();
    }
  }
  
  private function findsame($idpost) {
    $posts = tposts::instance();
    $post = tpost::instance($idpost);
    $list = $post->categories;
    if (count($list) == 0) return array();
    $cats = tcategories::instance();
    $cats->loadall();
    $same = array();
    foreach ($list as $id) {
      if (!isset($cats->items[$id])) continue;
      $itemsposts = $cats->itemsposts->getposts($id);
      $posts->stripdrafts($itemsposts);
      foreach ($itemsposts as $i) {
        if ($i == $idpost) continue;
        if (isset($same[$i])) {
          $same[$i]++;
        } else {
          $same[$i] = 1;
        }
      }
    }
    
    arsort($same);
return array_slice(array_keys($same), 0, $this->maxcount);
  }
  
  private function getsame($id) {
    if (dbversion) {
      $items = $this->db->getvalue($id, 'items');
      if (is_string($items)) {
        return $items == '' ? array() : explode(',', $items);
      } else {
                $result = $this->findsame($id);
        $this->db->add(array('id' => $id, 'items' => implode(',', $result)));
        return $result;
      }
    } else {
      $filename = litepublisher::$paths->data . 'posts' . DIRECTORY_SEPARATOR . $id .DIRECTORY_SEPARATOR . 'same.php';
      $data = null;
      if (tfiler::unserialize($filename, $data)) {
        if ($data['revision'] == $this->revision) return $data['items'];
      }
      
      $result= $this->findsame($id);
      $data = array(
      'revision' => $this->revision,
      'items' => $result
      );
      tfiler::serialize($filename, $data);
      return $result;
    }
  }
  
  public function getcontent($id, $sitebar) {
    $post = $this->getcontext('tpost');
    $list = $this->getsame($post->id);
    if (count($list) == 0) return'';
    $posts = tposts::instance();
    $posts->loaditems($list);
    $theme = ttheme::instance();
    return $theme->getpostswidgetcontent($list, $sitebar, '');
  }
  
}//class
?>