<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tsourcefiles extends tplugin {
  public $ignore;
  private $item;
  private $geshi;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->table = 'sourcefiles';
    $this->addmap('ignore', array());
  }
  
  public function request($arg) {
    $this->item = $this->db->getitem($arg);
  }
  
  public function gettitle() {
    $result = $this->item['dir'];
    $result .= $this->item['filename'] == '' ?  '' : '/'. $this->item['filename'];
    return $result;
  }
  
  public function getkeywords() {
    return $this->item['filename'];
  }
  
public function getdescription() { }
public function gethead() { }
  public function getcont() {
    $updir = $this->item['filename'] == '' ? '' : sprintf('<ul><li><a href="%1$s/source/%2$s/" title="%2$s">..</a></li>', litepublisher::$options->url, $this->item['dir']);
    $theme = ttheme::instance();
    if (strend($this->item['filename'], '.php')) {
      $dir = str_replace('/', DIRECTORY_SEPARATOR, $this->item['dir']);
      $realdir = litepublisher::$paths->home;
      $realdir .= $dir == '' ? 'litepublisher' . DIRECTORY_SEPARATOR . 'srcfiles' . DIRECTORY_SEPARATOR . 'root': $dir;
      $realfile = $realdir . DIRECTORY_SEPARATOR. $this->item['filename'] ;
      return sprintf($theme->content->simple, $updir . highlight_file($realfile , true));
    } else {
      return sprintf($theme->content->simple, $updir . $this->item['content']);
    }
  }
  
  public function add($dir, $filename, $realdir = '') {
    $realdir = $realdir == '' ? litepublisher::$paths->home : $realdir;
    $realfile = $realdir . str_replace('/', DIRECTORY_SEPARATOR, $dir) . DIRECTORY_SEPARATOR . $filename;
    $dir = str_replace(DIRECTORY_SEPARATOR, '/', $dir);
    $dir = trim($dir, '/');
    $hash = md5_file ($realfile);
    if ($item = $this->db->finditem(sprintf('filename = %s and dir = %s', dbquote($filename), dbquote($dir)))) {
      if ($hash != $item['hash']) {
        $item['hash'] = $hash;
        $item['content'] = $this->syntax($realfile);
        $this->db->updateassoc($item);
      }
      return $item['id'];
    }
    
    $item = array(
    'idurl' => 0,
    'filename' => $filename,
    'dir' => $dir,
    'hash' => $hash,
    'content' => $this->syntax($realfile)
    );
    if (strlen($item['content']) > 100000) $item['content'] = 'big';
    $id =$this->db->add($item);
    if ($dir != '') $dir .= '/';
    $idurl = litepublisher::$urlmap->add("/source/$dir$filename", get_class($this), $id);
    $this->db->setvalue($id, 'idurl', $idurl);
    return $id;
  }
  
  public function syntax($filename) {
    if (strend($filename, '.php')) return '';
    $source = file_get_contents($filename);
    $ext = substr($filename, -3);
    if ($ext == 'tml') $ext = 'htm';
    
    if (!isset($this->geshi)) {
      require_once(dirname(__file__) .DIRECTORY_SEPARATOR . 'geshi.php');
      $this->geshi = new GeSHi();
      $this->geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
    }
    
    $lang = $this->geshi->get_language_name_from_extension($ext);
    $this->geshi->set_language($lang);
    $this->geshi->set_source($source);
    return $this->geshi->parse_code();
  }
  
  public function adddir($dir) {
    $dir = str_replace(DIRECTORY_SEPARATOR, '/', $dir);
    $dir = trim($dir, '/');
    $realdir = litepublisher::$paths->home . str_replace('/', DIRECTORY_SEPARATOR, $dir) . DIRECTORY_SEPARATOR;
    $dirs = array();
    $files = array();
    $content = '';
    $url = litepublisher::$options->url;
    $updir = dirname($dir);
    $updir = $updir == '.' ? '' : $updir . '/';
    $dircontent = sprintf('<li><a href="%1$s/source/%2$s"><strong>..</strong></a></li>', $url, $updir);
    if ($list = scandir ($realdir)) {
      foreach ($list as $filename) {
        if (preg_match('/^(\.|\.\.|\.htaccess|index\.htm|\.svn)$/', $filename)) continue;
        if (in_array($dir . '/' . $filename, $this->ignore)) continue;
        if (is_dir($realdir . $filename)) {
          $newdir = $dir . '/' . $filename;
          $dirs[] = dbquote($newdir);
          $id = $this->adddir($newdir);
          $dircontent .= sprintf('<li><a href="%1$s/source/%2$s/" title="%3$s"><strong>%3$s</strong></a></li>', $url, $newdir , strtoupper($filename));
          $dircontent .= "\n";
        } else {
          if (preg_match('/\.(php|tml|css|ini|sql|js|txt)$/', $filename)) {
            if (strend($filename, '.min.js') || (($dir == 'lib/languages') && strend($filename, '.php'))) continue;
            $files[] = dbquote($filename);
            $id = $this->add($dir, $filename);
            $content .= sprintf('<li><a href="%1$s/source/%2$s/%3$s" title="%3$s">%3$s</a></li>', $url, $dir, $filename);
          } elseif (preg_match('/\.(jpg|gif|png|bmp|ico)$/', $filename)) {
            $content .= sprintf('<li><img src="%1$s/%2$s/%3$s" alt="%3$s" /></li>', $url, $dir, $filename);
          }
          $content .= "\n";
        }
      }
    }
    
    $content = sprintf("<ul>\n%s\n%s\n</ul>\n", $dircontent, $content);
    
    $sqlfiles = sprintf("(dir = %s and filename <> '' ", dbquote($dir));
    $sqlfiles .= count($files) == 0 ?  ')' : sprintf(' and filename not in (%s))', implode(',', $files));
    $sqldirs = sprintf(' or (filename = \'\' and dir <> %1$s and left(dir, %2$d) = %1$s', dbquote($dir), strlen($dir));
    $sqldirs .= count($dirs) == 0 ? ')' : sprintf(' and dir not in (%s))', implode(',', $dirs));
    if ($deleted = $this->db->getitems($sqlfiles . $sqldirs)) {
      $items = array();
      $idurls = array();
      foreach ($deleted as $item) {
        $items[] = $item['id'];
        $idurls[] = $item['idurl'];
      }
      litepublisher::$urlmap->loaditems($idurls);
      /*
      $robot = trobotstxt::instance();
      $robot->lock();
      foreach ($idurls as $idurl) {
        $robot->AddDisallow(litepublisher::$urlmap->getvalue($idurl, 'url'));
      }
      $robot->unlock();
      */
      
      litepublisher::$urlmap->db->deleteitems($idurls);
      $this->db->deleteitems($items);
    }
    
    if ($item = $this->db->finditem("filename = '' and dir = ". dbquote($dir))) {
      $this->db->setvalue($item['id'], 'content', $content);
      return $id;
    } else {
      $item = array(
      'idurl' => 0,
      'hash' => '',
      'filename' => '',
      'dir' => $dir,
      'content' => $content
      );
      $id = $this->db->add($item);
      $idurl = litepublisher::$urlmap->add("/source/$dir/", get_class($this), $id);
      $this->db->setvalue($id, 'idurl', $idurl);
      return $id;
    }
  }
  
}//class
?>