<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/


class tadminsameposts {
  
  public function getcontent() {
    $tml = '<h2>$note</h2>
    <form name="form" action="" method="post" >
    <p><strong>$templ:</strong><br />
    [area:tml]</p>
    
    <p><input type="submit" name="Submit" value="$lang.update"/></p>
    </form>';
    
    $plugin = tsameposts::instance();
    $html = THtmlResource::instance();
    $args = targs::instance();
    $args->tml = $plugin->tml;
    $admin = tadminplugins::instance();
    $about = $admin->abouts[$_GET['plugin']];
    $args->note = $about['note'];
    $args->templ =  $about['templ'];
    return $html->parsearg($tml, $args);
  }
  
  public function processform() {
    $plugin = tsameposts::instance();
    $plugin->lock();
    $plugin->tml = $_POST['tml'];
    $plugin->unlock();
    return '';
  }
  
}
?>