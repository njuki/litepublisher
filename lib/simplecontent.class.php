<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tsimplecontent  {
  public $text;
  public $html;
  
  public function  httpheader() {
    return "<?php
    @Header( 'Content-Type: text/html; charset=utf-8' );
    @Header( 'Cache-Control: no-cache, must-revalidate');
    @Header( 'Pragma: no-cache');
    ?>";
  }
  
  function GetTemplateContent() {
    $result = empty($this->text) ? $this->html : sprintf("<h2>%s</h2>\n", $this->text);
    $theme =ttheme::instance();
    return sprintf($theme->content->simple, $result);
  }
  
  public static function html($content) {
    $class = __class__;
    $self = new $class();
    $self->html = $content;
    $template = ttemplate::instance();
    return $template->request($self);
  }
  
  public static function content($content) {
    $class = __class__;
    $self = new $class();
    $self->text = $content;
    $template = ttemplate::instance();
    return $template->request($self);
  }
  
}//class

?>