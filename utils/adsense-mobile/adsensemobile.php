<?php

class TAdsensemobile extends TPlugin {
 
 public static function &Instance() {
  return GetInstance(__class__);
 }

 public function body() {
global $Urlmap;
if ($Urlmap->is404 || $Urlmap->IsAdminPanel) return '';

return "<?php require_once('" . dirname(__file__) . DIRECTORY_SEPARATOR . "google.php'); ?>";
}

}
?>