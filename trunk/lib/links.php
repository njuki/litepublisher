<?php
class tlinks extends TItems {
  public $redirlink;

  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'links';
    $this->redirlink = '/links/';
    $this->data['redir'] = true;
  }
  
  public function getwidgetcontent($id) {
    global $options;
    $Template = TTemplate::Instance();
    $lang = &TLocal::$data['default'];
    
    $result = '';
    $class = isset($Template->theme['class']['links']) ? $Template->theme['class']['rss'] : '';
    $class = empty($class) ? '' : "class=\"$class\"";
    
    foreach ($this->items as $id => $item) {
      $url =  $item['url'];
      if ($this->redir &&(strncmp($url, $options->url, strlen($options->url)) != 0)) {
      $url = "$options->url$this->redirlink{$options->q}id=$id";
      }
      
  $result .=   "<li $class><a href=\"$url\" title=\"{$item['title']}\">{$item['text']}</a></li>\n";
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