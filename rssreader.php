<?php
/*

File: rssreader.php
Author: Gary White
Last modified: May 2, 2005

June 21, 2005 - Added some minor error handling updates

May 2, 2005 - Modified again to drop using cURL library and
instead use direct socket i/o. This eliminates any external 
server configuration dependencies.

Apr 21, 2005 - Modified to use cURL library instead of 
file_get_contents function. This class now has a dependency
in that the curl library must be enabled on the server.

Copyright (C) 2005, Gary White

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details at:
http://www.gnu.org/copyleft/gpl.html

Typical usage:

	include("rssreader.php");
	$url="http://www.wired.com/news/feeds/rss2/0,2610,,00.xml";
	$rss=new rssFeed($url);
	if($rss->error){
		print "<h1>Error:</h1>\n<p><strong>$rss->error</strong></p>";
	}else{
		$rss->parse();
		$rss->showHeading("h1");
		if($rss->link){
			print "<p>Provided courtesy of:<br>\n";
			print "<a href=\"$rss->link\">$rss->link</a>\n";
		}
		$rss->image->show("middle");
		$rss->showDescription();
		$rss->showStories();
	}


rssFeed: object
	Purpose: Creates an rssFeed class that allows easy display of RSS data.
	
	Public Methods:
		parse           Parses the XML and populates properties with the resulting data
		showHeading     Displays the title
		showImage       Displays the image associated with the feed
		showLink        Displays a link to the RSS provider
		showDescription Displays the feed description
		showStories     Displays the feed items
	
	Public Properties:
		title:          The title of the RSS feed
		copyright:      The copyright information included in the RSS feed
		description:    The description included in the RSS feed
		image           An instance of an rssImage object (see below)
		stories         An array of newsStory objects (see below)
		url             The URI of the RSS feed
		xml             The raw XML data obtained from the feed
		error           A text description of the most recent error encountered
		maxstories      The maximum number of stories to show (zero = no limit)

rssImage: object
	Purpose: Creates a class to store information about an image for an RSS feed

	Public Methods:
		show            Displays the image

	Public Properties:
		title           The image title, used for ALT attribute
		url             The URL of the image
		link            The URL that the image should link to
		width           The image width in pixels
		height          The image height in pixels

newsStory: object
	Purpose: Creates a class to store an news story in an RSS feed

	Public Methods:
		show            Displays the story

	Public Properties:
		show            Displays the story
		title           The headline associated with the story
		link            The link to the full story
		description     A short description, or teaser, of the story
		pubdate         Date/Time the story was published
	
*/

//classes follow

// Generic container for the complete RSS feed
class rssFeed{
	var $title="";
	var $copyright="";
	var $description="";
	var $image;
	var $stories=array();
	var $url="";
	var $xml="";
	var $link="";
	var $error="";
	var $maxstories=0;
	
	// public methods
	function parse(){
		$parser=xml_parser_create();
		xml_set_element_handler($parser, "startElement", "endElement");
		xml_set_character_data_handler($parser, "characterData");
		xml_parse($parser, $this->xml, true)
			or die(sprintf("XML error: %s at line %d", 
				xml_error_string(xml_get_error_code($parser)),
				xml_get_current_line_number($parser)));
		xml_parser_free($parser);
	}

	function showHeading($tag=""){
		$tag=$tag?$tag:"h1";
		if($this->title)
			print "<$tag>$this->title</$tag>\n";
	}

	function showImage($align=""){
		$this->image->show($align);
	}

	function showLink(){
		if($this->link)
			print "<a href=\"$this->link\">$this->link</a>\n";
	}
	function showDescription(){
		if($this->description)
			print "<p>$this->description</p>\n";
	}

	function showStories(){
		echo "<dl>\n";
		$n=0;
		foreach($this->stories as $story){
			$n++;
			if ($this->maxstories && $n>$this->maxstories)
				break;
			$story->show();
		}
		echo "</dl>\n";
	}
	
