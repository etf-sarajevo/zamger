<?php

// LIB/OAUTH2_CLIENT - helper functions for authentication using OAuth2 protocol

// TODO: Currently this code uses Stevenmaguire\Keycloak.
// Apparently we can switch to phpleague/oauth2 with almost no change, but this should be further verified


class OAuth2Helper
{
	private static $provider = null;
	
	private static function getProvider() {
		global $conf_site_url, $conf_keycloak_url, $conf_keycloak_realm, $conf_keycloak_client_id, $conf_keycloak_client_secret, $conf_script_path;
		
		require_once "$conf_script_path/vendor/autoload.php"; // keycloak
		
		if (self::$provider === null)
			self::$provider = new Stevenmaguire\OAuth2\Client\Provider\Keycloak([
				'authServerUrl' => $conf_keycloak_url,
				'realm' => $conf_keycloak_realm,
				'clientId' => $conf_keycloak_client_id,
				'clientSecret' => $conf_keycloak_client_secret,
				'redirectUri' => $conf_site_url . "/index.php",
				'encryptionAlgorithm' => null,
				'encryptionKey' => null,
				'encryptionKeyPath' => null
			]);
		return self::$provider;
	}
	
	// Redirect to login page specified by OAuth2
	public static function loginScreen() {
		global $conf_site_url;
		
		// After successful login, user should be redirected to the url where they were before
		// We will use some logic to generate exactly such url and put it into $_SESSION
		// However gen_uri() for some reason doesn't do a good job here
		// Also we won't use 'redirectUri' to avoid nasty redirect loops (perhaps this is fixed now?)
		// TODO fix gen_uri?
		$url = $conf_site_url . "/index.php";
		$forbidden_keys = ["state", "session_state", "code"];
		foreach ($_GET as $key => $value) {
			if (!in_array($key, $forbidden_keys)) {
				if ($url == $conf_site_url . "/index.php")
					$url .= "?" . urlencode($key) . "=" . urlencode($value);
				else
					$url .= "&" . urlencode($key) . "=" . urlencode($value);
			}
			if ($key == "sta" && $value == "logout") {
				// If we are here, user clicked the logout button but his session is already expired
				// In that case send him to login screen, then to start page
				$url = $conf_site_url . "/index.php";
				break;
			}
		}
		$_SESSION['come_back_to'] = $url;
		

		
		// Obtain authentication URL and state cookie
		$authUrl = self::getProvider()->getAuthorizationUrl();
		$_SESSION['oauth2state'] = self::getProvider()->getState();
		
		// We want the next call to check_cookie to verify $_GET['code'] and not use the old ['keycloak_code']
		unset($_SESSION['keycloak_code']);
		header('Location: ' . $authUrl);
	}
	
	
	// Helper function to generate URL at OAuth2 server that facilitates redirect
	public static function logoutUrl() {
		global $conf_keycloak_url, $conf_keycloak_realm, $conf_site_url;
		$logout_url = $conf_keycloak_url . "/realms/$conf_keycloak_realm/protocol/openid-connect/logout?redirect_uri=" . urlencode($conf_site_url . '/index.php');
		return $logout_url;
	}
	
	
	// Verify if OAuth2 session is valid
	public static function checkSession() {
		global $conf_site_url, $conf_files_path, $uspjeh;
		
		$login = "";
		
		// There is an active session, check that it's valid and not expired
		// Ignore non-oauth sessions
		if (isset($_SESSION['login']) && isset($_SESSION['keycloak_code'])) {
			$login = db_escape($_SESSION['login']);
			self::refreshToken($login);
		}
		
		// OAuth2 server returned an error, show it
		else if (isset($_GET['error']) && isset($_GET['state']) && $_GET['state'] == $_SESSION['ouath2state']) {
			niceerror("KeyCloak greška: " . $_GET['error'] . ": " . $_GET['error_description']);
			// $login will remain empty, meaining that login has failed
		}
		
		// First access to Zamger, this is a redirect from login page
		else if (isset($_GET['code'])) {
			// Check given state against previously stored one to mitigate CSRF attack
			if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
				// This sometimes happens when user goes back to URL with outdated 'state'
				// Going back to site URL will either work or redirect back to keycloak
				header('Location: ' . $conf_site_url);
				//$uspjeh = 2;
				exit(0);
			}
			$_SESSION['keycloak_code'] = $code = $_GET['code'];
			
			// Try to get an access token (using the authorization coe grant)
			try {
				$token = self::getProvider()->getAccessToken('authorization_code', [
					'code' => $code
				]);
				if ($token->hasExpired()) {
					// This should never happen since we just arrived from OAuth server!
					niceerror('Token je već istekao! Kontaktirajte administratora (2)');
					//$uspjeh = 2
					exit(0);
				}
				
				// We will receive $login from OAuth2 server
				$user = self::getProvider()->getResourceOwner($token);
				$login = $user->toArray()['preferred_username'];
				$token_file = $conf_files_path . "/keycloak_token/$login";
				
				// Store token into file
				if (!file_exists($conf_files_path . "/keycloak_token"))
					mkdir($conf_files_path . "/keycloak_token");
				
				file_put_contents($token_file, serialize($token));
				$_SESSION['login'] = $login;
			} catch (Exception $e) {
				if ($e->getMessage() == "invalid_grant: Code not valid") {
					// Authorization code is invalid, probably because the session has expired
					// We will redirect to logout url so that user can log back in
					self::logOut();
				}
				
				niceerror('Autentikacija na keycloak neuspjela, kontaktirajte administratora: ' . $e->getMessage());
				$login = '';
				// Resume so that we can accept a possible Zamger session
			}
		}
		return $login;
	}
	
	// Refresh expired OAuth2 access token
	public static function refreshToken($username) {
		global $conf_files_path;
		
		$token_file = $conf_files_path . "/keycloak_token/$username";
		if (!file_exists($token_file)) {
			// We somehow lost the token file so we must logout
			self::logOut();
		}
		$token = unserialize(file_get_contents($token_file));
		
		if ($token->hasExpired()) {
			// Token is expired, get new token from server and write to file
			try {
				$newAccessToken = self::getProvider()->getAccessToken('refresh_token', [
					'refresh_token' => $token->getRefreshToken()
				]);
				
				$token_file = $conf_files_path . "/keycloak_token/$username";
				file_put_contents($token_file, serialize($newAccessToken));
				
			} catch (Exception $e) {
				// Session has expired, redirect to logout url
				self::logOut();
			}
		}
	}
	
	// Quickly get KeyCloak token string
	public static function getToken($username) {
		global $conf_files_path;
		$token_file = $conf_files_path . "/keycloak_token/$username";
		if (!file_exists($token_file)) return null;
		$token = unserialize(file_get_contents($token_file));
		return $token->getToken();
	}
	
	// Force logout
	public static function logOut() {
		$_SESSION = array();
		session_destroy();
		header('Location: ' . self::logoutUrl());
		// After redirect there is no point in continuing
		exit(0);
	}
}