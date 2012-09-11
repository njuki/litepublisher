<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tregservices extends titems {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = false;
    parent::create();
    $this->basename = 'regservices' . DIRECTORY_SEPARATOR . 'index';
    $this->data['dirname'] = '';
    $this->data['url'] = '/admin/regservice.htm';
    $this->data['widget'] = '';
    $this->data['widget_title'] = '';
  }
  
  public function add(tregservice $service) {
    $this->lock();
    $this->items[$service->name] = get_class($service);
    $service->save();
    $this->update_widget();
    $this->unlock();
  }
  
  public function update_widget() {
    $widget = '';
    $url = litepublisher::$site->url . $this->url . litepublisher::$site->q . 'id';
    $iconurl = litepublisher::$site->files . '/plugins/' . $this->dirname . '/icons/';
    foreach ($this->items as $name => $classname) {
      $service = getinstance($classname);
      if ($service->valid()) {
        //$icon = $service->icon ? sprintf('<img src="%s%s" alt="%s" height="32" width="32" />', $iconurl, $service->icon, $service->title) : '';
        //$widget .= sprintf('<li><a href="%s=%s&backurl=" class="$name-regservice">%s%s</a></li>', $url, $name, $icon, $service->title);
        $widget .= "<li><a href=\"$ur=$name&backurl=\"><span class=\"$name-regservice\"></span>$service->title</a></li>";
      }
    }
    $widget = str_replace('&', '&amp;', $widget);
    $this->widget = $this->widget_title . sprintf('<ul class="regservices">%s</ul>', $widget);
    $this->save();
    
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
    // hook for clien disabled cookies
    if (!isset($_GET['cookietest'])) {
      setcookie('litepubl_cookie_test', 'test', time() + 8000, litepublisher::$site->subdir . '/', false);
      return litepublisher::$urlmap->redir(litepublisher::$urlmap->url . '&cookietest=true');
    }
    
    if (!isset($_COOKIE['litepubl_cookie_test'])) return 403;
    setcookie('litepubl_cookie_test', '', 0, litepublisher::$site->subdir . '/', false);
    
    $id = empty($_GET['id']) ? 0 : $_GET['id'];
    if (!isset($this->items[$id])) return 404;
    $service = getinstance($this->items[$id]);
    if (!$service->valid()) return 403;
    $url = $service->getauthurl();
    if (!$url) return 403;
    if (!empty($_GET['backurl'])) setcookie('backurl', $_GET['backurl'], time() + 8 * 3600, litepublisher::$site->subdir . '/', false);
    
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