<?php 

/***
 * Plugin Name: Simple Notice
 * Description: Displays a custom notice box using a shortcode
 * Version: 1.0
 * Author: Makitweb
 */

function show_notice_output(){
	$message = get_option('simple_notice_message','This is a custom notice');
	$type = get_option('simple_notice_type', 'info') ;

	$colors = [
		'info' => ['bg' => '#cfe2ff', 'border' => '#084298'],
		'success' => ['bg' => '#d1e7dd', 'border' => '#0f5132'],
		'warning' => ['bg' => '#fff3cd', 'border' => '#664d03'],
	];

	if(!isset($colors [$type])){
		$type = 'info';
	}

	$bg = $colors[$type]['bg'];
	$border = $colors[$type]['border'];

	return '<div style="background: '.$bg.'; border: 1px solid '.$border.'; padding: 15px; border-radius:5px; "><strong>NOTICE: </strong>'.esc_html($message).'</div>';
}

add_shortcode('show_notice','show_notice_output');

function simple_notice_menu() {
	add_options_page(
		'Simple Notice Settings', // Page title
		'Simple Notice', // Menu title
		'manage_options', // Capability
		'simple-notice', // Slug
		'simple_notice_settings_page' // Callback function
	);
}

add_action('admin_menu', 'simple_notice_menu');

function simple_notice_sanitize_type($value){
	$allowed_types = ['info','success','warning'];

	return in_array($value, $allowed_types,true) ? $value : 'info';
}

function simple_notice_settings_init(){
	register_setting(
		'simple_notice_group',
		'simple_notice_message',
		[
			'sanitize_callback' => 'sanitize_text_field'
		]
	);

	register_setting(
		'simple_notice_group',
		'simple_notice_type',
		[
			'sanitize_callback' => 'simple_notice_sanitize_type'
		]
	);

	add_settings_section(
		'simple_notice_section',
		'Notice Settings',
		null,
		'simple-notice'
	);

	add_settings_field(
		'simple_notice_message',
		'Notice Message',
		'simple_notice_message_field',
		'simple-notice',
		'simple_notice_section'
	);

	add_settings_field(
		'simple_notice_type',
		'Notice Type',
		'simple_notice_type_field',
		'simple-notice',
		'simple_notice_section'
	);
}

add_action('admin_init','simple_notice_settings_init');

function simple_notice_message_field(){
	$value = get_option(
		'simple_notice_message',
		'This is a custom notice'
	);

	echo '<input type="text" name="simple_notice_message" value="'.esc_attr($value).'" class="regular-text">';
}

function simple_notice_type_field(){
	$value = get_option(
		'simple_notice_type',
		'info'
	); 

	$types = [
		'info' => 'Info',
		'success' =>'Success',
		'warning' => 'Warning'
	];

	echo '<select name="simple_notice_type" > ';

	foreach($types as $key=>$label){
		$selected = selected($value, $key, false );

		echo '<option value="'.esc_attr($key).'" '.$selected.' >'.esc_html($label).'</option>';
	}

	echo '</select>';
}

function simple_notice_settings_page(){
	?>
	<div class="wrap">
		<h1>Simple Notice Settings</h1>
		<form method="post" action="options.php">
			<?php 
			settings_fields('simple_notice_group');

			do_settings_sections('simple-notice');

			submit_button();
			?>
		</form>
	</div>
	<?php
}






