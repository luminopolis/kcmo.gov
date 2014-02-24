=== aitch ref! ===
Contributors: postpostmodern, pinecone-dot-io
Donate link: http://www.heifer.org/
Tags: url, href
Requires at least: 3.0.0
Tested up to: 3.8
Stable tag: trunk

Remove most absolute urls in your html.  Useful for switching between development / staging / production environments and painless deployment.  Requires PHP >= 5.2 ( json_encode )

== Description ==
Useful for switching between different development environments.  Attempts to replace any absolute urls, whether generated though Wordpress option like 'siteurl' or 'home', or through hardcoded urls in posts.

== Installation ==
1. Place entire /aitch-ref/ directory to the /wp-content/plugins/ directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Enter all possible site urls in the text box, each on a new line. Example could be http://wordpress.org/ , http://dev.wordpress.org/, http://dev.wordpress. Note - subdirectories to not currently work, so http://127.0.0.1/~wordpress/ would give you bad results
1. *Important* if SSL is used on site - each record needs an entry for ssl.  For example, if your dev environment is http://dev.wordpress you will need to enter https://dev.wordpress as well even if it does not exist.
1. Look at your source, now back to me.  Now back to your source.

== Changelog ==
= .86 =
moving to github

= .85 =
fixed short tags, escape textarea

= .8 = 
better MU functionality

= .75 = 
added SSL support - for now you must add additional entries for each environment even if a dev ssl does not exist
added filter for `stylesheet_uri`

= .71 =
added filter for `login_url`

= .70 =
removed __DIR__ for php 5.2 compat

= .69 =
removes duplicates from url list in admin 

= .68 =
added filter for `wp_get_attachment_url` 

= .66 =
pre_post_link changed to not use absolute link, was causing double http:// on some sites

= .65 =
added aitch() helper function

= .62 =
force baseurl in absolute filter

= .61 =
added filter for `term_link`

= 0.6 =
added filter for `style_loader_src`

= 0.59 =
added filter for `content_url`

= 0.58 =
`admin_url` uses absolute url filter now

= 0.55 = 
fixed bug affecting 'upload_dir' filter

= 0.53 = 
added filter for admin scripts

= 0.51 = 
fixed deprecated argument in add_options_page()

= 0.5 =
discovered incompatibility in wordpress 2.x, bumped minimum version to 3.0 - no new functionality

= 0.49 = 
fixed bug in self::$path, discrepancies on $_SERVER['DOCUMENT_ROOT'] on certain environments

= 0.42 = 
fixed bug in updating options on a multiuser install

= 0.3 =
better handling of mu / single blog installs.  MU uses blog #1 for all options db storage

= 0.2 =
minor code cleanups, using json_encode for options db storage, fancy graphic

= 0.15 =
minor code cleanups

= 0.1 =
yes, its here

== Screenshots ==
1. time out for you `/trunk/screenshot-1.jpg`