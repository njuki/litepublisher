<?php

class tadminthemes extends tadminmenuitem {
  private $plugin;
 
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    global $options, $template, $paths;
$result = '';
$html = $this->html;
$args = targs::instance();
if ($plugin = $this->getplugin())  {
    $template = ttemplate::instance();
      $args->themename = $Template->theme;
$args->url = $options->url . $this->url . $options->q ."plugin=$template->theme";
$result .= $html->pluginlink($args);
    }

    switch ($this->name) {
      case 'themes':
      if ($plugin && !empty($_GET['plugin'])) {
$result .= $plugin->getcontent();
return $result;
}
      $template = ttemplate::instance();
$result .= $html->formheader();
      $list =    tfiler::getdir($paths['themes']);
      sort($list);
$args->editurl = $options->url . $this->url . 'edit/' . $options->q . 'theme';
      foreach ($list as $name) {
        $about = $this->GetAbout($name);
$args->name = $name;
        $args->checked = $name == $template->theme;
$args->version = $about['version'];
$args->url = $about['url'];
$args->author = $about['author'];
$args->description = $about['description'];
$result .= $html->radioitem($args);
      }
$result .= $html->formfooter();
return str_replace("'", '"', $result);

            case 'edit':
      $themename = !empty($_GET['theme']) ? $_GET['theme'] : $template->theme;
if (preg_match('/\/\.\\\<\>/', $themename, $m)) return $this->notfound;
      $result = sprintf($html->h2->filelist, $themename);
      $result .= "<ul>\n";
      $filelist = tfiler::getfiles($paths['themes'] . $themename . DIRECTORY_SEPARATOR  );
      sort($filelist);
      foreach ($filelist as $filename) {
      $result .= "<li><a href=\"$options->url/admin/themes/edit/{$options->q}themename=$themename&filename=$filename\">$filename</a></li>\n";
      }
      $result .= "</ul>\n";
      if (!empty($_GET['file'])) {
if (preg_match('/\/\.\\\<\>/', $_GET['file'], $m)) return $this->notfound;
        $args->content = file_get_contents($paths['themes'].$themename . DIRECTORY_SEPARATOR  . $_GET['filename']);
$html->filename($args);
        $result .= sprintf($s, $_GET['filename']);
      } else {
        $args->content = '';
      }
      eval('$result .= "'. $html->editform . '\n";');
      break;
    }
    return $result;
  }
  
  public function processform() {
    global $options, $paths;
    
    switch ($this->arg) {
      case 'themes':
      if (!empty($_GET['plugin']) && ($plugin = $this->getplugin())) return $plugin->processform();
      
      if (empty($_POST['selection']))   return '';
        $template = ttemplate::instance();
        try {
          $template->theme = $_POST['selection'];
        } catch (Exception $e) {
          $template->theme = 'default';
          return 'Caught exception: '.  $e->getMessage() . "<br>\ntrace error\n<pre>\n" .  $e->getTraceAsString() . "\n</pre>\n";
        }
return $this->html->h2->success;

            case 'edit':
      if (!empty($_GET['file']) && !empty($_GET['theme'])) {
//проверка на безопасность, чтобы не указывали в запросе файлы не в теме
if (preg_match('/\.\/\\/', $_GET['file'] . $_GET['theme'], $m)) return '';
        if (!file_put_contents($paths['themes'] . $_GET['theme'] . DIRECTORY_SEPARATOR . $_GET['file'], $_POST['content'])) {
return  $this->html->h2->errorsave;
}
          $urlmap = turlmap::instance();
          $urlmap->clearcache();
      }
      break;
    }
    return '';
  }

private function getabout($name) {
$parser = tthemeparser::instance();
return $parser->getabout($name);
}
  
  private function  getplugin() {
    if (!isset($this->plugin)) {
      $template =  ttemplate::instance();
      $about = $this->GetAbout($template->name);
      if (empty($about['adminclassname']))  return false;
      $class = $about['adminclassname'];
      if (!class_exists($class))  require_once($template->path . $about['adminfilename']);
      $this->plugin = getinstance($class);
    }
return $this->plugin;
  }
  
}//class
?>