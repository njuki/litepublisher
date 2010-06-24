<?php

function bloginfo($show='') {
	echo get_bloginfo($show, 'display');
}

function get_bloginfo($show = '', $filter = 'raw') {
	switch($show) {
		case 'url' :
		case 'home' : // DEPRECATED
		case 'siteurl' : // DEPRECATED
			$output = get_option('home');
			break;
		case 'wpurl' :
			$output = get_option('siteurl');
			break;
		case 'description':
			$output = get_option('blogdescription');
			break;
		case 'rdf_url':
			$output = get_feed_link('rdf');
			break;
		case 'rss_url':
			$output = get_feed_link('rss');
			break;
		case 'rss2_url':
			$output = get_feed_link('rss2');
			break;
		case 'atom_url':
			$output = get_feed_link('atom');
			break;
		case 'comments_atom_url':
			$output = get_feed_link('comments_atom');
			break;
		case 'comments_rss2_url':
			$output = get_feed_link('comments_rss2');
			break;
		case 'pingback_url':
			$output = get_option('siteurl') .'/xmlrpc.php';
			break;
		case 'stylesheet_url':
			$output = get_stylesheet_uri();
			break;
		case 'stylesheet_directory':
			$output = get_stylesheet_directory_uri();
			break;
		case 'template_directory':
		case 'template_url':
			$output = get_template_directory_uri();
			break;
		case 'admin_email':
			$output = get_option('admin_email');
			break;
		case 'charset':
$output = 'UTF-8';
			break;
		case 'html_type' :
			$output = get_option('html_type');
			break;
		case 'version':
			$output = litepublisher::$options->version;
			break;

		case 'language':
			$output = sprintf('%1$s-%1$s', litepublisher::$options->language);
			break;

		case 'text_direction':
			$output = 'ltr';
			break;

		case 'name':
		default:
			$output = get_option('blogname');
			break;
	}

	return $output;
}

function get_option( $setting, $default = false ) {
$options = litepublisher::$options;
switch ($setting) {
		case 'url' :
		case 'home' : // DEPRECATED
		case 'siteurl' : // DEPRECATED
		case 'wpurl' :
return $options->url;

case 'blogname':
return $options->name;



		case 'description':
case 'blogdescription':
return $options->description;

case 'html_type':
return 'text/html';

case 'stylesheet':
return 'style.css';

case 'admin_email':
return $options->email;

default:
return $default;
}
}

function get_default_feed() {
return litepublisher::$options->url . 'rss.xml';
}

function get_feed_link($feed = '') {
	if ( false !== strpos($feed, 'comments_') ) return litepublisher::$options->url . 'comments.xml';

switch ($feed) {
case 'rdf':
return litepublisher::$options->url . 'rdf.xml';


default:
return get_default_feed();
	}

}

function get_stylesheet_uri() {
return get_stylesheet_directory_uri();
}

function get_stylesheet_directory_uri() {
$template = ttemplate::instance();
return $template->url . '/style.css';
}

function get_stylesheet() {
return get_option('stylesheet'));
}

function get_theme_root_uri( $stylesheet_or_template = false ) {
return litepublisher::$options->files . '/themes';
}

function get_template_directory_uri() {
$template = ttemplate::instance();
return $template->url;
}

function get_bloginfo_rss($show = '') {
return get_bloginfo($show);
}

function bloginfo_rss($show = '') {
	echo get_bloginfo($show);
}

function wp_parse_args( $args, $defaults = '' ) {
	if ( is_object( $args ) )
		$r = get_object_vars( $args );
	elseif ( is_array( $args ) )
		$r =& $args;
	else
		wp_parse_str( $args, $r );

	if ( is_array( $defaults ) )
		return array_merge( $defaults, $r );
	return $r;
}

