<?php

class TXMLRPCBlogger  extends TXMLRPCAbstract {
  
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  public function getUsersBlogs(&$args) {
    global $Options;
    if (!$this->CanLogin($args, 1)) {
      return $this->Error;
    }
    
    $Result = array(
    //'isAdmin'  => true,
    'url'      => $Options->url . $Options->home,
    'blogid'   => '1',
    'blogName' => $Options->name
    );
    return array($Result);
  }
  
  public function getUserInfo(&$args) {
    global $Options;
    if (!$this->CanLogin($args, 1)) {
      return $this->Error;
    }
    
    $Result= array(
    'nickname'  => 'admin',
    'userid'    => 1,
    'url'       => $Options->url .'/',
    'lastname'  => '',
    'firstname' => ''		);
    return $Result;
  }
  
  public function getPost(&$args) {
    if (!$this->CanLogin($args, 2)) {
      return $this->Error;
    }
    $id    = (int) $args[1];
    $Posts= &TPost::Instance();
    if (!$Posts->ItemExists($id)) {
      return new IXR_Error(404, "Sorry, no such post.");
    }
    
    $Post = &TPost::Instance($id);
    $categories = implode(',', $Post->categories);
    
    $content  = '<title>'.$Post->title .'</title>';
    $content .= '<category>'.$categories.'</category>';
    $content .= $Post->content;
    
    $Result= array(
    'userid'    => 1,
    'dateCreated' => new IXR_Date($Post->date),
    'content'     => $content,
    'postid'  => $id
    );
    
    return $Result;
  }
  
  public function getRecentPosts(&$args) {
    if (!$this->CanLogin($args, 2)) {
      return $this->Error;
    }
    
    $num_posts  = $args[4];
    $Posts = &TPosts::Instance();
    $Items = $Posts->GetPublishedRange(0, $num_posts  );
    
    foreach ($Items as $id) {
      $Post = &TPost::Instance($id);
      $categories = implode(',', $Post->categories);
      $content  = '<title>'.$Post->title . '</title>';
      $content .= '<category>'.$categories.'</category>';
      $content .= $Post->content;
      
      $Result[] = array(
      'userid' => 1,
      'dateCreated' => new IXR_Date($Post->date),
      'content' => $content,
      'postid' => $Post->id,
      );
    }
    
    return $Result;
  }
  
  public function getTemplate(&$args) {
    if (!$this->CanLogin($args, 2)) {
      return $this->Error;
    }
    
    $template   = $args[4]; /* could be 'main' or 'archiveIndex', but we don't use it */
    //craze method
    return '';
  }
  public function setTemplate(&$args) {
    if (!$this->CanLogin($args, 2)) {
      return $this->Error;
    }
    
    $content    = $args[4];
    $template   = $args[5]; /* could be 'main' or 'archiveIndex', but we don't use it */
    //not supported
    return true;
  }
  
  private function getposttitle($content) {
    if ( preg_match('/<title>(.+?)<\/title>/is', $content, $matchtitle) ) {
      $Result = $matchtitle[0];
      $Result = preg_replace('/<title>/si', '', $Result);
      $Result = preg_replace('/<\/title>/si', '', $Result);
    } else {
      $Result = 'no title';
    }
    return $Result;
  }
  
  private function getpostcategory($content) {
    if ( preg_match('/<category>(.+?)<\/category>/is', $content, $matchcat) ) {
      $Result = trim($matchcat[1], ',');
      $Result = explode(',', $Result);
    } else {
      $Result = array(1);
    }
    return $Result;
  }
  
  private function removepostdata($content) {
    $content = preg_replace('/<title>(.+?)<\/title>/si', '', $content);
    $content = preg_replace('/<category>(.+?)<\/category>/si', '', $content);
    $content = trim($content);
    return $content;
  }
  
  public function newPost($args) {
    if (!$this->CanLogin($args, 2)) {
      return $this->Error;
    }
    
    $Posts = &TPosts::Instance();
    $Post = &TPost::Instance(0);
    
    $content    = $args[4];
    $publish    = $args[5];
    $Post->status = ($publish) ? 'published' : 'draft';
    
    $Post->title = $this->getposttitle($content);
    $Post->content = $this->removepostdata($content);
    $Post->categories = $this->getpostcategory($content);
    
    $id = $Posts->Add($Post);
    return $id;
  }
  
  function editPost(&$args) {
    if (!$this->CanLogin($args, 2)) {
      return $this->Error;
    }
    
    $id     = (int) $args[1];
    $content     = $args[4];
    $publish     = $args[5];
    $Posts = &TPosts::Instance();
    if (!$Posts->itemExists($id)) {
      return new IXR_Error(404, 'Sorry, no such post.');
    }
    $Post = &TPost::Instance($id);
    $Post->status = ($publish) ? 'published' : 'draft';
    $Post->title = $this->getposttitle($content);
    $Post->content = $this->removepostdata($content);
    $Post->categories = $this->getpostcategory($content);
    $Posts->Edit($Post);
    return true;
  }
  
  public function deletePost(&$args) {
    if (!$this->CanLogin($args, 2)) {
      return $this->Error;
    }
    
    $id     = (int) $args[1];
    $Posts = &TPosts::Instance();
    if (!$Posts->itemExists($id)) {
      return new IXR_Error(404, 'Sorry, no such post.');
    }
    $Posts->Delete($id);
    return true;
  }
  
}

?>