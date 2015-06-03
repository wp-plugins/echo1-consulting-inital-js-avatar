<?php
/*
Plugin Name: Echo1 Consulting - Inital JS Avatar
Plugin URI: http://www.echo1consulting.com/
Description: Simple jQuery plugin to make gmail-like text avatars for profile pictures. These avatars can be scaled up to any size as they are SVG based.
Author: Echo1 Consulting
Version: 1.0
Author URI: http://www.echo1consulting.com/
*/
 
if( !defined('ABSPATH') ) die();
 
// A general plugin slug (used for prefixes and text domain)
if( !defined('ECHO1_IJSA_PLUGIN_SLUG') ) define( 'ECHO1_IJSA_PLUGIN_SLUG', 'e1ijsa' );

// The full url to the plugin directory (ends with trailing slash)
if( !defined('ECHO1_IJSA_PLUGIN_DIR_URL') ) define( 'ECHO1_IJSA_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );

// The full path to the plugin directory (ends with trailing slash)
if( !defined('ECHO1_IJSA_PLUGIN_DIR_PATH') ) define( 'ECHO1_IJSA_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );

// The full path to this file
if( !defined('ECHO1_IJSA_PLUGIN_MAIN_FILE') ) define( 'ECHO1_IJSA_PLUGIN_MAIN_FILE', __FILE__ );

class Echo1InitialJSAvatar {
	
	private $avatar_setting = array( 'initialjs' => 'Initial JS (Generated)' );

	private $avatar_setting_key = '';
	
	private $default_character_count = 1;
	
	public function __construct() {

		$this->initialize_class();
		
		$this->initialize_hooks();
		
	}
	
	/**
	 * Initialize the class
	 * @category function
	 */
	public function initialize_class() {

		reset( $this->avatar_setting );
		
		$this->avatar_setting_key = key( $this->avatar_setting );

	}
	
	/**
	 * Initialize the hooks
	 * @category function
	 */
	public function initialize_hooks() {
		
		add_filter( 'get_avatar', array( &$this, 'get_avatar_filter' ), 15, 5 );
				
		add_filter( 'avatar_defaults', array( &$this, 'avatar_defaults_filter' ), 15, 1 );
		
		add_action( 'wp_enqueue_scripts', array( &$this, 'wp_enqueue_scripts_action' ), 15 );

		add_action( 'admin_enqueue_scripts', array( &$this, 'wp_enqueue_scripts_action' ), 15 );

		add_action( 'wp_footer', array( &$this, 'wp_footer' ), 15 );
		
		add_action( 'admin_footer', array( &$this, 'wp_footer' ), 15 );
				
		register_activation_hook( ECHO1_IJSA_PLUGIN_MAIN_FILE, array(&$this, 'plugin_activation') );

		register_deactivation_hook( ECHO1_IJSA_PLUGIN_MAIN_FILE, array(&$this, 'plugin_deactivation') );
		
	}
	
	/**
	 * Functions to run on activation
	 * @category hook
	 */
	public function plugin_activation() {
		
		// Update the default avatar format
		update_option( 'avatar_default', $this->avatar_setting_key );
		
	}
	
	
	/**
	 * Functions to run on deactivation
	 * @category hook
	 */	
	 public function plugin_deactivation() {
		
		// Update the default avatar format
		update_option( 'avatar_default', 'identicon' );
		
	}
	
	/**
	 * Filter the avatar to retrieve.
	 *
	 * @param string            $avatar      &lt;img&gt; tag for the user's avatar.
	 * @param int|object|string $id_or_email A user ID, email address, or comment object.
	 * @param int               $size        Square avatar width and height in pixels to retrieve.
	 * @param string            $alt         Alternative text to use in the avatar image tag.
	 *                                       Default empty.
	 * @param array             $args        Arguments passed to get_avatar_data(), after processing.
	 * @category hook
	 */
	public function get_avatar_filter( $avatar, $id_or_email, $size, $default, $alt = '' ) {

		// Initialize the user variable 
		$user = null;
		
		// Initialize the comment variable
		$comment = null;
		
		// Initialize the data name
		$data_name = '';
		
		// If the default avatar is the initialjs
		if( $default === $this->avatar_setting_key ) {

			// If this is a comment object
			if( is_object( $id_or_email ) ) {
			
				// Set the comment object
				$comment = $id_or_email;
				
				// If the comment author is set
				if( !empty( $id_or_email->comment_author ) ) {
					
					// Set the data name
					$data_name = $id_or_email->comment_author;
	
				// If the comment author email is set
				} else {
					
					$data_name = 'Unknown';
					
				}
	
			}
			
			// If this is an integer
			if( is_numeric( $id_or_email ) ) {
				
				// Get the user by id
				$user = get_user_by( 'id', (int) $id_or_email );
				
			// If this is an email
			} elseif ( is_string( $id_or_email ) ) {
				
				// Get the user by email
				$user = get_user_by( 'email', $id_or_email );
				
			// If this is a comment object try and get the user by email
			} elseif( is_string( $id_or_email->comment_author_email ) ) {
				
				$user = get_user_by( 'email', $id_or_email->comment_author_email );
				
			}

			// If the user is an instance of \WP_User
			if ( ( $user instanceof \WP_User ) ) {
				
				// Set the data name to the users display name
				$data_name = $user->display_name;
			
			} else {
				
				$user = null;
				
			}

			// Set default img attributes
			$img_attributes = array( 'alt' 				=> $data_name,								// The users name (alt)
									 'class'			=> "avatar " . $this->avatar_setting_key,	// The image name
									 'data-name'		=> $data_name,								// The users name (used for initial)
									 'data-char-count' 	=> $this->default_character_count,			// The character count	
									 'data-bg-color'	=> '',										// The avatar background color		
									 'data-text-color'	=> '#ffffff',								// The initial text color
									 'data-font-size'	=> ceil( $size * .75 ),						// The initial font size (percent or whole number)
									 'data-font-weight'	=> '400',									// The font weight
									 'data-height'		=> $size,									// The image height
									 'data-width'		=> $size,									// The image width
									 'height'			=> $size,									// The image height
									 'width'			=> $size,									// The image width

								   );
			
			// Apply filters to image attributes ( e1ijsa_image_attributes )
			$img_attributes = apply_filters( ECHO1_IJSA_PLUGIN_SLUG . '_image_attributes', $img_attributes, $user, $comment );
			
			/**
			 * Example use of filter
			 * @category hook
			 */			
			// add_filter( 'e1ijsa_image_attributes', function( $img_attributes, $user, $comment ) {
			// 
			// 	return $img_attributes;
			// 	
			// }, 10, 3 );

			// Initialize $img_attributes_string
			$img_attributes_string = '';
			
			// For each $img_attributes
			foreach ( $img_attributes as $img_attribute_key => $img_attribute_value ) {
				
				// Build the $img_attributes_string
				$img_attributes_string .= "$img_attribute_key='$img_attribute_value' ";
				
			}

			// Return the specific image tag, embedding $img_attributes_string
			return "<img $img_attributes_string/>" ;
			
		}

		return $avatar;
		
	}
	
	/**
	 * Filter the default avatars.
	 *
	 * Avatars are stored in key/value pairs, where the key is option value,
	 * and the name is the displayed avatar name.
	 *
	 * @param array $avatar_defaults Array of default avatars.
	 * @category hook
	 */
	public function avatar_defaults_filter( $avatar_defaults ) {
		
		return $avatar_defaults + $this->avatar_setting;

	}
	
	/**
	 * Enqueue the initial js scripts
	 * @return null
	 * @category hook
	 */
	public function wp_enqueue_scripts_action() {
		
		wp_enqueue_script( ECHO1_IJSA_PLUGIN_SLUG . '-initialjs' , ECHO1_IJSA_PLUGIN_DIR_URL . 'assets/js/initial.min.js', array('jquery'), '1.0.0' );

	}

	/**
	 * Print the footer init javascript 
	 * @return null
	 * @category hook
	 */
	public function wp_footer() {
		
		echo "	<script>
					jQuery(document).ready(function ($) {
				 		$('.{$this->avatar_setting_key}').initial(); 
					});
				</script>";
	}
	
}

// Initialize the WordPressLoader class
new Echo1InitialJSAvatar();