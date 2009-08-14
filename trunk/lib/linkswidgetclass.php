<?php
class TLinksWidget extends TItems {
  public $redirlink;
  
  protected function CreateData() {
    parent::CreateData();
    $this->basename = 'linkswidget';
    $this->redirlink = '/linkswidget/';
    $this->Data['redir'] = true;
  }
  
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  public function GetWidgetContent($id) {
    global $Options;
$Template = TTemplate::Instance();
    $lang = &TLocal::$data['default'];
    
    $result = $Template->GetBeforeWidget('links');
    $class = isset($Template->theme['class']['links']) ? $Template->theme['class']['rss'] : '';
    $class = empty($class) ? '' : "class=\"$class\"";
    
    
    foreach ($this->items as $id => $item) {
      $url =  $item['url'];
      if ($this->redir &&(strncmp($url, $Options->url, strlen($Options->url)) != 0)) {
      $url = "$Options->url$this->redirlink{$Options->q}id=$id";
      }
      
  $result .=   "<li $class><a href=\"$url\" title=\"{$item['title']}\">{$item['text']}</a></li>\n";
    }
    
    $result .= $Template->GetAfterWidget();
    return $result;
  }
  
  public function Add($url, $title, $text) {
    $this->items[++$this->lastid] = array(
    'url' => $url,
    'title' => $title,
    'text' => $text
    );
    
    $this->Save();
    $this->Added($this->lastid);
    return $this->lastid;
  }
  
  public function Edit($id, $url, $title, $text) {
    $this->items[$id] = array(
    'url' => $url,
    'title' => $title,
    'text' => $text
    );
    
    $this->Save();
  }
  
  public function Request($arg) {
    $this->CacheEnabled = false;
    $id = empty($_GET['id']) ? 1 : (int) $_GET['id'];
    if (!isset($this->items[$id])) return 404;
  return "<?php @header('Location: {$this->items[$id]['url']}'); ?>";
  }
  
}//class

?>