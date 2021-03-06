<?php
class FeedItem_RSS extends FeedItem_Common {
	function get_id() {
		if( isset($this->id ) ){
			return $this->id;
		}

		$id = $this->elem->getElementsByTagName("guid")->item(0);

		if ($id) {
			return $this->set_id($id->nodeValue);
		} else {
			return $this->set_id($this->get_link());
		}
	}

	function get_date() {
		if( isset($this->date ) ){
			return $this->date;
		}

		$pubDate = $this->elem->getElementsByTagName("pubDate")->item(0);

		if ($pubDate) {
			return $this->set_date(strtotime($pubDate->nodeValue));
		}

		$date = $this->xpath->query("dc:date", $this->elem)->item(0);

		if ($date) {
			return $this->set_date(strtotime($date->nodeValue));
		}
	}

	function get_link() {
		if( isset($this->link ) ){
			return $this->link;
		}

		$links = $this->xpath->query("atom:link", $this->elem);

		foreach ($links as $link) {
			if ($link && $link->hasAttribute("href") &&
				(!$link->hasAttribute("rel")
					|| $link->getAttribute("rel") == "alternate"
					|| $link->getAttribute("rel") == "standout")) {

				return $this->set_link(trim($link->getAttribute("href")));
			}
		}

		$link = $this->elem->getElementsByTagName("guid")->item(0);

		if ($link && $link->hasAttributes() && $link->getAttribute("isPermaLink") == "true") {
			return $this->set_link(trim($link->nodeValue));
		}

		$link = $this->elem->getElementsByTagName("link")->item(0);

		if ($link) {
			return $this->set_link(trim($link->nodeValue));
		}
	}

	function get_title() {
		if( isset($this->title ) ){
			return $this->title;
		}

		$title = $this->elem->getElementsByTagName("title")->item(0);

		if ($title) {
			return $this->set_title(trim($title->nodeValue));
		}
	}

	function get_content() {
		if( isset($this->content ) ){
			return $this->content;
		}

		$contentA = $this->xpath->query("content:encoded", $this->elem)->item(0);
		$contentB = $this->elem->getElementsByTagName("description")->item(0);

		if ($contentA && !$contentB) {
			return $this->set_content($contentA->nodeValue);
		}


		if ($contentB && !$contentA) {
			return $this->set_content($contentB->nodeValue);
		}

		if ($contentA && $contentB) {
			return $this->set_content(mb_strlen($contentA->nodeValue) > mb_strlen($contentB->nodeValue) ?
				$contentA->nodeValue : $contentB->nodeValue);
		}
	}

	function get_description() {
		if( isset($this->description ) ){
			return $this->description;
		}

		$summary = $this->elem->getElementsByTagName("description")->item(0);

		if ($summary) {
			return $this->set_description($summary->nodeValue);
		}
	}

	function get_categories() {
		if( isset($this->categories ) ){
			return $this->categories;
		}

		$categories = $this->elem->getElementsByTagName("category");
		$cats = array();

		foreach ($categories as $cat) {
			array_push($cats, trim($cat->nodeValue));
		}

		$categories = $this->xpath->query("dc:subject", $this->elem);

		foreach ($categories as $cat) {
			array_push($cats, trim($cat->nodeValue));
		}

		return $this->set_categories($cats);
	}

	function get_enclosures() {
		if( isset($this->enclosures ) ){
			return $this->enclosures;
		}

		$enclosures = $this->elem->getElementsByTagName("enclosure");

		$encs = array();

		foreach ($enclosures as $enclosure) {
			$enc = new FeedEnclosure();

			$enc->type = $enclosure->getAttribute("type");
			$enc->link = $enclosure->getAttribute("url");
			$enc->length = $enclosure->getAttribute("length");

			array_push($encs, $enc);
		}

		$enclosures = $this->xpath->query("media:content", $this->elem);

		foreach ($enclosures as $enclosure) {
			$enc = new FeedEnclosure();

			$enc->type = $enclosure->getAttribute("type");
			$enc->link = $enclosure->getAttribute("url");
			$enc->length = $enclosure->getAttribute("length");

			$desc = $this->xpath->query("media:description", $enclosure)->item(0);
			if ($desc) $enc->title = strip_tags($desc->nodeValue);

			array_push($encs, $enc);
		}


		$enclosures = $this->xpath->query("media:group", $this->elem);

		foreach ($enclosures as $enclosure) {
			$enc = new FeedEnclosure();

			$content = $this->xpath->query("media:content", $enclosure)->item(0);

			if ($content) {
				$enc->type = $content->getAttribute("type");
				$enc->link = $content->getAttribute("url");
				$enc->length = $content->getAttribute("length");

				$desc = $this->xpath->query("media:description", $content)->item(0);
				if ($desc) {
					$enc->title = strip_tags($desc->nodeValue);
				} else {
					$desc = $this->xpath->query("media:description", $enclosure)->item(0);
					if ($desc) $enc->title = strip_tags($desc->nodeValue);
				}

				array_push($encs, $enc);
			}
		}

		$enclosures = $this->xpath->query("media:thumbnail", $this->elem);

		foreach ($enclosures as $enclosure) {
			$enc = new FeedEnclosure();

			$enc->type = "image/generic";
			$enc->link = $enclosure->getAttribute("url");

			array_push($encs, $enc);
		}

		return $this->set_enclosures($encs);
	}

}
?>
