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
  $lang = &TLocal::Instance();
  
  $result = '';
  
  switch ($this->arg) {
   case null:
   eval('$result .=  "'. $html->uploadform . '\n";');
   break;
   
   case 'delete':
   $id = $this->idget();
   if ($files->ItemExists($id)) {
    $confirm = sprintf(TLocal::$data[$this->basename]['confirm'], $files->items[$id]['filename']);
    eval('$result .= "'. $html->confirmform . '\n";');
   } else {
    if (!$this->confirmed()) eval('$result .= "'. $html->notfound. '\n";');
    eval('$result .=  "'. $html->uploadform . '\n";');
   }
   break;
   
   case 'edit':
   $id = $this->idget();
   if ($files->ItemExists($id)) {
    $item = $files->items[$id];
    eval('$result .= "'. $html->editform . '\n";');
   } else {
    eval('$result .= "'. $html->notfound. '\n";');
    break;
   }
   
  }
  
  eval('$result .= "'. sprintf($html->countfiles, count($files->items)) . '\n";');
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
    eval('$result = "'. sprintf($html->attack, $_FILES["filename"]["name"]) . '\n";');
    return $result;
   }
   
   $overwrite  = isset($_POST['overwrite']);
   
   $files->AddFile($_FILES["filename"]["name"], file_get_contents($_FILES["filename"]["tmp_name"]),
   $_POST['title'], $overwrite);
   eval('$result = "'. $html->success . '\n";');
   return $result;
   
   case 'delete':
   $id = $this->idget();
   if (!$files->ItemExists($id)) {
    eval('$result = "'. $html->notfound . '\n";');
    return $result;
   }
   if ($this->confirmed()) {
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