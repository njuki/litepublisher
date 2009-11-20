<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class TXMLRPCBlogger  extends TXMLRPCAbstract {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function getUsersBlogs(&$args) {
    global $options;
    if (!$this->canlogin($args, 1))  return $this->error;

    $Result = array(
    //'isAdmin'  => true,
    'url'      => $options->url . $options->home,
    'blogid'   => '1',
    'blogName' => $options->name
    );
    return array($Result);
  }
  
  public function getUserInfo(&$args) {
    global $options;
    if (!$this->canlogin($args, 1)) {
      return $this->error;
    }
    
    $Result= array(
    'nickname'  => 'admin',
    'userid'    => 1,
    'url'       => $options->url .'/',
    'lastname'  => '',
    'firstname' => ''		);
    return $Result;
  }
  
  public function getPost(&$args) {
    if (!$this->canlogin($args, 2)) {
      return $this->error;
    }
    $id    = (int) $args[1];
    $posts= tposts::instance();
    if (!$posts->itemexists($id)) {
      return new IXR_Error(404, "Sorry, no such post.");
    }
    
    $Post = tpost::instance($id);
    $categories = implode(',', $Post->categories);
    
    $content  = '<title>'.$Post->title .'</title>';
    $content .= '<category>'.$categories.'</category>';
    $content .= $Post->content;
    
    $Result= array(
    'userid'    => $Post->author,
    'dateCreated' => new IXR_Date($Post->posted),
    'content'     => $content,
    'postid'  => $id
    );
    
    return $Result;
  }
  
  public function getRecentPosts(&$args) {
    if (!$this->canlogin($args, 2)) {
      return $this->error;
    }
    
    $num_posts  = $args[4];
    $Posts = tposts::instance();
    $Items = $Posts->GetPublishedRange(0, $num_posts  );
    
    foreach ($Items as $id) {
      $Post = tpost::instance($id);
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
    if (!$this->canlogin($args, 2)) {
      return $this->error;
    }
    
    $template   = $args[4]; /* could be 'main' or 'archiveIndex', but we don't use it */
    //craze method
    return '';
  }

  public function setTemplate(&$args) {
    if (!$this->canlogin($args, 2)) {
      return $this->error;
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
    if (!$this->canlogin($args, 2)) {
      return $this->error;
    }
    
    $Posts = tposts::instance();
    $Post = tpost::instance(0);
    
    $content    = $args[4];
    $publish    = $args[5];
    $Post->status = ($publish) ? 'published' : 'draft';
    
    $Post->title = $this->getposttitle($content);
    $Post->content = $this->removepostdata($content);
    $Post->categories = $this->getpostcategory($content);
    
    $id = $Posts->add($Post);
    return $id;
  }
  
  function editPost(&$args) {
    if (!$this->canlogin($args, 2)) {
      return $this->error;
    }
    
    $id     = (int) $args[1];
    $content     = $args[4];
    $publish     = $args[5];
    $Posts = &TPosts::instance();
    if (!$Posts->itemExists($id)) {
      return new IXR_Error(404, 'Sorry, no such post.');
    }
    $Post = &TPost::instance($id);
    $Post->status = ($publish) ? 'published' : 'draft';
    $Post->title = $this->getposttitle($content);
    $Post->content = $this->removepostdata($content);
    $Post->categories = $this->getpostcategory($content);
    $Posts->Edit($Post);
    return true;
  }
  
  public function deletePost(&$args) {
    if (!$this->canlogin($args, 2)) {
      return $this->error;
    }
    
    $id     = (int) $args[1];
    $Posts = &TPosts::instance();
    if (!$Posts->itemExists($id)) {
      return new IXR_Error(404, 'Sorry, no such post.');
    }
    $Posts->Delete($id);
    return true;
  }
  
}

?>