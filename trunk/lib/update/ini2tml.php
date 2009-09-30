<?php

function ini2tml($dir) {
    $ini = parse_ini_file($dir . 'comments.ini');

$comments = $ini['list'];
$i = strpos($comments, '%1$s');
$startcomments = substr($comments, 0, $i);
$endcomments = substr($comments, $i + strlen('%1$s'));
$comment = $ini['comment'];
if (isset($ini['itemclass'])) {
$class= "<!--class1-->{$ini['itemclass']}<!--/class1-->";
if (isset($ini['itemclass2'])) $class .= "<!--class2-->{$ini['itemclass2']}<!--/class2-->";
$comment = str_replace('$itemclass', $class, $comment);
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
<form action='\$Options->url/send-comment.php' method='post' id='commentform'>\n";
$tml = str_replace("'", '"', $tml);

$form = '';

$item = str_replace("'", '\"', $ini['field']);
$tabindex = 1;
$type = 'text';
$fields = array('name', 'email', 'url');
    foreach ($fields as $field) {
    $value = "{\$values['$field']}";
      $label = "\$lang->$field";
        eval('$form .= "'. $item . '\n\n";');
$tabindex++;
   }

//checkbox
$field = 'subscribe';
    $value = "{\$values['$field']}";
      $label = '$lang->' . $field;
        eval('$form .= "'. str_replace("'", '\"', $ini['checkbox']) . '\n\n";');
$tabindex++;

$form .= tabindex($ini['content'] . "\n\n", $tabindex++);
$form .= '<input type="hidden" name="postid" value="{$values[\'postid\']}" />
<input type="hidden" name="antispam" value="{$values[\'antispam\']}" />';
$form .= "\n\n";
$form .= tabindex($ini['button'], $tabindex++);
$tml .= "
$form
</form>
{$ini['formfooter']}
<!--/form-->";

@chmod($dir . 'comments.tml', 0666);
file_put_contents($dir . 'comments.tml', $tml);
chmod($dir . 'comments.tml', 0666);
//echo htmlspecialchars($tml);
    }

function tabindex($s, $i) {
$s = str_replace('$tabindex', $i, $s);
return str_replace("'", '"', $s);
}
?>