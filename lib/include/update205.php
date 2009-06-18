<?php

function Update205() {
 $openid = &TOpenid::Instance();
 $openid->Data['usebigmath'] = false;
 $openid->Save();
}
?>