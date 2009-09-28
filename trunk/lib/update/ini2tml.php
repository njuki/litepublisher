<?php

function ini2tml($dir) {
    $ini = parse_ini_file($dir . 'comments.ini');

$comments = $ini['list'];
$i = strpos($comments, '%1$s');
$startcomments = substr($comments, 0, $i);
$endcomments = substr($comments, $i + strlen('%1$s'));
$comment = $ini['comment'];
if (isset($ini['itemclass1'])) {
$classes = "<!--class1-->{$ini['itemclass1']}<!--/class1-->";
if (isset($ini['itemclass2'])) $classes .= "<!--class2-->{$ini['itemclass2']}<!--/class2-->";
$comment = str_replace('$itemclass', $classes, $comment);
}
$hold = "<!--hold-->{$ini['hold']}<!--/hold-->";
$comment = str_replace('$hold', $hold, $comment);

$tml = "<!--comments-->
<!--count-->
{$ini['count']}
<!--/count-->
$startcomments
<!--comment-->
$comment
<!--/comment-->
$endcomments
<!--/comments-->

<!--pingbacks-->
{$ini['pingbackhead']} 
$startcomments
<!--pingback-->
{$ini['pingback']}
<!--/pingback-->
$endcomments
<!--/pingbacks-->

<!--closed-->
{$ini['closed']}
<!--/closed-->

<!--form-->
{$ini['formheader']} 
";
$form = '<form action="$Options->url/send-comment.php" method="post" id="commentform">';

$tabindex = 1;
$type = 'text';
$fields = array('name', 'email', 'url');
    foreach ($fields as $field) {
    $value = "{\$values['$field']}";
      $label = '$lang->' . $field;
        eval('$form .= "'. $ini['field'] . '\n";');
$tabindex++;
   }

//checkbox
$field = 'subscribe';
    $value = "{\$values['$field']}";
      $label = '$lang->' . $field;
        eval('$form .= "'. $ini['checkbox'] . '\n";');    
$tabindex++;

$form .= tabindex($ini['content'],     $tabindex++);
$form .= '<input type="hidden" name="postid" value="{$values[\'postid\']}" />
<input type="hidden" name="antispam" value="{$values[\'antispam\']}" />';

$form .= tabindex($ini['button'], $tabindex++);

$tml .= "
$form
</form>
{$ini['formfooter']}
<!--/form-->";

$tml = str_replace("'", '"', $tml);
file_put_contents($dir . 'comments.tml', $tml);
    }

function tabindex($s, $i) {
return str_replace('$tabindex', $i, $s);
}
?>