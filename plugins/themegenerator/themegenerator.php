<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tthemegenerator extends tevents_itemplate {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename=  'plugins' .DIRECTORY_SEPARATOR  . strtolower(get_class($this));
  }

public function gethead() {

}
  public function getcont() {
    if (empty($theme->templates['content.admin.color'])) {
      $about = tplugins::getabout(tplugins::getname(__file__));
      $theme->templates['content.admin.color'] =
      '<p>
      <input type="text" name="$name" id="text-$name" value="$value" size="22" />
      <label for="text-$name"><strong>$lang.$name</strong></label>
      <input type="button" name="colorbutton-$name" id="colorbutton-$name" rel="text-$name"
      value="' . $about['changecolor'] . '" />
      </p>';
    }
  }
  
}//class