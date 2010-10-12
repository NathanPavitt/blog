<?php
/*****************************************************************************
//* Chat Catcher Script
//* This script can be used with any blog engine.
//* 
//*****************************************************************************
$ccVersion = 2.80;

//*****************************************************************************
//* WORDPRESS USERS - Stop.  All settings should be changed within WordPress.
//*****************************************************************************

//*****************************************************************************
//* REQUIRED SETTINGS 
//*****************************************************************************

//*****************************************************************************
//* Secret 
//* Example: $cc_secret = '740757a0-e1a3-11dd-ad8b-0800200c9a66';
//* A secret is just an id that is shared between this script and Chat Catcher.
//* The secret can be anything you like.
//* You may use the link below to generate a secret:
//* http://www.famkruithof.net/uuid/uuidgen
//*****************************************************************************
$cc_secret = '%%secret%%';

//*****************************************************************************
//* Admin Email
//*****************************************************************************
$admin_email='';

//*****************************************************************************
//* Blog Home
//* Your blog's home url.  Do not include index.php, index.htm, default.asp, etc.
//* You may include the 'www' or leave it out.  We will search for both.
//* Example:  http://myblog.com
//*****************************************************************************
$blog_home='http://';


//*****************************************************************************
//* OPTIONAL SETTINGS - You can change the settings below. 
//*****************************************************************************

//*****************************************************************************
//* Exclusions
//* 
//* A list of users who will be rejected.  You may include your own
//* username.  Each username should begin on a separate line.  Start by
//* replacing FirstUser, SecondUser, and ThirdUser.  Add additional lines
//* as needed, above the last KEEPME1 line.  
//*****************************************************************************
//Do not include '@'
$cc_exclude = <<<KEEPME1
FirstUser
SecondUser
ThirdUser
KEEPME1;

//*****************************************************************************
//* Word/Phrase Exclusions
//* 
//* A list of words or phrases which will cause rejections.
//* Each word or phrase should begin on a separate line.  Start by
//* replacing FirstWord, SecondWord, and ThirdWord.  Add additional lines
//* as needed, above the last KEEPMEWORD line.  
//*****************************************************************************
//Do not include '@'
$cc_exclude_words = <<<KEEPMEWORD
FirstWord
SecondWord
ThirdWord
KEEPMEWORD;

//*****************************************************************************
//* Exclude Replies - Replies are comments that do not contain links to the post.
//* Values 'P' (post), 'D' (delete)
//*****************************************************************************
$cc_exclude_replies_action ='P';

//*****************************************************************************
//* Log - Writes a log file.
//* Values 'Y' or 'N'
//*****************************************************************************
$cclog='N';


//*****************************************************************************
//* WORDPRESS SPECIFIC - Stop here for all other blogs.
//*****************************************************************************
/*
	Plugin Name: Chat Catcher
	Plugin URI: http://chatcatcher.com
	Description: Post comments from social media services to your blog.
	Author: Shannon Whitley
	Author URI: http://chatcatcher.com
	Version: 2.80
*/

//*****************************************************************************
//* WordPress: Comment Template - You can modify the HTML below.
//*
//* Special variables are enclosed in double-percent signs.
//* Do not remove %%excerpt%% (the main comment).
//*****************************************************************************
$cc_template = <<<KEEPME2
<strong>%%title%%</strong>
<a href="%%profile_link%%" title="%%title%%">
<div class="ccimg1" title="%%blog_name%%" style="float:left;margin-right:10px;padding:0;width:60px;height:60px;">
<img name="cc_image" title="%%blog_name%%" style="float:left;margin-right:10px;padding:0;width:50px;height:50px;" src="%%pic%%">
</div>
</a>
%%excerpt%%
KEEPME2;

//Initialize the comment author format.
$cc_comment_author="%%screen_name%% (%%name%%)";

//*****************************************************************************
//* END SETTINGS - Stop!!!
//*****************************************************************************

