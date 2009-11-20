<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

require_once($paths['llibinclude']. 'class-IXR.php');

class TXMLRPCClient  extends TDataClass {
  protected $client;
  protected $login;
  protected $password;
  
  public static function &Instance($id) {
    return GetInstance(__class__);
  }
  
  public function SetServer($url, $login, $password) {
    $this->client = new IXR_Client($Url. '/rpc.xml');
    $this->login = $login;
    $this->password = $password;
  }
  
  public function metaWeblog_getCategories() {
    $args = array(
    0 => 0,
    1 => $this->login,
    2 => $this->password,
    );
    
    if (!$this->client->query('metaWeblog.getCategories' , $args)) {
      return $this->Error('Something went wrong - '.$this->client->getErrorCode().' : '.
      $this->client->getErrorMessage());
    }
    return $this->client->getResponse();
  }
  
  public function metaWeblog_getRecentPosts($count) {
    $args = array(
    0 => 0,
    1 => $this->login,
    2 => $this->password,
    3 => $count
    );
    
    if (!$this->client->query('metaWeblog.getRecentPosts', $args)) {
      return $this->Error('Something went wrong - '.$this->client->getErrorCode().' : '.
      $this->client->getErrorMessage());
    }
    return $this->client->getResponse();
  }
  
} //class

?>