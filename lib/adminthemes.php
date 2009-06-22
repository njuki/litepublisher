<?php

class TAdminThemes extends TAdminPage {
 private $adminplugin;
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 protected function CreateData() {
  parent::CreateData();
  $this->basename = 'themes';
 }
 
 public function GetMenu() {
  global $paths, $Options;
  $result = parent::GetMenu();
  $Template = &TTemplate::Instance();
  $about = $Template->GetAbout($Template->themename);
  if (!empty($about['adminclassname'])) {
   $themename = $Template->themename;
   $html = &THtmlResource::Instance();
   $html->section = $this->basename;
$lang = &TLocal::Instance();
   eval('$result .= "' . $html->adminplugin . '\n";');
  }
  return $result;
 }
 
 public function Getcontent() {
  global $Options, $Template, $paths;
  $html = &THtmlResource::Instance();
  $html->section = $this->basename;
$lang = &TLocal::Instance();
  
  switch ($this->arg) {
   case null:
   if (!empty($_GET['adminplugin'])) return $this->GetAdminPlugin('Getcontent');
   $Template = &TTemplate::Instance();
   $result = $html->formheader;
   $item = $html->radioitem;
   $admin = &TRemoteAdmin::Instance();
   $list =$admin->GetThemesList();
   sort($list);
   foreach ($list as $name) {
    $checked = $name == $Template->themename ? "checked='checked'" : '';
    $about = $Template->GetAbout($name);
    eval('$result .= "'. $item . '\n";');
   }
   eval('$result .= "' . $html->formfooter . '\n";');
   $result = str_replace("'", '"', $result);
   return $result;
   
   case 'edit':
   $themename = !empty($_GET['themename']) ? $_GET['themename'] : $Template->themename;
   $result = sprintf($html->filelist, $themename);
   $result .= "\n<ul>\n";
   $filelist = TFiler::GetFileList($paths['themes'] . $themename . DIRECTORY_SEPARATOR  );
   sort($filelist);
   foreach ($filelist as $filename) {
    $result .= "<li><a href=\"$Options->url/admin/themes/edit/?themename=$themename&filename=$filename\">$filename</a></li>\n";
   }
   $result .= "</ul>\n";
   if (!empty($_GET['filename'])) {
    $content = file_get_contents($paths['themes'].$themename . DIRECTORY_SEPARATOR  . $_GET['filename']);
    $content = $this->ContentToForm($content);
    $result .= sprintf($html->filename, $_GET['filename']);
   } else {
    $content = '';
   }
   eval('$result .= "'. $html->editform . '\n";');
   break;
  }
  return $result;
 }
 
 public function ProcessForm() {
  global $Options, $paths;
  $html = &THtmlResource::Instance();
  $html->section = $this->basename;
$lang = &TLocal::Instance();
  
  switch ($this->arg) {
   case null:
   if (!empty($_GET['adminplugin'])) return $this->GetAdminPluginContent();
   if (!empty($_GET['adminplugin'])) return $this->GetAdminPlugin('ProcessForm');
   
   if (!empty($_POST['selection']))  {
    $Template = &TTemplate::Instance();
    try {
     $Template->themename = $_POST['selection'];
    } catch (Exception $e) {
     $Template->themename = 'default';
     return 'Caught exception: '.  $e->getMessage() . "<br>\ntrace error\n<pre>\n" .  $e->getTraceAsString() . "\n</pre>\n";
    }
    
    return $html->success;
   }
   break;
   
   case 'edit':
   if (!empty($_GET['filename']) && !empty($_GET['themename'])) {
    if (file_put_contents($paths['themes'] . $_GET['themename'] . DIRECTORY_SEPARATOR . $_GET['filename'], $_POST['content'])) {
     $Urlmap = &TUrlmap::Instance();
     $Urlmap->ClearCache;
    } else {
     return $html->errorsave;
    }
   }
   break;
  }
  return '';
 }
 
 public function  GetAdminPlugin($method) {
  if (!isset($this->adminplugin)) {
   $Template =  &TTemplate::Instance();
   $about = $Template->GetAbout($Template->themename);
   if (empty($about['adminclassname']))  return '';
   $class = $about['adminclassname'];
   if (!@class_exists($class)) {
    @require_once($Template->path . $about['adminfilename']);
   }
   
   $this->adminplugin = new $class ();
  }
  
  return $this->adminplugin->$method();
 }
 
}//class
?>