/*
Copyright (c) 2009 Whitley Media

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

//*****************************************************************************
//* MAIN
//*****************************************************************************
$WPBlog = 'N';
if(!isset($pageShow))
{
    $pageShow = 'Y';
}
$cc_plugins = array();

//Plugin File
//Plugin Files must be named 'xxxxx_cc_plugin.php'
if ($cchandle = opendir(dirname(__FILE__))) { 
    while (false !== ($ccfile = readdir($cchandle))) {
        $ccplugin = explode('cc_plugin.php',$ccfile);
        if(count($ccplugin) > 1)
        {
            include_once(dirname(__FILE__).'/'.$ccfile);        
        }
    } 
    closedir($cchandle); 
}

//WordPress
if(function_exists('wp_signon'))
{
    if ( get_option( "cc_more" ) != "" )
      $cc_more = get_option('cc_more');
    add_action("init", "cc_contact_accept",1);
    add_filter("get_avatar", "cc_get_avatar");
    add_filter("get_comment_author", "cc_comment_author");
    add_filter("comment_text", "cc_comment_text");    
    //add_action('wp_head', 'cc_add_headers', 0);	
    $WPBlog = 'Y';
   	add_action("admin_menu", "cc_config_page");
    $pageShow = 'N';
    if($cc_more == 'Y')
    {
        add_action('comment_form', 'cc_comment_form');
    }

 


    //Globals
    if ( get_option( "cc_secret" ) != "" )
      $cc_secret = get_option('cc_secret');
    if ( get_option( "cc_template" ) != ""  && !isset($_POST["cc_template"]))
      $cc_template = get_option('cc_template');
    if ( get_option( "cc_comment_author" ) != "" )
      $cc_comment_author = get_option('cc_comment_author');
    if ( get_option( "cc_use_gravatar" ) != "" )
      $cc_use_gravatar = get_option('cc_use_gravatar');      
}

if(!isset($_REQUEST["ccwp"]))
{
  if(isset($_REQUEST["secret"]))
  {
      if($_POST["secret"] == $cc_secret)
      {
          if(isset($_POST['trackback-uri']))
          {
              ccTrackBack();
          }
          else
          {
        	  header('Content-Type: text; charset=UTF-8');
              die('trackback-uri not found');
          }        
      }
  }
  else
  {
      if($pageShow == 'Y')
      {
    	  header('Content-Type: text/html; charset=UTF-8');
    	  echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
          echo '<html><head><title>Chat Catcher</title></head><body>';
          echo '<div style="font-family:Arial"><h1>Chat Catcher</h1>';
          echo '<p><a href="chatcatcher.php?blog_activate=1">Activate This Script</a></p>';
          echo '<p><a href="http://www.chatcatcher.com/tester.aspx?&a='.cc_selfURL().'">Test This Script</a></p>';
          echo '<p>&nbsp;</p><p><strong>System Information</strong>';
          echo '<br/>Version: '.$ccVersion.'<br/>';
          echo '<p><a href="http://www.chatcatcher.com">Chat Catcher</a></p>';
          echo '</div>';
          echo '</body></html>';
      }
  }
}

if(isset($_GET['blog_activate']))
{
    ccBlog_Register();
}

function cc_comment_form()
{
//Thank you - http://websitebuildersresource.com/2008/12/20/show-partial-content-slide-animate-with-jquery/
echo '<script type="text/javascript">
var $j = jQuery.noConflict();
// Set the initial height
var sliderHeight;
$j(document).ready(function(){
sliderHeight = $j(".slider").attr("more");
if($j(".slider").height() < sliderHeight)
{
    $j(".slider_menu").html("");
    return;
}
	// Show the slider content
	$j(".slider").show();
	$j(".slider").each(function () {
		var current = $j(this);
		current.attr("box_h", current.height()+$j(".slider_menu").height());
	});
	$j(".slider").css("height",sliderHeight);
         $j(".slider").css("overflow","hidden");
});
// Set the initial slider state
var slider_state = "close";
function sliderAction()
{
	if (slider_state == "close")
	{
		sliderOpen();
		slider_state = "open"
		$j(".slider_menu").html("<a href=\"#\" onclick=\"return sliderAction();\">Close</a>");
	}
	else if (slider_state == "open")
	{
		sliderClose();
		slider_state = "close";
		$j(".slider_menu").html("<a href=\"#\" onclick=\"return sliderAction();\">More...</a>");
	}
	return false;
}
function sliderOpen()
{
	var open_height = $j(".slider").attr("box_h") + "px";
	$j(".slider").animate({"height": open_height}, {duration: "slow" });
}
function sliderClose()
{
	$j(".slider").animate({"height": sliderHeight}, {duration: "slow" });
}
</script>';

}

//*****************************************************************************
//* cc_get_avatar - Hide avatar on Chat Catcher comments or display as gravatar.
//*****************************************************************************
function cc_get_avatar($avatar)
{
    $ret = $avatar; 
    global $cc_use_gravatar;
        
    $text = get_comment_text();
    if(strpos($text,'cc_image') !== false)
    {
        if($cc_use_gravatar == 'Y')
        {
            $pattern = '/<img[^>]+src[\\s=\'"]';
            $pattern .= '+([^"\'>\\s]+)/is';
            if(preg_match($pattern,$text,$match))
            {
                $ret = "<img alt='' src='$match[1]' class='avatar avatar-32 photo avatar-default' height='32' width='32' />";
            }
        }
        else
        {
            $ret = '';
        }
    }
    return $ret;
}

//*****************************************************************************
//* cc_comment_text - Remove image from text.
//*****************************************************************************
function cc_comment_text($comment_text)
{
    global $cc_use_gravatar;
    
    if(strpos($comment_text,'cc_image') !== false  && is_single())
    {
        if($cc_use_gravatar == 'Y')
        {
            $comment_text = strip_tags($comment_text,'<a><p><strong><br><li><ul>');
        }
    }

    return $comment_text;
}


//*****************************************************************************
//* cc_comment_author - Reformat the name based on user preference.
//*****************************************************************************
function cc_comment_author($comment_author)
{
    global $cc_comment_author;
    
    if(strpos($comment_author,'(') > 2)
    {
	    $temp = explode('(',$comment_author);
	    $screen_name = trim($temp[0]);
        $name = str_replace(')','',$temp[1]);

	    $comment_author = str_replace('%%screen_name%%',$screen_name,$cc_comment_author);
	    $comment_author = str_replace('%%name%%',$name,$comment_author);
    }
    return $comment_author;
}

//*****************************************************************************
//* cc_add_headers - Add special header code for Chat Catcher.
//*****************************************************************************
function cc_add_headers()
{
    $cc_style = WP_PLUGIN_URL.'/chatcatcher/cc_style.css';
    if(file_exists(WP_PLUGIN_DIR.'/chatcatcher/cc_style.css'))
    {
        wp_enqueue_style('cc_css', $cc_style, false, 'screen');
	    wp_print_styles(array('cc_css'));
    }
}


//*****************************************************************************
//* ccTrackBack - Handle CC Input
//*****************************************************************************
function ccTrackBack() {

    global $cc_exclude, $cc_exclude_words, $cc_exclude_replies_action, $WPBlog, $cc_plugins;
    
    $title = $_POST['title'];
    $excerpt = $_POST['excerpt'];
    $url = $_POST['url'];
    $blog_name = $_POST['blog'];
    $tb_url = $_POST['trackback-uri'];
    $pic = $_POST['pic'];
    $profile_link = $_POST['profile_link'];
    $in_reply_to = '';
    if(isset($_POST['in_reply_to']))
    {
        $in_reply_to = $_POST['in_reply_to'];    
    }
    
    $temp = explode('(',$blog_name);
    $blog_screen_name = trim($temp[0]);

    if($WPBlog == 'Y')
    {
        //WordPress Comment
        ccWPComment($title, $excerpt, $url, $blog_name, $tb_url, $pic, $profile_link, $in_reply_to);
    }
    else
    {


        //Process user exclusion list.
        $cc_exclude = str_replace("\r","",$cc_exclude);
        $excludeArray = explode("\n",$cc_exclude);
        foreach($excludeArray as $screen_name)
        {
            $screen_name = trim($screen_name);
            if(strtolower($blog_screen_name) == strtolower($screen_name))
            {
                ccTrackback_response(0, 'Excluded User');
                return;
            }
        }

        
        //Process word exclusion list.
        $cc_exclude_words = str_replace("\r","",$cc_exclude_words);
        $excludeArray = explode("\n",$cc_exclude_words);
        foreach($excludeArray as $word)
        {
            $word = trim($word);
            if(strpos($excerpt, $word) !== false)
            {
                ccTrackback_response(0, 'Excluded Word '.$word);
                return;
            }
        }


        
        //Exclude Replies
        if($cc_exclude_replies_action != 'P')
        {
            if(strpos($excerpt, '[link to post]') === false)
            {
                ccTrackback_response(0, 'Excluded Reply');
                return;
            }
        }
        
        if(count($cc_plugins) > 0)
        {
            foreach($cc_plugins as $cc_plugin)
            {
                if(strlen($cc_plugin) > 0)
                {
                    $cc_plugin($title,$excerpt, $url, $blog_name, $tb_url, $pic, $profile_link);
                }
            }
        }
        else
        {
            //Standard Trackback
            $title = stripslashes($title);
            $excerpt = stripslashes($excerpt);
            $url = $url;
            $blog_name = stripslashes($blog_name);
            
            $http = new Http();
            $http->setMethod('POST');
            $http->addParam('title', $title);
            $http->addParam('url', $url);
            $http->addParam('blog_name', $blog_name);
            $http->addParam('excerpt', $excerpt);
            $http->setTimeout(120);                    
            $http->execute($tb_url);
            $results = ($http->error) ? $http->error : $http->result;
	    }
	}
	
	die($results);

}

    require_once(ABSPATH .'wp-includes/rewrite.php');

//*****************************************************************************
//* ccWPComment - WordPress Comment Processing
//*****************************************************************************
function ccWPComment($title, $excerpt, $url, $blog_name, $tb_url, $pic, $profile_link, $in_reply_to)
{
    global $wpdb, $wp_query, $cc_template, $cc_comment_author;

    
    $moderate_comments = get_option('cc_moderate_comments');
    $moderate_this = 'N';
    if($moderate_comments == 'Y')
    {
        $moderate_this = 'Y';
    }
    
    $title     = stripslashes($title);
    $excerpt   = stripslashes($excerpt);
    $excerpt   = str_replace('Posted using Chat Catcher','<a href="http://chatcatcher.com" target="_blank">Posted using Chat Catcher</a>',$excerpt);
    $blog_name = stripslashes($blog_name);


    $tb_id = url_to_postid($tb_url); 



    if ( !intval( $tb_id ) )
	    ccTrackback_response(1, 'I really need an ID for this to work.');

    //Set Comment Type
    $comment_type = get_option('cc_comment_type');
    if($comment_type == 'comment')
    {
        $comment_type = '';
    }
    

	$comment_post_ID = (int) $tb_id;
	$comment_author = $blog_name;
	$comment_author_email = '';
	$comment_author_url = $url;
	$comment_content = $cc_template;

    //Process Template Variables	
	$comment_content = str_replace('%%title%%',$title,$comment_content);
	$comment_content = str_replace('%%pic%%',$pic,$comment_content);
    $comment_content = str_replace('%%profile_link%%',$profile_link,$comment_content);
	$comment_content = str_replace('%%excerpt%%',$excerpt,$comment_content);
	$temp = explode('(',$blog_name);
	$screen_name = trim($temp[0]);
    $name = str_replace(')','',$temp[1]);
    
    //Process user exclusion list.
    $cc_exclude = str_replace("\r","",get_option('cc_exclude'));
    $excludeArray = explode("\n",$cc_exclude);
    foreach($excludeArray as $screen_name_exclude)
    {
        $screen_name_exclude = trim($screen_name_exclude);
        if(strtolower($screen_name_exclude) == strtolower($screen_name))
        {
            if(get_option('cc_exclude_action') == 'D')
            {
                ccTrackback_response(0, 'Excluded User');
                return;
            }
            else
            {
                $moderate_this = 'Y';   
            }
        }
    }


    
    //Process word exclusion list.
    $cc_exclude_words = str_replace("\r","",get_option('cc_exclude_words'));
    $excludeArray = explode("\n",$cc_exclude_words);
    foreach($excludeArray as $word)
    {
        $word = trim($word);
        if($word && strpos($excerpt, $word) !== false)
        {
            if(get_option('cc_exclude_words_action') == 'D')
            {
                ccTrackback_response(0, 'Excluded Word '.$word);
                return;
            }
            else
            {
                $moderate_this = 'Y';
            }
        }
    }
    
    //Exclude Replies
    $cc_exclude_replies_action = get_option('cc_exclude_replies_action');    
    if($cc_exclude_replies_action != 'P')
    {
        if(strpos($excerpt, '[link to post]') === false)
        {
            if($cc_exclude_replies_action == 'D')
            {
                ccTrackback_response(0, 'Excluded Reply');
                return;
            }
            else
            {
                $moderate_this = 'Y';  
            }
        }
    }

	$comment_content = str_replace('%%screen_name%%',$screen_name,$comment_content);
	$comment_content = str_replace('%%blog_name%%',$blog_name,$comment_content);
    $comment_content = str_replace('%%plugin_url%%',WP_PLUGIN_URL,$comment_content);
	


	$dupe = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_author_url = %s", $comment_post_ID, $comment_author_url) );
	if ( $dupe )
		ccTrackback_response(1, 'We already have a ping from that URL for this post.');
	
	$comment_parent = 0;	
	
    if(strlen($in_reply_to) > 0)
    {
    	$parents = $wpdb->get_results( $wpdb->prepare("SELECT comment_ID FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_author_url = %s", $comment_post_ID, $in_reply_to) );
        foreach($parents as $parent)
        {
            $comment_parent = $parent->comment_ID;
        }
    }    

    //Do not moderate so post as admin.
    //Retrieve admin users
    if($moderate_comments != 'Y')
    {
        $users = $wpdb->get_results( "SELECT user_id FROM $wpdb->users, $wpdb->usermeta WHERE " . $wpdb->users . ".ID = " . $wpdb->usermeta . ".user_id AND meta_key = '" . $wpdb->prefix . "capabilities' and meta_value='10' LIMIT 1 " );
        foreach($users as $user)
        {
            $user_id = $user["user_id"];
        }
	    $userdata = get_userdata($user_id);
    }
	$commentdata = compact('comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_type', 'userdata', 'comment_parent');

    //Allows HTML
    kses_remove_filters();
    //Remove Subscribe To Comments (Subscribers are confused by HTML in message)
    ccRemoveSubscribeToComments();
    
    //Post the Comment
	$commentdata['comment_post_ID'] = (int) $commentdata['comment_post_ID'];
	$commentdata['user_ID']         = (int) $commentdata['user_ID'];

	$commentdata['comment_parent'] = absint($commentdata['comment_parent']);
	$parent_status = ( 0 < $commentdata['comment_parent'] ) ? wp_get_comment_status($commentdata['comment_parent']) : '';
	$commentdata['comment_parent'] = ( 'approved' == $parent_status || 'unapproved' == $parent_status ) ? $commentdata['comment_parent'] : 0;

	$commentdata['comment_author_IP'] = preg_replace( '/[^0-9a-fA-F:., ]/', '',$_SERVER['REMOTE_ADDR'] );
	$commentdata['comment_agent']     = $_SERVER['HTTP_USER_AGENT'];

	$commentdata['comment_date']     = current_time('mysql');
	$commentdata['comment_date_gmt'] = current_time('mysql', 1);

	$commentdata = wp_filter_comment($commentdata);

    if($moderate_this == 'Y')
    {
  	    $commentdata['comment_approved'] = 0;
  	}
  	else
  	{
  	    $commentdata['comment_approved'] = wp_allow_comment($commentdata);  	
  	}

    $comment_ID = wp_insert_comment($commentdata);

    do_action('comment_post', $comment_ID, $commentdata['comment_approved']);

    if ( 'spam' !== $commentdata['comment_approved'] ) { // If it's spam save it silently for later crunching
        if ( '0' == $commentdata['comment_approved'] )
	        wp_notify_moderator($comment_ID);

        $post = &get_post($commentdata['comment_post_ID']); // Don't notify if it's your own comment

        if ( get_option('comments_notify') && $commentdata['comment_approved'] && $post->post_author != $commentdata['user_ID'] )
	        wp_notify_postauthor($comment_ID, $commentdata['comment_type']);
	}
	
	
	//DISQUS
	if(function_exists('dsq_is_installed'))
	{
        class ccDisqusAPI extends DisqusAPI  {
	        function create_post($thread_id, $message, $author_name, $author_email, $author_url) {
		        $response = urlopen(DISQUS_API_URL . '/api/create_post/', array(
			        'forum_api_key' => $this->forum_api_key,
			        'thread_id'	=> $thread_id,
			        'message'	=> $message,
			        'author_name' => $author_name,
			        'author_email'	=> $author_email,
			        'author_url'	=> $author_url
		        ));
		        $data = $response['data'];
		        if(!$data) {
			        return -1;
		        }
		        return $data;
	        }
        }
        $post = get_post($comment_post_ID); 
    	$permalink = $post->guid;
    	$title = $post->post_title;
    	$excerpt = $post->post_excerpt;

        $cc_dsq_api = new ccDisqusAPI(get_option('disqus_forum_url'), get_option('disqus_api_key'));

        //Retrieve the Thread ID        
	    $dsq_response = $cc_dsq_api->get_thread($post, $permalink, $title, $excerpt);
	    if( $dsq_response < 0 ) {
            ccTrackback_response(1, 'DISQUS get_thread failed.');	    
		    return false;
	    }
	    $thread_id = $dsq_response['thread_id'];

        //Convert div to code -- DISQUS doesn't allow div.
        $comment_content = str_replace('div','code',$comment_content);
        $comment_content = strip_tags($comment_content,'<a><p><br><strong><code>');
        $comment_content = str_replace('float:left;','background: url('.$pic.') no-repeat;float:left;',$comment_content);        

        //Create the comment on DISQUS.
        $dsq_response = $cc_dsq_api->create_post($thread_id,$comment_content,$comment_author,$comment_author_email,$comment_author_url);
        if( $dsq_response < 0 ) {
            ccTrackback_response(1, 'DISQUS create_post failed.');
        }        
	}
}

//*****************************************************************************
//* ccTrackback_response - XML Response
//*****************************************************************************
function ccTrackback_response($error = 0, $error_message = '') {
  ccLogIt($error.' '.$error_message);
  ob_get_clean();
  
	header('Content-Type: text/xml; charset=UTF-8');
	if ($error) {
		echo '<?xml version="1.0" encoding="utf-8"?'.">\n";
		echo "<response>\n";
		echo "<error>1</error>\n";
		echo "<message>$error_message</message>\n";
		echo "</response>";
		die();
	} else {
		echo '<?xml version="1.0" encoding="utf-8"?'.">\n";
		echo "<response>\n";
		echo "<error>0</error>\n";
		echo "</response>";
	}
	die();
}

//*****************************************************************************
//* ccRemoveSubscribeToComments
//*****************************************************************************
function ccRemoveSubscribeToComments()
{
    global $wp_filter;

	$action_ref	= 'comment_post';
	$filter 	= $wp_filter[$action_ref];
	$_lambda	= array();

	//Priority is 50 for Subscribe to Comments.
	foreach(range(50,50) as $priority){
		if (isset($filter[$priority]))
		{
			foreach($filter[$priority] as $registered_filter ){
				$callback = (string) $registered_filter['function'];
				if ( preg_match("/lambda/", $callback) ) {
		 	 		$_lambda[$priority][] = $callback;
				}
			}
		}
	}

	if ( count($_lambda) >= 0 ){
		foreach($_lambda as $priority => $callback) {
			if ( has_filter($action_ref,$callback) ){
				remove_filter($action_ref, $callback, $priority, 1);
			}
		}
	}
}

//*****************************************************************************
//* ccLogIt
//*****************************************************************************
function ccLogIt($msg)
{
	global $cclog;
	if ($cclog == 'Y') {
		$fp = fopen("./cc.log","a+");
		$date = gmdate("Y-m-d H:i:s ");
		fwrite($fp, "\n\n".$date.$msg);
		fclose($fp);
	}
	return true;
}

//*****************************************************************************
//* cc_contact_accept - WP init process to handle communication from CC.
//*****************************************************************************
function cc_contact_accept()
{
    global $cc_secret, $cc_more;
    
    if(isset($_REQUEST["ccwp"]) && isset($_REQUEST["secret"]))
    {
        if($_POST["secret"] == $cc_secret)
        {
            if(isset($_POST['trackback-uri']))
            {
              ccTrackBack();
            }
            else
            {
        	    header('Content-Type: text; charset=UTF-8');
              die('trackback-uri not found');
            }        
        }
    }
    else
    {
        if(!is_admin() && $cc_more == 'Y')
        {
            wp_enqueue_script('jquery');            
        }
    }
}

//*****************************************************************************
//* ccPlugin_Register - WordPress registration with CC.
//*****************************************************************************
function ccPlugin_Register() {

    global $cc_secret, $cc_template, $ccOverride;
    
    if ( !class_exists('Snoopy') ) {
        include_once( ABSPATH . WPINC . '/class-snoopy.php' );
    } 
    $siteurl = get_option('siteurl');
    $home = get_option('home');
    $plugin_url = cc_slashit($home).'?ccwp=true';
    $email = get_option("cc_admin_email");

    $snoopy = new Snoopy();
    $snoopy->agent = 'WP-ChatCatcher (Snoopy)';
    $snoopy->host = $_SERVER[ 'HTTP_HOST' ];
    $snoopy->read_timeout = "180";
    $snoopy->use_gzip = MAGPIE_USE_GZIP;
    $url = 'http://api.chatcatcher.com/register.aspx';
    $post = array();
    $post['script'] = $plugin_url;
    $post['secret'] = $cc_secret;
    $post['email'] = $email;
    $post['homeurl'] = $home;

    
    if(@$snoopy->submit($url,$post))
    {
        $results = $snoopy->results;
    } 
    else {
        $results = "Error contacting Chat Catcher: ".$snoopy->error."\n";
    }
    wp_die($results.'<p><a href="options-general.php?page=chatcatcher/chatcatcher.php">Return</a></p>');

}

//*****************************************************************************
//* ccBlogRegister - Non-WordPress registration with CC
//*****************************************************************************
function ccBlog_Register() {

    global $cc_secret, $blog_home, $admin_email;

    $home = $blog_home;
    $plugin_url = cc_selfURL();
    $email = $admin_email;
    
    $post = 'script='.$plugin_url.'&secret='.$cc_secret.'&email='.$email.'&homeurl='.$home;
    
    $http = new Http();
    $http->setMethod('POST');
    $http->addParam('script', $plugin_url);
    $http->addParam('secret', $cc_secret);
    $http->addParam('email', $email);
    $http->addParam('homeurl', $home);
                    
    $http->setTimeout(60);                    
    $http->execute('http://api.chatcatcher.com/register.aspx');
    $results = ($http->error) ? $http->error : $http->result;

    $results = '<div style="color:red;"><hr/><p>'.$results.'</p></div>';
    
    if(strpos($results,'Congratulations') != false)
    {
        die($results);
    }
    if(strlen($results) > 0 && strpos($results,'Congratulations') === false)
    {
        die($results);
    }
    else {
        $results = '<div style="color:red"><hr/>Error contacting Chat Catcher: '.$http->error.'</div>';                
        die($results);
    }
}

//*****************************************************************************
//* cc_config_page - WordPress admin page
//*****************************************************************************
function cc_config_page()
{
	add_submenu_page("options-general.php", "Chat Catcher",
		"Chat Catcher", 10, __FILE__, "chatcatcher_configuration");
}

//*****************************************************************************
//* ccClear - WordPress clear db options
//*****************************************************************************
function ccClear()
{
    delete_option( "cc_secret");
    delete_option( "cc_comment_type");
    delete_option( "cc_moderate_comments");
    delete_option( "cc_template");
    delete_option( "cc_comment_author");
    delete_option( "cc_exclude");
}

//*****************************************************************************
//* chatcatcher_configuration - WordPress admin page processing
//*****************************************************************************
function chatcatcher_configuration()
{
    global $cc_secret, $cc_template, $cc_comment_author, $cc_exclude;
    
        //Update the Bad Behavior Whitelist
        if(isset($_POST["cc_badbehavior"]))
        {
            cc_BadBehaviorWhiteList();
            echo 'Bad Behavior Whitelist Updated';
        }

		// Save Options
		if (isset($_POST["cc_save"]) || isset($_POST["cc_activate"])) {
			// ...the options are updated.
			update_option('cc_secret', stripslashes($_POST["cc_secret"]) );
			update_option('cc_template', stripslashes($_POST["cc_template"]) );
			update_option('cc_comment_author', stripslashes($_POST["cc_comment_author"]) );
			update_option('cc_exclude', stripslashes($_POST["cc_exclude"]) );
			update_option('cc_exclude_words', stripslashes($_POST["cc_exclude_words"]) );			
			update_option('cc_admin_email', stripslashes($_POST["cc_admin_email"]) );
			update_option('cc_comment_type',stripslashes($_POST["cc_comment_type"]) );
			
			update_option('cc_exclude_action', $_POST["cc_exclude_action"] );
			update_option('cc_exclude_words_action', $_POST["cc_exclude_words_action"] );			
			update_option('cc_exclude_replies_action', $_POST["cc_exclude_replies_action"] );						
			
			
			update_option('cc_use_gravatar',
				( $_POST["cc_use_gravatar"] == 1 ? 'Y' : 'N' ));
                            update_option('cc_more',
				( $_POST["cc_more"] == 1 ? 'Y' : 'N' ));

			//Reverse the logic for the user.
			update_option('cc_moderate_comments',
				( $_POST["cc_moderate_comments"] == 1 ? 'Y' : 'N' ));
		}
		
	    // First Time?
	    if ( get_option( "cc_secret" ) == "" )
            update_option( "cc_secret" , cc_NewGuid() );
	    if ( get_option( "cc_comment_type" ) == "" )
	    {
	        $cc_postTrackbacks = get_option( "cc_postTrackbacks" );
	        delete_option( "cc_postTrackbacks" );
	        if($cc_postTrackbacks == 'Y')
	        {
	            update_option( "cc_comment_type" , "trackback" );
	        }
	        if($cc_postTrackbacks == 'N')
	        {
		        update_option( "cc_comment_type" , "comment" );
		    }
  	        if($cc_postTrackbacks == '')
  	        {
    	        update_option( "cc_comment_type" , "trackback" );
  	        }
        }
	    
	    if ( get_option( "cc_exclude_action" ) == "" )
		    update_option( "cc_exclude_action" , "D" );
	    if ( get_option( "cc_exclude_words_action" ) == "" )
		    update_option( "cc_exclude_words_action" , "D" );		    
	    if ( get_option( "cc_exclude_replies_action" ) == "" )
		    update_option( "cc_exclude_replies_action" , "P" );		    
		    	    
	    if ( get_option( "cc_postAsAdmin" ) == 'Y' )
	    {
		    update_option( "cc_moderate_comments" , "N" );		    
		    delete_option( "cc_postAsAdmin" );		    
		}
	    if ( get_option( "cc_postAsAdmin" ) == 'N' )
	    {
		    update_option( "cc_moderate_comments" , "Y" );		    
		    delete_option( "cc_postAsAdmin" );		    
		}
		
	    if ( get_option( "cc_moderate_comments" ) == "" )
		    update_option( "cc_moderate_comments" , "N" );		    		
	    
	    if ( get_option( "cc_use_gravatar" ) == "" )
		    update_option( "cc_use_gravatar" , "N" );	
	    if ( get_option( "cc_more" ) == "" )
		    update_option( "cc_more" , "N" );		    
	    
	    if ( get_option( "cc_template" ) == "" )
		    update_option( "cc_template" , $cc_template );
	    if ( get_option( "cc_comment_author" ) == "" )
		    update_option( "cc_comment_author" , $cc_comment_author );
	    if ( get_option( "cc_admin_email" ) == "" )
		    update_option( "cc_admin_email" , get_option("admin_email") );

	
		// Clear?
		if(isset($_POST["cc_clear"]))
		{
		    ccClear();
		}	
			
		// Get the Data
		$cc_secret = get_option('cc_secret');
		$cc_template = get_option('cc_template');
		$cc_comment_author = get_option('cc_comment_author');
		$cc_exclude = get_option('cc_exclude');
		$cc_exclude_action = get_option('cc_exclude_action');		
		$cc_exclude_words = get_option('cc_exclude_words');		
		$cc_exclude_words_action = get_option('cc_exclude_words_action');				
		$cc_exclude_replies_action = get_option('cc_exclude_replies_action');						
		$cc_comment_type = get_option('cc_comment_type');
		$cc_use_gravatar = ( get_option('cc_use_gravatar') == 'Y' ?
			"checked='true'" : "");			
		$cc_more = ( get_option('cc_more') == 'Y' ?
			"checked='true'" : "");			
		$cc_moderate_comments = ( get_option('cc_moderate_comments') == 'Y' ?
			"checked='true'" : "");
		$cc_admin_email = get_option('cc_admin_email');
		
		// Register
		if (isset($_POST["cc_activate"])) {
		    ccPlugin_Register();
    }
?>
<img src="<?php echo WP_PLUGIN_URL."/chatcatcher/"; ?>ccbbl.png" align="right" style="margin:20px;"/>
    <h3>Chat Catcher Configuration</h3>
    <form action='' method='post' id='cc_conf'>
      <h4>Actions</h4>
      <p>
        <a href="http://www.chatcatcher.com/tester.aspx?&a=<?php
        $home = get_option('home');
        $home_slash = cc_slashit($home);
        echo $home_slash; ?>?ccwp=true&s=<?php echo $cc_secret ?>" target="_blank">Test Chat Catcher
        </a> | <a href="http://www.chatcatcher.com" target="_blank">Chat Catcher Home</a>
        | <a href="http://www.chatcatcher.com/da.aspx?siteurl=<?php echo $home ?>&secret=<?php echo $cc_secret ?>" target="_blank">Data Export</a>
      </p>
      <p class="submit">
        <input type='submit' name='cc_activate' value='Register This Blog' />
        <br/><br/><small>You must register your blog with Chat Catcher.<br/>The address of this blog, your Chat Catcher secret, and<br/>
        the email address below will be sent to <a href="http://chatcatcher.com" target="_blank">Chat Catcher</a>.</small>
      </p>
      <p>
      Email Address:  <input type="text" id="cc_admin_email" name="cc_admin_email" value="<?php echo $cc_admin_email ?>" size="50" />
      <br/><small>This contact email address is only used for important service notifications.</small>
      </p>
      <?php
        //$file = WP_PLUGIN_DIR.'/bad-behavior/bad-behavior/whitelist.inc.php';
        //if(file_exists($file)) {
      ?>
      <!--<p class="submit">
        <input type='submit' name='cc_badbehavior' value='Update the Bad Behavior Whitelist' />
        <br/><br/><small>
          Click on this button to add the Chat Catcher server<br/>to the Bad Behavior Whitelist.
          <br/>(Must be done after each Bad Behavior upgrade.)
        </small>

      </p>-->
      <?php //} ?>
<p>&nbsp;</p>
      <h4>Options</h4>
      <table cellspacing="20" width="60%">
        <tr>
          <td width="20%" valign="top">Secret</td>
          <td>
            <input type='text' name='cc_secret' value='<?php echo $cc_secret ?>' size="50" />
                <br/><small>
                  A secret is required to communicate with the Chat Catcher server.<br/>You should not need to change this.
                </small>
              
          </td>
        </tr>
        <tr>
          <td valign="top">Comment Type</td>
          <td>
          <select name="cc_comment_type">
            <option value='comment' <?php if($cc_comment_type == 'comment'){echo 'selected';} ?> >Comment</option>
            <option value='trackback' <?php if($cc_comment_type == 'trackback'){echo 'selected';} ?> >Trackback</option>
            <option value='ctrackback' <?php if($cc_comment_type == 'ctrackback'){echo 'selected';} ?> >Custom Trackback</option>
           </select>
              <br/><small>Set the default comment type for each incoming comment.<br/>Changing this value does not affect previously saved comments.<br/>Control the display of these comments in your theme.<br/>[comment_type = '', 'trackback', or 'ctrackback'].<br/><br/>Custom Trackbacks require special WordPress theme modifications.</small>
            </td>
        </tr>
        <tr>
        <td valign="top">Moderate All?</td>
        <td>
          <input type='checkbox' name='cc_moderate_comments' value='1' 
            <?php echo $cc_moderate_comments ?>/>
            &nbsp;&nbsp;<small>Check this box if you'd like to moderate all Chat Catcher posts.</small>
          </td>
        </tr>
        <tr>
          <td valign="top">Username List</td>
          <td>
            <textarea name='cc_exclude' rows="5" cols="50"><?php echo $cc_exclude; ?></textarea>
            <br/>
            <small>Scan for usernames from external services.  You may include your own username.  Each username must begin on a separate line.</small>
            <br/><br/>            
           Action When Found:
          <input type='radio' name='cc_exclude_action' value='M' 
            <?php if($cc_exclude_action == 'M'){echo ' checked ';} ?> /> Moderate 
          <input type='radio' name='cc_exclude_action' value='D' 
            <?php if($cc_exclude_action == 'D'){echo ' checked ';} ?> /> Delete            
            <br/><br/>
          </td>
        </tr>
        <tr>
        <td valign="top">Replies</td>
        <td>
        'Replies' are comments that do not include a link to the post.<br/><br/>
        Action When Found:
          <input type='radio' name='cc_exclude_replies_action' value='P' 
            <?php if($cc_exclude_replies_action == 'P'){echo ' checked ';} ?> /> Publish        
          <input type='radio' name='cc_exclude_replies_action' value='M' 
            <?php if($cc_exclude_replies_action == 'M'){echo ' checked ';} ?> /> Moderate
          <input type='radio' name='cc_exclude_replies_action' value='D' 
            <?php if($cc_exclude_replies_action == 'D'){echo ' checked ';} ?> /> Delete            
            <br/><br/>
          </td>
        </tr>
        <tr>
          <td valign="top">Word/Phrase List</td>
          <td>
            <textarea name='cc_exclude_words' rows="5" cols="50"><?php echo $cc_exclude_words; ?></textarea>
            <br/>
            <small>The comment will be scanned for any of the words or phrases contained in this list.  Each entry must begin on a separate line.  Example:  Enter <code>RT @</code> to scan for retweets.</small>
            <br/><br/>
           Action When Found:
          <input type='radio' name='cc_exclude_words_action' value='M' 
            <?php if($cc_exclude_words_action == 'M'){echo 'checked';} ?> /> Moderate 
          <input type='radio' name='cc_exclude_words_action' value='D' 
            <?php if($cc_exclude_words_action == 'D'){echo 'checked';} ?> /> Delete            
            <br/><br/>
          </td>
        </tr>
        <tr>
        <td valign="top">Use Gravatar?</td>
        <td>
          <input type='checkbox' name='cc_use_gravatar' value='1' 
            <?php echo $cc_use_gravatar ?>/> <small>Check this box if you'd like to remove the default profile image from the comment and display it in the gravatar position.  Your theme must already support gravatars.</small>
          </td>
        </tr>
        <tr>
          <td valign="top">Template</td>
          <td>
            <textarea name='cc_template' rows="5" cols="50"><?php echo $cc_template; ?></textarea>
            <br/>
            <small>You can modify the HTML in this comment template.  Special variables are enclosed in double-percent signs.  Do not remove %%excerpt%% (the main comment).</small>
          </td>
        </tr>
        <tr>
          <td valign="top">Comment Author Format</td>
          <td>
            <input type="text" name='cc_comment_author' value="<?php echo $cc_comment_author; ?>" size="50" />
          </td>
        </tr>
        <tr>
        <td valign="top">Use More...</td>
        <td>
          <input type='checkbox' name='cc_more' value='1' 
            <?php echo $cc_more ?>/> <small>(optional) Check this box to turn on jQuery and enable the "more" script.  This will only work on the <strong>comments</strong> page.</small>
<br/><br/><small>The "more" code below can be wrapped around code in your theme (such as a special-comments section).  The content will be partially hidden.  When this code is used in a theme, a <strong>more</strong> link will display at the bottom of the content.  If the <strong>more</strong> link is clicked, the wrapped content will toggle between <i>display</i> and <i>hide</i>.  The "more" attribute (default=200) tells the script the minimum height (in pixels) to display when the rest of the content is hidden.</small><br/>
<br/><small>Use this code in your theme:</small><br/>
<textarea onclick="this.select()" rows="5" cols="50"><div class="slider" more="200">
{comments loop}
</div>
<div class="slider_menu" more="200">
<a href="#" onclick="return sliderAction();">More...</a>
</div>
</textarea>

          </td>
        </tr>
      </table>
      <p class="submit">
        <input type='submit' name='cc_save' value='Save Settings' />
      </p>
      <!--<p class="submit">
        <input type='submit' name='cc_clear' value='Clear Settings' />
      </p>-->
    </form>
<?php
			
}

//*****************************************************************************
//* cc_slashit - Utility for end slash on blog urls
//*****************************************************************************
function cc_slashit($val)
{
    $val = $val.'/';
    $val = str_replace('//','/',$val);
    $val = str_replace(':/','://',$val);
    return $val;
}

//*****************************************************************************
//* cc_NewGuid
//*****************************************************************************
function cc_NewGuid() { 
    $s = strtoupper(md5(uniqid(rand(),true))); 
    $guidText = 
        substr($s,0,8) . '-' . 
        substr($s,8,4) . '-' . 
        substr($s,12,4). '-' . 
        substr($s,16,4). '-' . 
        substr($s,20); 
    return $guidText;
}

//*****************************************************************************
//* cc_BadBehaviorWhiteList
//*****************************************************************************
function cc_BadBehaviorWhiteList()
{
    $ccip = '174.129.96.212';  //Make this dynamic in the future.
    // Open file for read and string modification
    $file = WP_PLUGIN_DIR.'/bad-behavior/bad-behavior/whitelist.inc.php';
    $searchString = '$bb2_whitelist_ip_ranges = array(';
    $replaceString = '$bb2_whitelist_ip_ranges = array("'.$ccip.'",';
    if(file_exists($file))
    {
        $fh = fopen($file, 'r+');
        $contents = fread($fh, filesize($file));
        if(strpos($contents,$ccip) === false)
        {
            $new_contents = str_replace($searchString, $replaceString, $contents);
            fclose($fh);
            
            // Open file to write
            $fh = fopen($file, 'r+');
            fwrite($fh, $new_contents);
            fclose($fh);
        }
    }
}

//*****************************************************************************
//* cc_selfURL - Utility to get the url of the current page
//*****************************************************************************
function cc_selfURL() {
    $addl = '';
    if(isset($_SERVER['REQUEST_URI']))
    {
        $addl = $_SERVER['REQUEST_URI'];
    }
    if(!isset($_SERVER['REQUEST_URI']))
    {
        $addl = $_SERVER['SCRIPT_NAME'];
    }    
    $s = empty($_SERVER["HTTPS"]) ? "" : ($_SERVER["HTTPS"] == "on") ? "s" : "";
    $protocol = cc_strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s;
    $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
    return $protocol."://".$_SERVER['SERVER_NAME'].$port.$addl;
}

function cc_strleft($s1, $s2) {
    $values = substr($s1, 0, strpos($s1, $s2));
    return  $values;
}

/**
 * HTTP Class
 *
 * This is a wrapper HTTP class that uses either cURL or fsockopen to 
 * harvest resources from web. This can be used with scripts that need 
 * a way to communicate with various APIs who support REST.
 *
 * @author      Md Emran Hasan <phpfour@gmail.com>
 * @package     HTTP Library
 * @copyright   2007-2008 Md Emran Hasan
 * @link        http://www.phpfour.com/lib/http
 * @since       Version 0.1
 */

