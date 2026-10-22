<?php 

/***
 * Plugin Name: Simple Notice
 * Description: Displays a custom notice box using a shortcode
 * Version: 1.0
 * Author: Makitweb
 */

function show_notice_output(){
	return '<div style="background:#fff3cd; border:1px solid #ffc107;
		padding:15px; border-radius:5px;"><strong>Notice:</strong> This is a custom notice from my plugin.</div»';
}

add_shortcode('show_notice','show_notice_output');











