<?php
require_once(dirname(__file__) . DIRECTORY_SEPARATOR  . 'xmlrpc-abstractclass.php');

class TXMLRPCMetaWeblog extends TXMLRPCAbstract {
  
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  protected function MWSetPingCommentStatus(&$Struct, &$Item) {
    global $Options;
    if(isset($Struct["mt_allow_comments"])) {
      if(!is_numeric($Struct["mt_allow_comments"])) {
        switch($Struct["mt_allow_comments"]) {
          case "closed":
          $Item->commentsenabled = false;
          break;
          case "open":
          $Item->commentsenabled = true;
          break;
          default:
          $Item->commentsenabled = $Options->commentsenabled;
          break;
        }
      }
      else {
        switch((int) $Struct["mt_allow_comments"]) {
          case 0:
          $Item->commentsenabled = false;
          break;
          case 1:
          $Item->commentsenabled = true;
          break;
          default:
          $Item->commentsenabled = $Options->commentsenabled;
          break;
        }
      }
    }
    else {
      $Item->commentsenabled = $Options->commentsenabled;
    }
    
    if(isset($Struct["mt_allow_pings"])) {
      if(!is_numeric($Struct["mt_allow_pings"])) {
        switch($Struct['mt_allow_pings']) {
          case "closed":
          $Item->pingenabled = false;
          break;
          case "open":
          $Item->pingenabled = true;
          break;
          default:
          $Item->pingenabled = $Options->pingenabled;
          break;
        }
      }
      else {
        switch((int) $Struct["mt_allow_pings"]) {
          case 0:
          $Item->pingenabled = false;
          break;
          case 1:
          $Item->pingenabled = true;
          break;
          default:
          $Item->pingenabled = $Options->pingenabled;
          break;
        }
      }
    }
    else {
      $Item->pingenabled = $Options->pingenabled;
    }
  }
  
  protected function MWSetDate(&$Struct, &$item) {
    if (empty($Struct['dateCreated'])) {
      $item->date = time();
    } else {
      $item->date = $Struct['dateCreated']->getTimestamp();
    }
  }
  
  //forward implementation
  public function wp_newPage(&$args) {
    if (!$this->CanLogin($args, 1)) {
      return $this->Error;
    }
    
    $Menu = &TMenu::Instance();
    $Item = &TMenuItem::Instance(0);
    $Item->status = $args[4] == 'publish' ? 'published' : 'draft';
    $Struct = &$args[3];
    $this->WPAssignPage($Struct, $item);
    $Menu->Add($Item);
    return  $Item->id;
  }
  
  protected function  WPAssignPage(&$Struct, &$Item) {
    if(isset($Struct["wp_slug"])) {
      $Item->url = TLinkGenerator::AddSlashes($Struct['wp_slug']);
    }
    
    if(isset($Struct["wp_password"])) {
      $Item->password = $Struct["wp_password"];
    }
    
    if(isset($Struct["wp_page_parent_id"])) {
      $Item->parent = $Struct["wp_page_parent_id"];
    }
    
    if(isset($Struct["wp_page_order"])) {
      $item->order = $Struct["wp_page_order"];
    }
    $Item->title = $Struct['title'];
    $Item->content = $Struct['description'];
    if ($Struct['mt_text_more']) {
      $Item->content = $Item->rawcontent . "\n[more " . TLocal::$data['post']['more'] . "]\n".  $Struct['mt_text_more'];
    }
    
    $this->MWSetPingCommentStatus($Struct, $Item);
    $this->MWSetDate($Struct, $Item);
  }
  
  protected function  MWSetPost(&$Struct, &$Item) {
    if(isset($Struct["wp_slug"])) {
      $Item->url = TLinkGenerator::AddSlashes($Struct["wp_slug"] . '/');
    }
    
    if(isset($Struct["wp_password"])) {
      $Item->password = $Struct["wp_password"];
    }
    
    $Item->title = $Struct['title'];
    
    $more = isset($Struct['mt_text_more']) ? trim($Struct['mt_text_more']) : '';
    if ($more == '') {
      $Item->content = $Struct['description'];
    } else {
      $Item->content = $Struct['description']. '[more '. TLocal::$data['post']['more'] ."]\n". $more;
    }
    
    $excerpt =isset($Struct['mt_excerpt']) ? trim($Struct['mt_excerpt']) : '';
    if ($excerpt != '') $Item->excerpt = $excerpt;
    
    $this->MWSetDate($Struct, $Item);
    
    if (!empty($Struct['mt_keywords'])) {
      $Item->tagnames = $Struct['mt_keywords'];
    }
    
    if (isset($Struct['categories']) && is_array($Struct['categories'])) {
      $Item->catnames = $Struct['categories'];
    }
  }
  
