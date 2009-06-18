<?php

function Update209() {
 global $Urlmap;
 $Urlmap->tree['admin']['items']['moderator']['final'] = true;
 $Urlmap->Save();
}
?>