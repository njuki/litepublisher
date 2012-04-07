<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tjsonserver extends titems {

  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = false;
    parent::create();
    $this->basename = 'jsonserver';
    $this->cache = false;
    $this->addevents('beforecall', 'aftercall', 'getmethods');
    $this->data['eventnames'] = &$this->eventnames;
    $this->map['eventnames'] = 'eventnames';
  }
  
  public function request($param) {
    global$HTTP_RAW_POST_DATA;
    if ( !isset( $HTTP_RAW_POST_DATA ) ) {
      $HTTP_RAW_POST_DATA = file_get_contents( 'php://input' );
    }
    if ( isset($HTTP_RAW_POST_DATA) ) {
      $HTTP_RAW_POST_DATA = trim($HTTP_RAW_POST_DATA);
    }
    
    if (litepublisher::$debug) {
      tfiler::log("request:\n" . $HTTP_RAW_POST_DATA, 'json.txt');
      $reqname = litepublisher::$paths->data . 'logs' . DIRECTORY_SEPARATOR  . 'request.json';
      file_put_contents($reqname, $HTTP_RAW_POST_DATA);
      @chmod($reqname, 0666);
      //$HTTP_RAW_POST_DATA = file_get_contents($GLOBALS['paths']['home'] . 'raw.txt');
    }
    
    $this->getmethods();
    $Result = $this->Server->XMLResult;
    $this->aftercall();
    if (litepublisher::$debug) tfiler::log("response:\n".$Result, 'json.txt');

    $head = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
    $length = strlen($head) + strlen($xml);
    $this->XMLResult = "<?php
    @header('Connection: close');
    @header('Content-Length: $length');
    @header('Content-Type: text/xml; charset=utf-8');
    @header('Date: ".date('r') . "');
    @Header( 'Cache-Control: no-cache, must-revalidate');
    @Header( 'Pragma: no-cache');
    @header('X-Pingback: ". litepublisher::$site->url . "/rpc.xml');
    echo'$head';
    ?>" . $xml;

    return $Result;
  }
  
  public function addevent($name, $class, $func) {
if (!in_array($method, $this->eventnames)) $this->eventnames[] = $method;
return parent::addevent($name, $class, $func);
  }
  
}//class

class TXMLRPCAbstract extends tevents {
  
  public function uninstall() {
    $caller = TXMLRPC::i();
    $caller->deleteclass(get_class($this));
  }
  
  public static function auth($email, $password, $group) {
    if (litepublisher::$options->auth($email, $password))  {
      if ((litepublisher::$options->group == 'admin') || (litepublisher::$options->group == $group) || ($group == 'nobody')) return true;
      $groups = tusergroups::i();
      if ($groups->hasright(litepublisher::$options->group, $group)) return true;
    }
    throw new Exception('Bad login/pass combination.', 403);
  }
  
  public static function canedit($email, $password, $idpost) {
    if (litepublisher::$options->auth($email, $password))  {
      $group = litepublisher::$options->group;
      if (($group == 'admin') || ($group == 'editor')) return true;
      $groups = tusergroups::i();
      if ($groups->hasright($group, 'author')) {
        if ($idpost == 0) return true;
        $post = tpost::i($idpost);
        return $post->author == litepublisher::$options->user;
      }
    }
    throw new Exception('Bad login/pass combination.', 403);
  }
  
  public static function xerror($code, $msg) {
    return new IXR_Error($code, $msg);
  }
  
}//class