<?php
	#
	# To use this plugin with a different SSO system, modify these two functions
	# to return the current username and email address.
	#

	function GodAuth_getUser(){
		return $_ENV['GodAuth_User'];
	}

	function GodAuth_getEmail(){
		global $wgAuthDomain;

		return $_ENV['GodAuth_User'] . "@" . $wgAuthDomain;
	}

	##############################################################################################

	$wgGroupPermissions['*']['createaccount']   = false;
	$wgGroupPermissions['*']['read']            = false;
	$wgGroupPermissions['*']['edit']            = false;

	require_once('AuthPlugin.php');

	function GodAuth_hook(){

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

		$id = User::idFromName(GodAuth_getUser());
		$user->mId = $id;
		$user->loadFromId();
		$wgUser = $user;

		$wgUser->setCookies();
		return;
	}

	class GodAuth extends AuthPlugin{

		function GodAuth(){

			if (strlen(GodAuth_getUser())){
				global $wgExtensionFunctions;
				if (!isset($wgExtensionFunctions)){
					$wgExtensionFunctions = array();
				}else if (!is_array($wgExtensionFunctions)){
					$wgExtensionFunctions = array( $wgExtensionFunctions );
				}
				array_push($wgExtensionFunctions, 'GodAuth_hook');
			}

			global $wgHooks;
			$wgHooks['PersonalUrls'][] = 'GodAuthSSOActive';

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
			$test = GodAuth_getUser();
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
			$user->setEmail(GodAuth_getEmail());
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

	function GodAuthSSOActive(&$personal_urls, $title){

		$personal_urls['logout'] = null;
		return true;
	}

?>