MediaWiki SSO
=============

A plugin to enable Single Sign-On (SSO) for MediaWiki.
This is set up to use <a href="http://github.com/exflickr/GodAuth">GodAuth</a> but can be easily be modified for any SSO system.


Installation
------------

Copy the file <code>GodAuth.php</code> into the <code>/extensions/</code> folder.
Then open up your <code>LocalSettings.php</code> file and add these lines at the bottom:

    $wgAuthDomain = 'mydomain.com';

    require_once('extensions/GodAuth.php');
    $wgAuth = new GodAuth();
