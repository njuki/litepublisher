<?php

class TAdminPostContentPlugin  {


public function Getcontent() {
global $Options;
$plugin = &TPostContentPlugin ::Instance();
$tml = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . "postcontent$Options->language.tml");
eval('$result = "'. $tml . '\n";');
$result = str_replace("'", '"', $result);
return $result;
}

public function ProcessForm() {
extract($_POST);
$plugin = &TPostContentPlugin ::Instance();
$plugin->Lock();
$plugin->before = $before;
$plugin->after = $after;
$plugin->Unlock();		
return '';
}

}
?>