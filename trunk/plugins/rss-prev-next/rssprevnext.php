<?php

class TRSSPrevNext extends TPlugin {
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 public function BeforePostContent($id) {
$tp = TTemplatePost::Instnace();
return $tp->GetPrevNextLinks(TPost::Instance($id));
}

}//class
?>