class Http
{
    /**
     * Contains the target URL
     *
     * @var string
     */
    var $target;
    
    /**
     * Contains the target host
     *
     * @var string
     */
    var $host;
    
    /**
     * Contains the target port
     *
     * @var integer
     */
    var $port;
    
    /**
     * Contains the target path
     *
     * @var string
     */
    var $path;
    
    /**
     * Contains the target schema
     *
     * @var string
     */
    var $schema;
    
    /**
     * Contains the http method (GET or POST)
     *
     * @var string
     */
    var $method;
    
    /**
     * Contains the parameters for request
     *
     * @var array
     */
    var $params;
    
    /**
     * Contains the cookies for request
     *
     * @var array
     */
    var $cookies;
    
    /**
     * Contains the cookies retrieved from response
     *
     * @var array
     */
    var $_cookies;
    
    /**
     * Number of seconds to timeout
     *
     * @var integer
     */
    var $timeout;
    
    /**
     * Whether to use cURL or not
     *
     * @var boolean
     */
    var $useCurl;
    
    /**
     * Contains the referrer URL
     *
     * @var string
     */
    var $referrer;
    
    /**
     * Contains the User agent string
     *
     * @var string
     */
    var $userAgent;
    
    /**
     * Contains the cookie path (to be used with cURL)
     *
     * @var string
     */
    var $cookiePath;
    
