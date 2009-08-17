<?php

class TNicedit extends TPlugin {

 public static function &Instance() {
  return GetInstance(__class__);
 }

public function Onhead() {
global $Options;
$url = $Options->url . '/plugins/nicedit';

$result =<<<i_C
<script type="text/javascript" src="$url/nicEdit.js"></script>
<script type="text/javascript">
   bkLib.onDomLoaded(function()
{ nicEditors.allTextAreas({fullPanel : true});
});
</script>
i_C;

return $result;
}
}//class
?>