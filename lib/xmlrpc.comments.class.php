<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class TXMLRPCComments extends TXMLRPCAbstract {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function delete($login, $password, $id, $idpost) {
$this->auth($login, $password, 'moderator');
$manager = tcommentmanager::instance();
return $manager->delete((int) $id, (int) $idpost);
}

  public function setstatus($login, $password, $id, $idpost, $status) {
$this->auth($login, $password, 'moderator');
$manager = tcommentmanager::instance();
return $manager->setstatus((int) $id, (int) $idpost, $status);
}

  public function add($login, $password, $idpost, $name, $email, $url, $content) {
$this->auth($login, $password, 'moderator');
$manager = tcommentmanager::instance();
return $manager->add((int) $idpost, $name, $email, $url, $content);
}

  public function getcomment($login, $password, $id, $idpost) {
$this->auth($login, $password, 'moderator');
$comments = tcomments::instance((int) $idpost);
$comment = $comments->getcomment((int) $id);
return $comment->data;
}

  public function getrecent($login, $password, $count) {
$this->auth($login, $password, 'moderator');
$manager = tcommentmanager::instance();
return $manager->getrecent($count);
}

public function moderate($login, $password, $idpost, $list, $action) {
$this->auth($login, $password, 'moderator');
$idpost = (int) $idpost;
$manager = tcommentmanager::instance();
$delete = $action == 'delete';
foreach ($list as $id) {
$id = (int) $id;
if ($delete) {
$manager->delete($id, $idpost);
} else {
$manager->setstatus($id, $idpost, $action);
}
}
}
return true;
}

//wordpress api
/* only db version */
public function wpgetCommentCount($blog_id, $login, $password, $idpost) {
$this->auth($login, $password, 'moderator');
$idpost = (int) $idpost;
$comments = tcomments::instance($idpost);
if (dbversion) {
$approved = $comments->getcount("post = $idpost and status = 'approved'");
$hold = $comments->getcount("post = $idpost and status = 'hold'");
$spam= $comments->getcount("post = $idpost and status = 'spam'");
$total = $comments->getcount("post = $idpost");
} else {
$approved = $comments->count;
$hold = $comments->hold->count;
$spam= 0;
$total = $approved + $spam;
}

		return array(
			"approved" => $approved,
			"awaiting_moderation" => $hold,
			"spam" => $spam,
			"total_comments" => $total
		);
}

public function wpgetComment($blog_id, $login, $password, $id) {
$this->auth($login, $password, 'moderator');
$id = (int) $id;
$comments = tcomments::instance();
if ($comments->itemexists($id)) return $this->xerror(404, 'Invalid comment ID.');
$comment = $comments->getcomment($id);
return $this->_wpgetcomment($comment);
}

private function _wpgetcomment(tcomment $comment) {
global $options;
$data = $comment->data;

return array(
			"date_created_gmt"		=> new IXR_Date($comment->posted - $options.gmt),
			"user_id"				=> $data['author'],
			"comment_id"			=> $id,
			"parent"				=> $data['parent'],
			"status"				=> $data['status'] == 'approved' ? 'approve' : $data['status'],
			"content"				=> $data['content'],
			"link"					=> $comment->link,
			"post_id"				=> $data['post'],
			"post_title"			=> $comment->posttitle,
			"author"				=> $data['name'],
			"author_url"			=> $data['url'],
			"author_email"			=> $data['email'],
			"author_ip"				=> $data['ip'],
			"type"					=> ''
		);
}

public function wpgetComments($blog_id, $login, $password, $struct) {
$this->auth($login, $password, 'moderator');
$where = '';
$where .= isset($struct['status']) ? ' status = '. dbquote($struct['status']) : '';
$where .= isset($struct['post_id']) ? (' post = ' . int) $struct['post_id'] : '';
$offset = isset($struct['offset']) ? (int) $struct['offset'] : 0;
$count= isset($struct['number']) ? (int) $struct['number'] : 10;
$where .= " limit $offset, $count order by posted";

$comments = tcomments::instance();
$items = $comments->getitems($where);
$result = array();
$comment = new tcomment();
foreach ($items as $item) {
$comment->data = $item;
$result[] = $this->_getcomment($comment);
}
return $result;
}

public function wpdeleteComment($blog_id, $login, $password, $id) {
$this->auth($login, $password, 'moderator');
$id = (int) $id;
$comments = tcomments::instance();
if (!$comments->itemexists($id)) return $this->xerror(404, 'Invalid comment ID.');
$manager = tcommentmanager::instance();
return $manager->delete($id);
}


}//class
?>