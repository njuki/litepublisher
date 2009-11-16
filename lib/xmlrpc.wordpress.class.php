<?php

class TXMLRPCWordpress extends TXMLRPCMetaWeblog {
  public function GetBaseName() {
    return 'wpremote';
  }
  
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  private function GetMenuItemForWP($id) {
    $Menu= &TMenu::Instance();
    if (!$Menu->ItemExists($id)) {
      return new IXR_Error(404, "Sorry, no such page.");
    }
    
    $MenuItem = &TMenuItem::Instance($id);
    
    if ($MenuItem->parent > 0) {
      $ParentTitle = $Menu->GetTitle($MenuItem->parent);
    } else {
      $ParentTitle = "";
    }
    
    $Result = array(
    "dateCreated"			=> new IXR_Date($MenuItem->date),
    "userid"				=> $MenuItem->author,
    "page_id"				=> $MenuItem->id,
    "page_status"			=> $MenuItem->status == 'published' ? 'publish' : 'draft',
    "description"			=> $MenuItem->content,
    "title"					=> $MenuItem->title,
    "link"					=> $MenuItem->url,
    "permaLink"				=> $MenuItem->url,
    "categories"			=> array(),
    "excerpt"				=> '',
    "text_more"				=> '',
    //"mt_allow_comments"		=> $MenuItem->commentsenabled ? 1 : 0,
    "mt_allow_comments"		=> 0,
    //"mt_allow_pings"		=> $MenuItem->pingenabled ? 1 : 0,
    "mt_allow_pings"		=> 0,
    
    "wp_slug"				=> $MenuItem->url,
    "wp_password"			=> $MenuItem->password,
    "wp_author"				=> $Options->authorname,
    "wp_page_parent_id"		=> $MenuItem->parent,
    "wp_page_ParentTitle"	=> $ParentTitle,
    "wp_page_order"			=> $MenuItem->order,
    "wp_author_id"			=> $MenuItem->author,
    "wp_author_display_name"	=> $Options->authorname,
    "date_created_gmt"		=> new IXR_Date($MenuItem->date - gmt_offset)
    );
    
    return$Result;
  }
  
  public function wp_getPage(&$args) {
    global $Options;
    if (!$this->CanLogin($args, 2)){
      return$this->error;
    }
    
    $id	= (int) $args[1];
    return $this->GetMenuItemForWP($id);
  }
  
  public function wp_getPages(&$args) {
    if (!$this->CanLogin($args, 1)) {
      return $this->Error;
    }
    
    $Result = array();
    $Menu = &TMenu::Instance();
    $Items = &$Menu->items;
    foreach ($Items as $id => $Item) {
      $Result[] = $this->GetMenuItemForWP($id);
    }
    return $Result;
  }
  
  public function wp_deletePage(&$args) {
    if (!$this->CanLogin($args, 1)) {
      return $this->Error;
    }
    
    $id	= (int) $args[3];
    $Menu = &TMenu::Instance();
    if (!$Menu->ItemExists($id)) {
      return new IXR_Error(404, "Sorry, no such page.");
    }
    return $Menu->Delete($id);
  }
  
  public function wp_getPageList(&$args) {
    if (!$this->CanLogin($args, 1)) {
      return $this->Error;
    }
    
    $Menu = &TMenu::Instance();
    $Items = &$Menu->items;
    $Result = array();
    foreach ($Items as $id =>$Item) {
      $Result[] = array(
      'page_id' => $id,
      'page_title' => $Item['title'],
      'page_parent_id' => $Item['parent'],
      'dateCreated' => new IXR_Date($Item['date'] ),
      'date_created_gmt' => new IXR_Date($Item['date'] - gmt_offset)
      );
    }
    
    return $Result;
  }
  public function wp_newCategory(&$args) {
    if (!$this->CanLogin($args,1)) {
      return $this->Error;
    }
    
    $category				= &$args[3];
    if(empty($category["slug"])) {
      $category["slug"] = "";
    }
    
    $Categories = &TCategories::Instance();
    return$Categories->Add($category["name"], $category["slug"]);
  }
  
}

?>