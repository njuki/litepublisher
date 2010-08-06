<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tthemeparser extends tdata {
  public $theme;
  public $warnings;
  private $abouts;
  private $default;
  private $fixold = true;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public static function getwidgetnames() {
    return array('submenu', 'categories', 'tags', 'archives', 'links', 'posts', 'comments', 'friends', 'meta') ;
  }
  
  public function parsetag(&$s, $tag, $replace) {
    $result = '';
    $opentag = "<!--$tag-->";
    $closetag = "<!--/$tag-->";
    if(is_int($i = strpos($s, $opentag)) && ($j = strpos($s, $closetag))) {
      $result = substr($s, $i + strlen($opentag), $j - $i - strlen($opentag));
      if ($replace === false) $replace = $result;
      $s = substr_replace($s, $replace, $i, $j - $i + strlen($closetag));
      $s = str_replace("\n\n", "\n", $s);
    }
    return $result;
  }
  
  public function requiretag(&$s, $tag, $replace) {
    if ($result = $this->parsetag($s, $tag, $replace)) return $result;
    tlocal::loadlang('admin');
    $lang = tlocal::instance('themes');
    $this->error(sprintf($lang->error, $tag));
  }
  
  public function gettag(&$s, $tag, $replace, $default = null) {
    if ($result = $this->parsetag($s, $tag, $replace)) return $result;
    return (string) $default;
    /*
    tlocal::loadlang('admin');
    $lang = tlocal::instance('themes');
    $this->warnings[] = sprintf($lang->warning, $tag);
    return $result;
    */
  }
  
  public function deletespaces($s) {
    return trim(str_replace(
    array('   ', '  ', "\r", " \n", "\n\n"),
    array(' ', ' ', "\n", "\n", "\n"),
    $s));
  }
  
  public function parse(ttheme $theme) {
    $filename = litepublisher::$paths->themes . $theme->name . DIRECTORY_SEPARATOR . $theme->tmlfile . '.tml';
    if (!@file_exists($filename))  return $this->checktheme($theme);
    if (($theme->name == 'default') && ($theme->tmlfile == 'default')) {
      $this->default = new tdefaulttheme();
    } else {
$about = $this->getabout($theme->name); 
if (($about['type'] == 'litepublisher') && !empty($about['parent'])) {
$parent = $this->getabout($about['parent']);
if (($parent['type'] != 'litepublisher') || !empty($parent['parent'])) {
$this->error(sprintf('Parent theme %s of theme %s has parent', $about['parent'], $theme->name));
}
      $this->default = ttheme::getinstance($about['parent'], 'index');
$theme->parent = $about['parent'];
} else {
      $this->default = ttheme::getinstance('default', 'default');
}
    }
    
    $s = file_get_contents($filename);
    $s = str_replace(array("\r\n", "\r", "\n\n"), "\n", $s);
    $theme->type = 'litepublisher';
    $theme->title = $this->parsetitle($s);
    $theme->menu = $this->parsemenu($s);
    $theme->content = $this->parsecontent($s);
    $theme->sitebars = $this->parsesitebars($s);
$s = $this->deletespaces($s);
    $theme->theme= $s != ''? $s : (string) $this->default->theme;
    return true;
  }
  
  private function parsetitle(&$s) {
    return $this->gettag($s, 'title', '$template.title', $this->default->title);
  }
  
  private function parsemenu(&$str) {
    $menu = $this->default->menu;
    $s = $this->parsetag($str, 'menulist', '$template.menu');
    if ($s == '') return $menu->array;
    $result = array();
    $item = trim($this->parsetag($s, 'item', '$items'));
    $result['submenu'] = $this->parsetag($item, 'submenu', '$submenu', $menu->submenu);
    $result['item'] = $item != '' ? $item : $menu->item;
    $result['current'] = $this->parsetag($s, 'current', '', $menu->current);
    //fix old version
    if ($this->fixold) {
      if (strpos($result['submenu'], '%')) $result['submenu'] = sprintf($result['submenu'], '$items');
      if (strpos($result['item'], '%')) $result['item'] = sprintf($result['item'], '$options.url$url', '$title', '$submenu');
      if (strpos($result['current'], '%')) $result['current'] = sprintf($result['current'], '$options.url$url', '$title', '$submenu');
    }
    
    //hover
    $nohover = '<!--nohover-->';
    if (is_int($i = strpos($s, $nohover))) {
      $result['hover'] = false;
      $s = substr_replace($s, '', $i, strlen($nohover));
    } elseif ($id = tcontentfilter::getidtag('*', $s)) {
      $result['id'] = $id;
      preg_match('/\<(\w*)/',$item, $t);
      $result['tag'] = $t[1];
      $result['hover'] = true;
    }
    
    $s = $this->deletespaces($s);
    if ($s != '') {
      if (!isset(    $result['hover'])) $result['hover'] = false;
      $result[0] = $s;
    } else {
      if (!isset(    $result['hover'])) {
        $result['hover'] = $menu->hover;
        if ($result['hover']) {
          $result['id'] = $menu->id;
          $result['tag'] = $menu->tag;
        }
      }
      $result[0] = (string) $menu;
    }
    return $result;
  }
  
  private function parsecontent(&$str) {
    $s = $this->parsetag($str, 'content', '$template.content');
    if ($s == '') return $this->default->content->array;
    $result = array();
    $result['post']= $this->parsepost($s);
    $result['excerpts'] = $this->parse_excerpts($s, $result['post']);
    $result['navi'] = $this->parsenavi($s);
    $result['admin'] = $this->parseadmin($s);
$default = $this->default->content;
    $result['menu']= $this->gettag($s, 'menu', '', $default->menu);
    $result['simple'] = $this->gettag($s, 'simple', '', $default->simple);
    $result['notfound'] = $this->gettag($s, 'notfound', '', $default->notfound);
if ($this->fixold) {
if (strpos($result['simple'], '%')) $result['simple'] = sprintf($result['simple'], '$content');
if (strpos($result['notfound'], '%')) $result['notfound'] = sprintf($result['notfound'], '$content');
}
    return $result;
  }
  
  private function parse_excerpts(&$str, array &$post) {
$s = $this->parsetag($str, 'excerpts', '');
if ($s == '') return $this->default->content->excerpts->array;
    $result = array();
    $result['excerpt'] = $this->parse_excerpt($s, $post);
    $result['lite'] = $this->parselite($s);
$s = $this->deletespaces($s);
    $result[0] = $s != '' ? $s : (string) $this->default->content->excerpts;
    return $result;
  }
  
  private function parselite(&$str) {
$default = $this->default->content->excerpts->lite;
$s= $this->gettag($str, 'lite', '');
if ($s == '') return $default->array;
    $result = array();
    $result['excerpt'] = $this->parsetag($s, 'excerpt', '$items', $default->excerpt);
$s = $this->deletespaces($s);
    $result[0] = $s != '' ? $s : (string) $default;
    return $result;
  }
  
  private function parse_excerpt(&$str, array &$post) {
$s = $this->parsetag($str, 'excerpt', '$items');
if ($s == '') return $this->default->content->excerpts->excerpt->array;
    $result = array();
    $categories = $this->parse_post_tags($s, 'categories', '$post.categorieslinks');
    $tags = $this->parse_post_tags($s, 'tags', '$post.tagslinks');
    $common = $this->parse_post_tags($s, 'commontags', '');
    
    if ($categories) {
      $result['categories'] = $categories;
    } elseif ($common) {
      $result['categories'] = $common;
      $result['categories'][0] = str_replace('commontags', 'categories', $common[0]);
    } elseif ($tags) {
      $result['categories'] = $tags;
      $result['categories'][0] = str_replace('tags', 'categories', $tags[0]);
    } else {
      $result['categories'] = $post['categories'];
    }
    
    if ($tags) {
      $result['tags'] = $tags;
    } elseif ($common) {
      $result['tags'] = $common;
      $result['tags'][0] = str_replace('commontags', 'tags', $common[0]);
    } elseif ($categories) {
      $result['tags'] = $categories;
      $result['tags'][0] = str_replace('categories', 'tags', $categories[0]);
    } else {
      $result['tags'] = $post['tags'];
    }

    $result['files'] = $this->parsefilesexcerpt($s, $post['files']);
$default = $this->default->content->excerpts->excerpt;
    $result['more'] = $this->gettag($s, 'more', '$post.morelink', $default->more);
    $result['dateformat'] = self::strftimetodate($this->parsetag($s, 'date', '$post.excerptdate', $post['dateformat']));
$s = $this->deletespaces($s);
    $result[0] = $s != '' ? $s : (string) $default;
    return $result;
  }
  
  private function parse_post_tags(&$s, $name, $replace) {
    $section = $this->parsetag($s, $name, $replace);
    if ($section == '') return false;
    $result = array();
    $result['item'] = trim($this->parsetag($section, 'item', '$items'));
    $result['divider'] = $this->parsetag($section, 'divider', '');
    $result[0] = trim($section);
    return $result;
  }
  
  private function parsepost(&$str) {
    $s = $this->parsetag($str, 'post', '');
    if ($s == '') return $this->default->content->post->array;
    $default = $this->default->content->post;
    $result = array();
    
    $categories = $this->parse_post_tags($s, 'categories', '$post.categorieslinks');
    $tags = $this->parse_post_tags($s, 'tags', '$post.tagslinks');
    $common = $this->parse_post_tags($s, 'commontags', '');
    
    if ($categories) {
      $result['categories'] = $categories;
    } elseif ($common) {
      $result['categories'] = $common;
      $result['categories'][0] = str_replace('commontags', 'categories', $common[0]);
    } elseif ($tags) {
      $result['categories'] = $tags;
      $result['categories'][0] = str_replace('tags', 'categories', $tags[0]);
    } else {
      $result['categories'] = $default->array['categories'];
    }
    
    if ($tags) {
      $result['tags'] = $tags;
    } elseif ($common) {
      $result['tags'] = $common;
      $result['tags'][0] = str_replace('commontags', 'tags', $common[0]);
    } elseif ($categories) {
      $result['tags'] = $categories;
      $result['tags'][0] = str_replace('categories', 'tags', $categories[0]);
    } else {
      $result['tags'] = $default->array['tags'];
    }
    
    $result['files'] = $this->parsefiles($s);
    $result['more'] = $this->gettag($s, 'more', '', $this->default->content->post->more);
    $result['rss'] = $this->gettag($s, 'rss', '$post.subscriberss', $this->default->content->post->rss);
    $result['prevnext']  = $this->parseprevnext($s);
    $result['templatecomments'] = $this->parsetemplatecomments($this->requiretag($s, 'templatecomments', '$post.templatecomments'));
    // after coments due to section 'date' in comment
    $result['dateformat'] = self::strftimetodate($this->parsetag($s, 'date', '$post.date', $this->default->content->post->dateformat));
    $s = trim($s);
    $result[0] = $s != '' ? $s : (string) $this->default->content->post;
    return $result;
  }
  
  private function parsefiles(&$str) {
    $default = $this->default->content->post->files;
    $s = $this->parsetag($str, 'files', '$post.filelist');
    if ($s == '') return $default->array;
    
    $result = array();
    $result['file'] = $this->gettag($s, 'file', '$items', $default->file);
    $result['image'] = $this->gettag($s, 'image', '', $default->image);
    $result['preview'] = $this->gettag($s, 'preview', '', $default->preview);
    $result['audio'] = $this->gettag($s, 'audio', '', $default->audio);
    $result['video'] = $this->parsetag($s, 'video', '', $default->video);
    $s = trim($s);
    $result[0] = $s != '' ? $s : (string) $default;
    return $result;
  }

  private function parsefilesexcerpt(&$str, array &$files) {
    $s = $this->parsetag($str, 'files', '$post.excerptfilelist');
    if ($s == '') return $files;
    $default = new tarray2prop();
$default ->array = $files;
        $result = array();
    $result['file'] = $this->gettag($s, 'file', '$items', $default->file);
    $result['image'] = $this->gettag($s, 'image', '', $default->image);
    $result['preview'] = $this->gettag($s, 'preview', '', $default->preview);
    $result['audio'] = $this->gettag($s, 'audio', '', $default->audio);
    $result['video'] = $this->parsetag($s, 'video', '', $default->video);
    $s = trim($s);
    $result[0] = $s != '' ? $s : $files[0];
    return $result;
  }
  
  
  private function parseprevnext(&$str) {
    $s = $this->parsetag($str, 'prevnext', '$post.prevnext');
    if ($s == '') return $this->default->content->post->prevnext->array;
    $default = $this->default->content->post->prevnext;
    $result = array();
    $result['prev'] = $this->gettag($s, 'prev', '$prev', $default->prev);
    $result['next'] = $this->parsetag($s, 'next', '$next', $default->next);
    $s = trim($s);
    $result[0] = $s != '' ? $s : (string) $default;
    return $result;
  }
  
  private function parsenavi(&$str) {
$s = $this->parsetag($s, 'navi', '');
if ($s == '') return $this->default->content->navi->array;
$default = $this->default->content->navi;
    $result = array();
    $result['prev'] = $this->parsetag($s, 'prev', '$items', $default->prev);
    $result['next'] = $this->parsetag($s, 'next', '', $default->next);
    $result['link'] = $this->parsetag($s, 'link', '', $default->link);
    $result['current'] = $this->parsetag($s, 'current', '', $default->current);
    $result['divider'] = $this->parsetag($s, 'divider', '', $default->divider);
if ($this->fixold) {
$result['prev'] = sprintf($result['prev'], '$link');
$result['next'] = sprintf($result['next'], '$link');
$result['link'] =sprintf($result['link'], '$options.url$url', '$page');
$result['current'] =sprintf($result['current'], '$options.url$url', '$page');
}
$s = $this->deletespaces($s);
    $result[0] = $s != '' ? $s : (string) $default;
    return $result;
  }
  
  private function parseadmin(&$str) {
$default = $this->default->content->admin;
$s = $this->parsetag($str, 'admin', '');
if ($s == '') return $default->array;
    $result = array();
    $result['area'] = trim($this->gettag($s, 'area', '', $default->area));
    $result['edit'] = trim($this->gettag($s, 'edit', '', $default->edit));
    return $result;
  }
  
  private function parsetemplatecomments($s) {
    $result = array();
    $result['comments'] = $this->parsecomments($this->requiretag($s, 'comments', ''));
    $result['moderateform'] = $this->parsemoderateform($this->requiretag($s, 'moderateform', ''));
    $result['pingbacks'] = $this->parsepingbacks($this->gettag($s, 'pingbacks', ''));
    $result['closed'] = $this->requiretag($s, 'closed', '');
    $result['form'] = $this->requiretag($s, 'form', '');
    $result['confirmform'] = $this->gettag($s, 'confirmform', '');
    if ($result['confirmform'] == '') $result['confirmform'] = $this->getdefaultconfirmform();
    return $result;
  }
  
  private function parsecomments($s) {
    $result = array();
    $result['count'] = $this->gettag($s, 'count', '');
    $result['hold'] = $this->gettag($s, 'hold', '');
    $result['comment'] = $this->parsecomment($this->requiretag($s, 'comment', '%1$s'));
    $result['commentsid'] = $this->requiretag($s, 'commentsid', false);
    $result[0] = $s;
    return $result;
  }
  
  private function parsecomment($s) {
    $result = array();
    $result['class1'] = $this->parsetag($s, 'class1', '$class');
    $result['class2'] = $this->parsetag($s, 'class2', '');
    $result['moderate'] = $this->gettag($s, 'moderate', '$moderate');
    
    $result['dateformat'] = self::strftimetodate($this->parsetag($s, 'date', '$comment.date'));
    $result[0] = $s;
    return $result;
  }
  
  private function parsemoderateform($s) {
    return $s;
    /*
    $result = array();
    $result[0] = $s;
    return $result;
    */
  }
  
  private function parsepingbacks($s) {
    $result = array();
    $result['pingback'] = $this->parsetag($s, 'pingback', '%1$s');
    $result[0] = $s;
    return $result;
  }
  
  private function parsesitebars(&$str) {
    $result = array();
    while ($sitebar = $this->parsetag($s, 'sitebar', '$template.sitebar')) {
      $result[] = $this->parsesitebar($sitebar, count($result));
    }
if (count($result) == 0) return $this->default->sitebars;
    return $result;
  }
  
  private function parsesitebar($s, $sitebar) {
    $result = array();
$default = $this->default->sitebars[$sitebar];
$isdef = $this->default instanceof tdefaulttheme;
    if ($widget = $this->parsetag($s, 'widget', '$items')) {
    $result['widget'] = $this->parsewidget($widget, 'widget', $sitebar);
} else {
$result['widget'] = $default['widget'];
}
    
    foreach (self::getwidgetnames() as $name) {
      if ($widget =$this->parsetag($s, $name, ''))  {
        $result[$name] = $this->parsewidget($widget, $name, $sitbar);
} elseif ($isdef) {
$result[$name] = $result['widget'];
      } else {
        $result[$name]  = $default[$name];
      }
    }
    
$s = $this->deletespaces($s);
    $result[0] = $s != '' ? $s : $default[0];
    return $result;
  }
  
  private function parsewidget(&$str, $name, $sitebar) {
$default = $this->default->sitebars[$sitebar][$name];
    $result = array();
    if ($items = $this->parsetag($s, 'items', '$items');
    if ($item = $this->parsetag($items, 'item', '%s')) {
      $result['item'] = $item;
    } else {
      $result['item'] = $this->GetDefaultWidgetItem($name);
    }
    
    if ($name == 'meta') {
      $result['classes'] = array('rss' => '', 'comments' => '', 'media' => '', 'foaf' => '', 'profile' => '', 'sitemap' => '');
      if ($classes = $this->parsetag($items, 'metaclasses', '')) {
        $classes = explode(',', $classes);
        foreach ($classes as $class) {
          if ($i = strpos($class, '=')) {
            $classname = trim(substr($class, 0, $i));
            $value = trim(substr($class, $i + 1));
            if ($value != '') $result['classes'][$classname] = sprintf('class="%s"', $value);
          }
        }
      }
    }
    
    $result['items'] = $this->deletespaces($items);
    $result[0] = $this->deletespaces($s);
    return $result;
  }
  
  //manager
  public function getabout($name) {
    if (!isset($this->abouts)) $this->abouts = array();
    if (!isset($this->abouts[$name])) {
      if (      $about = parse_ini_file(litepublisher::$paths->themes . $name . DIRECTORY_SEPARATOR . 'about.ini', true)) {
$about['about']['type'] = 'litepublisher';
        //join languages
        if (isset($about[litepublisher::$options->language])) {
          $about['about'] = $about[litepublisher::$options->language] + $about['about'];
        }
        $this->abouts[$name] = $about['about'];
      } elseif ($about =  $this->get_about_wordpress_theme($name)){
$about['type'] = 'wordpress';
        $this->abouts[$name] = $about;
      } else {
        $this->abouts[$name] = false;
      }
    }
    return $this->abouts[$name];
  }
  
  public function changetheme($old, $name) {
    $template = ttemplate::instance();
    if ($about = $this->getabout($old)) {
      if (!empty($about['about']['pluginclassname'])) {
        $plugins = tplugins::instance();
        $plugins->delete($old);
      }
    }
    
    $template->data['theme'] = $name;
    $template->path = litepublisher::$paths->themes . $name . DIRECTORY_SEPARATOR  ;
    $template->url = litepublisher::$options->url  . '/themes/'. $template->theme;
    
    $theme = ttheme::getinstance($name, 'index');
    
    $about = $this->getabout($name);
    if (!empty($about['about']['pluginclassname'])) {
      $plugins = tplugins::instance();
      $plugins->addext($name, $about['about']['pluginclassname'], $about['about']['pluginfilename']);
    }
    
    litepublisher::$urlmap->clearcache();
  }
  
  public function reparse() {
    $theme = ttheme::instance();
    $theme->lock();
    $this->parse($theme);
    ttheme::clearcache();
    $theme->unlock();
  }
  
  private function getdefaultconfirmform() {
    return '<h2>$lang->formhead</h2>
    <form name="preform" method="post" action="">
    <p><input type="submit" name="submit" value="$lang->robot"/></p>
    </form>
    
    <form name="form" method="post" action="">
    <input type="hidden" name="confirmid" value="$confirmid" />
    <p><input type="submit" name="submit" value="$lang->human"/></p>
    </form>';
  }
  
  private function GetDefaultWidgetItem($name) {
    switch ($name) {
      case 'submenu':
      case 'categories':
      case  'tags':
      return '<li><a href="$options.url$url" title="$title">$icon$title</a>$count</li>';
      
      case 'archives':
      return '<li><a href="$options.url$url" rel="archives" title="$title">$icon$title</a>$count</li>';
      
      case 'posts':
      return '<li><strong><a href="$post.link" rel="bookmark" title="$lang.permalink $post.title">$post.title</a></strong><br />
      <small>$post.date</small></li>';
      
      case 'comments':
      return '<li><strong><a href=" $options.url$posturl#comment-$id" title="$name $onrecent $title">$name $onrecent $title</a></strong>: $content...</li>';
      
      case 'link':
      return '<li><a href="$url" title="$title">$text</a></li>';
      
      case 'foaf':
      return '<li><a href="$url" rel="friend" title="$nick">$nick</a></li>';
      
      //case 'widget':
      default:
      return '<li><a href="%1$s" title="%2$s">%2$s</a></li>';
    }
  }
  
  
  public static function strftimetodate($format) {
    static $trans;
    if (!isset($trans)) $trans = array(
    '%a' => 'D',
    '%A' => 'l',
    '%b' => 'M',
    '%B' => 'F',
    '%c' => tlocal::$data['datetime']['dateformat'],
    '%C' => 'y',
    '%d' => 'd',
    '%D' => 'i/d/y',
    '%e' => 'j',
    '%g' => 'Y',
    '%G' => 'Y',
    '%h' => 'F',
    '%H' => 'H',
    '%I' => 'h',
    '%j' => 'z',
    '%m' => 'm',
    '%M' => 'i',
    '%n' => "\n",
    '%p' => 'A',
    '%r'  => 'ga.',
    '%R' => 'G',
    '%S' =>  's',
    '%t' => "\t",
    '%T' => 'H:i:s',
    '%u'=> 'w', // must be +1
    '%U' => 'W',
    '%V' => 'W',
    '%W' => 'W',
    '%w' => 'w',
    '%x' => tlocal::$data['datetime']['dateformat'],
    '%X' => 'H:i:s',
    '%y' => 'y',
    '%Y' => 'Y',
    '%Z' => 't',
    '%%' => '%'
    );
    
    return strtr($format, $trans);
  }
  
  //wordpress
  public function checktheme(ttheme $theme) {
    if ($about = $this->get_about_wordpress_theme($theme->name)) {
      $theme->type = 'wordpress';
      return true;
    }
    return false;
  }
  
  public function get_about_wordpress_theme($name) {
    $filename = litepublisher::$paths->themes . $name . DIRECTORY_SEPARATOR . 'style.css';
    if (!@file_exists($filename)) return false;
    $data = $this->wp_get_theme_data($filename);
    $about = array(
    'author' => $data['Author'],
    'url' => $data['URI'] != ''  ? $data['URI'] :$data['AuthorURI'],
    'description' => $data['Description'],
    'version' => $data['Version']
    );
    
    return $about;
  }
  
  public function wp_get_theme_data( $theme_file ) {
    $default_headers = array(
    'Name' => 'Theme Name',
    'URI' => 'Theme URI',
    'Description' => 'Description',
    'Author' => 'Author',
    'AuthorURI' => 'Author URI',
    'Version' => 'Version',
    'Template' => 'Template',
    'Status' => 'Status',
    'Tags' => 'Tags'
    );
    
    $theme_data = $this->wp_get_file_data( $theme_file, $default_headers, 'theme' );
    
    $theme_data['Name'] = $theme_data['Title'] = strip_tags( $theme_data['Name']);
    $theme_data['URI'] = strip_tags( $theme_data['URI'] );
    $theme_data['AuthorURI'] = strip_tags( $theme_data['AuthorURI'] );
    $theme_data['Version'] = strip_tags( $theme_data['Version'], $themes_allowed_tags );
    
    if ( $theme_data['Author'] == '' ) {
      $theme_data['Author'] = 'Anonymous';
    }
    
    return $theme_data;
  }
  
  public function wp_get_file_data( $file, $default_headers, $context = '' ) {
    $fp = fopen( $file, 'r' );
    $file_data = fread( $fp, 8192 );
    fclose( $fp );
    
    foreach ( $default_headers as $field => $regex ) {
    preg_match( '/' . preg_quote( $regex, '/' ) . ':(.*)$/mi', $file_data, ${$field});
    if ( !empty( ${$field} ) )
  ${$field} = _cleanup_header_comment( ${$field}[1] );
      else
    ${$field} = '';
    }
    
    return compact( array_keys($default_headers) );
  }
  
}//class

function _cleanup_header_comment($str) {
  return trim(preg_replace("/\s*(?:\*\/|\?>).*/", '', $str));
}

class tdefaulttheme implements arrayaccess {
public function __get($name) { return $this; }
public function __set($name, $value) { }
public function __tostring() { return ''; }
  
public function offsetSet($offset, $value) {}
public function offsetExists($offset) { return true; }
public function offsetUnset($offset) {}
public function offsetGet($offset) { return $this; }
}//class

?>