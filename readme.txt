=== Get Post ===
Contributors: jtatum
Tags: post, page, retrieve, display, latest, search, criteria, random
Requires at least: 3.0
Tested up to: 3.1.3
Stable tag: 2.0.0
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=CG8P46WLYX6LQ

Get Post adds a tag that allows you to retrieve and display the latest post
identified by a specific set of parameters.

== Description ==

Get Post adds a tag that allows you to retrieve and display the latest post
identified by a specific set of parameters.

When this plugin is active, any post or page can contain the get-post markup
as follows:

    [get-post tag=some-tag]

This will be replaced with the latest post tagged with the given tag.

= Parameters =

The parameters control which posts are retrieved by get-post. Options can be
used in combination to build a list of criteria for post or posts to retrieve.
By mixing criteria, you can exert a lot of control over the post(s) which are
found by the plugin.

    [get-post tag=some-tag random show=3]

One note on Wordpress in general: if you specify criteria that Wordpress can't
match, it will make something up. For instance, if you specify a tag or
category that doesn't exist, it will simply retrieve the latest post with no
indication that anything is wrong. Take care to ensure that the options you
specify are what you intend.

Several of these parameters work best when specifying the slugs. For instance,
when specifying a tag it's best to use the slug value rather than the full
name of the tag. To find the slug, see the admin panel -> posts -> post tags.
The slug is listed right on that page.

You may wish to specify an option value with a space in it. This is
accomplished with quotes:

    [get-post option="a value with spaces"]

* tag:
Specify a tag to search for. The tag's slug should be specified.

        [get-post tag=some-tag]

* category:
Specify a category to search for. The category's slug should be specified.

        [get-post category=some-category]

* show:
Specify the number of posts to show.

        [get-post show=5]

* template:
Specify the template to use. This specification overrides the template set in
the options panel of the admin interface. See the template section for more
details on template tags. __NOTE:__ If entering any HTML into this, please be
sure to select the HTML editor rather than the visual editor.

        [get-post template="<h1>Title: {title}</h1>"]

* random:
Select a random post from the matching criteria

        [get-post random]

* _default_:
This isn't really an option. _By default_, get-post displays the latest blog
post.

        [get-post]

= Templates =

Get-post ships with a default template that should render the usual post
details in a form that fits well with most themes. You can customize this
template to add or remove data from the included post. Additionally, you can
use the `template` parameter to the `[get-post]` tag to specify a one-off
template.

Using the `show` parameter will retrieve multiple posts. In this case, the
template will be repeated one time for each retrieved post.

These tags are replaced with the value from the retrieved post. For instance,
a template containing `{title}` will actually have the title of the retrieved
post, rather than the word `title`.

* {title}:
The title of the post.

* {content}:
The content of the post.

* {author}:
The author of the post.

* {date}:
The date the post was written.

* {time}:
The time the post was written.

* {permalink}:
A link to the post itself.

* {authorlink}:
A link to all posts by the post's author.

* {id}:
The post's ID number.

= Examples =

Indicate when your blog was last updated:

    Blog last updated [get-post template="{date} at {time}."]

Link to the latest post:

    Check out my post: [get-post template="<a href='{permalink}'>{title}</a>"]

= How it works =

This plugin does something a little bit unorthodox: It reenters ["The
Loop"][the loop] while the post content is being rendered. Then, it calls
whatever Wordpress internal functions will safely work, using raw data from
$post when these functions are unsafe to call again. People curious about the
internals should examine `class-get-post-getter.php`. The class is structured
to be reusable by any other GPL2 projects.

[the loop]: http://codex.wordpress.org/The_Loop

== Installation ==

1. Upload `get-post.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Use the [get-post] markup in a post or page

== Screenshots ==

1. Example page showing get-post invoked three times - once to display some
   dates, once to show a link, and once with the default template.

== Changelog ==

= 2.0.0 5-Jun-2011 =

* Rewrite of internals. The option parser is a lot more powerful. The new
  design makes adding new options simpler.
* Plugins that affect post content should be correctly rendered now.
* To a very limited extent, you can use get-post recursively. (If you do,
  please email me a link or let me know what you're doing with this.)
* Adding several optional parameters to the parser tag - category, tag,
  random, show.
* Switching to a template system rather than hard-coding the HTML for the post
  display.
* Display errors when unknown parameters are specified.
* Using the template option, users can output individual elements from posts
* Plugin settings are now available in the admin panel. You can edit the
  template used for the tag to make it match your theme here.
* Plugin can be uninstalled.

= 1.0.2 13-Jun-2009 =

* Did not update the version number properly in 1.0.1. Oops.

= 1.0.1 13-Jun-2009 =

* Fixed issue where comments and other post data bled over into the "getter"
  from the "gotten" post.

= 1.0.0 25-Nov-2008 =

* Initial release

== Upgrade Notice ==

= 2.0.0 =
Many new features including the ability to customize post output, a lot of
new available parameters, and more
