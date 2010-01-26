<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class TXMLRPCWordpress extends TXMLRPCMetaWeblog {
  public static function instance() {
    return getinstance(__class__);
  }
  
  private function menutostruct($ID) {
    $id	= (int) $ID;
        $menus = tmenus::instance();
    if (!$menus->itemexists($id))  return xerror(404, "Sorry, no such page.");
    $menu = tmenu::instance($id);

    if ($MENU->parent > 0) {
$parent= tmenu::instance($menu->parent);
      $ParentTitle = $parent->title;
    } else {
      $ParentTitle = "";
    }
    
    $Result = array(
    "dateCreated"			=> new IXR_Date($MENU->date),
    "userid"				=> $MENU->author,
    "page_id"				=> $MENU->id,
    "page_status"			=> $MENU->status == 'published' ? 'publish' : 'draft',
    "description"			=> $MENU->content,
    "title"					=> $MENU->title,
    "link"					=> $MENU->url,
    "permaLink"				=> $MENU->url,
    "categories"			=> array(),
    "excerpt"				=> '',
    "text_more"				=> '',
    //"mt_allow_comments"		=> $MENU->commentsenabled ? 1 : 0,
    "mt_allow_comments"		=> 0,
    //"mt_allow_pings"		=> $MENU->pingenabled ? 1 : 0,
    "mt_allow_pings"		=> 0,
    
    "wp_slug"				=> $MENU->url,
    "wp_password"			=> $MENU->password,
    "wp_author"				=> 'ADMIN',
    "wp_page_parent_id"		=> $MENU->parent,
    "wp_page_ParentTitle"	=> $ParentTitle,
    "wp_page_order"			=> $MENU->order,
    "wp_author_id"			=> $MENU->author,
    "wp_author_display_name"	=> 'ADMIN',
    "date_created_gmt"		=> new IXR_Date($MENU->date - $options->gmt)
    );
    
    return$Result;
  }
  
// return struct
  public function wp_getPage($blogid, $id, $username, $password) {
$this->auth($username, $password, 'editor');
    return $this->menutostruct($id);
  }
  
  public function wp_getPages($blogid, $username, $password) {
    $this->auth($username, $password, 'editor');
    $result = array();
    $menus = tmenus::instance();
    foreach ($menus->Items as $id => $item) {
      $result[] = $this->menutostruct($id);
    }
    return $result;
  }

  public function wp_getPageList($blogid, $username, $password) {
    $this->auth($username, $password, 'editor');
    $result = array();
    $menus = tmenus::instance();
    foreach ($menus->Items as $id => $item) {
      $result[] = array(
      'page_id' => $id,
      'page_title' => $item['title'],
      'page_parent_id' => $item['parent'],
      'dateCreated' => new IXR_Date(time()),
      );
    }
    
    return $result;
  }
  
  public function wp_deletePage($blogid, $username, $password, $id) {
    $this->auth($username, $password, 'editor');
$id = (int) $id;
    $menus = tmenus::instance();
    if (!$menus->itemexists($id))  return xerror(404, "Sorry, no such page.");
    $menus->delete($id);
return true;
  }
  
  public function wp_newCategory($blogid, $username, $password, $struct) {
    $this->auth($username, $password, 'editor');
    $categories = tcategories::instance();
    return(int) $categories->add($struct["name"], $category["slug"]);
  }

  public function deleteCategory ($blogid, $username, $password, $id) {
    $this->auth($username, $password, 'editor');
$id = (int) $id;
    $categories = tcategories::instance();
     if (!$categories->itemexists($id))  return xerror(404, "Sorry, no such page.");
$categories->delete($id);
return true;
  }

}//class

?>