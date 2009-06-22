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
    if (!$this->confirmed()) $result .= $html->notfound;
    $result .=  $html->uploadform;
   }
   break;
   
   case 'edit':
   $id = $this->idget();
   if ($files->ItemExists($id)) {
    $item = $files->items[$id];
    eval('$result .= "'. $html->editform . '\n";');
   } else {
    $result .= $html->notfound;
    break;
   }
   
  }
  
  $result .= sprintf($html->countfiles, count($files->items));
  $result .= $html->tableheader;
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
    return sprintf($html->attack, $_FILES["filename"]["name"]);
   }
   
   $overwrite  = isset($_POST['overwrite']);
   
   $files->AddFile($_FILES["filename"]["name"], file_get_contents($_FILES["filename"]["tmp_name"]),
   $_POST['title'], $overwrite);
   return $html->success;
   
   case 'delete':
   $id = $this->idget();
   if (!$files->ItemExists($id)) return $html->notfound;
   if ($this->confirmed()) {
    $files->Delete($id);
    return $html->deleted;
   }
   
   case 'edit':
   $id = $this->idget();
   if (!$files->ItemExists($id)) return $html->notfound;
   $files->items[$id]['title'] = $_POST['title'];
   $files->Save();
   return $html->edited;
  }
  
  return '';
  
 }
 
}//class
?>