  public function wp_editPage(&$args) {
    if (!$this->CanLogin($args, 2)) {
      return $this->Error;
    }
    
    $id	= (int) $args[1];
    $Menu = &TMenu::Instance();
    if (!$Menu->ItemExists($id)) {
      return new IXR_Error(404, "Sorry, no such page.");
    }
    
    $Item = &TMenuItem::Instance($id);
    $Struct	= &$args[4];
    $Item->status = $args[5] == 'publish' ? 'published' : 'draft';
    $this->WPAssignPage($Struct, $item);
    $Menu->Edit($Item);
    return true;
  }
  
  public function getCategories(&$args) {
    global $Options;
    if (!$this->CanLogin($args,1)) {
      return $this->Error;
    }
    $Categories = &TCategories::Instance();
    $Items = &$Categories->items;
    $Result = array();
    foreach ( $Items as $id => $Item) {
      $Result[] = array(
      'categoryId' => $id,
      'parentId' => 0,
      'description' => $Item['name'],
      'categoryName' => $Item['name'],
      'title' => $Item['name'],
      'htmlUrl' => $Options->url . $Item['url'],
      'rssUrl' =>  $Options->url . $Item['url']
      );
    }
    
    return $Result;
  }
  
  public function newPost(&$args) {
    $Struct = &$args[3];
    if(!empty($Struct["post_type"]) && ($Struct["post_type"] == "page")) {
      return (string) $this->wp_newPage($args);
    }
    
    if (!$this->CanLogin($args, 1)) {
      return $this->Error;
    }
    
    $Posts = &TPosts::Instance();
    $Item = &TPost::Instance(0);
    $Item->status = $args[4] == 'publish' ? 'published' : 'draft';
    $this->MWSetPost($Struct, $Item);
    $Posts->Add($Item);
    return (string) $Item->id;
  }
  
  public function editPost(&$args) {
    $Struct = &$args[3];
    if(!empty($Struct["post_type"]) && ($Struct["post_type"] == "page")) {
      return (string) $this->wp_editPage($args);
    }
    
    if (!$this->CanLogin($args, 1)) {
      return $this->Error;
    }
    
    $id=(int) $args[0];
    $Posts = &TPosts::Instance();
    if (!$Posts->ItemExists($id)) {
      return new IXR_Error(404, "Invalid post id.");
    }
    
    $Item = &TPost::Instance($id);
    $Item->status = $args[4] == 'publish' ? 'published' : 'draft';
    $this->MWSetPost($Struct, $Item);
    $Posts->Edit($Item);
    return true;
  }
  
  public function getPost(&$args) {
    if (!$this->CanLogin($args, 1)) {
      return $this->Error;
    }
    
    $id=(int) $args[0];
    $Posts = &TPosts::Instance();
    if (!$Posts->ItemExists($id)) {
      return new IXR_Error(404, "Invalid post id.");
    }
    
    $Item = &TPost::Instance($id);
    return $this->GetStruct($Item);;
  }
  
  private function GetStruct(&$Item) {
    global $Options;
    return array(
    'dateCreated' => new IXR_Date($Item->date),
    'userid' => '1',
    'postid' =>  (string) $Item->id,
    'description' => $Item->rawcontent,
    'title' => $Item->title,
    'link' => $Options->url . $Item->url,
    'permaLink' => $Options->url . $Item->url,
    'categories' => $Item->catnames,
    'mt_excerpt' => $Item->excerpt,
    'mt_text_more' => '',
    'mt_allow_comments' => $Item->commentsenabled ? 1 : 0,
    'mt_allow_pings' => $Item->pingenabled ? 1 : 0,
    'mt_keywords' => $Item->tagnames,
    'wp_slug' => $Item->url,
    'wp_password' => $Item->password,
    'wp_author_id' => 1,
    'wp_author_display_name'	=> 'admin',
    'date_created_gmt' => new IXR_Date($Item->date - gmt_offset)
    );
  }
  
  public function getRecentPosts(&$args) {
    if (!$this->CanLogin($args, 1)) {
      return $this->Error;
    }
    
    $count = (int) $args[3];
    $Posts = &TPosts::Instance();
    $list = $Posts->GetRecent($count);
    $Result = array();
    foreach ($list as $id) {
      $Item = &TPost::Instance($id);
      $Result[] = $this->GetStruct($Item);
    }
    
    return $Result;
  }
  
  public function newMediaObject(&$args) {
    global $Options;
    if (!$this->CanLogin($args, 1)) {
      return $this->Error;
    }
    
    $data        = &$args[3];
    $filename = $data['name'] ;
    $mimetype =$data['type'];
    $overwrite = isset($data["overwrite"]) && ($data["overwrite"] == true) ? true : false;
    
    if (empty($filename)) {
      return new IXR_Error(500, "Empty filename");
    }
    
    $files = &TFiles::Instance();
    $id = $files->AddFile($filename, $data['bits'], '', $overwrite );
    if (!$id) {
      return new IXR_Error(500, "Could not write file $name");
    }
    
    return array(
    'file' => $files->items[$id]['filename'],
    'url' => $Options->url . $files->Geturl($id),
    'type' => $mimetype
    );
  }
  
}

?>