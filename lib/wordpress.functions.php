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


?>