	// Methods used internally
	// Constructor: Expects one string parameter that is the URI of the RSS feed
	function rssFeed($uri=''){
		$this->image=new rssImage();
		if($uri){
			$this->url=$uri;
			$this->getFeed();
		} else {
			$this->error="No URL for RSS feed";
		}
	}

	// Retrieves the XML from the RSS supplier
	function getFeed(){
		// if we have a URL
		if ($this->url){
			if (extension_loaded('curl')) {
				$this->xml=$this->getRemoteFile($this->url);
			}
		}
	}

	function getRemoteFile($url){
		$s=new gwSocket();
		if($s->getUrl($url)){
			if(is_array($s->headers)){
				$h=array_change_key_case($s->headers, CASE_LOWER);
				if($s->error) // failed to connect with host
					$buffer=$this->errorReturn($s->error);
				elseif(preg_match("/404/",$h['status'])) // page not found
					$buffer=$this->errorReturn("Page Not Found");
				elseif(preg_match("/xml/i",$h['content-type'])) // got XML back
					$buffer=$s->page;
				else // got a page, but wrong content type
					$buffer=$this->errorReturn("The server did not return XML. The content type returned was ".$h['content-type']);
			} else {
				$buffer=$this->errorReturn("An unknown error occurred.");
			}
		}else{
			$buffer=$this->errorReturn("An unknown error occurred.");
		}
		return $buffer;
	}

	function errorReturn($error){
		$retVal="<?xml version=\"1.0\" ?>\n".
			"<rss version=\"2.0\">\n".
			"\t<channel>\n".
			"\t\t<title>Failed to Get RSS Data</title>\n".
			"\t\t<description>An error was ecnountered attempting to get the RSS data: $error</description>\n".
			"\t\t<pubdate>".date("D, d F Y H:i:s T")."</pubdate>\n".
			"\t\t<lastbuilddate>".date("D, d F Y H:i:s T")."</lastbuilddate>\n".
			"\t</channel>\n".
			"</rss>\n";
		return $retVal;
	}

	function addStory($o){
		if(is_object($o))
			$this->stories[]=$o;
		else
			$this->error="Type mismatach: expected object";
	}

}

class rssImage{
	var $title="";
	var $url="";
	var $link="";
	var $width=0;
	var $height=0;
	
	function show($align=""){
		if($this->url){
			if($this->link)
				print "<a href=\"$this->link\">";
			print "<img src=\"$this->url\" style=\"border:none;\"";
			if($this->title)
				print " alt=\"$this->title\"";
			if($this->width)
				print " width=\"$this->width\" height=\"$this->height\"";
			if($align)
				print " align=\"$align\"";
			print ">";	
			if($this->link)
				print "</a>";
		}
	}
}

class newsStory{
	var $title="";
	var $link="";
	var $description="";
	var $pubdate="";
	
	function show(){
		if($this->title){
			if($this->link){
				echo "<dt><a href=\"$this->link\">$this->title</a></dt>\n";
			}elseif($this->title){
				echo "<dt>$this->title</a></dt>\n";
			}
			echo "<dd>";
			if($this->pubdate)
				echo "<i>$this->pubdate</i> - ";
			if($this->description)
				echo "$this->description";
			echo "</dd>\n";
		}
	}
}


class gwSocket{
	var $Name="gwSocket";
	var $Version="0.1";
	var $userAgent="Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)";
	var $headers;
	var $page="";
	var $result="";
	var $redirects=0;
	var $maxRedirects=3;
	var $error="";

