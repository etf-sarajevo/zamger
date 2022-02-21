<?php


// GET_TOKEN.PHP - return updated bearer token string

require_once("lib/config.php");
require_once("lib/dblayer.php");
require_once("lib/zamger.php");
require_once("lib/session.php"); // check_cookie
require_once("lib/hack.php");
require_once("lib/oauth2_client.php");

db_connect($conf_dbhost,$conf_dbuser,$conf_dbpass,$conf_dbdb);
check_cookie();
if ($userid != 0) {
	print "Token: " . OAuth2Helper::getToken($login);
} else {
	header("HTTP/1.0 401 Unauthorized");
}