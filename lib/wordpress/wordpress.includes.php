$req = true;

if (	$commenter = tcommentform::getcomuser()) {
  $comment_author = $commenter['name'];
  $comment_author_email = $commenter['email'];
  $comment_author_url = $commenter['url'];
}
/** @todo Use API instead of SELECTs. */
if ( $user_ID) {
  $comments = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->comments WHERE comment_post_ID = %d AND (comment_approved = '1' OR ( user_id = %d AND comment_approved = '0' ) )  ORDER BY comment_date_gmt", $post->ID, $user_ID));
} else if ( empty($comment_author) ) {
  $comments = get_comments( array('post_id' => $post->ID, 'status' => 'approve', 'order' => 'ASC') );
} else {
  $comments = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->comments WHERE comment_post_ID = %d AND ( comment_approved = '1' OR ( comment_author = %s AND comment_author_email = %s AND comment_approved = '0' ) ) ORDER BY comment_date_gmt", $post->ID, wp_specialchars_decode($comment_author,ENT_QUOTES), $comment_author_email));
}

// keep $comments for legacy's sake
$wp_query->comments = apply_filters( 'comments_array', $comments, $post->ID );
$comments = &$wp_query->comments;
$wp_query->comment_count = count($wp_query->comments);
update_comment_cache($wp_query->comments);

if ( $separate_comments ) {
  $wp_query->comments_by_type = &separate_comments($comments);
  $comments_by_type = &$wp_query->comments_by_type;
}

$overridden_cpage = FALSE;
if ( '' == get_query_var('cpage') && get_option('page_comments') ) {
  set_query_var( 'cpage', 'newest' == get_option('default_comments_page') ? get_comment_pages_count() : 1 );
  $overridden_cpage = TRUE;
}

if ( !defined('COMMENTS_TEMPLATE') || !COMMENTS_TEMPLATE)
define('COMMENTS_TEMPLATE', true);