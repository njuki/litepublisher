<?php

class trobotstxt extends titems {

  public static function instance() {
    return getinstance(__class__);
  }

  public function create() {
parent::create();
$this->basename = 'robots.txt';
$this->data['idurl'] = 0;
  }
  
    public function AddDisallow($url) {
    return $this->add("Disallow: $url");
  }
  
  public function add($value) {
    if (!in_array($value, $this->items)) {
      $this->items[] = $value;
      $this->save();
      $urlmap = turlmap::instance();
      $Urlmap->setexpired($this->idurl);
      $this->added($value);
    }
  }
  
  public function request($arg) {
    $s = "<?php
    @header('Content-Type: text/plain');
    ?>";
    $s .= implode("\n", $this->items);
    return  $s;
  }
  
}//class

?>