<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tregservice extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->data['name'] = 'service';
    $this->data['title'] = 'service';
    $this->data['icon'] = '';
    $this->data['url'] = '';
    $this->data['client_id'] = '';
    $this->data['client_secret'] = '';
  }
  
  public function getbasename() {
    return 'regservices' . DIRECTORY_SEPARATOR . $this->name;
  }
  
  public function valid() {
    return $this->client_id && $this->client_secret;
  }
  
  public function install() {
    if ($this->url) litepublisher::$urlmap->addget($this->url, get_class($this));
  }
  
  public function uninstall() {
    turlmap::unsub($this);
  }
  
  public static function http_post($url, array $post) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    
    $response = curl_exec($ch);
    $headers = curl_getinfo($ch);
    curl_close($ch);
    if ($headers['http_code'] != "200") return false;
    return $response;
  }
  
  
  public function start_session() {
    ini_set('session.use_cookies', 1);
    ini_set('session.use_trans_sid', 0);
    ini_set('session.use_only_cookies', 1);
    
    if (tfilestorage::$memcache) {
      ini_set('session.save_handler', 'memcache');
      ini_set('session.save_path', 'tcp://127.0.0.1:11211');
    } else {
      ini_set('session.save_handler', 'files');
    }
    
    session_cache_limiter(false);
    //session_id (md5($this->token));
    session_start();
  }
  
  //handle callback
  public function request($arg) {
    $this->cache = false;
    if (empty($_REQUEST['code'])) return 403;
    $this->start_session();
    if (empty($_REQUEST['state']) || empty($_SESSION['state'])) return 403;
    if ($_REQUEST['state'] != $_SESSION['state']) return 403;
    session_destroy();
  }
  
  public function newstate() {
    $this->start_session();
    $state = md5(mt_rand() . litepublisher::$secret. microtime());
    $_SESSION['state'] = $state;
    session_write_close();
    return $state;
  }
  
  public function getauthurl() {
    $url = 'response_type=code';
    $url .= '&redirect_uri=' . urlencode(litepublisher::$site->url . $this->url);
    $url .= '&client_id=' . $this->client_id;
    $url .= '&state=' . $this->newstate();
    return $url;
  }
  
  protected function getadmininfo($lang) {
    return array(
    'regurl' => '',
    'client_id' => $lang->client_id,
    'client_secret' =>$lang->client_secret
    );
  }
  
  public function gettab($html, $args, $lang) {
    $a = $this->getadmininfo($lang);
    $result = $html->p(sprintf(tlocal::i()->reg, $a['regurl'], litepublisher::$site->url . $this->url));
    $result .= $html->getinput('text', "client_id_$this->name", tadminhtml::specchars($this->client_id), $a['client_id']) ;
    $result .= $html->getinput('text', "client_secret_$this->name", tadminhtml::specchars($this->client_secret), $a['client_secret']) ;
    return $result;
  }
  
  public function errorauth() {
  }
  
  public function adduser(array $item) {
    $users = tusers::i();
    if (!empty($item['email'])) {
      if ($id = $users->indexOf('email', $item['email'])) {
        
      } else {
      }
    } else {
      $reguser =tregserviceuser::i();
      $uid = !empty($item['uid']) ? $item['uid'] : (!empty($item['website']) ? $item['website'] : '');
      if ($uid) {
        if ($id = $reguser->find($this->name, $uid)){
        } else {
          $id =
          $reguser->add($id, $this->name, $uid);
        }
      } else {
        //nothing found and hasnt email or uid
        return 403;
      }
    }
    
    $expired = time() + 1210000;
    $cookie = md5uniq();
    litepublisher::$options->user = $id;
    litepublisher::$options->setcookies($cookie, $expired);
    $groups = tusergroups::i();
    if ($groups->ingroup($id, 'admin')) setcookie('litepubl_user_flag', 'true', $expired, litepublisher::$site->subdir . '/', false);
    if (!empty($_COOKIE['backurl'])) {
      $backurl = $_COOKIE['backurl'];
    } else {
      $user = $users->getitem($id);
      $backurl =  $groups->gethome($user['idgroups'][0]);
    }
    
    return turlmap::redir($backurl);
  }
  
}//class

class tregserviceuser extends titems {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->basename = 'regservices' . DIRECTORY_SEPARATOR . 'users';
    $this->table = 'regservices';
  }
  
  public function add($id, $service, $uid) {
    if (dbversion) {
      $this->db->insert_a(array(
      'id' => $id,
      'service' => $service,
      'uid' => $uid
      ));
    } else {
      $this->items[$id] = array(
      'service' => $service,
      'uid' => $uid
      );
      $this->save();
    }
  }
  
  public function find($service, $uid) {
    if ($this->dbversion){
      return $this->db->findid('service = '. dbquote($service) . ' and uid = ' . dbquote($uid));
    }
    
    foreach ($this->items as $id => $item) {
      if (($item['service'] == $service) && ($item['uid'] == $uid)) {
        return $id;
      }
    }
    return false;
  }
  
}//class