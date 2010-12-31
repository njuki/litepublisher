<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class TXMLRPCMovableType extends TXMLRPCAbstract {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  // on success, array of structs containing ISO.8601 dateCreated, String userid, String postid, String title; on failure, fault
  public function getRecentPostTitles($blogid , $username, $password, $count) {
    $this->auth($username, $password, 'editor');
    $count =(int) $count;
    $posts = tposts::instance();
    $list = $posts->GetRecent($count);
    $posts->loaditems($list);
    $result = array();
    foreach ($list as $id) {
      $post = tpost::instance($id);
      $result[] = array(
      'dateCreated' => new IXR_Date($post->posted),
      'userid' => (string) $post->author,
      'postid' => (string) $post->id,
      'title' => $post->title
      );
    }
    
    return $result;
  }
  // On success, an array of structs containing String categoryId and String categoryName; on failure, fault.
  public function getCategoryList($blogid, $username, $password) {
    $this->auth($username, $password, 'editor');
    $categories = tcategories::instance();
    $categories->loadall();
    $result = array();
    foreach ($categories->items as $id => $item) {
      $result[] = array(
      'categoryId' => (string) $id,
      'categoryName' => $item['title']
      );
    }
    return $result;
  }
  // on success, an array of structs containing String categoryName, String categoryId, and boolean isPrimary; on failure, fault.
  public function getPostCategories($id, $username, $password) {
    $this->auth($username, $password, 'editor');
    $id = (int) $id;
    $posts = tposts::instance();
    if (!$posts->itemexists($id)) return $this->xerror(404, "Invalid post id.");
    $post = tpost::instance($id);
    $categories = tcategories::instance();
    $categories->loaditems($post->categories);
    $isPrimary = true;
    $result = array();
    foreach ($post->categories as $idcat) {
      $item = $categories->getitem($idcat);
      $result[] =array(
      'categoryName' => $item['title'],
      'categoryId' => (string) $idcat,
      'isPrimary' => 			$isPrimary
      );
      $isPrimary = false;
    }
    return $result;
  }
  
  // on success, boolean true value; on failure, fault
  public function setPostCategories($id, $username, $password, $catlist) {
    $this->auth($username, $password, 'editor');
    $id = (int) $id;
    $posts = tposts::instance();
    if (!$posts->itemexists($id)) return $this->xerror(404, "Invalid post id.");
    $post = tpost::instance($id);
    
    $list = array();
    foreach ($catlist as  $Cat) {
      $list[] = $Cat['categoryId'];
    }
    $post->categories = $list;
    $posts->edit($post);
    return true;
  }
  
  public function supportedTextFilters() {
    return array();
  }
  
  public function getTrackbackPings($id) {
    $id = (int) $id;
    $posts = tposts::instance();
    if (!$posts->itemexists($id)) return $this->xerror(404, "Invalid post id.");
    $post = tpost::instance($id);
    if ($post->status != 'published') return $this->xerror(403, 'Target post not published');
    $result = array();
    $pingbacks = tpingbacks::instance($id);
    if (dbversion) {
      $items = $tpingbacks->db->getitems("post = $id and status = 'approved' order by posted");
      foreach ($items as $item) {
        $result[] = array(
        'pingIP' => $item['ip'],
        'pingURL' => $item['url'],
        'pingTitle' => $item['title']
        );
      }
    } else {
      foreach ($pingbacks->items as $url => $item) {
        if (!$item['approved']) continue;
        $result[] = array(
        'pingIP' => $item['ip'],
        'pingURL' => $item['url'],
        'pingTitle' => $item['title']
        );
      }
    }
    return $result;
  }
  
  public function publishPost($id, $username, $password) {
    $this->auth($username, $password, 'editor');
    $id = (int) $id;
    $posts = tposts::instance();
    if (!$posts->itemexists($id)) return $this->xerror(404, "Invalid post id.");
    $post = tpost::instance($id);
    $post->status = 'published';
    $posts->edit($post);
    return true;
  }
  
}//class

?>