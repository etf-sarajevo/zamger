<?php

// Modul: lms/portal
// Klasa: PortalFeedRenderer
// Opis: Wrapper za rendering RSSa korištenjem biblioteke lib/rss2html

require_once(Config::$backend_path."core/DB.php");
require_once(Config::$backend_path."core/Person.php");

// FIXME: u bazi ne postoji tabela portal
// polje portal tabele rss se zove "projekat"


class PortalFeedRenderer {
	public $url;
	public $template, $cacheTimeoutSeconds, $FeedMaxItems, $LongDateFormat, $ShortDateFormat, $LongTimeFormat, $ShortTimeFormat, $stripTagsFromFeed, $limitItemTitleLength, $limitItemDescriptionLength;

	public function defaults() {
		$this->FeedMaxItems = 5;
		$this->template = "lib/rss_sablon.html";
		$this->LongDateFormat = "F jS, Y";
		$this->ShortDateFormat = "m/d/Y";
		$this->LongTimeFormat = "H:i:s T O";
		$this->ShortTimeFormat = "h:i A";
		$this->stripTagsFromFeed = false;
		$this->limitItemTitleLength = 0;
		$this->limitItemDescriptionLength = 0;
	}

	public function render() {
		// Read cache file
		$cachefile = Config::$backend_file_path . "/cache";
		if (!file_exists($cachefile)) mkdir ($cachefile,0777, true);

		$cachefile .= "/rss";
		if (!file_exists($cachefile)) mkdir ($cachefile,0777, true);

		$cachefile .= hash("md5", $this->url . $this->template) . ".html";

		if (file_exists($cachefile) && (time() - filemtime($cachefile) < $this->cacheTimeoutSeconds ))
			return file_get_contents($cachefile);

		// Moving variables to global namespace for rss2html script
		global $XMLfilename; $GLOBALS["XMLFILE"] = $XMLfilename = $this->url;
		global $FeedMaxItems; $FeedMaxItems = $this->FeedMaxItems;
		global $TEMPLATEfilename; $TEMPLATEfilename = $this->template;
		global $LongDateFormat; $LongDateFormat = $this->LongDateFormat;
		global $ShortDateFormat; $ShortDateFormat = $this->ShortDateFormat;
		global $LongTimeFormat; $LongTimeFormat = $this->LongTimeFormat;
		global $ShortTimeFormat; $ShortTimeFormat = $this->ShortTimeFormat;
		global $stripTagsFromFeed; $stripTagsFromFeed = $this->stripTagsFromFeed;
		global $limitItemTitleLength; $limitItemTitleLength = $this->limitItemTitleLength;
		global $limitItemDescriptionLength; $limitItemDescriptionLength = $this->limitItemDescriptionLength;

		// Get output from rss2html into buffer
		ob_start();
		include(Config::$backend_path . "lib/rss2html/rss2html.php");
		$output = ob_get_contents();
		ob_end_clean();

		// Write to cache file
		$fp = fopen($cachefile, 'w');
		fwrite($fp, $output);
		fclose($fp);
	
		return $output;
	}
}

?>