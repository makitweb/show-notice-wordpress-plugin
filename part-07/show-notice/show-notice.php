<?php 

/***
 * Plugin Name: Simple Notice
 * Description: Displays a custom notice box using a shortcode
 * Version: 1.0
 * Author: Makitweb
 */

if(!class_exists('WP_List_Table')){
	require_once ABSPATH.'wp-admin/includes/class-wp-list-table.php';
}

class Simple_Notice_List_Table extends WP_List_Table{

	public function get_columns(){
		return [
			'title' => 'Title',
			'message' => 'Message',
			'type' => 'Type',
			'created_at' => 'Date'
		];
	}

	public function prepare_items(){
		global $wpdb;

		$table_name = $wpdb->prefix."simple_notices";

		$this->items = $wpdb->get_results(
			"SELECT * FROM $table_name ORDER BY id DESC"
		);

		$this->_column_headers = [
			$this->get_columns(),
			[],
			[],
		];
	}

	public function column_default($item, $column_name){
		if($column_name === 'created_at'){
			return esc_html(
				date_i18n(
					get_option('date_format'),
					strtotime($item->created_at)
				)
			);
		}

		return esc_html($item->$column_name);
	}
}

register_activation_hook(__FILE__,'simple_notice_activate');

function simple_notice_activate(){
	simple_notice_create_table();
	simple_notice_insert_sample_data();
}

function simple_notice_create_table(){
	global $wpdb;
	$table_name = $wpdb->prefix."simple_notices";

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name(
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		title VARCHAR(255) NOT NULL,
		message TEXT NOT NULL,
		type VARCHAR(50) NOT NULL DEFAULT 'info',
		created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id)
	) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta($sql);

}

function simple_notice_insert_sample_data(){
	global $wpdb;
	$table_name = $wpdb->prefix."simple_notices";

	$existing_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

	if($existing_count > 0){
		return;
	}

	$wpdb->insert($table_name, [
		'title' => 'Welcome Notice',
		'message' => 'Thank you for using Simple Notice plugin.',
		'type' => 'info',
	]);

	$wpdb->insert($table_name, [
		'title' => 'Success Notice',
		'message' => 'Your settings have been saved successfully.',
		'type' => 'success',
	]);

	$wpdb->insert($table_name, [
		'title' => 'Warning Notice',
		'message' => 'Please review your settings before publishing.',
		'type' => 'warning',
	]);
}


function show_notice_output(){

	global $wpdb;
	$table_name = $wpdb->prefix."simple_notices";

	$notice = $wpdb->get_row("SELECT * FROM $table_name ORDER BY id DESC LIMIT 1");

	if(!$notice){
		return '<p>No notice found.</p>';
	}

	$message = get_option('simple_notice_message','This is a custom notice');
	$type = get_option('simple_notice_type','info');

	$colors = [
		'info' => ['bg' => '#cfe2ff', 'border' => '#084298'],
		'success' => ['bg' => '#d1e7dd', 'border' => '#0f5132'],
		'warning' => ['bg' => '#fff3cd', 'border' => '#664d03'],
	];

	if(!isset($colors[$type])){
		$type = 'info';
	}

	$bg = $colors[$notice->type]['bg'] ?? $colors['info']['bg'];
	$border = $colors[$notice->type]['border'] ?? $colors['info']['border'];

	return '<div style="background: '.$bg.'; border: 1px solid '.$border.'; padding: 15px; border-radius:5px; "><strong>'.esc_html($notice->title).': </strong>'.esc_html($notice->message).'</div>';
}

add_shortcode('show_notice','show_notice_output');

function simple_notice_menu(){
	add_menu_page(
		'Simple Notice Settings', // Page title
		'Simple Notice', // Menu title
		'manage_options', // Capability
		'simple-notice', // Slug
		'simple_notice_settings_page', // Callback function
		'dashicons-admin-generic',
		80
	);

	

	add_submenu_page(
		'simple-notice',
		'Settings',
		'Settings',
		'manage_options',
		'simple-notice',
		'simple_notice_settings_page'
	);

	add_submenu_page(
		'simple-notice',
		'Help',
		'Help',
		'manage_options',
		'simple-notice-help',
		'simple_notice_help_page'
	);

	add_submenu_page(
		'simple-notice',
		'All Notices',
		'All Notices',
		'manage_options',
		'simple-notice-list',
		'simple_notice_list_page'
	);

	add_submenu_page(
	    'simple-notice',
	    'Add New Notice',
	    'Add New',
	    'manage_options',
	    'simple-notice-add',
	    'simple_notice_add_page'
	);

}

add_action('admin_menu','simple_notice_menu');

function simple_notice_list_page(){
	$table = new Simple_Notice_List_Table();
	$table->prepare_items();

	?>
	<div class="wrap">
		<h1>All Notices</h1>
		<form method="post">
			<?php $table->display(); ?>
		</form>
	</div>
	<?php 
}

function simple_notice_add_page() {
    ?>
    <div class="wrap">
        <h1>Add New Notice</h1>
        <form method="post" action="">
            <?php wp_nonce_field( 'simple_notice_save', 'simple_notice_nonce' ); ?>
            <table class="form-table">
                <tr>
                    <th><label for="title">Title</label></th>
                    <td><input type="text" name="title" id="title" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="message">Message</label></th>
                    <td><textarea name="message" id="message" rows="4" class="large-text" required></textarea></td>
                </tr>
                <tr>
                    <th><label for="type">Type</label></th>
                    <td>
                        <select name="type" id="type">
                            <option value="info">Info</option>
                            <option value="success">Success</option>
                            <option value="warning">Warning</option>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button( 'Save Notice' ); ?>
        </form>
    </div>
    <?php
}

function simple_notice_handle_save() {
    if ( ! isset( $_POST['simple_notice_nonce'] ) ) {
        return;
    }

    if ( ! wp_verify_nonce( $_POST['simple_notice_nonce'], 'simple_notice_save' ) ) {
        wp_die( 'Security check failed.' );
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'You do not have permission to do this.' );
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'simple_notices';

    $title   = sanitize_text_field( $_POST['title'] );
    $message = sanitize_textarea_field( $_POST['message'] );
    $type    = sanitize_text_field( $_POST['type'] );

    $wpdb->insert( $table_name, [
        'title'   => $title,
        'message' => $message,
        'type'    => $type,
    ]);

    wp_redirect( admin_url( 'admin.php?page=simple-notice-list' ) );
    exit;
}
add_action( 'admin_init', 'simple_notice_handle_save' );

function simple_notice_help_page(){
	?>
	<div class="wrap">
		<h1>Simple Notice - Help</h1>
		<p>Use the shortcode <strong>[show_notice]</strong> on any page or post.</p>
		<p>Go to <a href="<?= esc_url(admin_url('admin.php?page=simple-notice')) ?>">Settings</a> to change the message and type</p>
	</div>
	<?php
}

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