    /**
     * Whether to use cookie at all
     *
     * @var boolean
     */
    var $useCookie;
    
    /**
     * Whether to store cookie for subsequent requests
     *
     * @var boolean
     */
    var $saveCookie;
    
    /**
     * Contains the Username (for authentication)
     *
     * @var string
     */
    var $username;
    
    /**
     * Contains the Password (for authentication)
     *
     * @var string
     */
    var $password;
    
    /**
     * Contains the fetched web source
     *
     * @var string
     */
    var $result;
    
    /**
     * Contains the last headers 
     *
     * @var string
     */
    var $headers;
    
    /**
     * Contains the last call's http status code
     *
     * @var string
     */
    var $status;
    
    /**
     * Whether to follow http redirect or not
     *
     * @var boolean
     */
    var $redirect;
    
    /**
     * The maximum number of redirect to follow
     *
     * @var integer
     */
    var $maxRedirect;
    
    /**
     * The current number of redirects
     *
     * @var integer
     */
    var $curRedirect;
    
    /**
     * Contains any error occurred
     *
     * @var string
     */
    var $error;
    
    /**
     * Store the next token
     *
     * @var string
     */
    var $nextToken;
    
    /**
     * Whether to keep debug messages
     *
     * @var boolean
     */
    var $debug;
    
