<?php
require_once(dirname(__file__) . DIRECTORY_SEPARATOR  . 'xmlrpc-abstractclass.php');

class TXMLRPCMovableType extends TXMLRPCAbstract {
  
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  public function getRecentPostTitles(&$args) {
    if (!$this->CanLogin($args, 1)) {
      return $this->Error;
    }
    $count =(int) $args[3];
    $Posts = &TPosts::Instance();
    $list = $Posts->GetRecent($count);
    $Result = array();
    foreach ($list as $id) {
      $Item = &TPost::Instance($id);
      $Result[] = array(
      'dateCreated' => new IXR_Date($Item->date),
      'userid' => 1,
      'postid' => $Item->id,
      'title' => $Item->title,
      'date_created_gmt' => new IXR_Date($Item->date + gmt_offset)
      );
    }
    
    return $Result;
  }
  
  public function getCategoryList(&$args) {
    if (!$this->CanLogin($args, 1)) {
      return $this->Error;
    }
    
    $Categories = &TCategories::Instance();
    $Items = &$Categories->items;
    $Result = array();
    foreach ($Items as $id => $Item) {
      $Result[] = array(
      'categoryId' => (string) $id,
      'categoryName' => $Item['name']
      );
    }
    return $Result;
  }
  
  public function getPostCategories(&$args) {
    if (!$this->CanLogin($args, 1)) {
      return $this->Error;
    }
    
    $id = (int) $args[0];
    $Posts = &TPosts::Instance();
    if (!$Posts->ItemExists($id)) {
      return new IXR_Error(404, "Invalid post id.");
    }
    $Post = &TPost::Instance($id);
    $Categories = &TCategories::Instance();
    $Items = &$Categories->items;
    $isPrimary = true;
    $Result = array();
    foreach ($Post->categories as $id) {
      $Result =array(
      'categoryName' => $Items[$id]['name'],
      'categoryId' => (string) $id,
      'isPrimary' => 			$isPrimary
      );
      $isPrimary = false;
    }
    return $Result;
  }
  
  public function setPostCategories(&$args) {
    if (!$this->CanLogin($args, 1)) {
      return $this->Error;
    }
    
    $id = (int) $args[0];
    $Posts = &TPosts::Instance();
    if (!$Posts->ItemExists($id)) {
      return new IXR_Error(404, "Invalid post id.");
    }
    
    $CatList = $args[3];
    $list = array();
    foreach ($CatList as  $Cat) {
      $list[] = $Cat['categoryId'];
    }
    $Post = &TPost::Instance($id);
    $Post->categories = $list;
    $Posts->Edit($Post);
    return true;
  }
  
  public function supportedMethods(&$args) {
    $Caller = &TXMLRPC::Instance();
    return array_keys($Caller->methods);
  }
  
  public function supportedTextFilters(&$args) {
    return array();
  }
  
  public function getTrackbackPings(&$args) {
    $id = intval($args);
    $Posts = &TPosts::Instance();
    if (!$Posts->ItemExists($id)) {
      return new IXR_Error(404, "Invalid post id.");
    }
    $Post = &TPost::Instance($id);
    //not implemeted
    return $Result;
  }
  
  public function publishPost(&$args) {
    if (!$this->CanLogin($args, 1)) {
      return $this->Error;
    }
    
    $id = (int) $args[0];
    $Posts = &TPosts::Instance();
    if (!$Posts->ItemExists($id)) {
      return new IXR_Error(404, "Invalid post id.");
    }
    
    $Post = &TPost::Instance($id);
    $Post->status = 'published';
    $Posts->Edit($Post);
    return true;
  }
  
}

?>