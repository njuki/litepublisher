<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminregoauth2 implements iadmin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function  gethead() {
    return tuitabs::gethead();
  }
  
  public function getcontent() {
    $plugin = tregoauth2 ::i();
    $html = tadminhtml::i();
    $tabs = new tuitabs();
    $args = targs::i();
    $lang = tplugins::getlangabout(__file__);
    $args->formtitle = $lang->options;
        foreach ($plugin->items as $id => $item) {
$service = getinstance($item['class']);
      $tabs->add($service->title, $service->gettab($html, $args, $lang));
    }
    
    return $html->adminform($tabs->get(), $args);
  }
  
  public function processform() {
    $plugin = tregoauth2 ::i();
    $plugin->lock();
    foreach ($plugin->items as $id => $item) {
$service = getinstance($item['class']);
if (isset($_POST["client_id_$id"])) $service->client_id = $_POST["client_id_$id"]; 
if (isset($_POST["client_secret_$id"])) $service->client_id = $_POST["client_secret_$id"]; 
$service->save();
    }
    $plugin->unlock();
    return '';
  }
  
}//class