    /**
     * Stores the debug messages
     *
     * @var array
     * @todo will keep debug messages
     */
    var $debugMsg;
    
    /**
     * Constructor for initializing the class with default values.
     * 
     * @return void  
     */
    function Http()
    {
        $this->clear();    
    }
    
    /**
     * Initialize preferences
     * 
     * This function will take an associative array of config values and 
     * will initialize the class variables using them. 
     * 
     * Example use:
     * 
     * <pre>
     * $httpConfig['method']     = 'GET';
     * $httpConfig['target']     = 'http://www.somedomain.com/index.html';
     * $httpConfig['referrer']   = 'http://www.somedomain.com';
     * $httpConfig['user_agent'] = 'My Crawler';
     * $httpConfig['timeout']    = '30';
     * $httpConfig['params']     = array('var1' => 'testvalue', 'var2' => 'somevalue');
     * 
     * $http = new Http();
     * $http->initialize($httpConfig);
     * </pre>
     *
     * @param array Config values as associative array
     * @return void
     */    
    function initialize($config = array())
    {
        $this->clear();
        foreach ($config as $key => $val)
        {
            if (isset($this->$key))
            {
                $method = 'set' . ucfirst(str_replace('_', '', $key));
                
                if (method_exists($this, $method))
                {
                    $this->$method($val);
                }
                else
                {
                    $this->$key = $val;
                }            
            }
        }
    }
    