	function getUrl( $url ) {
		$retVal="";
		$url_parsed = parse_url($url);
		$scheme = $url_parsed["scheme"];
		$host = $url_parsed["host"];
		$port = $url_parsed["port"]?$url_parsed["port"]:"80";
		$user = $url_parsed["user"];
		$pass = $url_parsed["pass"];
		$path = $url_parsed["path"]?$url_parsed["path"]:"/";
		$query = $url_parsed["query"];
		$anchor = $url_parsed["fragment"];

		if (!empty($host)){

			// attempt to open the socket
			if($fp = fsockopen($host, $port, $errno, $errstr, 2)){

				$path .= $query?"?$query":"";
				$path .= $anchor?"$anchor":"";

				// this is the request we send to the host
				$out = "GET $path ".
					"HTTP/1.0\r\n".
					"Host: $host\r\n".
					"Connection: Close\r\n".
					"User-Agent: $this->userAgent\r\n";
				if($user)
					$out .= "Authorization: Basic ".
						base64_encode("$user:$pass")."\r\n";
				$out .= "\r\n";

				fputs($fp, $out);
				while (!feof($fp)) {
					$retVal.=fgets($fp, 128);
				}
				fclose($fp);
			} else {
				$this->error="Failed to make connection to host.";//$errstr;
			}
			$this->result=$retVal;
			$this->headers=$this->parseHeaders(trim(substr($retVal,0,strpos($retVal,"\r\n\r\n"))));
			$this->page=trim(stristr($retVal,"\r\n\r\n"))."\n";
			if(isset($this->headers['Location'])){
				$this->redirects++;
				if($this->redirects<$this->maxRedirects){
					$location=$this->headers['Location'];
					$this->headers=array();
					$this->result="";
					$this->page="";
					$this->getUrl($location);
				}
			}
		}
		return (!$retVal="");
	}
	
	function parseHeaders($s){
		$h=preg_split("/[\r\n]/",$s);
		foreach($h as $i){
			$i=trim($i);
			if(strstr($i,":")){
				list($k,$v)=explode(":",$i);
				$hdr[$k]=substr(stristr($i,":"),2);
			}else{
				if(strlen($i)>3)
					$hdr[]=$i;
			}
		}
		if(isset($hdr[0])){
			$hdr['Status']=$hdr[0];
			unset($hdr[0]);
		}
		return $hdr;
	}

}

/*
	end of classes - global functions follow
*/

function startElement($parser, $name, $attrs) {
	global $insideitem, $tag, $isimage;
	$tag = $name;
	if($name=="IMAGE")
		$isimage=true;
	if ($name == "ITEM") {
		$insideitem = true;
	}
}

function endElement($parser, $name) {
	global $insideitem, $title, $description, $link, $pubdate, $stories, $rss, $globaldata, $isimage;
	$globaldata=trim($globaldata);
	// if we're finishing a news item
	if ($name == "ITEM") {
		// create a new news story object
		$story=new newsStory();
		// assign the title, link, description and publication date
		$story->title=trim($title);
		$story->link=trim($link);
		$story->description=trim($description);
		$story->pubdate=trim($pubdate);
		// add it to our array of stories

        $rss->addStory($story);
		// reset our global variables
		$title = "";
		$description = "";
		$link = "";
		$pubdate = "";
		$insideitem = false;
	} else {
		switch($name){
			case "TITLE":
				if(!$isimage)
					if(!$insideitem)
						$rss->title=$globaldata;
				break;
			case "LINK":
				if(!$insideitem)
					$rss->link=$globaldata;
				break;
			case "COPYRIGHT":
				if(!$insideitem)
					$rss->copyright=$globaldata;
				break;
			case "DESCRIPTION":
				if(!$insideitem)
					$rss->description=$globaldata;
				break;
		}
	}
	if($isimage){
		switch($name){
			case "TITLE": $rss->image->title=$globaldata;break;
			case "URL": $rss->image->url=$globaldata;break;
			case "LINK": $rss->image->link=$globaldata;break;
			case "WIDTH": $rss->image->width=$globaldata;break;
			case "HEIGHT": $rss->image->height=$globaldata;break;
		}
	}
	if($name=="IMAGE")	
		$isimage=false;
	$globaldata="";
}

function characterData($parser, $data) {
	global $insideitem, $tag, $title, $description, $link, $pubdate, $globaldata;
	if ($insideitem) {
		switch ($tag) {
			case "TITLE":
				$title .= $data;
				break;
			case "DESCRIPTION":
				$description .= $data;
				break;
			case "LINK":
				$link .= $data;
				break;
			case "PUBDATE":
			case "DC:DATE":
				$pubdate .= $data;
				break;
		}
	} else {
		$globaldata.=$data;
	}
}

?>
