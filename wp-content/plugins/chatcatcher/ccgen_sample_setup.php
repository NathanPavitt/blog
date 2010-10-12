<?php
//*****************************************************************************
//* Chat Catcher Sample Setup Script
//* Use this script as an example for creating a custom blog setup page.
//* Write your own custom script for your blog platform.
//*****************************************************************************
//
//Include all of the default variables from the main CC script.
//
//Don't show the Chat Catcher page.
$pageShow = 'N';
if(!function_exists('ccTrackBack'))
{
    include_once('chatcatcher.php');
}

//*****************************************************************************
//* cc_plugin_blog_registration - Sample function to register a blog.
//*****************************************************************************
function cc_plugin_blog_registration() {

    //You can pull these from your database and override them before
    //calling this function.
    global $cc_secret, $blog_home, $admin_email;

    $home = $blog_home;
    //*****************************************************************************
    //Continue to use ChatCatcher.php as the main interface to the Chat Catcher server.
    //Use a "cc_plugin.php" file for custom comment handling.
    //*****************************************************************************
    $plugin_url = str_replace(basename(__FILE__),'chatcatcher.php',cc_selfURL());
    
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

//The "Activate This Script" link was clicked
if(isset($_GET['custom_blog_activate']))
{
    cc_plugin_blog_registration();
}


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html><head><title>Chat Catcher</title></head><body>
<div style="font-family:Arial"><h1>Chat Catcher</h1>
<p><a href="ccgen_sample_setup.php?custom_blog_activate=1">Activate This Script</a></p>
<p><a href="http://www.chatcatcher.com/tester.aspx?&a=<?php echo cc_selfURL() ?>">Test This Script</a></p>
<p>&nbsp;</p><p><strong>System Information</strong>
<br/>Version: <?php echo $ccVersion ?><br/>
</p>
<p><a href="http://www.chatcatcher.com">Chat Catcher</a></p>
</div>
</body></html>