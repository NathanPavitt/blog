<?php
/*
Plugin Name: Olympics Countdown
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: Plugin for the olympic countdown
Version: 0.1
Author: Nigel McHugh
Author URI: http://URI_Of_The_Plugin_Author
*/


function dateDiffToday($withDate) {
	
	$today = date("m/d/Y");
	
	$date1 = explode("/", $today);
	$date2 = explode("/", $withDate);
	$start=gregoriantojd($date1[0], $date1[1], $date1[2]);
	$end=gregoriantojd($date2[0], $date2[1], $date2[2]);

	return $end - $start;
}

function summmerOlympicsCountdown() {
	$olympicsStartDate="07/27/2012";
	return dateDiffToday($olympicsStartDate);
}

function paraOlympicsCountdown() {
	$paraOlympicsStartDate="08/29/2012";
	return dateDiffToday($paraOlympicsStartDate);
}

/*
add_action(get_sidebar, summerOlympicsCountdown );
add_action(get_sidebar, paraOlympicsCountdown );
*/
?>