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

public function add(tgoogleregservice $service) {
$this->lock();
$id = $this->additem(array(
'class' => get_class($service),
));
$service->id = $id;
$service->save();
$this->update_widget();
$this->unlock();
return $id;
}

public function update_widget() {
$widget = '';
$url = litepublisher::$site->url . $this->url . litepublisher::$site->q . 'id';
$iconurl = litepublisher::$site->files . '/plugins/bookmarks/icons/';
foreach ($this->items as $id => $item) {
$service = getinstance($item['class']);
if ($service->valid()) {
$icon = $service->icon ? sprintf('<img src="%s%s" alt="%s" />', $iconurl, $service->icon, $service->title) : '';
$widget .= sprintf('<li><a href="%s=%s">%s%s</a></li>', $url, $id, $icon, $service->title);
}
}
$this->widget = $this->widget_title . sprintf('<ul>%s</ul>', $widget);
$this->save();
}

public function oncontent(&$s) {
$s = $this->widget . $s;
}

  public function request($arg) {
$this->cache = false;
$id = empty($_GET['id']) ? 0 : (int) $_GET['id'];
if (!$this->itemexists($id)) return 404;
$service = getinstance($this->items[$id]['class']);
if (!$service->valid) return 403;
$url = $service->getauthurl();
return turlmap::redir($url);
}

}//class