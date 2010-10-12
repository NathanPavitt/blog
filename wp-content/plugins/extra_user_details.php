<?php
/*
Plugin Name: Extra User Details
Plugin URI: http://vadimk.com/wordpress-plugins/extra-user-details/
Description: Allows you to add additional fields to the user profile like Facebook, Twitter etc.
Author: Vadim Khukhryansky
Version: 0.1
Author URI: http://vadimk.com/
*/

if(!get_option('eudfields')) add_option('eudfields', '');

add_action('edit_user_profile', 'eudextract_ExtraFields');
add_action('show_user_profile', 'eudextract_ExtraFields');
add_action('profile_update', 'eudupdate_ExtraFields');

function eudextract_ExtraFields() {
	if(get_option('eudfields')){
		
		$all_fields = unserialize(get_option('eudfields'));

		if(count($all_fields) > 0){
	
			echo '<h3>Extra User Details</h3>
					<table class="form-table">';
	
			foreach ($all_fields as $key => $value) {
			
			echo '<tr>
					<th><label for="eud'.$value[1].'">'.$value[0].'</label></th>
						<td><input name="eud'.$value[1].'" id="eud'.$value[1].'" type="text" value="'.get_usermeta(get_user_id(),$value[1]).'" class="regular-text code" />&nbsp;<span class="description">Fill this additional field to your profile.</span></td>
				</tr>';
			}
		echo '</table>';
		}
		
	}
}

function eudupdate_ExtraFields() {
	$get_user_id = get_user_id();
	foreach ($_POST as $key => $value) {
		if(eregi('eud',$key)){
			$key = str_replace('eud','',$key);
			if(!empty($value))update_value_sef($get_user_id,$key,$value);
			else delete_value_sef($get_user_id,$key,$value);
			}
		}
	}

function update_value_sef($get_user_id,$eudfield,$value){
	update_usermeta($get_user_id,str_replace('eud','',$eudfield),$value);
	}
	
function delete_value_sef($get_user_id,$eudfield,$value){
	delete_usermeta($get_user_id,str_replace('eud','',$eudfield),$value);
	}

function get_user_id(){
	$get_user_id = empty($_GET['user_id'])?null:$_GET['user_id'];
	if(isset($get_user_id)){}
	else{
		global $current_user;
		get_currentuserinfo();
		$get_user_id = $current_user->ID ;
		}
	return $get_user_id;
	}

//Administration
add_action('admin_menu', 'eudplugin_menu');

function eudplugin_menu() {
  add_options_page('Extra User Details Options', 'Extra User Details', 8, 'extra_user_details', 'eudplugin_options');
}

function eudplugin_options() {
	if(!empty($_POST)){
		$all_fields = array();
		foreach ($_POST as $key => $value) {
			if($key !== 'eudoptions' && $key !== 'submit' && !empty($value))
				if(!isset($_POST['delete_'.str_replace(' ','-',strtolower(trim($value)))])){
					$item = array($value,str_replace(' ','-',strtolower(trim($value))));
					array_push($all_fields, $item);
				}
			}
		
		if(count($all_fields) > 0) {
			update_option('eudfields',serialize($all_fields));
			}
		else {
			update_option('eudfields','');
			}
	}
		
	echo '<form action="" method="post">';
	echo '<div class="wrap"><div class=icon32 id=icon-options-general><br /></div>';
	echo '<h2>Extra User Details Options</h2>';
	if(get_option('eudfields')) $all_fields = unserialize(get_option('eudfields'));
	echo '<p>
	<form action="" method="post">
	New Extra Field: <input name="new_field" type="text" class="regular-text" />
	<input name="eudoptions" type="hidden" value="1" />
	<input name="submit" type="submit" value="Add" /><br />
	';
	
	if(count($all_fields) > 0){
		foreach ($all_fields as $key => $value) {
			echo '<br /><input name="'.$value[1].'" type="text" value="'.$value[0].'" class="regular-text" size="50" />&nbsp;<span class="description">Slug: '.$value[1].'</span><label style="margin-left:20px;font-size:10px;"> Delete this field: <input name="delete_'.$value[1].'" type="checkbox" value="" /></label>';
			}		
		}
echo '</p></div><br />
<input name="submit" type="submit" value="Update Fields" /></form>';
}

?>
