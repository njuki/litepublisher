<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminthemetree extends tadminmenu implements iwidgets {
  private $ini;
  private $theme;
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function gethead() {
    $result = parent::gethead();
    $template = ttemplate::i();
    $result .= $template->getjavascript('/js/litepublisher/themetree.min.js');
    $name = tadminhtml::getparam('theme', '');
    if (($name != '') && ttheme::exists($name)) {
      $this->theme = ttheme::getinstance($name);
      tlocal::usefile('theme');
      $this->ini = tlocal::$self->ini['themetree'];
    }
    return $result;
  }
  
  public function themetojavascript($name) {
    $result = '  <script type="text/javascript">
    var theme = [];
    ';
    $theme = ttheme::getinstance($name);
    foreach ($theme->templates as $name => $value) {
      if (is_string($value)) {
        $value = tadminhtml::specchars($value);
        $value = str_replace("\r\n", '\n', $value);
        $value = str_replace("\n", "\\n\"+\n\"", $value);
        $result .= "theme['$name'] = \"$value\";\n";
      }
    }
    $value =$theme->templates['menu.hover'] ? 'true' : 'false';
    $result .= "theme['menu.hover'] = '$value';\n";
    foreach ($theme->templates['sidebars'] as $i => &$sidebar) {
      $pre = $i == 0 ? 'sidebar' : "sidebar$i";
      foreach ($sidebar as $name => $value) {
        if (!is_string($value)) {
          $value = implode(',', array_map(create_function('$k, $v', 'return "$k=$v";'),
          array_keys($value), array_values($value)));
        }
        $value = tadminhtml::specchars($value);
        $value = str_replace("\r\n", '\n', $value);
        $value = str_replace("\n", "\\n\"+\n\"", $value);
        $result .= "theme['$pre.$name'] = \"$value\";\n";
      }
    }
    
    $result .=   "\n</script>";
    return $result;
  }
  
  private function getignore($name) {
    return ($name == 'content') || ($name == 'content.post.templatecomments') || ($name == 'content.admin');
  }
  
  private function getitem($name) {
    return sprintf('<li><a rel="%s" href="">%s</a>%s</li>
    ',
    $this->getignore($name) ? 'ignore' : $name,
    isset($this->ini[$name]) ? $this->ini[$name] : $name,
    $this->getsubitems($name));
  }
  
  private function getsubitems($name) {
    $items = $this->theme->reg("/$name.\w*+\$/");
    if (count($items) == 0) return '';
    $result = '';
    foreach ($items as $name => $val) {
      $result .= $this->getitem($name);
    }
    return sprintf('<ul>
    %s
    </ul>', $result);
  }
  
  private function getthemesidebars() {
    $result = sprintf('<li><a rel="ignore" href="">%s</a>
    <ul>', $this->ini['sidebars']);
    $names = ttheme::getwidgetnames();
    array_unshift($names, 'widget');
    foreach ($this->theme->templates['sidebars'] as $i => &$widgets) {
      $result .= sprintf('<li><a rel="ignore" href="">%d</a>
      <ul>', $i);
      $pre = $i == 0 ? 'sidebar' : "sidebar$i";
      foreach ($names as $name) {
        if (isset($widgets[$name])) {
          $subitems = sprintf('<li><a rel="%s" href="">%s</a></li>', "$pre.$name.subcount", $this->ini["sidebar.widget.subcount"]);
          $subitems .= sprintf('<li><a rel="%s" href="">%s</a></li>', "$pre.$name.subitems", $this->ini["sidebar.widget.subitems"]);
          $item = sprintf('<li><a rel="%s" href="">%s</a>
          <ul>%s</ul>
          </li>', "$pre.$name.item", $this->ini['sidebar.widget.item'], $subitems);
          $items = sprintf('<li><a rel="%s" href="">%s</a>
          <ul>%s</ul>
          </li>', "$pre.$name.items", $this->ini["sidebar.widget.items"], $item);
          if ($name == 'meta') $items .= sprintf('<li><a rel="%s" href="">%s</a></li>',  "$pre.meta.classes", $this->ini["sidebar.meta.classes"]);
          $result .= sprintf('<li><a rel="%s" href="">%s</a>
          <ul>%s</ul>
          </li>',  "$pre.$name", $this->ini["sidebar.$name"], $items);
          
          
        }
      }
      $result .= '</ul>
      </li>';
    }
    $result .= '</ul>
    </li>';
    return $result;
  }
  
  public function getwidgetcontent($id, $sidebar) {
    $result = '';
    //root tags
    $result .= $this->getitem('index');
    $result .= $this->getitem('title');
    $result .= $this->getitem('menu');
    $result .= $this->getitem('content');
    $result .= $this->getthemesidebars();
    return sprintf('<ul id="themetree">%s</ul>', $result);
  }
  
  public function getwidget($id, $sidebar) {
    $title = $this->gettitle();
    $content = $this->getwidgetcontent(0, $sidebar);
    $content = str_replace('<ul>', '<ul style="display: none;">', $content);
    $theme = ttheme::i();
    return $theme->getwidget($title, $content, 'widget', $sidebar);
  }
  
  //iwidget
public function getwidgets(array &$items, $sidebar) { }
  
  public function getsidebar(&$content, $sidebar) {
    if (($sidebar > 0) || !isset($this->theme)) return;
    $content = $this->getwidget(0, 0) . $content;
  }
  
  public function getcontent() {
    $html = $this->html;
    $args = targs::i();
    if (isset($this->theme)) {
      $args->formtitle = $this->gettitle();
      return $html->adminform('<div id="themeeditor"></div>', $args);
    } else {
      return tadminthemes::getthemes();
    }
  }
  
  public function processform() {
    $name = tadminhtml::getparam('theme', '');
    if (($name === '') || !ttheme::exists($name)) return '';
    $this->theme = ttheme::getinstance($name);
    $templates = &$this->theme->templates;
    $sidebars = &$this->theme->templates['sidebars'];
    foreach ($_POST as $name => $value) {
      $name = str_replace('_', '.', $name);
      $value = trim($value);
      $value = str_replace("\r\n", "\n", $value);
      if (isset($templates[$name])) {
        $templates[$name] = $value;
      } elseif (strbegin($name, 'sidebar')) {
        if (strbegin($name, 'sidebar.')) {
          $index = 0;
        } elseif (preg_match('/^sidebar(\d)\./', $name, $m)) {
          $index = (int) $m[1];
        } else {
          continue;
        }
        $name = substr($name, strpos($name, '.') +1);
        if (isset($sidebars[$index][$name])){
          if ($name == 'meta.classes') $value = tthemeparser::getmetaclasses($value);
          $sidebars[$index][$name] = $value;
        }
      }
    }
    
    $templates['menu.hover'] = $templates['menu.hover'] == 'true' ? 'true' : 'false';
    
    $this->theme->save();
    tthemeparser::compress($this->theme);
    return '';
  }
  
}//class