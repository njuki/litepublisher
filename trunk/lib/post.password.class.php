<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpostpassword extends tevents_itemplate implements itemplate {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'post.password';
  }
  
public function request($arg) {

}

public function gettitle() {}
  
  public function getcont() {
    $this->cache = false;
    $view = tview::getview($this);
    $theme = $view->theme;
    if ($this->text != '') return $theme->simple($this->text);
    
    $lang = tlocal::i('default');
    if ($this->basename == 'forbidden') {
      return $theme->simple(sprintf('<h1>%s</h1>', $lang->forbidden));
    } else {
      return $theme->parse($theme->content->notfound);
    }
  }


public function getform(tpost $post) {
$result = '<?php
if (litepublisher::$options->group != \'admin\') {
$cookie = isset($_COOKIE[\'post_password\']) ? $_COOKIE[\'post_password\'] : '';
if ($cookie != \'' . $this->getpasswordcookie() . '\') {';

//$result .= form

$result .= '} } else { ?>';
return $result;
}
  
}//class  
}//class