function wp_parse_str( $string, &$array ) {
	parse_str( $string, $array );
	if ( get_magic_quotes_gpc()  $array = stripslashes_deep( $array );
return $array 
}

function stripslashes_deep($value) {
	$value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
	return $value;
}

function esc_attr( $text ) {
//return _wp_specialchars( $text, ENT_QUOTES );
return @htmlspecialchars( $text, ENT_QUOTES)
}

function wp_list_categories( $args = '' ) {
	$defaults = array(
		'show_option_all' => '',
 'orderby' => 'name',
		'order' => 'ASC',
 'show_last_update' => 0,
		'style' => 'list',
 'show_count' => 0,
		'hide_empty' => 1,
 'use_desc_for_title' => 1,
		'child_of' => 0,
 'feed' => '',
 'feed_type' => '',
		'feed_image' => '',
 'exclude' => '',
 'exclude_tree' => '',
 'current_category' => 0,
		'hierarchical' => true,
 'title_li' => tlocal::$data['default']['categories'],
		'echo' => 1,
 'depth' => 0
	);

	$r = wp_parse_args( $args, $defaults );

	extract( $r );

	$output = '';
	if ( $title_li && 'list' == $style ) $output = '<li class="categories">' . $r['title_li'] . '<ul>';

	if ( empty( $categories ) ) {
		if ( 'list' == $style )
			$output .= '<li>No categories</li>';
		else
			$output .= "No categories";
	} else {
		if( !empty( $show_option_all ) )
			if ( 'list' == $style )
				$output .= '<li><a href="' .  get_bloginfo( 'url' )  . '">' . $show_option_all . '</a></li>';
			else
				$output .= '<a href="' .  get_bloginfo( 'url' )  . '">' . $show_option_all . '</a>';

		//$output .= walk_category_tree( $categories, $depth, $r );

$sortnames = array(
'ID' => 'id',
'name' => 'title',
'slug' => 'title',
'count' => 'count',
'term_group' => 'title'
);
$limit = isset($number) ? $number : 0;
$categories = tcategories::instance();
$items = $categories->getsorted($sortnames[$r['orderby']], $limit);
$theme = ttheme::instance();
    $tml = '<li><a href="$options.url$url" title="$title">$icon$title</a>$count</li>';
    $args = targs::instance();
    $args->count = '';
foreach ($items as $id) {
$item = $categories->getitem($id);
      $args->add($item);
      $args->icon = litepublisher::$options->icondisabled ? '' : $this->geticonlink($id);
      if ($show_count) $args->count = sprintf(' (%d)', $item['itemscount']);
      $output .= $theme->parsearg($tml,$args);
}

	}

	if ( $title_li && 'list' == $style  $output .= '</ul></li>';

	if ( $echo )
		echo $output;
	else
		return $output;
}

function is_category ($category = '') {
$template = ttemplate::instance();
return $template->context instanceof tcommontags;
}
function wp_get_archives($args = '') {
	$defaults = array(
		'type' => 'monthly', 'limit' => '',
		'format' => 'html', 'before' => '',
		'after' => '', 'show_post_count' => false,
		'echo' => 1
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );
$output = '';
$arch = tarchives::instance();

	if ('link' == $format)
		$tml = "\t<link rel='archives' title='\$title' href='\$options.url\$url' />\n";
	elseif ('option' == $format)
		$tml = "\t<option value='\$options.url\$url'>$before \$title$after</option>\n";
	elseif ('html' == $format)
		$tml = "\t<li>$before<a href='\$options.url\$url' title='\$title'>\$title</a>\$count$after</li>\n";
	else // custom
		$tml = "\t$before<a href='\$options.url\$url' title='\$title'>\$title</a>\$count$after\n";

    $theme = ttheme::instance();
    $args = targs::instance();
    foreach ($arch->items as $date => $item) {
      $args->add($item);
      $args->icon = '';
    $args->count = $show_post_count ? "({$item['count']})" : '';
      $output .= $theme->parsearg($tml, $args);
    }

	if ( $echo )
		echo $output;
	else
		return $output;
}


function wp_list_bookmarks($args = '') {
	$defaults = array(
		'orderby' => 'name', 'order' => 'ASC',
		'limit' => -1, 'category' => '', 'exclude_category' => '',
		'category_name' => '', 'hide_invisible' => 1,
		'show_updated' => 0, 'echo' => 1,
		'categorize' => 1, 'title_li' => __('Bookmarks'),
		'title_before' => '<h2>', 'title_after' => '</h2>',
		'category_orderby' => 'name', 'category_order' => 'ASC',
		'class' => 'linkcat', 'category_before' => '<li id="%id" class="%class">',
		'category_after' => '</li>',
		'before' => '<li>', 'after' => '</li>', 'between' => "\n"
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );
	$output = '';
$links = tlinkswidget::instance();
if (count($links->items) > 0) {
			if ( !empty( $title_li ) ){
				$output .= str_replace(array('%id', '%class'), array("linkcat-1", $class), $category_before);
				$output .= "$title_before$title_li$title_after\n\t<ul class='xoxo blogroll'>\n";
}
		$tml = "$before<a href=\"%1\$s\" title=\"%2\$s\">%3\$s</a>$after\n";
    $theme = ttheme::instance();
    $args = targs::instance();
    foreach ($links->items as $id => $item) {
      $url =  $item['url'];
      if ($links->redir && !strbegin($url, litepublisher::$options->url)) {
        $url = litepublisher::$options->url . $links->redirlink . litepublisher::$options->q . "id=$id";
      }
            $output .=   sprintf($tml, $url, $item['title'], $item['text']);
    }
    
			if ( !empty( $title_li ) ){
				$output .= "\n\t</ul>\n$category_after\n";
}

		}

	if ( !$echo )
		return $output;
	echo $output;
}


//posts

class wordpress {
public static $current_post = -1;
public static $post_count = -1;
public static $posts;
public static $post;
[public static $pages;

public static function have_posts() {
		if (self::$post_count == -1) {
$context = ttemplate::instance()->context;
$items = array();
if ($context instanceof thomepage){
$items = $context->getitems();
} elseif ($context instanceof tcommontags) {
$items = $context->itemsposts->getposts($context->id);
    $posts = litepublisher::$classes->posts;
    $items = $posts->stripdrafts($items);
    $items = $posts->sortbyposted($items);
} elseif ($context instanceof tarchives) {
$items = $context->getposts();
}

    $perpage = litepublisher::$options->perpage;
self::$pages = ceil(count($items)/ $perpage);
    self::$posts = array_slice($items, (litepublisher::$urlmap->page - 1) * $perpage, $perpage);
self::$post_count = count(self::$posts);
}

return self::$current_post + 1 < self::$post_count;
	}

public static 	function the_post() {
		if ( self::$current_post == -1 ) // loop has just started
		self::$post = self::next_post();
}
	}

	function next_post() {
		self::$current_post++;
		self::$post = tpost::instance(self::$posts[self::$current_post]);
		return self::$post;
	}

}//class

function have_posts() {
return wordpress::have_posts();
}

function the_post() {
return wordpress::the_post();
}

function the_ID() {
	echo wordpress::$post->id;
}

function the_permalink() {
	echo wordpress::$post->link;
}

function the_title_attribute( $args = '' ) {
echo wordpress::$post->title;
}
function the_title($before = '', $after = '', $echo = true) {
echo wordpress::$post->title;
}

function the_time( $d = '' ) {
	echo get_the_time( $d );
}

function get_the_time( $d = '', $post = null ) {
	if ( '' == $d ) return wordpress::$post->localdate;
return tlocal::translate(date($d, wordpress::$post->date), 'datetime');
}

function the_author() {
return 'admin';
}

function the_content($more_link_text = null, $stripteaser = 0) {
	$content = get_the_content($more_link_text, $stripteaser);
	$content = str_replace(']]>', ']]&gt;', $content);
	echo $content;
}

function get_the_content($more_link_text = null, $stripteaser = 0) {
return wordpress::$post->excerpt;
}

function the_tags( $before = null, $sep = ', ', $after = '' ) {
	if ( null === $before )
		$before = tlocal::$data['default']['tags'];
	echo get_the_tag_list($before, $sep, $after);
}

function get_the_tag_list( $before = '', $sep = '', $after = '' ) {
    $tags = ttags::instance();
$links = $tags->getlinks(wordpress::$post->tags);
if (count($links) == 0) return false;
	return $before . join( $sep, $links) . $after;
}

function the_category( $separator = '', $parents='', $post_id = false ) {
	echo get_the_category_list( $separator, $parents, $post_id );
}

function get_the_category_list( $separator = '', $parents='', $post_id = false ) {
$post = $post_id ? tpost::instance($post_id) : wordpress::$post;
if (count($post->categories) == 0) return 'Uncategorized';

	$rel = 'rel="category"';
	$thelist = '';
$cats = tcategories::instance();
$cats->loaditems($post->categories);
$links = array();
		foreach ( $post->categories as $id) {
$item = $cats->getitem($id);
					$links[] = '<a href="' . litepublisher::$options->url . $item['url'] . '" title="' . esc_attr( sprintf( "View all posts in %s", $item['title']) ) . '" ' . $rel . '>' . $item['title'] .'</a>';
}

	if ( '' == $separator ) {
		$thelist .= '<ul class="post-categories">'  . "\n\t<li>";
$thelist .= implode("</li>\n\t<li>", $links);
		$thelist .= '</li></ul>';
	} else {
$thelist .= implode($separator, $links);
	}
	return $thelist;
}

//empty function
function edit_post_link() {}
function get_search_form();

function next_posts_link( $label = 'Next Page &raquo;', $max_page = 0 ) {
	echo get_next_posts_link( $label, $max_page );
}

function get_next_posts_link( $label = 'Next Page &raquo;', $max_page = 0 ) {
	if ( !$max_page ) {
		$max_page = wordpress::$pages;
	}

	if ( !$paged )
		$paged = litepublisher::$urlmap->page;

	$nextpage = intval($paged) + 1;
	if ( ( empty($paged) || $nextpage <= $max_page) ) {
		return '<a href="' . litepublisher::$options->url . rtrim('/', litepublisher::$url) . "/page/$nextpage/"  . "\">". preg_replace('/&([^#])(?![a-z]{1,8};)/', '&#038;$1', $label) .'</a>';
	}
}

function previous_posts_link( $label = '&laquo; Previous Page' ) {
	echo get_previous_posts_link( $label );
}

function get_previous_posts_link( $label = '&laquo; Previous Page' ) {
$page = litepublisher::$urlmap->page;
	if ( $page > 1 ) {
$url = litepublisher::$options->url . litepublisher::$urlmap->itemrequested['url'];
if ($page > 2)  $url = ltrim('/', $url) . '/page/' . --$page . '/';
		return '<a href="' . $url .
 . "\">". preg_replace( '/&([^#])(?![a-z]{1,8};)/', '&#038;$1', $label ) .'</a>';
	}
}
