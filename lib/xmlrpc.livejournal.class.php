<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class TXMLRPCLivejournal extends TXMLRPCAbstract {
  
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->data['challenge'] = '';
    $this->data['expired'] = 0;
  }
  
  private function CheckLogin(&$struct) {
    global $options;
    extract($struct);
    if ($username != $Options->login) return false;
    
    switch ($auth_method) {
      case 'challenge':
      return ($this->challenge == $auth_challenge) && ($auth_response == md5($this->challenge . $Options->password));
      
      case 'clear':
      return $this->password == md5("$Options->login:$Options->realm:$password");
      
      case 'cookie':
      
    }
    
    return false;
  }
  
  public function login(&$args) {
    if (!$this->CheckLogin($args[0])) {
      return new IXR_Error(403, 'Bad login/pass combination.');
    }
    
    $profile = &TProfile::Instance();
    $result = array(
    'userid' => 1,
    'fullname' => $profile->nick,
    'friendgroups' => array()
    )  ;
    return $result;
  }
  
  public function getchallenge(&$args) {
    $this->challenge =md5(secret. uniqid( microtime()) . 'challenge');
    $this->expired = time() + 3600;
    $this->Save();
    
    return array(
    'auth_scheme' => 'c0',
    'challenge' => $this->challenge,
    'expire_time' => $this-> expired,
    'server_time' => time()
    );;
  }
  
  public function postevent(&$args) {
    if (!$this->CheckLogin($args[0])) {
      return new IXR_Error(403, 'Bad login/pass combination.');
    }
    return $this->EditPost($args[0], 0);
  }
  
  private function EditPost(&$struct, $id) {
    $post = &TPost::Instance($id);
    $post->content = $struct['event'];
    //$lineendings = $struct['lineendings']; canbe \n \r \r\n
    $post->title = $struct['subject'];
    
    /* not supported
    if (isset($struct['security'])) {
      switch ($struct['security']) {
        case 'public':
        break;
        
        case 'private':
        break;
        
        case 'usemask':
        $allowmask = $args[0]['allowmask'];
        
        // A 32-bit unsigned integer representing which of the user's groups of friends are allowed to view this post. Turn bit 0 on to allow any defined friend to read it. Otherwise, turn bit 1-30 on for every friend group that should be allowed to read it. Bit 31 is reserved.
        break;
      }
    }
    */
    
    $post->date = mktime($struct['hour'], $struct['min'], 0, $struct['mon'], $struct['day'], $struct['year']);
    
    if (isset($struct['props'])) {
      $props = &$struct['props'];
      $post->commentsenabled = $props['opt_nocomments'] ? false : true;
      if ($props['opt_preformatted']) {
        $post->filtered = $args[0]['event'];
      }
      
      if (isset($props['taglist'])) {
        $post->tagnames = $props['taglist'];
      }
      
      if (isset($props['statusvis']) ) {
        $post->status = $props['statusvis'] == 'S' ? 'draft' : 'published';
      }
      
    }
    
    /* not supported
    if (isset($struct['usejournal'])) {
      //Journal username that authenticating user has 'usejournal' access in, as given in the 'login' mode.
      $usejournal = $struct['usejournal'];
    }
    */
    
    $posts = &TPosts::Instance();
    if ($id == 0) {
      $id = $posts->Add($post);
    } else {
      $posts->Edit($post);
    }
    
    return array(
    'itemid' => $id,
    'anum' => $post->url,
    'url' => $post->url
    );;
  }
  
  public function editevent (&$args) {
    if (!$this->CheckLogin($args[0])) {
      return new IXR_Error(403, 'Bad login/pass combination.');
    }
    $id = (int) $args[0]['itemid'];
    $posts = &TPosts::Instance();
    if (!$posts->ItemExists($id)) {
      return new IXR_Error(404, "Invalid post id.");
    }
    if (empty($args[0]['event'])) {
      $post = &TPost::Instance($id);
      $url = $post->url;
      $posts->Delete($id);
      return array(
      'itemid' => $id,
      'anum' => $url,
      'url' => $url
      );;
    }
    
    return $this->EditPost($args[0]);
  }
  
  /*
  public function checkfriends ($args) {
    if (!$this->CheckLogin($args[0])) {
      return new IXR_Error(403, 'Bad login/pass combination.');
    }
  }
  */
  
}//class

?>