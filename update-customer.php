<?php
/**
* Plugin Name: Update customer
* Plugin URI:  https://wp-experts.gr/en/wordpress-plugins/update-customer-wordpress-plugin/
* Description: Useful for freelancers or agencies that need to inform their customers automatically when maintenance is being carried out to their websites.
* Version:     1.0.1
* Author:      Konstantinos Sofianos
* Author URI:  https://wp-experts.gr/en/kostas-sofianos/
* Text Domain: update-customer
* Domain Path: /languages
* License:     GPL2

Update customer is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Update customer is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Update customer. If not, see https://opensource.org/licenses/category.
*/

defined( 'ABSPATH' ) or die( 'Nope, not accessing this' );

function sokoEnqueueScripts() {
	wp_enqueue_style( 'mystyles', plugins_url('assets/styles.css', __FILE__ ) );
	wp_enqueue_script( 'myscripts', plugins_url('assets/scripts.js', __FILE__ ) );
}

add_action( 'admin_enqueue_scripts', 'sokoEnqueueScripts' );

if ( !class_exists( 'SokoUpdateCustomer' ) ) {

	class SokoUpdateCustomer {
	    public function __construct() {
		    // Hook into the admin menu
		    add_action( 'admin_menu', array( $this, 'sokoSettingsPage' ) );
		    add_action( 'admin_init', array( $this, 'sokoSetupSections' ) );
		    add_action( 'admin_init', array( $this, 'sokoSetupFields' ) );
		    add_action( 'upgrader_process_complete', array( $this, 'sokoSendEmail' ), 10, 2 );
		    add_action( 'admin_post_send_test_email', array( $this, 'sokoSendEmailTest' ) );
		    add_action( 'admin_init', array( $this, 'sokoTestEmailTransientTrush' ) );
		}

		

		// Add the menu item and page
		public function sokoSettingsPage() {
		    $page_title = 'Update customer';
		    $menu_title = 'Update customer';
		    $capability = 'manage_options';
		    $slug = 'update-customer';
		    $callback = array( $this, 'sokoSettingsPageContent' );
		    $icon = 'dashicons-email';
		    $position = 100;

		    add_submenu_page( 'options-general.php', $page_title, $menu_title, $capability, $slug, $callback );
		}

		public function sokoSettingsPageContent() { ?>
			<div class="wrap soko-wrap">
				<h1><?php echo __('Update customer settings', 'update-customer' ) ?></h1>
				<p><?php echo __('Build here the email content you want your customer to receive each time WordPress core is updated.', 'update-customer' ) ?></p>
				<form method="post" action="options.php">
					<?php
					settings_fields( 'update-customer' );
					do_settings_sections( 'update-customer' );
					submit_button();
					?>
				</form>
				<form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
				  <input type="hidden" name="action" value="send_test_email">
				  <input type="submit" class="button button-primary" value="Send Test Email">
				  <span class="save-first"><?php echo __( 'You have to save first', 'update-customer' ); ?></span>
				</form>
			</div>		
		    
		<?php 
		}

		public function sokoSetupSections() {
		    add_settings_section( 'email_section', 'Customer Email', false, 'update-customer' );
		    add_settings_section( 'subject_section', 'Email Subject', false, 'update-customer' );
		    add_settings_section( 'message_section', 'Email Message', false, 'update-customer' );
		}
		public function sokoSectionCallback( $arguments ) {
		    switch( $arguments['id'] ){
		        case 'email_section':
		        	echo "1.";
	            break;
	            case 'subject_section':
	        		echo "2.";
	            break;
		        case 'message_section':
		        	echo "3.";
	            break;
		    }
		}

		// Register and add settings
		public function sokoSetupFields() {
			register_setting( 'update-customer', 'message_field' );
			register_setting( 'update-customer', 'subject_field' );
			register_setting( 'update-customer', 'email_field' );
			add_settings_field( 
		    	'email_field',
		    	'Email',
		    	array( $this, 'sokoEmailFieldCallback' ),
		    	'update-customer',
		    	'email_section',
		    	array( 'label_for' => 'email_field' ) 
		    );
		    add_settings_field( 
		    	'subject_field',
		    	'Subject',
		    	array( $this, 'sokoSubjectFieldCallback' ),
		    	'update-customer',
		    	'subject_section',
		    	array( 'label_for' => 'subject_field' ) 
		    );
		    add_settings_field( 
		    	'message_field',
		    	'Message',
		    	array( $this, 'sokoMessageFieldCallback' ),
		    	'update-customer',
		    	'message_section',
		    	array( 'label_for' => 'message_field' ) 
		    );
		}


		// To, Subject and message fields
		public function sokoEmailFieldCallback( $arguments ) {
			$value = esc_attr( get_option( 'email_field' ) );
		    echo '<input name="email_field" id="email_field" class="soko-input" type="text" value="' . $value . '" />';
		}

		public function sokoSubjectFieldCallback( $arguments ) {
			$value = esc_attr( get_option( 'subject_field' ) );
		    echo '<input name="subject_field" id="subject_field" class="soko-input" type="text" value="' . $value . '" />';
		}

		public function sokoMessageFieldCallback( $arguments ) {
			global $allowedposttags;
			$value = wp_kses( get_option( 'message_field' ), $allowedposttags );
		    wp_editor( $value , 'message_field' );
		}


		//Send email when core is update
		function sokoSendEmail( $upgrader_object, $options ) {
			if( $options['action'] === 'update' && $options['type'] === 'core' ) {
				set_transient( 'core_is_updated_transient', 1 );
			}
			if( get_transient( 'core_is_updated_transient' ) ) {
				global $allowedposttags;
				$soko_the_mail = esc_attr( get_option( 'email_field' ) );
		     	$soko_the_subject = esc_attr( get_option( 'subject_field' ) );
		     	$soko_the_message_content = wp_kses( get_option( 'message_field' ), $allowedposttags);
		     	$soko_the_message = preg_replace("/\r\n|\r|\n/", '<br/>', $soko_the_message_content);
		     	$soko_the_headers = array('Content-Type: text/html; charset=UTF-8');
		     	wp_mail( $soko_the_mail, $soko_the_subject, $soko_the_message, $soko_the_headers);
				delete_transient( 'core_is_updated_transient' );
			}
		}

		// Send email for testing with a button
		function sokoSendEmailTest() {
			set_transient( 'soko_send_test_email', 1 );
			global $allowedposttags;
			$soko_the_mail = esc_attr( get_option( 'email_field' ) );
	     	$soko_the_subject = esc_attr( get_option( 'subject_field' ) );
	     	$soko_the_message_content = wp_kses( get_option( 'message_field' ), $allowedposttags);
	     	$soko_the_message = preg_replace("/\r\n|\r|\n/", '<br/>', $soko_the_message_content);
	     	$soko_the_headers = array('Content-Type: text/html; charset=UTF-8');
	     	wp_mail( $soko_the_mail, $soko_the_subject, $soko_the_message, $soko_the_headers);
	     	wp_redirect($_SERVER['HTTP_REFERER']);
		}

		// Set transient so a message can be printed after page reload. It will be refactored with ajax call
		function sokoTestEmailTransientTrush(){
			$soko_success_msg = __('Your test email has been sent.', 'update-customer');
			if( get_transient( 'soko_send_test_email' ) ) {
				add_settings_error( 'update-customer', 'update-customer', $soko_success_msg, 'updated' );
			}
			delete_transient( 'soko_send_test_email' );
		}

	}

	new SokoUpdateCustomer();

}