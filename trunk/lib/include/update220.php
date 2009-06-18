<?php

function Update220() {
 $authors = &TCommentUsers ::Instance();
 $authors->Data['hidelink'] = false;
 $authors->Data['redir'] = false;
 $authors->Data['nofollow'] = false;
 $authors->Save();
 
 $Urlmap = &TUrlmap::Instance();
 $Urlmap->AddFinal('authors', get_class($authors));
 
 $robots = &TRobotstxt ::Instance();
 $robots->AddDisallow('/authors/');
}

?>