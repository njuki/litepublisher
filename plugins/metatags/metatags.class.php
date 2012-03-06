<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tmetatags extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function themeparsed(ttheme $theme) {
$theme->templates['index'] = strtr($theme->templates['index'], array(
'$template.keywords' => '$metatags.keywords',
'$template.description' => '$metatags.description',
));
}

public function getkeywords() {
$context = ttemplate::i()->context;
if ($context instanceof thomepage) {
} elseif ($context instanceof tcommontags) {
} else {
return ttemplate::i()->getkeywords();
}
}

public function getdescription() {
$context = ttemplate::i()->context;
if ($context instanceof thomepage) {
} elseif ($context instanceof tcommontags) {
} else {
return ttemplate::i()->getdescription();
}

}

}//class