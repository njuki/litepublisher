<?php
/* Translates old comment.ini to new standart comments.tml */

function ini2tml() {
    $template = TTemplate::Instance();
$tc = TTemplateComment ::Instance();
    
    $ini = parse_ini_file($template->path . 'comments.ini');

$comments = $ini['list'];
$i = strpos($comments, '%1$s');
$startcomments = substr($comments, 0, $i);
$endcomments = substr($comments, $i +

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
	<h3 id="comments">$lang->pingbacks</h3>
	<ol class="commentlist" start="1">
<!--pingback-->
<li class="alt" id="comment-$comment->id">
			<cite>$comment->authorlink</cite>
		</li>
<!--/pingback-->
 </ol>
<!--/pingbacks-->

<!--closed-->
<p class="nocomments">$lang->closed</p>
<!--/closed-->

<!--form-->
<h3 id="respond">$lang->reply</h3>
<form action="$Options->url/send-comment.php" method="post" id="commentform">
<p><input type="text" name="name" id="name" value="{$values['name']}" size="22" tabindex="1" />
<label for="name"><strong>$lang->name</strong></label></p>

<p><input type="text" name="email" id="email" value="{$values['email']}" size="22" tabindex="2" />
<label for="email"><strong>$lang->email</strong></label></p>

<p><input type="text" name="url" id="url" value="{$values['url']}" size="22" tabindex="3" />
<label for="url"><strong>$lang->url</strong></label></p>

<p><input type="checkbox" name="subscribe" id="subscribe" checked="{$values['subscribe']}" size="22" tabindex="4" />
<label for="subscribe"><strong>$lang->subscribe</strong></label></p>

<p><textarea name="content" id="comment" cols="100%" rows="10" tabindex="5"></textarea></p>

<input type="hidden" name="postid" value="{$values['postid']}" />
<input type="hidden" name="antispam" value="{$values['antispam']}" />

<p><input name="submit" type="submit" id="submit" tabindex="6" value="$lang->send" />
</form>
<!--/form-->
"

file_put_contents($template->path . 'comments.tml', $tml);
    

}
?>