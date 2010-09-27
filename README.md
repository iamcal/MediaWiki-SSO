MediaWiki SSO
=============

A plugin to enable Single Sign-On (SSO) for MediaWiki.
This is set up to use <a href="http://github.com/exflickr/GodAuth">GodAuth</a> but can be easily be modified for any SSO system.
The <code>HTTPAuth.php</code> plugin is an alternative version that just uses a <code>$_SERVER</code> variable. It should be very easy to adapt.


Installation
------------

Copy the file <code>GodAuth.php</code> into the <code>/extensions/</code> folder.
Then open up your <code>LocalSettings.php</code> file and add these lines at the bottom:

    $wgAuthDomain = 'mydomain.com';

    require_once('extensions/GodAuth.php');
    $wgAuth = new GodAuth();


Credits
-------

* Plugin created by <a href="http://github.com/iamcal">Cal Henderson</a>
* Fixes from <a href="http://github.com/jacques">Jacques Marneweck</a>
