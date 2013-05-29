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
    $this->data['url'] = '/admin/regservice.htm';
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

  public function adduser(array $item, $rawdata) {
    $users = tusers::i();
    $reguser =tregserviceuser::i();
    if (!empty($item['email'])) {
      if ($id = $users->emailexists($item['email'])) {
        $user = $users->getitem($id);
        if ($user['status'] == 'comuser') $users->approve($id);
      } elseif (litepublisher::$options->reguser) {
        $id = $users->add(array(
        'email' => $item['email'],
        'name' => $item['name'],
        'website' => isset($item['website']) ? tcontentfilter::clean_website($item['website']) : ''
        ));
        if (isset($item['uid'])) {
          $uid = $item['uid'];
          if (strlen($uid) >= 22) $uid = basemd5($uid);
          $reguser->add($id, $this->name, $uid);
        }
      } else {
        //registration disabled
        return 403;
      }
    } else {
      $uid = !empty($item['uid']) ? $item['uid'] : (!empty($item['website']) ? $item['website'] : '');
      if ($uid) {
        if (strlen($uid) >= 22) $uid = basemd5($uid);
        if ($id = $reguser->find($this->name, $uid)){
          //nothing
        } elseif (litepublisher::$options->reguser) {
          $id = $users->add(array(
          'email' => '',
          'name' => $item['name'],
          'website' => isset($item['website']) ? tcontentfilter::clean_website($item['website']) : ''
          ));
          $users->approve($id);
          $reguser->add($id, $this->name, $uid);
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
    
    setcookie('litepubl_regservice', $this->name, $expired, litepublisher::$site->subdir . '/', false);
    
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
  
  public function request($arg) {
    $this->cache = false;
    Header( 'Cache-Control: no-cache, must-revalidate');
    Header( 'Pragma: no-cache');
    
    // hook for clien disabled cookies
    if (!isset($_GET['cookietest'])) {
      $backurl = !empty($_GET['backurl']) ? $_GET['backurl'] : (!empty($_GET['amp;backurl']) ? $_GET['amp;backurl'] :  (isset($_COOKIE['backurl']) ? $_COOKIE['backurl'] : ''));
      if ($backurl) setcookie('backurl', $backurl, time() + 8 * 3600, litepublisher::$site->subdir . '/', false);
      setcookie('litepubl_cookie_test', 'test', time() + 8000, litepublisher::$site->subdir . '/', false);
      return litepublisher::$urlmap->redir(litepublisher::$urlmap->url . '&cookietest=true');
    }
    
    if (!isset($_COOKIE['litepubl_cookie_test'])) return 403;
    setcookie('litepubl_cookie_test', '', 0, litepublisher::$site->subdir . '/', false);
    
    $url = $service->getauthurl();
    if (!$url) return 403;
    return litepublisher::$urlmap->redir($url);
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