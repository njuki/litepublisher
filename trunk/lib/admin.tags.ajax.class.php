<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tajaxtageditor extends tajaxposteditor  {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function install() {
    litepublisher::$urlmap->addget('/admin/ajaxtageditor.htm', get_class($this));
  }
  
  public function request($arg) {
    if ($err = self::auth()) return $err;
    return $this->getcontent();
  }
  
  public function getcontent() {
    $type = tadminhtml::getparam('type', 'tags') == 'tags' ? 'tags' : 'categories';
    $tags = $type == 'tags' ? ttags::instance() : tcategories::instance();if ($err = self::auth()) return $err;
    $id = tadminhtml::idparam();
    if (($id > 0) && !$tags->itemexists($id)) return self::error403();
    
    $theme = tview::instance(tviews::instance()->defaults['admin'])->theme;
    $html = tadminhtml ::instance();
    $html->section = 'tags';
    $lang = tlocal::instance('tags');
    
    if ($id == 0) {
      $views = tviews::instance();
      $name = $type == 'tags' ? 'tag' : 'category';
      $item = array(
      'title' => '',
      'idview' => isset($views->defaults[$name]) ? $views->defaults[$name] : 1,
      'icon' => 0,
      'url' => '',
      'keywords' => '',
      'description' => ''
      );
    } else {
      $item = $tags->getitem($id);
    }
    
    switch ($_GET['get']) {
      case 'view':
      $result = $this->getviewicon($item['idview'], $item['icon']);
      break;
      
      case 'seo':
      $args = targs::instance();
      if ($id == 0) {
        $args->url = '';
        $args->keywords = '';
        $args->description = '';
      } else {
        $args->add($tags->contents->getitem($id));
      }
      $result = $html->parsearg('[text=url] [text=description] [text=keywords]', $args);
      break;
      
      case 'text':
      $result = $this->geteditor('raw', $id == 0 ? '' : $tags->contents->getcontent($id), true);
      $result .= $this->dogethead('');
      break;
      
      default:
      $result = var_export($_GET, true);
    }
    return turlmap::htmlheader(false) . $result;
  }
  
}//class
?>