    /**
     * Clear Everything
     * 
     * Clears all the properties of the class and sets the object to
     * the beginning state. Very handy if you are doing subsequent calls 
     * with different data.
     *
     * @return void
     */
    function clear()
    {
        // Set the request defaults
        $this->host         = '';
        $this->port         = 0;
        $this->path         = '';
        $this->target       = '';
        $this->method       = 'GET';
        $this->schema       = 'http';
        $this->params       = array();
        $this->headers      = array();
        $this->cookies      = array();
        $this->_cookies     = array();
        
        // Set the config details        
        $this->debug        = FALSE;
        $this->error        = '';
        $this->status       = 0;
        $this->timeout      = '25';
        $this->useCurl      = TRUE;
        $this->referrer     = '';
        $this->username     = '';
        $this->password     = '';
        $this->redirect     = TRUE;
        
        // Set the cookie and agent defaults
        $this->nextToken    = '';
        $this->useCookie    = TRUE;
        $this->saveCookie   = TRUE;
        $this->maxRedirect  = 3;
        $this->cookiePath   = 'cookie.txt';
        $this->userAgent    = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.9';
    }
    
    /**
     * Set target URL
     *
     * @param string URL of target resource
     * @return void
     */
    function setTarget($url)
    {
        if ($url)
        {
            $this->target = $url;
        }   
    }
    
    /**
     * Set http method
     *
     * @param string HTTP method to use (GET or POST)
     * @return void
     */
    function setMethod($method)
    {
        if ($method == 'GET' || $method == 'POST')
        {
            $this->method = $method;
        }   
    }
    
    /**
     * Set referrer URL
     *
     * @param string URL of referrer page
     * @return void
     */
    function setReferrer($referrer)
    {
        if ($referrer)
        {
            $this->referrer = $referrer;
        }   
    }
    
    /**
     * Set User agent string
     *
     * @param string Full user agent string
     * @return void
     */
    function setUseragent($agent)
    {
        if ($agent)
        {
            $this->userAgent = $agent;
        }   
    }
    
    /**
     * Set timeout of execution
     *
     * @param integer Timeout delay in seconds
     * @return void
     */
    function setTimeout($seconds)
    {
        if ($seconds > 0)
        {
            $this->timeout = $seconds;
        }   
    }
    
    /**
     * Set cookie path (cURL only)
     *
     * @param string File location of cookiejar
     * @return void
     */
    function setCookiepath($path)
    {
        if ($path)
        {
            $this->cookiePath = $path;
        }   
    }
    
    /**
     * Set request parameters
     *
     * @param array All the parameters for GET or POST
     * @return void
     */
    function setParams($dataArray)
    {
        if (is_array($dataArray))
        {
            $this->params = array_merge($this->params, $dataArray);
        }   
    }
    
    /**
     * Set basic http authentication realm
     *
     * @param string Username for authentication
     * @param string Password for authentication
     * @return void
     */
    function setAuth($username, $password)
    {
        if (!empty($username) && !empty($password))
        {
            $this->username = $username;
            $this->password = $password;
        }
    }
    
    /**
     * Set maximum number of redirection to follow
     *
     * @param integer Maximum number of redirects
     * @return void
     */
    function setMaxredirect($value)
    {
        if (!empty($value))
        {
            $this->maxRedirect = $value;
        }
    }
    
    /**
     * Add request parameters
     *
     * @param string Name of the parameter
     * @param string Value of the parameter
     * @return void
     */
    function addParam($name, $value)
    {
        if (!empty($name) && !empty($value))
        {
            $this->params[$name] = $value;
        }   
    }
    
    /**
     * Add a cookie to the request
     *
     * @param string Name of cookie
     * @param string Value of cookie
     * @return void
     */
    function addCookie($name, $value)
    {
        if (!empty($name) && !empty($value))
        {
            $this->cookies[$name] = $value;
        }   
    }
    
    /**
     * Whether to use cURL or not
     *
     * @param boolean Whether to use cURL or not
     * @return void
     */
    function useCurl($value = TRUE)
    {
        if (is_bool($value))
        {
            $this->useCurl = $value;
        }   
    }
    
    /**
     * Whether to use cookies or not
     *
     * @param boolean Whether to use cookies or not
     * @return void
     */
    function useCookie($value = TRUE)
    {
        if (is_bool($value))
        {
            $this->useCookie = $value;
        }   
    }
    
    /**
     * Whether to save persistent cookies in subsequent calls
     *
     * @param boolean Whether to save persistent cookies or not
     * @return void
     */
    function saveCookie($value = TRUE)
    {
        if (is_bool($value))
        {
            $this->saveCookie = $value;
        }   
    }
    
