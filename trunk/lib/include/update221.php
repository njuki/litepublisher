<?php

function Update221() {
 TClasses::Register('THtmlResource', 'htmlresource.php');
 TClasses::Unregister('TAdminQuickPost');
 TClasses::Register('TPostEditor', 'adminposteditor.php');
}

?>