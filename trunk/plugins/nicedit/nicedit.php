<?php

class tnicedit extends tplugin {

 public static function instance() {
  return getinstance(__class__);
 }

public function onhead(&$head) {
$head .= sprintf('<script type="text/javascript" src="%s/plugins/nicedit/nicEdit.js"></script>', litepublisher::$options->files);
$head .= '
<script type="text/javascript">
   bkLib.onDomLoaded( function() {
 nicEditors.allTextAreas({fullPanel : true});
});
</script>
';
}

}//class
?>