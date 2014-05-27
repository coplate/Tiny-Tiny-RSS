<?php
abstract class FeedItem {
	abstract function get_id();
	abstract function set_id($id);
	abstract function get_date();
	abstract function set_date($date);
	abstract function get_link();
	abstract function set_link($link);
	abstract function get_title();
	abstract function set_title($title);
	abstract function get_description();
	abstract function set_description($description);
	abstract function get_content();
	abstract function set_content($content);
	abstract function get_categories();
	abstract function set_categories($categories);
	abstract function get_enclosures();
	abstract function set_enclosures($enclosures);
	/* common */
	abstract function get_author();
	abstract function set_author($author);
	abstract function get_comments_url();
	abstract function set_comments_url($comments_url);
	abstract function get_comments_count();
	abstract function set_comments_count($comments_count);

}
?>
