><?php

class tthemetree extends tplugin {
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->basename = 'themetree';
}

public function getsingleitem($name) {
return sprintf('<li><a rel="%s" href="">%s</a></li>', $name, $this->getitemtitle($name));
}

public function generatetree() {
$result = '';
//root tags
$result .= $this->getsingle('title');
$result .= $this->gettree('menu');
$result .= $this->gettree('content');
$result .= $this->getsidebars();
return sprintf('<ul id="themetree">%s</ul>', $result);
}

}//class