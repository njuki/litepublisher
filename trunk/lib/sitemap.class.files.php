<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tsitemap extends titems_itemplate implements itemplate {
  private $lastmod;
  private $count;
  private $fd;
  private $prio;
  
  public static function i() {
    return Getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'sitemap';
    $this->addevents('onindex');
    $this->data['date'] = time();
    $this->data['countfiles'] = 1;
  }
  
  public function add($url, $prio) {
    $this->items[$url] = (int) $prio;
    $this->save();
  }
  
  public function cron() {
    $this->createfiles();
  }
  
  //itemplate
  public function gettitle() {
    //dumpvar(tlocal::i()->ini['default']);
    return     tlocal::get('default', 'sitemap');
  }
  
  public function getcont() {
    $result = '';
    $posts = tposts::i();
    $theme = $this->view->theme;
    $perpage = 1000;
    $from = (litepublisher::$urlmap->page - 1) * $perpage;
      $list = array_slice(array_keys($posts->archives), (litepublisher::$urlmap->page - 1) * $perpage, $perpage);
      $result = $theme->getposts($list, true);

    if (litepublisher::$urlmap->page  == 1) {
      $menus = tmenus::i();
      $result .= '<h2>' . tlocal::get('default', 'menu') . "</h2>\n<ul>\n";
      foreach ($menus->items as $id => $item) {
        if ($item['status'] == 'draft') continue;
        $result .= sprintf('<li><a href="%s%s" title="%3$s">%3$s</a></li>', litepublisher::$site->url, $item['url'], $item['title']);
      }
      $result .= '</ul>';
      
      $result .= $this->gettags(tcategories::i());
      $result .= $this->gettags(ttags::i());
      $arch = tarchives::i();
      if (count($arch->items) > 0) {
        $result .= '<h2>' . tlocal::get('default', 'archive') . "</h2>\n<ul>\n";
        foreach ($arch->items as $date => $item) {
          $result .= sprintf('<li><a href="%s%s" title="%3$s">%3$s</a></li>', litepublisher::$site->url, $item['url'], $item['title']);
        }
        $result .= '</ul>';
      }
    }
    
    $result .=$theme->getpages('/sitemap.htm', litepublisher::$urlmap->page, ceil($posts->archivescount / $perpage));
    return $result;
  }
  
  private function gettags(tcommontags $tags) {
    $tags->loadall();
    if ($tags->count == 0)  return '';
    $result = '<h2>' . tlocal::get('default', $tags->postpropname) . "</h2>\n<p>\n";
    foreach ($tags->items as $id => $item) {
      $result .= sprintf('<a href="%s%s" title="%3$s">%3$s</a>, ', litepublisher::$site->url, $item['url'], $item['title']);
    }
    $result .= '</p>';
    return $result;
  }
  
  public function request($arg) {
    if ($arg == 'xml') {
      return '<?php turlmap::sendxml(); ?>' .
      $this->GetIndex();
    }
  }
  
  public function getIndex() {
    $lastmod = date('Y-m-d', $this->date);
    $result = '<sitemapindex xmlns="http://www.google.com/schemas/sitemap/0.84">';
    $url = litepublisher::$site->files . '/files/' . litepublisher::$domain;
    $exists = true;
    for ($i =1; $i <= $this->countfiles; $i++) {
      $result .= "<sitemap><loc>$url.$i.xml.gz</loc>      <lastmod>$lastmod</lastmod></sitemap>";
      if ($exists) $exists = file_exists(litepublisher::$paths->files . "$i.xml.gz");
    }
    $this->callevent('onindex', array(&$result));
    $result .= '</sitemapindex>';
    if (!$exists)     $this->createfiles();
    return $result;
  }
  
  public function createfiles() {
    $this->countfiles = 0;
    $this->count = 0;
    $this->date = time();
    $this->lastmod = date('Y-m-d', $this->date);
    $this->openfile();
    
    //home page
    $this->prio = 9;
    $this->write('/', ceil(litepublisher::$classes->posts->archivescount / litepublisher::$options->perpage));
    $this->prio = 8;
    $this->writeposts();
    
    $this->prio = 8;
    $this->writemenus();
    
    $this->prio = 7;
    $this->writetags(litepublisher::$classes->categories);
    $this->writetags(litepublisher::$classes->tags);
    
    $this->prio = 5;
    $this->writearchives();
    
    //урлы ккоторые добавлены в items
    foreach ($this->items as $url => $prio) {
      $this->writeitem($url, $prio);
    }
    
    $this->closefile();
    $this->Save();
  }
  
  private function writeposts() {
      $posts = tposts::i();
      foreach ($posts->archives as $id => $posted) {
        $post = tpost::i($id);
        $this->write($post->url, $post->countpages);
        $post->free();
      }
    }
 
  private function writemenus() {
    $menus = tmenus::i();
    foreach ($menus->items as $id => $item) {
      if ($item['status'] == 'draft') continue;
      $this->writeitem($item['url'], $this->prio);
    }
  }
  
  private function writetags($tags) {
    $perpage = $tags->lite ? 1000 : litepublisher::$options->perpage;
      foreach ($tags->items as $id => $item) {
        $this->write($item['url'], ceil($item['itemscount']/ $perpage));
      }
  }
  
  private function writearchives() {
    $db = litepublisher::$db;
    $arch = tarchives::i();
    $perpage = $arch->lite ? 1000 : litepublisher::$options->perpage;
    if (dbversion) $db->table = 'posts';
    foreach ($arch->items as $date => $item) {
      if (dbversion) {
    $count = $db->getcount("status = 'published' and year(posted) = '{$item['year']}' and month(posted) = '{$item['month']}'");
      } else {
        $count = count($item['posts']);
      }
      $this->write($item['url'], ceil($count/ $perpage));
    }
  }
  
  private function write($url, $pages) {
    $this->writeitem($url, $this->prio);
    $url = rtrim($url, '/');
    for ($i = 2; $i < $pages; $i++) {
      $this->writeitem("$url/page/$i/", $this->prio);
    }
  }
  
  private function writeitem($url, $prio) {
    $url = litepublisher::$site->url . $url;
    gzwrite($this->fd, "<url><loc>$url</loc><lastmod>$this->lastmod</lastmod>".
    "<changefreq>daily</changefreq><priority>0.$prio</priority></url>");
    
    if (++$this->count  >= 30000) {
      $this->closefile();
      $this->openfile();
    }
  }
  
  private function openfile() {
    $this->count = 0;
    $this->countfiles++;
    if ($this->fd = gzopen(litepublisher::$paths->files . litepublisher::$domain . ".$this->countfiles.xml.gz", 'w')) {
      $this->WriteHeader();
    } else {
      tfiler::log("error write file to folder " . litepublisher::$paths->files);
      exit();
    }
  }
  
  private function closefile() {
    $this->WriteFooter();
    gzclose($this->fd);
    @chmod(litepublisher::$paths->files . litepublisher::$domain . ".$this->countfiles.xml.gz", 0666);
    $this->fd = false;
  }
  
  private function WriteHeader() {
    gzwrite($this->fd, '<?xml version="1.0" encoding="UTF-8"?>' .
    '<urlset xmlns="http://www.google.com/schemas/sitemap/0.84"'.
    ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' .
    ' xsi:schemaLocation="http://www.google.com/schemas/sitemap/0.84 http://www.google.com/schemas/sitemap/0.84/sitemap.xsd">');
  }
  
  private function WriteFooter() {
    gzwrite($this->fd, '</urlset>');
  }
  
}//class