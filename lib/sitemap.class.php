<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tsitemap extends titems {
  private $lastmod;
  private $count;
  private $fd;
  public $title;
  
  public static function instance() {
    return Getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'sitemap';
    $this->data['date'] = time();
    $this->data['countfiles'] = 1;
  }
  
  public function add($class, $prio) {
    $this->items[$class] = (int) $prio;
    $this->Save();
  }
  
  public function cron() {
    $this->createfiles();
  }
  
  public function GetTemplateContent() {
    global $options, $Urlmap;
    $posts = &TPosts::instance();
$theme = ttheme::instance();
    $postsperpage = 1000;
    $list = array_slice(array_keys($posts->archives), ($Urlmap->page - 1) * $postsperpage, $postsperpage);
    $result = $TemplatePost->LitePrintPosts($list);
    
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
    $this->title = TLocal::$data['default']['sitemap'];
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
    $this->countfiles = 0;
    $this->count = 0;
    $this->date = time();
    $this->lastmod = strftime("%Y-%m-%d", $this->date);
    $this->OpenFile();
    
    //home page
    $this->WriteItem('/', 9);
    $this->WritePosts();
    $this->WriteNamed('menus', 8);
    $this->WriteNamed('categories', 7);
    $this->WriteNamed('tags', 6);
    $this->WriteNamed('archives', 5);
    
    $this->CloseFile();
    $this->Save();
  }
  
  private function WritePosts() {
    global $classes;
    $Urlmap = TUrlmap::instance();
    $posts = TPosts::instance();
    foreach ($Urlmap->items as $url => $item) {
      if (($item['class'] == $classes->classes['post']) && isset($posts->archives[$item['arg']])) {
        $this->WriteItem($url, 8);
      }
    }
  }
  
  private function WriteNamed($name, $prio = 5) {
global $classes;
    $instance = $classes->$name;
    foreach ($instance->items as $id => $item) {
      $this->WriteItem($item['url'], $prio);
    }
  }
  
  private function WalkUrlmap(&$items) {
    global $classes;
    $posts = TPosts::instance();
    foreach ($items as $url => $item) {
      $class = $item['class'];
      if (($class == $classes->classes['post']) && !isset($posts->archives[$item['arg']])) continue;
      $prio = $this->GetPriority($class);
      if ($prio == 0) continue;
      $this->WriteItem($url, $prio);
    }
  }
  
  private function GetPriority($class) {
    global $classes;
    if (isset($this->items[$class])) return $this->items[$class];
    switch ($class) {
      case $classes->classes['post']: return 8;
      case $classes->classes['categories']: return 6;
      case $classes->classes['tags']: return 5;
      case 'tmenu': return 8;
      case 'TContactForm': return 7;
      case 'TArchives': return 5;
      case 'THomepage': return 9;
    }
    return 0;
  }
  
  private function WriteItem($url, $prio = 5) {
    global $options;
    gzwrite($this->fd, "   <url>
    <loc>$options->url$url</loc>
    <lastmod>$this->lastmod</lastmod>
    <changefreq>daily</changefreq>
    <priority>0.$prio</priority>
    </url>\n");
    
    if (++$this->count  >= 45000) {
      $this->CloseFile();
      $this->OpenFile();
    }
  }
  
  private function OpenFile() {
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
  
  private function CloseFile() {
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