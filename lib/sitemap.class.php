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
    global $options, $Urlmap;
    $posts = &Tposts::instance();
$theme = ttheme::instance();
    $postsperpage = 1000;
    $list = array_slice(array_keys($posts->archives), ($Urlmap->page - 1) * $postsperpage, $postsperpage);
    $result = $TemplatePost->LitePrintposts($list);
    
    if ($Urlmap->page  == 1) {
      $result .= '<ul>' . TLocal::$data['default']['tags'];
      $tags = &TTags::instance();
      foreach ($tags->items as $id => $item) {
    $result .= "<li><a href=\"$options->url{$item['url']}\">{$item['name']}</a></li>\n";
      }
      $result .= "</ul>\n";
    }
    
    $result .=$theme->getpages('/sitemap/', $urlmap->page, ceil(count($posts->archives)/ $postsperpage));
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

    $this->title = tlcal::$data['default']['sitemap'];
  }
  
  public function getIndex() {
    global $options, $domain;
    $lastmod = strftime("%Y-%m-%d", $this->date);
    
    $result = '
    
    <sitemapindex xmlns="http://www.google.com/schemas/sitemap/0.84">
    ';
    
    for ($i =1; $i <= $this->countfiles; $i++) {
      $result .= "   <sitemap>
      <loc>$options->url/files/$domain.$i.xml.gz</loc>
      <lastmod>$lastmod</lastmod>
      </sitemap>\n";
    }
    
    $result .= '   </sitemapindex>';
    return $result;
  }
  
  public function createfiles() {
global $classes, $options;
    $this->countfiles = 0;
    $this->count = 0;
    $this->date = time();
    $this->lastmod = strftime("%Y-%m-%d", $this->date);
    $this->openfile();
    
    //home page
$this->prio = 9;
    $this->write('/', ceil($classes->posts->archivescount / $options->postsperpage));
$this->prio = 8;
    $this->writeposts();

$this->prio = 8;
$this->writemenus();

$this->prio = 7;
    $this->writetags($classes->categories);
    $this->writetags($classes->tags);

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
    global $options, $db;
if (dbversion) {
$res = $db->query("select $db->posts.pagescount, $db->posts.commentscount, $db->urlmap.url from $db->posts, $db->urlmap
where $db->posts.status = 'published' and $db->posts.posted < now() and $db->urlmap.id = $db->posts.id");
$res->setFetchMode (PDO::FETCH_ASSOC);
foreach ($res as $item) {
$comments = $options->commentpages ? ceil($item['commentscount'] / $options->commentsperpage) : 1;
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
global $options, $db;
      $postsperpage = $tags->lite ? 1000 : $options->postsperpage;
if (dbversion) {
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
global $options;
$arch = tarchives::instance();
      $postsperpage = $arch->lite ? 1000 : $options->postsperpage;
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
    global $options;
    gzwrite($this->fd, "   <url>
    <loc>$options->url$url</loc>
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
    global $paths, $domain;
    $this->count = 0;
    $this->countfiles++;
    if ($this->fd = gzopen($paths['files'] . "$domain.$this->countfiles.xml.gz", 'w')) {
      $this->WriteHeader();
    } else {
      echo "error write file to folder $paths[files]";
      exit();
    }
  }
  
  private function closefile() {
    global $paths, $domain;
    $this->WriteFooter();
    gzclose($this->fd);
    @chmod($paths['files'] . "$domain.$this->countfiles.xml.gz", 0666);
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