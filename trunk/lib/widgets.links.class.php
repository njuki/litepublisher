<?php
class tlinkswidget extends TItems {
  public $redirlink;

  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'linkswidget';
    $this->redirlink = '/linkswidget/';
    $this->data['redir'] = true;
  }
  
  public function getwidgetcontent($id) {
  global $options;
    $result = '';
$theme = ttheme::instance();
$tml = $theme->getwidgetitem('link');
$tml .= "\n";
$args = targs::instance();
    foreach ($this->items as $id => $item) {
      $url =  $item['url'];
      if ($this->redir && !strbegin($url, $options->url)) {
      $url = $options->url . $this->redirlink . $options->q . "id=$id";
      }
$args->url = $url
      $args->title = $item['title'];
$args->text = $item['text'];
  $result .=   $theme->parsearg($tml, $args);
    }
    
    return $result;
  }
  
  public function add($url, $title, $text) {
    $this->items[++$this->autoid] = array(
    'url' => $url,
    'title' => $title,
    'text' => $text
    );
    
    $this->save();
    $this->added($this->autoid);
    return $this->autoid;
  }
  
  public function edit($id, $url, $title, $text) {
    $this->items[$id] = array(
    'url' => $url,
    'title' => $title,
    'text' => $text
    );
    
    $this->save();
  }
  
  public function request($arg) {
    $this->CacheEnabled = false;
    $id = empty($_GET['id']) ? 1 : (int) $_GET['id'];
    if (!isset($this->items[$id])) return 404;
  return "<?php @header('Location: {$this->items[$id]['url']}'); ?>";
  }
  
}//class

?>