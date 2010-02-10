<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tsitemap extends titems implements itemplate {
  public $title;
  private $lastmod;
  private $count;
  private $fd;
  private $prio;
  
  public static function instance() {
    return Getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'sitemap';
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
public function gettitle() { return $this->title; }
public function gethead() {}
public function getkeywords() {}
public function getdescription() {}
  
  public function GetTemplateContent() {
    $posts = tposts::instance();
    $theme = ttheme::instance();
    $postsperpage = 1000;
    $list = array_slice(array_keys($posts->archives), (litepublisher::$urlmap->page - 1) * $postsperpage, $postsperpage);
    $result = $TemplatePost->LitePrintposts($list);
    
    if (litepublisher::$urlmap->page  == 1) {
      $result .= '<ul>' . TLocal::$data['default']['tags'];
      $tags = &TTags::instance();
      foreach ($tags->items as $id => $item) {
    $result .= "<li><a href=\"litepublisher::$options->url{$item['url']}\">{$item['name']}</a></li>\n";
      }
      $result .= "</ul>\n";
    }
    
    $result .=$theme->getpages('/sitemap/', litepublisher::$urlmap->page, ceil(count($posts->archives)/ $postsperpage));
    return $result;
  }
  
  public function request($arg) {
    if ($arg == 'xml') {
      $s = "<?php
      @header('Content-Type: text/xml; charset=utf-8');
      echo '<?xml version=\"1.0\" encoding=\"utf-8\" ?>';
      ?>";
      $s .= $this->GetIndex();
      return  $s;
    }
    
    $this->title = tlocal::$data['default']['sitemap'];
  }
  
  public function getIndex() {
    $lastmod = strftime("%Y-%m-%d", $this->date);
    
    $result = '
    
    <sitemapindex xmlns="http://www.google.com/schemas/sitemap/0.84">
    ';
    $url = litepublisher::$options->files . '/files/' . litepublisher::$domain;
    for ($i =1; $i <= $this->countfiles; $i++) {
      $result .= "   <sitemap>
      <loc>$url.$i.xml.gz</loc>
      <lastmod>$lastmod</lastmod>
      </sitemap>\n";
    }
    
    $result .= '   </sitemapindex>';
    return $result;
  }
  
  public function createfiles() {
    $this->countfiles = 0;
    $this->count = 0;
    $this->date = time();
    $this->lastmod = strftime("%Y-%m-%d", $this->date);
    $this->openfile();
    
    //home page
    $this->prio = 9;
    $this->write('/', ceil(litepublisher::$classes->posts->archivescount / litepublisher::$options->postsperpage));
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
    if (dbversion) {
$db = litepublisher::$db;
      $res = $db->query("select $db->posts.pagescount, $db->posts.commentscount, $db->urlmap.url from $db->posts, $db->urlmap
      where $db->posts.status = 'published' and $db->posts.posted < now() and $db->urlmap.id = $db->posts.idurl");
      $res->setFetchMode (PDO::FETCH_ASSOC);
      foreach ($res as $item) {
        $comments = litepublisher::$options->commentpages ? ceil($item['commentscount'] / litepublisher::$options->commentsperpage) : 1;
        $this->write($item['url'], max($item['pagescount'], $comments));
      }
    } else {
      $posts = tposts::instance();
      foreach ($posts->archives as $id => $posted) {
        $post = tpost::instance($id);
        $this->write($post->url, $post->countpages);
        $post->free();
      }
    }
  }
  
  private function writemenus() {
    $menus = tmenus::instance();
    foreach ($menus->items as $id => $item) {
      if ($item['status'] == 'draft') continue;
      $this->writeitem($item['url'], $this->prio);
    }
  }
  
  
  private function writetags($tags) {
    $postsperpage = $tags->lite ? 1000 : litepublisher::$options->postsperpage;
    if (dbversion) {
$db = litepublisher::$db;
      $table = $tags->thistable;
      $res = $db->query("select $table.itemscount, $db->urlmap.url from $table, $db->urlmap
      where $db->urlmap.id = $table.idurl");
      $res->setFetchMode (PDO::FETCH_ASSOC);
      foreach ($res as $item) {
        $this->write($item['url'], ceil($item['itemscount']/ $postsperpage));
      }
    } else {
      foreach ($tags->items as $id => $item) {
        $this->write($item['url'], ceil($item['itemscount']/ $postsperpage));
      }
    }
  }
  
  private function writearchives() {
$db = litepublisher::$db;
    $arch = tarchives::instance();
    $postsperpage = $arch->lite ? 1000 : litepublisher::$options->postsperpage;
    if (dbversion) $db->table = 'posts';
    foreach ($arch->items as $date => $item) {
      if (dbversion) {
    $count = $db->getcount("status = 'published' and year(posted) = '{$item['year']}' and month(posted) = '{$item['month']}'");
      } else {
        $count = count($item['posts']);
      }
      $this->write($item['url'], ceil($count/ $postsperpage));
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
$url = litepublisher::$options->url . $url;
    gzwrite($this->fd, "   <url>
    <loc>$url</loc>
    <lastmod>$this->lastmod</lastmod>
    <changefreq>daily</changefreq>
    <priority>0.$prio</priority>
    </url>\n");
    
    if (++$this->count  >= 45000) {
      $this->closefile();
      $this->openfile();
    }
  }
  
  private function openfile() {
    $this->count = 0;
    $this->countfiles++;
    if ($this->fd = gzopen(litepublisher::$paths['files'] . litepublisher::$domain . ".$this->countfiles.xml.gz", 'w')) {
      $this->WriteHeader();
    } else {
      tfiler::log("error write file to folder " . litepublisher::$paths[files]);
      exit();
    }
  }
  
  private function closefile() {
    $this->WriteFooter();
    gzclose($this->fd);
    @chmod(litepublisher::$paths['files'] . litepublisher::$domain . ".$this->countfiles.xml.gz", 0666);
    $this->fd = false;
  }
  
  private function WriteHeader() {
    gzwrite($this->fd, '<?xml version="1.0" encoding="UTF-8"?>
    <urlset xmlns="http://www.google.com/schemas/sitemap/0.84"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.google.com/schemas/sitemap/0.84
    http://www.google.com/schemas/sitemap/0.84/sitemap.xsd">
    ');
  }
  
  private function WriteFooter() {
    gzwrite($this->fd, "</urlset>");
  }
  
}//class

?>