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

function &get_categories( $args = '' ) {
	$defaults = array( 'type' => 'category' );
	$args = wp_parse_args( $args, $defaults );

	$taxonomy = apply_filters( 'get_categories_taxonomy', 'category', $args );
	if ( 'link' == $args['type'] )
		$taxonomy = 'link_category';
	$categories = (array) get_terms( $taxonomy, $args );

	foreach ( array_keys( $categories ) as $k )
		_make_cat_compat( $categories[$k] );

	return $categories;
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

	if ( !isset( $r['pad_counts'] ) && $r['show_count'] && $r['hierarchical'] ) {
		$r['pad_counts'] = true;
	}

	if ( isset( $r['show_date'] ) ) {
		$r['include_last_update_time'] = $r['show_date'];
	}

	if ( true == $r['hierarchical'] ) {
		$r['exclude_tree'] = $r['exclude'];
		$r['exclude'] = '';
	}

	extract( $r );

	$categories = get_categories( $r );

	$output = '';
	if ( $title_li && 'list' == $style )
			$output = '<li class="categories">' . $r['title_li'] . '<ul>';

	if ( empty( $categories ) ) {
		if ( 'list' == $style )
			$output .= '<li>' . __( "No categories" ) . '</li>';
		else
			$output .= __( "No categories" );
	} else {
		global $wp_query;

		if( !empty( $show_option_all ) )
			if ( 'list' == $style )
				$output .= '<li><a href="' .  get_bloginfo( 'url' )  . '">' . $show_option_all . '</a></li>';
			else
				$output .= '<a href="' .  get_bloginfo( 'url' )  . '">' . $show_option_all . '</a>';

		if ( empty( $r['current_category'] ) && is_category() )
			$r['current_category'] = $wp_query->get_queried_object_id();

		if ( $hierarchical )
			$depth = $r['depth'];
		else
			$depth = -1; // Flat.

		$output .= walk_category_tree( $categories, $depth, $r );
	}

	if ( $title_li && 'list' == $style )
		$output .= '</ul></li>';

	$output = apply_filters( 'wp_list_categories', $output );

	if ( $echo )
		echo $output;
	else
		return $output;
}

?>