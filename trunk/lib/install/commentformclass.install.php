<?php

function TCommentFormInstall(&$self) {
 global $Options;
 $self->Data= $self->Data +  array(
 'url' => '/send-comment.php',
 'Fields' => array(
 'name' => 'text',
 'email' => 'text',
 'url' => 'text',
 'subscribe' => 'checkbox'
 ),
 'Hidden' => array(
 'postid' => 0,
 'antispam' => ''
 )
 );
 
 $self->Save();
 
 $Urlmap = &TUrlmap::Instance();
 $Urlmap->Add($self->url, get_class($self), null);
}

function TCommentFormUninstall(&$self) {
 TUrlmap::unsub($self);
}

?>