<?php

class TSitemap extends TEventClass {
  private $lastmod;
  private $count;
  private $fd;
  public $title;
  
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  protected function CreateData() {
    parent::CreateData();
    $this->basename = 'sitemap';
    $this->Data['date'] = time();
    $this->Data['countfiles'] = 1;
  }
  
  public function Cron() {
    $this->CreateFiles();
  }
  
  public function GetTemplateContent() {
    global $Options, $Urlmap;
    $posts = &TPosts::Instance();
    $TemplatePost = &TTemplatePost::Instance();
    $postsperpage = 1000;
    $list = array_slice(array_keys($posts->archives), ($Urlmap->pagenumber - 1) * $postsperpage, $postsperpage);
    $result = $TemplatePost->LitePrintPosts($list);
    
    if ($Urlmap->pagenumber  == 1) {
      $result .= '<ul>' . TLocal::$data['default']['tags'];
      $tags = &TTags::Instance();
      foreach ($tags->items as $id => $item) {
    $result .= "<li><a href=\"$Options->url{$item['url']}\">{$item['name']}</a></li>\n";
      }
      $result .= "</ul>\n";
    }
    
    $result .=$TemplatePost->PrintNaviPages('/sitemap/', $Urlmap->pagenumber, ceil(count($posts->archives)/ $postsperpage));
    return $result;
  }
  
  public function Request($arg) {
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
  
  public function GetIndex() {
    global $Options, $domain;
    $lastmod = strftime("%Y-%m-%d", $this->date);
    
    $result = '
    
    <sitemapindex xmlns="http://www.google.com/schemas/sitemap/0.84">
    ';
    
    for ($i =1; $i <= $this->countfiles; $i++) {
      $result .= "   <sitemap>
      <loc>$Options->url/files/$domain.$i.xml.gz</loc>
      <lastmod>$lastmod</lastmod>
      </sitemap>\n";
    }
    
    $result .= '   </sitemapindex>';
    return $result;
  }
  
  public function CreateFiles() {
    $prio = array(
    'TPost' => 8,
    'TMenuItem' => 8,
    'TContactForm' => 8,
    'TArchives' => 5,
    'TCategories' => 6,
    'TTags' => 5,
    'THomepage' => 9);
    
    
    $this->countfiles = 0;
    $this->count = 0;
    $this->date = time();
    $this->lastmod = strftime("%Y-%m-%d", $this->date);
    $urlmap = &TUrlmap::Instance();
    $this->OpenFile();
    foreach ($urlmap->items as $url => $item) {
      if (isset($prio[$item['class']])) {
        $this->WriteItem($url, $prio[$item['class']]);
      }
    }
    
    $this->CloseFile();
    $this->Save();
  }
  
  private function WriteItem($url, $prio = 5) {
    global $Options;
    gzwrite($this->fd, "   <url>
    <loc>$Options->url$url</loc>
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