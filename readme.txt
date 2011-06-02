=== Get Post ===
Contributors: jtatum
Tags: post, retrieve, display, latest, tag
Requires at least: 3.0
Tested up to: 3.1.3
Stable tag: 2.0.0

Get Post adds a tag that allows you to retrieve and display the latest post
identified by a specific set of parameters.

== Description ==

Get Post adds a tag that allows you to retrieve and display the latest post
identified by a specific set of parameters.

When this plugin is active, any post or page can contain the get-post markup
as follows:

[get-post tag="(some tag)"]

This will be replaced with the latest post tagged with the given tag.

== Installation ==

1. Upload `get-post.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Use the [get-post] markup in a post or page

== Notes ==

This plugin does something a little bit unorthodox: It reenters ["The
Loop"][the loop] while the post content is being rendered. As it does not
process plugins while in the loop, an infinite loop is not possible at the
moment. Processing plugins properly is planned for a future release.

[the loop]: http://codex.wordpress.org/The_Loop

== Revision History ==

= 2.0.0 XX-Jun-2011 =
* Rewrite of internals. The option parser is a lot more powerful. The new
  design makes adding new options simpler.
* Adding several optional parameters to the parser tag - category, tag,
  random, show.
* Switching to a template system rather than hard-coding the HTML for the post
  display.
* Display errors when unknown parameters are specified.


= 1.0.2 13-Jun-2009 =

* Did not update the version number properly in 1.0.1. Oops.

= 1.0.1 13-Jun-2009 =

* Fixed issue where comments and other post data bled over into the "getter"
from the "gotten" post.

= 1.0.0 25-Nov-2008 =

* Initial release

== To-do ==

* Add more post selection criteria, such as category and author
* Allow the user to get the raw post content, title and permalink rather than
  outputting all these things
* Properly process the title and content using the_title() and the_content()
  if it is possible to do without getting in an infinite loop
* Add post date and correct formatting differences between included post and a
  real post
