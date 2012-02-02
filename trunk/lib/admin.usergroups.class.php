<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminusergroups extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function getcontent() {
    $result = '';
    $groups = tusergroups::i();
    $html = $this->html;
    $lang = tlocal::i('users');
    $args = targs::i();

switch ($this->action) {
case 'add':
break;

case 'edit':
break;

'delete':
break;
}

$result .= $html->buildtable($groups->items, array(
    array('center', '+', '<input type="checkbox" name="checkbox_$id" id="checkbox_$id" value="$from" />'),
    array('left', $lang->name, '<a href="$site.url$from" title="$from">$from</a>'),
    array('left', $lang->from, '<a href="$site.url$from" title="$from">$from</a>'),
));
return $result;
}
    
  public function processform() {
    $groups = tusergroups::i();
}

}//class