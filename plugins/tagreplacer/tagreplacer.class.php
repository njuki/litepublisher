<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class ttagreplacer extends titems {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename=  'plugins' .DIRECTORY_SEPARATOR  . strtolower(get_class($this));
}

    public function themeparsed(ttheme $theme) {
foreach ($this->items as $item) {
if (isset($theme->templates[$item['template']])) && (false == strpos($theme->templates[$item['template']], $item['replace']))) {
$theme->templates[$item['template']] = str_replace($item['source'], $item['replace'], $theme->templates[$item['template']]);
}
}
}

}//class