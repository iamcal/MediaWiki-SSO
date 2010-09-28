<?php
	#
	# To use this plugin with a different SSO system, modify these two functions
	# to return the current username and email address.
	#
	# What to use for different apache auth modules:
	# For mod_authz_ldap - $_SERVER['AUTHENTICATE_MAIL']
	# For digest/basic auth - $_SERVER['PHP_AUTH_USER']

	function HTTPAuth_getUser(){
		return ucfirst($_SERVER['AUTHENTICATE_MAIL']);
	}

	function HTTPAuth_getEmail(){
		return $_SERVER['AUTHENTICATE_MAIL'];
	}

	##############################################################################################

	$wgGroupPermissions['*']['createaccount']   = false;
	$wgGroupPermissions['*']['read']            = false;
	$wgGroupPermissions['*']['edit']            = false;

	require_once('AuthPlugin.php');

	function HTTPAuth_hook(){

		global $wgUser;
		global $wgRequest;

		$title = $wgRequest->getVal('title');
		if (($title == Title::makeName(NS_SPECIAL, 'Userlogout')) || ($title == Title::makeName(NS_SPECIAL, 'Userlogin'))){
			return;
		}

		$user = User::newFromSession();
		if (!$user->isAnon()){
			return;  // User is already logged in and not anonymous.
		}

		if(!isset($wgCommandLineMode) && !isset($_COOKIE[session_name()])){
			wfSetupSession();
		}


		#
		# Create a new MediaWiki account if needed
		#

		$_user = HTTPAuth_getUser();

		$id = User::idFromName($_user);
		if (is_null($id)){

			$u = User::newFromName($_user);
			$user->setName($_user);
			$user->setRealName('');
			$user->setEmail(HTTPAuth_getEmail());
			$user->mEmailAuthenticated = wfTimestampNow();
			$user->setToken();
			$user->saveSettings();
			$user->addToDatabase();
		}else{
			$user->mId = $id;
			$user->loadFromId();
		}


		$wgUser = $user;
		$wgUser->setCookies();
		return;
	}

	class HTTPAuth extends AuthPlugin{

		function HTTPAuth(){

			if (strlen(HTTPAuth_getUser())){
				global $wgExtensionFunctions;
				if (!isset($wgExtensionFunctions)){
					$wgExtensionFunctions = array();
				}else if (!is_array($wgExtensionFunctions)){
					$wgExtensionFunctions = array( $wgExtensionFunctions );
				}
				array_push($wgExtensionFunctions, 'HTTPAuth_hook');
			}

			global $wgHooks;
			$wgHooks['PersonalUrls'][] = 'HTTPAuthSSOActive';

			return;
		}

		function allowPasswordChange(){
			return true;
		}

		function setPassword($user, $password){
			return true;
		}

		function updateExternalDB($user){
			return true;
		}

		function canCreateAccounts(){
			return false;
		}

		function addUser($user, $password){
			return false;
		}

		function userExists($username){
			return true;
		}

		function authenticate($username, $password){
			$test = HTTPAuth_getUser();
			return isset($test) && (strtolower($username) == strtolower($test));
		}

		function validDomain($domain){
			return true;
		}

		function updateUser(&$user){
			return true;
		}

		function autoCreate(){
			return true;
		}

	 	function strict(){
			return true;
		}

		function initUser(&$user){
			$user->setRealName('');
			$user->setEmail(HTTPAuth_getEmail());
			$user->mEmailAuthenticated = wfTimestampNow();
			$user->setToken();
			$user->saveSettings();
		}

		function modifyUITemplate(&$template){
			$template->set('useemail', false);
			$template->set('remember', false);
			$template->set('create', false);
			$template->set('domain', false);
			$template->set('usedomain', false);
		}

		function getCanonicalName($username){
			return UcFirst(strtolower($username));
		}
	}

	function HTTPAuthSSOActive(&$personal_urls, $title){

		$personal_urls['logout'] = null;
		return true;
	}

?>
