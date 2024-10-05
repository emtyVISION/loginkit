<?php
/**
 * Plugin Name: LoginKit
 * Plugin URI:  https://emty.vision
 * Description: Customise your WP-Login.
 * Version:     1.0
 * Author:      emtyVISION
 * Author URI:  https://emty.vision
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Add the menu item under "Settings" in the admin dashboard
add_action('admin_menu', 'custom_login_url_menu');
function custom_login_url_menu() {
    add_options_page(
        'Login URL',           // Page title
        'Login URL',                      // Menu title
        'manage_options',             // Capability
        'login-url',           // Menu slug
        'custom_login_url_settings'   // Callback function
    );
}

// Render the settings page
function custom_login_url_settings() {
    ?>
    <div class="wrap">
        <h1>Login URL</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('custom_login_url_settings_group');
            do_settings_sections('login-url');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register and define the setting
add_action('admin_init', 'custom_login_url_settings_init');
function custom_login_url_settings_init() {
    register_setting('custom_login_url_settings_group', 'custom_login_url');

    add_settings_section(
        'custom_login_url_section',
        'Login URL Settings',
        'custom_login_url_section_callback',
        'login-url'
    );

    add_settings_field(
        'custom_login_url_field',
        'Login URL',
        'custom_login_url_field_callback',
        'login-url',
        'custom_login_url_section'
    );
}

// Section callback
function custom_login_url_section_callback() {
    echo '<p>Enter the custom URL for your login page. This will replace the default /wp-login.php URL.</p>';
}

// Field callback
function custom_login_url_field_callback() {
    $login_url = get_option('custom_login_url', 'login');
    echo '<input type="text" id="custom_login_url" name="custom_login_url" value="' . esc_attr($login_url) . '" />';
}

// Redirect to the home URL if visiting /wp-admin and not logged in
add_action('init', 'custom_login_url_redirect');
function custom_login_url_redirect() {
    $new_login_url = get_option('custom_login_url', 'login');

    // If the user is trying to access the default login URL
    if ($_SERVER['REQUEST_URI'] == '/wp-login.php' && !is_admin()) {
        if (!isset($_POST['log'])) { // If there's no login form submission
            wp_redirect(home_url('/'));
            exit();
        }
    }

    // Redirect to home if visiting /wp-admin and not logged in
    if (strpos($_SERVER['REQUEST_URI'], '/wp-admin') !== false && !is_user_logged_in()) {
        wp_redirect(home_url());
        exit();
    }
}



// Handle the new login URL
add_action('template_redirect', 'custom_login_url_handle');
function custom_login_url_handle() {
    $new_login_url = get_option('custom_login_url', 'login');

    // If the user is visiting the new login URL, load the default login page
    if (trim($_SERVER['REQUEST_URI'], '/') === trim($new_login_url, '/')) {
        require_once ABSPATH . 'wp-login.php';
        exit();
    }
}

// Ensure WordPress can handle login POST requests to the custom login URL
add_action('login_init', 'custom_login_url_login_post_handler');
function custom_login_url_login_post_handler() {
    $new_login_url = get_option('custom_login_url', 'login');
    
    // If the user is submitting the login form from the custom URL, process the login
    if (trim($_SERVER['REQUEST_URI'], '/') === trim($new_login_url, '/')) {
        global $pagenow;
        $pagenow = 'wp-login.php';
    }
}

?>

<?php

// Add menu under Settings
add_action('admin_menu', 'clp_create_menu');

function clp_create_menu() {
    add_options_page(
        'Login Appearance', // Page title
        'Login Appearance', // Menu title
        'manage_options', // Capability
        'login-page-appearance', // Menu slug
        'clp_settings_page' // Callback function
    );
}

// Register settings
add_action('admin_init', 'clp_register_settings');

function clp_register_settings() {
    register_setting('clp-settings-group', 'clp_logo');
    register_setting('clp-settings-group', 'clp_bg_color');
    register_setting('clp-settings-group', 'clp_bg_image');
    register_setting('clp-settings-group', 'clp_box_color');
	register_setting('clp-settings-group', 'clp_button_color');
}

// Create settings page HTML
function clp_settings_page() {
    ?>
    <div class="wrap">
        <h1>Login Page Appearance</h1>
        <form method="post" action="options.php" enctype="multipart/form-data">
            <?php settings_fields('clp-settings-group'); ?>
            <?php do_settings_sections('clp-settings-group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Login Logo (URL):</th>
                    <td><input type="text" name="clp_logo" value="<?php echo esc_attr(get_option('clp_logo')); ?>" placeholder="Logo URL" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Background Color:</th>
                    <td><input type="text" name="clp_bg_color" value="<?php echo esc_attr(get_option('clp_bg_color')); ?>" placeholder="#ffffff" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Background Image (URL):</th>
                    <td><input type="text" name="clp_bg_image" value="<?php echo esc_attr(get_option('clp_bg_image')); ?>" placeholder="Background Image URL" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Login Form Color:</th>
                    <td><input type="text" name="clp_box_color" value="<?php echo esc_attr(get_option('clp_box_color')); ?>" placeholder="#ffffff" /></td>
                </tr>
				<tr valign="top">
                    <th scope="row">Login Button Color:</th>
                    <td><input type="text" name="clp_button_color" value="<?php echo esc_attr(get_option('clp_button_color')); ?>" placeholder="#ffffff" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

add_action( 'admin_init', function() {
    if ( !is_user_logged_in() || current_user_can( 'subscriber' ) ) {
        wp_redirect( home_url() );
        exit;
    }
} );



add_filter( 'show_admin_bar', function( $show ) {
    if ( !is_user_logged_in() || current_user_can( 'subscriber' ) ) {
        return false;
    }
    return true;
});


// Enqueue custom login styles
add_action('login_enqueue_scripts', 'clp_custom_login_styles');

function clp_custom_login_styles() {
    $logo = esc_url(get_option('clp_logo'));
    $bg_color = esc_attr(get_option('clp_bg_color', '#ffffff'));
    $bg_image = esc_url(get_option('clp_bg_image'));
    $box_color = esc_attr(get_option('clp_box_color', '#ffffff'));
	$button_color = esc_attr(get_option('clp_button_color', '#000'));
    ?>
    <style type="text/css">
        body.login {
            <?php if ($bg_color) : ?>
                background-color: <?php echo $bg_color; ?>;
            <?php endif; ?>
            <?php if ($bg_image) : ?>
                background-image: url('<?php echo $bg_image; ?>');
                background-size: cover;
                background-position: center;
            <?php endif; ?>
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .login {
            width: 100%;
            
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
		.login #language-switcher {
			display: none !important;
		}
		input.button-primary {
            background: <?php echo $button_color; ?> !important;
			border-radius: 0px !important;
			border: none !important;
        }
        .login h1 {
            width: 100%;
            display: flex;
            justify-content: center;
            margin-bottom: 20px; /* Add some space below the logo */
        }
        .login h1 a {
            <?php if ($logo) : ?>
                background-image: url('<?php echo $logo; ?>') !important;
            <?php endif; ?>
            width: 100% !important;
            max-width:150px;
			max-height: 84px !important;
            background-size: contain !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
        }
        .login form {
            <?php if ($box_color) : ?>
                background-color: <?php echo $box_color; ?> !important;
            <?php endif; ?>
            padding: 26px 24px !important;
            box-shadow: none !important;
            border-radius: 0px !important;
			border: none !important;
            
            box-sizing: border-box;
			display: block;
            margin: 0 auto;
        }
        .login #login_error, .login .message {
            max-width: 320px;
            margin: 0 auto 16px;
        }
    </style>
    <?php
}


