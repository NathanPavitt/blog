=== Plugin Name ===
Contributors: swhitley
Donate link: 
Tags: comments, Twitter, FriendFeed, identi.ca, social media, blog, tweetback, tinyurl, bit.ly
Requires at least: 2.7
Tested up to: 2.9.1
Stable tag: 2.80

Post comments from social media services to your blog.

Examples can be found on this post (look for `Twitter Comment`) - http://www.voiceoftech.com/swhitley/?p=640


Chat Catcher - http://chatcatcher.com

== Description ==

Post comments from social media services to your blog.

Changes in Version 2.80

- Fixed a "strpos" warning that occurred during a search for word exclusions.
- Added a "more" feature for those who would like to only display a portion of their comments and allow viewers to click to see all (requires theme modifications).


== Installation ==

1. Upload `chatcatcher.php` and the included image files to the `/wp-content/plugins/chatcatcher/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Navigate to the `Chat Catcher` admin page under `Settings`.
1. Review the options and make any necessary changes.
1. Click on `Register This Blog`.  The plugin will contact the Chat Catcher server and confirm your registration.


== Change Log ==

2.80

- Fixed a "strpos" warning that occurred during a search for word exclusions.
- Added a "more" feature for those who would like to only display a portion of their comments and allow viewers to click to see all (requires theme modifications).


2.75

- New Custom Trackback comment type.  Separate Chat Catcher trackbacks from regular trackbacks/pingbacks using the custom trackback type.
- Username List:  New options to moderate or delete a comment if a match is found in the list.
- Replies:  Chat Catcher now captures Replies.  'Replies' are comments that are associated with a post, but they do not contain a link to the post.  Replies can be moderated or deleted automatically.
- Word/Phrase List: Enter any word or phrase into the list and Chat Catcher will moderate or delete comments based on the text you supply.  This feature can be used to filter retweets.
- Nested Comments:  Chat Catcher will post nested comments (parent/child relationships).  If your theme supports nested comments, you will see the nesting in the comment display.


2.69

06/17/2009 Shannon Whitley   

- Moderate All option fixed.


2.68

06/16/2009 Shannon Whitley   

- Default Template - Change default template to rely on img tag for profile image.
- You should clear out your "Template" textbox and let it default to the new text.
- Gravatars - New option to position profile image as gravatar.
- Email Address - Clearly note that email address is required and provide opportunity to change.

2.67

05/11/2009 Shannon Whitley   

- Bad Behavior Whitelisting.  A button was added to the Chat Catcher Settings page.
  Click on the button to add the Chat Catcher server to the Bad Behavior Whitelist.
- The plugin now sends the correct url when a blog's siteurl and home url are not the same.

