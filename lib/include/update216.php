<?php

function Update216() {
 global $Urlmap;
 $Urlmap->tree['admin']['items']['plugins']['final'] = true;
 $Urlmap->tree['admin']['items']['files']['final'] = true;
 $Urlmap->Save();
}
?>