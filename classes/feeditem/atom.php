<?php
class FeedItem_Atom extends FeedItem_Common {

	function get_id() {
		if( isset($this->id ) ){
			return $this->id;
		}

		$id = $this->elem->getElementsByTagName("id")->item(0);

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

		$updated = $this->elem->getElementsByTagName("updated")->item(0);

		if ($updated) {
			return $this->set_date(strtotime($updated->nodeValue));
		}

		$published = $this->elem->getElementsByTagName("published")->item(0);

		if ($published) {
			return $this->set_date(strtotime($published->nodeValue));
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

		$links = $this->elem->getElementsByTagName("link");

		foreach ($links as $link) {
			if ($link && $link->hasAttribute("href") &&
				(!$link->hasAttribute("rel")
					|| $link->getAttribute("rel") == "alternate"
					|| $link->getAttribute("rel") == "standout")) {
				$base = $this->xpath->evaluate("string(ancestor-or-self::*[@xml:base][1]/@xml:base)", $link);

				if ($base)
					return $this->set_link(rewrite_relative_url($base, trim($link->getAttribute("href"))));
				else
					return $this->set_link(trim($link->getAttribute("href")));

			}
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

		$content = $this->elem->getElementsByTagName("content")->item(0);

		if ($content) {
			if ($content->hasAttribute('type')) {
				if ($content->getAttribute('type') == 'xhtml') {
					for ($i = 0; $i < $content->childNodes->length; $i++) {
						$child = $content->childNodes->item($i);

						if ($child->hasChildNodes()) {
							return $this->set_content($this->doc->saveXML($child));
						}
					}
				}
			}

			return $this->set_content($content->nodeValue);
		}
	}

	function get_description() {
		if( isset($this->description ) ){
			return $this->description;
		}

		$content = $this->elem->getElementsByTagName("summary")->item(0);

		if ($content) {
			if ($content->hasAttribute('type')) {
				if ($content->getAttribute('type') == 'xhtml') {
					for ($i = 0; $i < $content->childNodes->length; $i++) {
						$child = $content->childNodes->item($i);

						if ($child->hasChildNodes()) {
							return $this->set_description($this->doc->saveXML($child));
						}
					}
				}
			}

			return $this->set_description($content->nodeValue);
		}

	}

	function get_categories() {
		if( isset($this->categories ) ){
			return $this->categories;
		}

		$categories = $this->elem->getElementsByTagName("category");
		$cats = array();

		foreach ($categories as $cat) {
			if ($cat->hasAttribute("term"))
				array_push($cats, trim($cat->getAttribute("term")));
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

		$links = $this->elem->getElementsByTagName("link");

		$encs = array();

		foreach ($links as $link) {
			if ($link && $link->hasAttribute("href") && $link->hasAttribute("rel")) {
				if ($link->getAttribute("rel") == "enclosure") {
					$enc = new FeedEnclosure();

					$enc->type = $link->getAttribute("type");
					$enc->link = $link->getAttribute("href");
					$enc->length = $link->getAttribute("length");

					array_push($encs, $enc);
				}
			}
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
