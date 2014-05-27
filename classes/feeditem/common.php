<?php
abstract class FeedItem_Common extends FeedItem {
	protected $elem;
	protected $xpath;
	protected $doc;

	protected $comments_url;
	protected $comments_count;
	protected $author_value;
	protected $id;
	protected $date;
	protected $link;
	protected $title;
	protected $content;
	protected $description;
	protected $categories;
	protected $enclosures;
	
	
	
	function __construct($elem, $doc, $xpath) {
		$this->elem = $elem;
		$this->xpath = $xpath;
		$this->doc = $doc;

		try {

			$source = $elem->getElementsByTagName("source")->item(0);

			// we don't need <source> element
			if ($source)
				$elem->removeChild($source);
		} catch (DOMException $e) {
			//
		}
	}
	function get_author() {
		if( isset($this->$author_value) ){
			return $this->$author_value;
		}
		$author = $this->elem->getElementsByTagName("author")->item(0);

		if ($author) {
			$name = $author->getElementsByTagName("name")->item(0);

			if ($name){
				return $this->set_author($name->nodeValue);
			}

			$email = $author->getElementsByTagName("email")->item(0);

			if ($email){
				return $this->set_author($email->nodeValue);
			}

			if ($author->nodeValue){
				return $this->set_author($author->nodeValue);
			}
		}

		$author = $this->xpath->query("dc:creator", $this->elem)->item(0);

		if ($author) {
			return $this->set_author($author->nodeValue);
		}
	}

	function get_comments_url() {
		if( isset($this->$comments_url ) ){
			return $this->$comments_url;
		}
		
		//RSS only. Use a query here to avoid namespace clashes (e.g. with slash).
		//might give a wrong result if a default namespace was declared (possible with XPath 2.0)
		$com_url = $this->xpath->query("comments", $this->elem)->item(0);

		if($com_url)
			return $this->set_comments_url($com_url->nodeValue);

		//Atom Threading Extension (RFC 4685) stuff. Could be used in RSS feeds, so it's in common.
		//'text/html' for type is too restrictive?
		$com_url = $this->xpath->query("atom:link[@rel='replies' and contains(@type,'text/html')]/@href", $this->elem)->item(0);

		if($com_url)
			return $this->set_comments_url($com_url->nodeValue);
	}

	function get_comments_count() {
		if( isset($this->$comments_count) ){
			return $this->$comments_count;
		}
		
		//also query for ATE stuff here
		$query = "slash:comments|thread:total|atom:link[@rel='replies']/@thread:count";
		$comments = $this->xpath->query($query, $this->elem)->item(0);

		if ($comments) {
			return $this->set_comments_count($comments->nodeValue);
		}
	}
	
	function set_author($author) {
		$this->$author = $author;
		return $this->$author;
		
	}
	function set_comments_url($url) {
		$this->$comments_url = $url;
		return $this->$comments_url;
	}
	function set_comments_count($count) {
		$this->$comments_count = $count;
		return $this->$comments_count;
	}
	function set_id($id) {
		$this->$id = $id;
		return $this->$id;
	}
	function set_date($date) {
		$this->$date = $date;
		return $this->$date;
	}
	function set_link($link) {
		$this->$link = $link;
		return $this->$link;
	}
	function set_title($title) {
		$this->$title = $title;
		return $this->$title;
	}
	function set_content($content) {
		$this->$content = $content;
		return $this->$content;
	}
	function set_description($description) {
		$this->$description = $description;
		return $this->$description;
	}
	function set_categories($categories) {
		$this->$categories = $categories;
		return $this->$categories;
	}
	function set_enclosures($enclosures) {
		$this->$enclosures = $enclosures;
		return $this->$enclosures;
	}

}
?>
