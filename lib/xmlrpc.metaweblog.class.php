<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class TXMLRPCMetaWeblog extends TXMLRPCAbstract {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function MWSetPingCommentStatus(array &$Struct, tpost $post) {
    global $options;
    if(isset($struct["mt_allow_comments"])) {
      if(!is_numeric($struct["mt_allow_comments"])) {
        switch($struct["mt_allow_comments"]) {
          case "closed":
          $post->commentsenabled = false;
          break;
          case "open":
          $post->commentsenabled = true;
          break;
          default:
          $post->commentsenabled = $options->commentsenabled;
          break;
        }
      }
      else {
        switch((int) $struct["mt_allow_comments"]) {
          case 0:
          $post->commentsenabled = false;
          break;
          case 1:
          $post->commentsenabled = true;
          break;
          default:
          $post->commentsenabled = $options->commentsenabled;
          break;
        }
      }
    }
    else {
      $post->commentsenabled = $options->commentsenabled;
    }
    
    if(isset($struct["mt_allow_pings"])) {
      if(!is_numeric($struct["mt_allow_pings"])) {
        switch($struct['mt_allow_pings']) {
          case "closed":
          $post->pingenabled = false;
          break;
          case "open":
          $post->pingenabled = true;
          break;
          default:
          $post->pingenabled = $options->pingenabled;
          break;
        }
      }
      else {
        switch((int) $struct["mt_allow_pings"]) {
          case 0:
          $post->pingenabled = false;
          break;
          case 1:
          $post->pingenabled = true;
          break;
          default:
          $post->pingenabled = $options->pingenabled;
          break;
        }
      }
    }
    else {
      $post->pingenabled = $options->pingenabled;
    }
  }
  
  protected function MWSetDate(array &$struct, $post) {
    if (empty($struct['dateCreated'])) {
      $post->posted = time();
    } else {
      $post->posted = $struct['dateCreated']->getTimestamp();
    }
  }
  
  //forward implementation
  public function wp_newPage(&$args) {
    if (!$this->canlogin($args, 1))  return $this->error;
    
    $menus = tmenus::instance();
    $menu = tmenu::instance(0);
    $menu->status = $args[4] == 'publish' ? 'published' : 'draft';
    $struct = &$args[3];
    $this->WPAssignPage($struct, $menu);
    return (int) $menus->add($menu);
  }
  
  protected function  WPAssignPage(array &$struct, tmenu $menu) {
    if(isset($struct["wp_slug"])) {
      $linkgen = tlinkgenerator::instance();
      $menu->url = $linkgen->AddSlashes($struct['wp_slug']);
    }
    
    if(isset($struct["wp_password"])) {
      $menu->password = $struct["wp_password"];
    }
    
    if(isset($struct["wp_page_parent_id"])) {
      $menu->parent = (int) $struct["wp_page_parent_id"];
    }
    
    if(isset($struct["wp_page_order"])) {
      $menu->order = (int) $struct["wp_page_order"];
    }
    $menu->title = $struct['title'];
    $menu->content = $struct['description'];
    if ($struct['mt_text_more']) {
      $menu->content = $post->rawcontent . "\n[more " . TLocal::$data['post']['more'] . "]\n".  $struct['mt_text_more'];
    }
    
    $this->MWSetDate($struct, $post);
  }
  
  protected function  MWSetPost(array &$struct, tpost $post) {
    if(isset($struct["wp_slug"])) {
      $linkgen = tlinkgenerator::instance();
      $post->url = $linkgen->AddSlashes($struct["wp_slug"] . '/');
    }
    
    if(isset($struct["wp_password"])) {
      $post->password = $struct["wp_password"];
    }
    
    $post->title = $struct['title'];
    
    $more = isset($struct['mt_text_more']) ? trim($struct['mt_text_more']) : '';
    if ($more == '') {
      $post->content = $struct['description'];
    } else {
      $post->content = $struct['description']. '[more '. TLocal::$data['post']['more'] ."]\n". $more;
    }
    
    $excerpt =isset($struct['mt_excerpt']) ? trim($struct['mt_excerpt']) : '';
    if ($excerpt != '') $post->excerpt = $excerpt;
    
    $this->MWSetDate($struct, $post);
    
    if (!empty($struct['mt_keywords'])) {
      $post->tagnames = $struct['mt_keywords'];
    }
    
    if (isset($struct['categories']) && is_array($struct['categories'])) {
      $post->catnames = $struct['categories'];
    }
  }
  
  public function wp_editPage(&$args) {
    if (!$this->canlogin($args, 2)) return $this->Error;
    
    $id	= (int) $args[1];
    $menus = tmenus::instance();
    if (!$menus->itemexists($id))  return new IXR_Error(404, "Sorry, no such page.");
    
    $menu= tmenu::instance($id);
    $struct	= &$args[4];
    $menu->status = $args[5] == 'publish' ? 'published' : 'draft';
    $this->WPAssignPage($struct, $post);
    $menus->edit($post);
    return true;
  }
  
  public function getCategories(&$args) {
    global $options;
    if (!$this->canlogin($args,1)) return $this->Error;
    
    $categories = tcategories::instance();
    if (dbversion) {
      global $db;
      $res = $db->query("select $categories->thistable.*, $db->urlmap.url as url  from $categories->thistable,  $db->urlmap
      where $db->urlmap.id  = $categories->thistable.idurl");
      $items =  $res->fetchAll(PDO::FETCH_ASSOC);
    } else {
      $Items = &$categories->items;
    }
    $result = array();
    foreach ( $Items as $item) {
      $result[] = array(
      'categoryId' => $item['id'],
      'parentId' => $item['parent'],
      'description' => $categories->contents->getdescription($item['id']),
      'categoryName' => $item['title'],
      'title' => $item['title'],
      'htmlUrl' => $options->url . $item['url'],
      'rssUrl' =>  $options->url . $item['url']
      );
    }
    
    return $result;
  }
  
  public function newPost(&$args) {
    $struct = &$args[3];
    if(!empty($struct["post_type"]) && ($struct["post_type"] == "page")) {
      return (string) $this->wp_newPage($args);
    }
    
    if (!$this->canlogin($args, 1))  return $this->Error;
    
    $posts = tposts::instance();
    $post = tpost::instance(0);
    $post->status = $args[4] == 'publish' ? 'published' : 'draft';
    $this->MWSetPost($struct, $post);
    $posts->add($post);
    return (string) $post->id;
  }
  
  public function editPost(&$args) {
    $struct = &$args[3];
    if(!empty($struct["post_type"]) && ($struct["post_type"] == "page")) {
      return (string) $this->wp_editPage($args);
    }
    
    if (!$this->canlogin($args, 1))  return $this->Error;
    
    $id=(int) $args[0];
    $posts = tposts::instance();
    if (!$posts->itemexists($id))  return new IXR_Error(404, "Invalid post id.");
    
    $post = tpost::instance($id);
    $post->status = $args[4] == 'publish' ? 'published' : 'draft';
    $this->MWSetPost($struct, $post);
    $posts->edit($post);
    return true;
  }
  
  public function getPost(&$args) {
    if (!$this->canlogin($args, 1))  return $this->Error;
    
    $id=(int) $args[0];
    $posts = tposts::instance();
    if (!$posts->itemexists($id))  return new IXR_Error(404, "Invalid post id.");
    
    $post = tpost::instance($id);
    return $this->GetStruct($post);;
  }
  
  private function GetStruct(tpost $post) {
    global $options;
    return array(
    'dateCreated' => new IXR_Date($post->date),
    'userid' => (string) $post->author,
    'postid' =>  (string) $post->id,
    'description' => $post->rawcontent,
    'title' => $post->title,
    'link' => $post->link,
    'permaLink' => $post->link,
    'categories' => $post->catnames,
    'mt_excerpt' => $post->excerpt,
    'mt_text_more' => '',
    'mt_allow_comments' => $post->commentsenabled ? 1 : 0,
    'mt_allow_pings' => $post->pingenabled ? 1 : 0,
    'mt_keywords' => $post->tagnames,
    'wp_slug' => $post->url,
    'wp_password' => $post->password,
    'wp_author_id' => $post->author,
    'wp_author_display_name'	=> 'admin',
    'date_created_gmt' => new IXR_Date($post->posted- $options->gmt),
    'publish' => $post->status == 'published'
    );
  }
  
  public function getRecentPosts(&$args) {
    if (!$this->canlogin($args, 1))  return $this->Error;
    
    $count = (int) $args[3];
    $posts = tposts::instance();
    $list = $posts->getrecent($count);
    $posts->loaditems($list);
    $result = array();
    foreach ($list as $id) {
      $post = tpost::instance($id);
      $result[] = $this->GetStruct($post);
    }
    
    return $result;
  }
  
  public function newMediaObject(&$args) {
    global $options;
    if (!$this->canlogin($args, 1))  return $this->Error;
    
    $data        = &$args[3];
    $filename = $data['name'] ;
    $mimetype =$data['type'];
    $overwrite = isset($data["overwrite"]) && ($data["overwrite"] == true) ? true : false;
    
    if (empty($filename)) {
      return new IXR_Error(500, "Empty filename");
    }
    
    $parser = tmediaparser::instance();
    $id = $parser->upload($filename, $data['bits'], '', $overwrite );
    if (!$id)  return new IXR_Error(500, "Could not write file $name");
    $files = tfiles::instance();
    $item = $files->getitem($id);
    
    return array(
    'file' => $item['filename'],
    'url' => $files->geturl($id),
    'type' => $item['mime']
    );
  }
  
}

?>