<?php
function CheckSimpleSpamProtection() {
 if (isset($_POST) && isset($_POST['FormValue'])) {
  $TimeKey = substr($_POST['FormValue'], strlen('_Value'));
  return time() < $TimeKey;
 }
 return false;
}
?>