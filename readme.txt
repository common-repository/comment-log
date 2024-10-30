=== Comment Log ===
Contributors: roamzero
Donate link: http://peopletab.com
Tags: comments
Requires at least: 2.3
Tested up to: 2.5
Stable tag: 1.2

Keep track of your comments on other sites using the cLog API.

== Description ==

Comment Log (or clog) is a plugin that allows you to store a copy of a comment you make on another site. 
This is done through implementing the [RESTTA](http://peopletab.com/restta.html)-based cLog API. 
As long as the remote site supports the API, you can keep track of any comments you make. 
This plugin also allows those that comment on your blog to keep track of their comments. 
The full details about the cLog spec can be found [HERE](http://peopletab.com/cLog.html)

== Installation ==

1. Upload `comment-log` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Edit the restta.xml file in the commentlog directory if your wp installation
is in a directory (e.g. mysite.com/wp/). Edit the pathPrefix element to surround the path (e.g. <pathPrefix>/wp</pathPrefix>)
4. Move the restta.xml file to the document root. If there already exists a restta.xml file, do not delete it. Open the existing file and
merge the appClass tag (along with its contents) in the original file with the other appClass tags in the already-existing file.

== Frequently Asked Questions ==

= Where do I send my inquiries? =

Please contact me at roamzero[at]gmail.com.

== Version Log ==

*   1.2 : Fixed a major bug, it should actually work properly now for the handful of people that use it :D

*   1.1 : Minor updates, you can select the page to display the comment log on from a dropdown now, and confirmed it working with 2.5

*   1.0 : Initial version.

== Screenshots ==

1. The comment log as it appears on a page.
2. Confirming a comment you made on another site.
3. Button added to allow others to log their comments.
4. Comment log administration screen.