    /**
     * Whether to follow HTTP redirects
     *
     * @param boolean Whether to follow HTTP redirects or not
     * @return void
     */
    function followRedirects($value = TRUE)
    {
        if (is_bool($value))
        {
            $this->redirect = $value;
        }   
    }
    
    /**
     * Get execution result body
     *
     * @return string output of execution
     */
    function getResult()
    {
        return $this->result;
    }
    
    /**
     * Get execution result headers
     *
     * @return array last headers of execution
     */
    function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get execution status code
     *
     * @return integer last http status code
     */
    function getStatus()
    {
        return $this->status;
    }
        
    /**
     * Get last execution error
     *
     * @return string last error message (if any)
     */
    function getError()
    {
        return $this->error;
    }

    /**
     * Execute a HTTP request
     * 
     * Executes the http fetch using all the set properties. Intellegently
     * switch to fsockopen if cURL is not present. And be smart to follow
     * redirects (if asked so).
     * 
     * @param string URL of the target page (optional)
     * @param string URL of the referrer page (optional)
     * @param string The http method (GET or POST) (optional)
     * @param array Parameter array for GET or POST (optional)
     * @return string Response body of the target page
     */    
    function execute($target = '', $referrer = '', $method = '', $data = array())
    {
        // Populate the properties
        $this->target = ($target) ? $target : $this->target;
        $this->method = ($method) ? $method : $this->method;
        
        $this->referrer = ($referrer) ? $referrer : $this->referrer;
        
        // Add the new params
        if (is_array($data) && count($data) > 0) 
        {
            $this->params = array_merge($this->params, $data);
        }
        
        // Process data, if presented
        if(is_array($this->params) && count($this->params) > 0)
        {
            // Get a blank slate
            $tempString = array();
            
            // Convert data array into a query string (ie animal=dog&sport=baseball)
            foreach ($this->params as $key => $value) 
            {
                if(strlen(trim($value))>0)
                {
                    $tempString[] = $key . "=" . urlencode($value);
                }
            }
            
            $queryString = join('&', $tempString);
        }
        
        // If cURL is not installed, we'll force fscokopen
        $this->useCurl = $this->useCurl && in_array('curl', get_loaded_extensions());
        
        // GET method configuration
        if($this->method == 'GET')
        {
            if(isset($queryString))
            {
                $this->target = $this->target . "?" . $queryString;
            }
        }
        
        // Parse target URL
        $urlParsed = parse_url($this->target);
        
        // Handle SSL connection request
        if ($urlParsed['scheme'] == 'https')
        {
            $this->host = 'ssl://' . $urlParsed['host'];
            $this->port = ($this->port != 0) ? $this->port : 443;
        }
        else
        {
            $this->host = $urlParsed['host'];
            $this->port = ($this->port != 0) ? $this->port : 80;
        }
        
        // Finalize the target path
        $this->path   = (isset($urlParsed['path']) ? $urlParsed['path'] : '/') . (isset($urlParsed['query']) ? '?' . $urlParsed['query'] : '');
        $this->schema = $urlParsed['scheme'];
        
        // Pass the requred cookies
        $this->_passCookies();
        
        // Process cookies, if requested
        if(is_array($this->cookies) && count($this->cookies) > 0)
        {
            // Get a blank slate
            $tempString   = array();
            
            // Convert cookiesa array into a query string (ie animal=dog&sport=baseball)
            foreach ($this->cookies as $key => $value) 
            {
                if(strlen(trim($value)) > 0)
                {
                    $tempString[] = $key . "=" . urlencode($value);
                }
            }
            
            $cookieString = join('&', $tempString);
        }
        
        // Do we need to use cURL
        if ($this->useCurl)
        {
            // Initialize PHP cURL handle
            $ch = curl_init();
    
            // GET method configuration
            if($this->method == 'GET')
            {
                curl_setopt ($ch, CURLOPT_HTTPGET, TRUE); 
                curl_setopt ($ch, CURLOPT_POST, FALSE); 
            }
            // POST method configuration
            else
            {
                if(isset($queryString))
                {
                    curl_setopt ($ch, CURLOPT_POSTFIELDS, $queryString);
                }
                
                curl_setopt ($ch, CURLOPT_POST, TRUE); 
                curl_setopt ($ch, CURLOPT_HTTPGET, FALSE); 
            }
            
            // Basic Authentication configuration
            if ($this->username && $this->password)
            {
                curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);
            }
            
            // Custom cookie configuration
            if($this->useCookie && isset($cookieString))
            {
                curl_setopt ($ch, CURLOPT_COOKIE, $cookieString);
            }
            
            curl_setopt($ch, CURLOPT_HEADER,         TRUE);                 // No need of headers
            curl_setopt($ch, CURLOPT_NOBODY,         FALSE);                // Return body
                
            curl_setopt($ch, CURLOPT_COOKIEJAR,      $this->cookiePath);    // Cookie management.
            curl_setopt($ch, CURLOPT_TIMEOUT,        $this->timeout);       // Timeout
            curl_setopt($ch, CURLOPT_USERAGENT,      $this->userAgent);     // Webbot name
            curl_setopt($ch, CURLOPT_URL,            $this->target);        // Target site
            curl_setopt($ch, CURLOPT_REFERER,        $this->referrer);      // Referer value
            
            curl_setopt($ch, CURLOPT_VERBOSE,        FALSE);                // Minimize logs
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);                // No certificate
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->redirect);      // Follow redirects
            curl_setopt($ch, CURLOPT_MAXREDIRS,      $this->maxRedirect);   // Limit redirections to four
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);                 // Return in string
            
            // Get the target contents
            $content = curl_exec($ch);
            $contentArray = explode("\r\n\r\n", $content);
            
            // Get the request info 
            $status  = curl_getinfo($ch);
			
			// Get the headers
			$resHeader = array_shift($contentArray);

			// Store the contents
			$this->result = implode($contentArray, "\r\n\r\n");

			// Parse the headers
			$this->_parseHeaders($resHeader);
            
            // Store the error (is any)
            $this->_setError(curl_error($ch));
            
            // Close PHP cURL handle
            curl_close($ch);
        }
        else
        {
            // Get a file pointer
            $filePointer = fsockopen($this->host, $this->port, $errorNumber, $errorString, $this->timeout);
       
            // We have an error if pointer is not there
            if (!$filePointer)
            {
                $this->_setError('Failed opening http socket connection: ' . $errorString . ' (' . $errorNumber . ')');
                return FALSE;
            }

            // Set http headers with host, user-agent and content type
            $requestHeader  = $this->method . " " . $this->path . "  HTTP/1.1\r\n";
            $requestHeader .= "Host: " . $urlParsed['host'] . "\r\n";
            $requestHeader .= "User-Agent: " . $this->userAgent . "\r\n";
            $requestHeader .= "Content-Type: application/x-www-form-urlencoded\r\n";
            
            // Specify the custom cookies
            if ($this->useCookie && $cookieString != '')
            {
                $requestHeader.= "Cookie: " . $cookieString . "\r\n";
            }

            // POST method configuration
            if ($this->method == "POST")
            {
                $requestHeader.= "Content-Length: " . strlen($queryString) . "\r\n";
            }
            
            // Specify the referrer
            if ($this->referrer != '')
            {
                $requestHeader.= "Referer: " . $this->referrer . "\r\n";
            }
            
            // Specify http authentication (basic)
            if ($this->username && $this->password)
            {
                $requestHeader.= "Authorization: Basic " . base64_encode($this->username . ':' . $this->password) . "\r\n";
            }
       
            $requestHeader.= "Connection: close\r\n\r\n";
       
            // POST method configuration
            if ($this->method == "POST")
            {
                $requestHeader .= $queryString;
            }           

            // We're ready to launch
            fwrite($filePointer, $requestHeader);
       
            // Clean the slate
            $responseHeader = '';
            $responseContent = '';

            // 3...2...1...Launch !
            do
            {
                $responseHeader .= fread($filePointer, 1);
            }
            while (!preg_match('/\\r\\n\\r\\n$/', $responseHeader));
            
            // Parse the headers
            $this->_parseHeaders($responseHeader);
            
            // Do we have a 302 redirect ?
            if ($this->status == '302' && $this->redirect == TRUE)
            {
                if ($this->curRedirect < $this->maxRedirect)
                {
                    // Let's find out the new redirect URL
                    $newUrlParsed = parse_url($this->headers['location']);
                    
                    if ($newUrlParsed['host'])
                    {
                        $newTarget = $this->headers['location'];    
                    }
                    else
                    {
                        $newTarget = $this->schema . '://' . $this->host . '/' . $this->headers['location'];
                    }
                    
                    // Reset some of the properties
                    $this->port   = 0;
                    $this->status = 0;
                    $this->params = array();
                    $this->method = 'GET';
                    $this->referrer = $this->target;
                    
                    // Increase the redirect counter
                    $this->curRedirect++;
                    
                    // Let's go, go, go !
                    $this->result = $this->execute($newTarget);
                }
                else
                {
                    $this->_setError('Too many redirects.');
                    return FALSE;
                }
            }
            else
            {
                // Nope...so lets get the rest of the contents (non-chunked)
                if ($this->headers['transfer-encoding'] != 'chunked')
                {
                    while (!feof($filePointer))
                    {
                        $responseContent .= fgets($filePointer, 128);
                    }
                }
                else
                {
                    // Get the contents (chunked)
                    while ($chunkLength = hexdec(fgets($filePointer)))
                    {
                        $responseContentChunk = '';
                        $readLength = 0;
                       
                        while ($readLength < $chunkLength)
                        {
                            $responseContentChunk .= fread($filePointer, $chunkLength - $readLength);
                            $readLength = strlen($responseContentChunk);
                        }

                        $responseContent .= $responseContentChunk;
                        fgets($filePointer);  
                    }
                }
                
                // Store the target contents
                $this->result = chop($responseContent);
            }
        }
        
        // There it is! We have it!! Return to base !!!
        return $this->result;
    }
    
    /**
     * Parse Headers (internal)
     * 
     * Parse the response headers and store them for finding the resposne 
     * status, redirection location, cookies, etc. 
     *
     * @param string Raw header response
     * @return void
     * @access private
     */
    function _parseHeaders($responseHeader)
    {
        // Break up the headers
        $headers = explode("\r\n", $responseHeader);
        
        // Clear the header array
        $this->_clearHeaders();
        
        // Get resposne status
        if($this->status == 0)
        {
            // Oooops !
            if(!eregi($match = "^http/[0-9]+\\.[0-9]+[ \t]+([0-9]+)[ \t]*(.*)\$", $headers[0], $matches))
            {
                $this->_setError('Unexpected HTTP response status');
                return FALSE;
            }
            
            // Gotcha!
            $this->status = $matches[1];
            array_shift($headers);
        }
        
        // Prepare all the other headers
        foreach ($headers as $header)
        {
            // Get name and value
            $headerName  = strtolower($this->_tokenize($header, ':'));
            $headerValue = trim(chop($this->_tokenize("\r\n")));
            
            // If its already there, then add as an array. Otherwise, just keep there
            if(isset($this->headers[$headerName]))
            {
                if(gettype($this->headers[$headerName]) == "string")
                {
                    $this->headers[$headerName] = array($this->headers[$headerName]);
                }
                    
                $this->headers[$headerName][] = $headerValue;
            }
            else
            {
                $this->headers[$headerName] = $headerValue;
            }
        }
            
        // Save cookies if asked 
        if ($this->saveCookie && isset($this->headers['set-cookie']))
        {
            $this->_parseCookie();
        }
    }
    
    /**
     * Clear the headers array (internal)
     *
     * @return void
     * @access private
     */
    function _clearHeaders()
    {
        $this->headers = array();
    }
    
    /**
     * Parse Cookies (internal)
     * 
     * Parse the set-cookie headers from response and add them for inclusion.
     *
     * @return void
     * @access private
     */
    function _parseCookie()
    {
        // Get the cookie header as array
        if(gettype($this->headers['set-cookie']) == "array")
        {
            $cookieHeaders = $this->headers['set-cookie'];
        }
        else
        {
            $cookieHeaders = array($this->headers['set-cookie']);
        }

        // Loop through the cookies
        for ($cookie = 0; $cookie < count($cookieHeaders); $cookie++)
        {
            $cookieName  = trim($this->_tokenize($cookieHeaders[$cookie], "="));
            $cookieValue = $this->_tokenize(";");
            
            $urlParsed   = parse_url($this->target);
            
            $domain      = $urlParsed['host'];
            $secure      = '0';
            
            $path        = "/";
            $expires     = "";
            
            while(($name = trim(urldecode($this->_tokenize("=")))) != "")
            {
                $value = urldecode($this->_tokenize(";"));
                
                switch($name)
                {
                    case "path"     : $path     = $value; break;
                    case "domain"   : $domain   = $value; break;
                    case "secure"   : $secure   = ($value != '') ? '1' : '0'; break;
                }
            }
            
            $this->_setCookie($cookieName, $cookieValue, $expires, $path , $domain, $secure);
        }
    }
    
    /**
     * Set cookie (internal)
     * 
     * Populate the internal _cookies array for future inclusion in 
     * subsequent requests. This actually validates and then populates 
     * the object properties with a dimensional entry for cookie.
     *
     * @param string Cookie name
     * @param string Cookie value
     * @param string Cookie expire date
     * @param string Cookie path
     * @param string Cookie domain
     * @param string Cookie security (0 = non-secure, 1 = secure)
     * @return void
     * @access private
     */
    function _setCookie($name, $value, $expires = "" , $path = "/" , $domain = "" , $secure = 0)
    {
        if(strlen($name) == 0)
        {
            return($this->_setError("No valid cookie name was specified."));
        }

        if(strlen($path) == 0 || strcmp($path[0], "/"))
        {
            return($this->_setError("$path is not a valid path for setting cookie $name."));
        }
            
        if($domain == "" || !strpos($domain, ".", $domain[0] == "." ? 1 : 0))
        {
            return($this->_setError("$domain is not a valid domain for setting cookie $name."));
        }
        
        $domain = strtolower($domain);
        
        if(!strcmp($domain[0], "."))
        {
            $domain = substr($domain, 1);
        }
            
        $name  = $this->_encodeCookie($name, true);
        $value = $this->_encodeCookie($value, false);
        
        $secure = intval($secure);
        
        $this->_cookies[] = array( "name"      =>  $name,
                                   "value"     =>  $value,
                                   "domain"    =>  $domain,
                                   "path"      =>  $path,
                                   "expires"   =>  $expires,
                                   "secure"    =>  $secure
                                 );
    }
    
    /**
     * Encode cookie name/value (internal)
     *
     * @param string Value of cookie to encode
     * @param string Name of cookie to encode
     * @return string encoded string
     * @access private
     */
    function _encodeCookie($value, $name)
    {
        return($name ? str_replace("=", "%25", $value) : str_replace(";", "%3B", $value));
    }
    
    /**
     * Pass Cookies (internal)
     * 
     * Get the cookies which are valid for the current request. Checks 
     * domain and path to decide the return.
     *
     * @return void
     * @access private
     */
    function _passCookies()
    {
        if (is_array($this->_cookies) && count($this->_cookies) > 0)
        {
            $urlParsed = parse_url($this->target);
            $tempCookies = array();
            
            foreach($this->_cookies as $cookie)
            {
                if ($this->_domainMatch($urlParsed['host'], $cookie['domain']) && (0 === strpos($urlParsed['path'], $cookie['path']))
                    && (empty($cookie['secure']) || $urlParsed['protocol'] == 'https')) 
                {
                    $tempCookies[$cookie['name']][strlen($cookie['path'])] = $cookie['value'];
                }
            }
            
            // cookies with longer paths go first
            foreach ($tempCookies as $name => $values) 
            {
                krsort($values);
                foreach ($values as $value) 
                {
                    $this->addCookie($name, $value);
                }
            }
        }
    }
    
    /**
    * Checks if cookie domain matches a request host (internal)
    * 
    * Cookie domain can begin with a dot, it also must contain at least
    * two dots.
    * 
    * @param string Request host
    * @param string Cookie domain
    * @return bool Match success
     * @access private
    */
    function _domainMatch($requestHost, $cookieDomain)
    {
        if ('.' != $cookieDomain{0}) 
        {
            return $requestHost == $cookieDomain;
        } 
        elseif (substr_count($cookieDomain, '.') < 2) 
        {
            return false;
        } 
        else 
        {
            return substr('.'. $requestHost, - strlen($cookieDomain)) == $cookieDomain;
        }
    }
    
    /**
     * Tokenize String (internal)
     * 
     * Tokenize string for various internal usage. Omit the second parameter 
     * to tokenize the previous string that was provided in the prior call to 
     * the function.
     *
     * @param string The string to tokenize
     * @param string The seperator to use
     * @return string Tokenized string
     * @access private
     */
    function _tokenize($string, $separator = '')
    {
        if(!strcmp($separator, ''))
        {
            $separator = $string;
            $string = $this->nextToken;
        }
        
        for($character = 0; $character < strlen($separator); $character++)
        {
            if(gettype($position = strpos($string, $separator[$character])) == "integer")
            {
                $found = (isset($found) ? min($found, $position) : $position);
            }
        }
        
        if(isset($found))
        {
            $this->nextToken = substr($string, $found + 1);
            return(substr($string, 0, $found));
        }
        else
        {
            $this->nextToken = '';
            return($string);
        }
    }
    
    /**
     * Set error message (internal)
     *
     * @param string Error message
     * @return string Error message
     * @access private
     */
    function _setError($error)
    {
        if ($error != '')
        {
            $this->error = $error;
            return $error;
        }
    }
}

?>