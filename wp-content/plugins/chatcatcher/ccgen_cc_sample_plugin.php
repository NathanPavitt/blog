<?php
//*****************************************************************************
//* Chat Catcher will include every file that ends in 'cc_plugin.php'
//* Example:  coolblogplatform_cc_plugin.php
//* Copy this file and remove "sample_" from the name.  You can also
//* change "ccgen" in the name to something descriptive.
//*****************************************************************************

//*****************************************************************************
//* Add plugin functions to the global array.
//* This array will be used by ChatCatcher.php to load plugin functions.
//*****************************************************************************
//* - Each function in the array will be called separately.
//* - Add your function to the array using array_push().
//* - Create multiple files or add multiple functions to the same file.
//*
//* Example:  array_push($cc_plugins,'my_unique_function_name');
//*
//*****************************************************************************
array_push($cc_plugins,'cc_plugin');


//*****************************************************************************
//* Sample Plugin function that is called after Chat Catcher posts a new comment
//* to the ChatCatcher.php script.
//*****************************************************************************
function cc_plugin($title, $excerpt, $url, $blog_name, $tb_url, $pic, $profile_link)
{
            //Standard Trackback
            $title = urlencode(stripslashes($title));
            $excerpt = urlencode(stripslashes($excerpt));
            $url = urlencode($url);
            $blog_name = urlencode(stripslashes($blog_name));

            //Create a new comment using the trackback variables.
}
?>