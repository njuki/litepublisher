<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class ulogin extends titems {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
$this->dbversion = true;
    parent::create();
$this->basename = 'ulogin';
$this->table = 'ulogin';
    $this->data['url'] = '/admin/ulogin.htm';
  }

  public function add($id, $service, $uid) {
    if (($id == 0) || ($service == '') || ($uid == '')) return;
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
  
public function set() {
    $admin = tadminlogin::i();
    $admin->widget = $this->widget;
    $admin->save();
    
    $admin = tadminreguser::i();
    $admin->widget = $this->widget;
    $admin->save();
    
    $tc = ttemplatecomments::i();
    if ($i = strpos($tc->regaccount, $this->widget_title)) {
      $tc->regaccount = trim(substr($tc->regaccount, 0, $i));
    }
    $tc->regaccount .= "\n" . $this->widget;
    $tc->save();
  }

  public function request($arg) {
    $this->cache = false;
    Header( 'Cache-Control: no-cache, must-revalidate');
    Header( 'Pragma: no-cache');

    if (empty($_POST['token'])) return 403;
if (!($s = http::get('http://ulogin.ru/token.php?token=' . $_POST['token'] . '&host=' . $_SERVER['HTTP_HOST']))) rturn 403;
if (!($info = json_decode($s, true))) rturn 403;
if (isset($info['error']) || !isset($info['network'])) return 403;

$name =!empty($info['first_name']) ? $info['first_name'] : '';
$name .=!empty($info['last_name']) ? ' . ' . $info['last_name'] : '';


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
        if (!empty($info['uid'])) {
          $uid = $info['uid'];
          if (strlen($uid) >= 22) $uid = basemd5($uid);
          $this->add($id, $info['network'], $uid);
        }
      } else {
        //registration disabled
        return 403;
      }
    } else {
      $uid = !empty($info['uid']) ? $info['uid'] : (!empty($info['profile']) ? $info['profile'] : '');
      if ($uid) {
        if (strlen($uid) >= 22) $uid = basemd5($uid);
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
    
    $this->onadd($id, $rawdata);
    
    if (isset($this->sessdata['comuser'])) {
      return tcommentform::i()->processform($this->sessdata['comuser'], true);
    }
    
    if (!empty($_COOKIE['backurl'])) {
      $backurl = $_COOKIE['backurl'];
    } else {
      $user = $users->getitem($id);
      $backurl =  tusergroups::i()->gethome($user['idgroups'][0]);
    }
    
    return litepublisher::$urlmap->redir($backurl);
  }
  
  public function oncomuser(array $values, $comfirmed) {
    //ignore $comfirmed, always return redirect
    $form = tcommentform::i();
    if ($err = $form->processcomuser($values)) return $err;
    $email = strtolower(trim($values['email']));
    $host = substr($email, strpos($email, '@') + 1);
    switch ($host) {
      case 'gmail.com':
      $name = 'google';
      break;
      
      case 'yandex.ru':
      $name = 'yandex';
      break;
      
      case 'mail.ru':
      case 'inbox.ru':
      case 'list.ru':
      case 'bk.ru':
      $name = 'mailru';
      break;
      
      default:
      return false;
    }
    
    if (!isset($this->items[$name])) return false;
    $service = getinstance($this->items[$name]);
    if (!$service->valid) return false;
    $service->sessdata['comuser'] = $values;
    $url = $service->getauthurl();
    if (!$url) return false;
    
    return $form->sendresult($url, array(
    ini_get('session.name') => $service->session_id
    ));
  }
  
}//class