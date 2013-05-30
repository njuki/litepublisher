<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class ulogin extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->addevents('added', 'onadd');
$this->table = 'ulogin';
    $this->data['url'] = '/admin/ulogin.htm';
$this->data['panel'] = '';
$this->data['button'] = '';
$this->data['nets'] = array();
  }

  public function add($id, $service, $uid) {
    if (($id == 0) || ($service == '') || ($uid == '')) return;
if (!in_array($service, $this->data['nets'])) {
$this->data['nets'][] = $service;
$this->save();
tdbmanager::i()->add_enum($this->table, 'service', $service);
}

    $this->db->insert(array(
    'id' => $id,
    'service' => $service,
    'uid' => $uid
    ));
    
    $this->added($id, $service);
return $id;
  }
  
  public function find($service, $uid) {
    return $this->db->findid('service = '. dbquote($service) . ' and uid = ' . dbquote($uid));
  }

public function userdeleted($id) {
$this->db->delete("id = $id");
}


  public function request($arg) {
    $this->cache = false;
    Header( 'Cache-Control: no-cache, must-revalidate');
    Header( 'Pragma: no-cache');

    if (empty($_POST['token'])) return 403;
if (!($s = http::get('http://ulogin.ru/token.php?token=' . $_POST['token'] . '&host=' . $_SERVER['HTTP_HOST']))) return 403;
if (!($info = json_decode($s, true))) return 403;
if (isset($info['error']) || !isset($info['network'])) return 403;

$name =!empty($info['first_name']) ? $info['first_name'] : '';
$name .=!empty($info['last_name']) ? ' . ' . $info['last_name'] : '';
if (!$name && !empty($info['nickname'])) $name = $info['nickname'];

      $uid = !empty($info['uid']) ? $info['uid'] :
(!empty($info['id']) ? $info['id'] : 
(!empty($info['identity']) ? $info['identity'] : 
(!empty($info['profile']) ? $info['profile'] : '')));
        if (strlen($uid) >= 22) $uid = basemd5($uid);

    $users = tusers::i();
    if (!empty($info['email'])) {
      if ($id = $users->emailexists($info['email'])) {
        $user = $users->getitem($id);
        if ($user['status'] == 'comuser') $users->approve($id);
      } elseif (litepublisher::$options->reguser) {
        $id = $users->add(array(
        'email' => $info['email'],
        'name' => $name,
        'website' => empty($info['profile']) ? '' : tcontentfilter::clean_website($info['profile']),
        ));
        if ($uid) {
          $this->add($id, $info['network'], $uid);
        }
      } else {
        //registration disabled
        return 403;
      }
    } else {
      if ($uid) {
        if ($id = $this->find($info['network'], $uid)){
          //nothing
        } elseif (litepublisher::$options->reguser) {
          $id = $users->add(array(
          'email' => '',
          'name' => $name,
        'website' => empty($info['profile']) ? '' : tcontentfilter::clean_website($info['profile']),
          ));
          $users->approve($id);
          $this->add($id, $info['network'], $uid);
        } else {
          //registration disabled
          return 403;
        }
      } else {
        //nothing found and hasnt email or uid
        return 403;
      }
    }
    
    $expired = time() + 1210000;
    $cookie = md5uniq();
    litepublisher::$options->user = $id;
    litepublisher::$options->updategroup();
    litepublisher::$options->setcookies($cookie, $expired);
    if (litepublisher::$options->ingroup('admin')) setcookie('litepubl_user_flag', 'true', $expired, litepublisher::$site->subdir . '/', false);
    
    setcookie('litepubl_regservice', $info['network'], $expired, litepublisher::$site->subdir . '/', false);
    
    $this->onadd($id, $info);

if (!empty($_GET['backurl'])) {
      $backurl = $_GET['backurl'];
        } elseif (!empty($_COOKIE['backurl'])) {
      $backurl = $_COOKIE['backurl'];
    } else {
      $user = $users->getitem($id);
      $backurl =  tusergroups::i()->gethome($user['idgroups'][0]);
    }

    return litepublisher::$urlmap->redir($backurl);
  }
  
public function addpanel($s, $panel) {
$open = '<!--ulogin-->';
$close = '<!--/ulogin-->';
$s = $this->deletepanel($s);
return $open . $panel . $close;
}

public function deletepanel($s) {
$open = '<!--ulogin-->';
$close = '<!--/ulogin-->';
    if (false !== ($i = strpos($s, $open))) {
if ($j = strpos($s, $close)) {
$s = trim(substr($s, 0, $i)) .
 trim(substr($s, $j + strlen($close) + 1));
} else {
$s = trim(substr($s, 0, $i));
}
}
return $s;
}

}//class