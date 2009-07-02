<?php

class TBackup2email extends TPlugin {
 
 public static function &Instance() {
  return GetInstance(__class__);
 }

 protected function CreateData() {
  parent::CreateData();
$this->Data['idcron'] = 0;
}

 public function SendBackup() {
global $Options, $domain;
  $admin = &TRemoteAdmin::Instance();
  $s = $admin->GetPartialBackup(true, true, true);
  $date = date('d-m-Y');
  $filename = "$domain-$date.zip";

$dir = dirname(__file__) . DIRECTORY_SEPARATOR;
if (@file_exists($dir . $Options->language . '.ini')) {
$ini = parse_ini_file($dir . $Options->language . '.ini');
} else {
$ini = parse_ini_file($dir . 'about.ini');
}
TMailer::SendAttachmentToAdmin("[backup] $filename", $ini['body'], $filename, $s);
}

}

?>