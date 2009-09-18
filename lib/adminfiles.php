<?php

class TAdminFiles extends TAdminPage {
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  protected function CreateData() {
    parent::CreateData();
    $this->basename = 'files';
  }
  
  public function Getcontent() {
    global $Options, $paths;
    $files = &TFiles::Instance();
    $html = &THtmlResource::Instance();
    $html->section = $this->basename;
    $lang = TLocal::Instance();
    $lang->section = $this->basename;
    
    $result = '';
    
    switch ($this->arg) {
      case null:
      $result .= $html->uploadform();
      break;
      
      case 'delete':
      $id = $this->idget();
      if ($files->ItemExists($id)) {
        $result .= $html->confirmform($id, sprintf($lang->confirm, $files->items[$id]['filename']));
      } else {
        if (!$this->confirmed) $result .=  $html->notfound;
        $result .=  $html->uploadform();
      }
      break;
      
      case 'edit':
      $id = $this->idget();
      if ($files->ItemExists($id)) {
        $result .= $html->editform($files->items[$id]['title']);
      } else {
        $result .= $html->notfound();
        break;
      }
      
    }
    eval('$s = "'. $html->countfiles. '\n";');
    $result .= sprintf($s, count($files->items));
    eval('$result .= "'. $html->tableheader. '\n";');
    $tableitem = $html->tableitem ;
    foreach ($files->items as $id =>$item) {
      eval('$result .= "' . $tableitem . '\n";');
    }
    eval('$result .= "'. $html->tablefooter . '\n";');;
    $result = str_replace("'", '"', $result);
    return $result;
  }
  
  public function ProcessForm() {
    global $Options, $paths;
    $files = &TFiles::Instance();
    $html = &THtmlResource::Instance();
    $html->section = $this->basename;
    $lang = &TLocal::Instance();
    
    switch ($this->arg) {
      case null:
      if (!is_uploaded_file($_FILES["filename"]["tmp_name"])) {
        eval('$s = "'. $html->attack. '\n";');
        return sprintf($s, $_FILES["filename"]["name"]);
      }
      
      $overwrite  = isset($_POST['overwrite']);
      
      $files->AddFile($_FILES["filename"]["name"], file_get_contents($_FILES["filename"]["tmp_name"]),
      $_POST['title'], $overwrite);
      eval('$result = "'. $html->success . '\n";');
      return $result;
      
      case 'delete':
      $id = $this->idget();
      if (!$files->ItemExists($id)) return $this->notfound();
      if ($this->confirmed) {
        $files->Delete($id);
        eval('$result = "'. $html->deleted . '\n";');
        return $result;
      }
      
      case 'edit':
      $id = $this->idget();
      if (!$files->ItemExists($id)) {
        eval('$result = "'. $html->notfound . '\n";');
        return $result;
      }
      $files->items[$id]['title'] = $_POST['title'];
      $files->Save();
      eval('$result = "'. $html->edited . '\n";');
      return $result;
    }
    
    return '';
    
  }
  
}//class
?>