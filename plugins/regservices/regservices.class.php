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
    $iconurl = litepublisher::$site->files . '/plugins/' . basename(dirname(__file__)) . '/icons/';
    foreach ($this->items as $name => $classname) {
      $service = getinstance($classname);
      if ($service->valid()) {
        $icon = $service->icon ? sprintf('<img src="%s%s" alt="%s" height="32" width="32" />', $iconurl, $service->icon, $service->title) : '';
        $widget .= sprintf('<li><a href="%s=%s&backurl=">%s%s</a></li>', $url, $name, $icon, $service->title);
      }
    }
    $this->widget = $this->widget_title . sprintf('<ul>%s</ul>', $widget);
    $this->save();
    
    $admin = tadminlogin::i();
    $admin->widget = $this->widget;
    $admin->save();
    
    $admin = tadminreguser::i();
    $admin->widget = $this->widget;
    $admin->save();
  }
  
  public function request($arg) {
    $this->cache = false;
    $id = empty($_GET['id']) ? 0 : $_GET['id'];
    if (!isset($this->items[$id])) return 404;
    $service = getinstance($this->items[$id]);
    if (!$service->valid) return 403;
    $url = $service->getauthurl();
    if (!empty($_GET['backurl'])) setcookie('backurl', $_GET['backurl'], time() + 8 * 3600, litepublisher::$site->subdir . '/', false);
    
    return turlmap::redir($url);
  }
  
}//class