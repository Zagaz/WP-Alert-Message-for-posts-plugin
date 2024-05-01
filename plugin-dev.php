<?php
/*
Plugin Name: Alert Message
Author: André Ranulfo
Aurhor URI: https://www.linkedin.com/in/andre-ranulfo/
Version: 1.0
Description: Just for fun!
language Domain: alert-message
Domain Path: /languages
Plugin URI: https://github.com/Zagaz/WP-Alert-Message-for-posts-plugin
*/

if (!defined('ABSPATH')) {
    exit;
}
if (!defined('WPINC')) {
    exit;
}

$alertMessage = new AlertMessage();

class AlertMessage
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'admin_page'));
        add_action('admin_init', array($this, 'settings'));
        add_filter('the_content', array($this, 'ifWrap'));
        add_action('init', array($this, 'languages'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue'));
        add_action('admin_notices', array($this, 'display_admin_notice'));
        // error alert


    }
    // PLUGIN    ===========

    function ifWrap($content)
    {
        if (is_single() && is_main_query()) {
            return $this->createHTML($content);
        }
        //return $content;
    }
    function createHTML($content)
    {
        // Get the values from the database
        $headline       = get_option('amsg_headline')  ? get_option('amsg_headline')   : false;
        $location       = get_option('amsg_location')  ? get_option('amsg_location')   : false;
        $msgtype        = get_option('amsg_msgtype')   ? get_option('amsg_msgtype')  : '4';

        // Icons from font-awesome
        $icon_info = '<i class="fas fa-info-circle"></i>';
        $icon_waring = '<i class="fas fa-exclamation-triangle"></i>';
        $icon_bomb  = '<i class="fas fa-bomb"></i>';
        $icon_success = '<i class="fas fa-check-circle"></i>';

        // Icons and classes names
        switch ($msgtype) {
            case '1':
                $icon = $icon_info;
                $class = 'alert-info';
                $classBox = 'alert-box-info';
                break;
            case '2':
                $icon = $icon_waring;
                $class = 'alert-warning';
                $classBox = 'alert-box-warning';
                break;
            case '3':
                $icon = $icon_bomb;
                $class = 'alert-bomb';
                $classBox = 'alert-box-bomb';
                break;
            case '4':
                $icon = $icon_success;
                $class = 'alert-success';
                $classBox = 'alert-box-success';
                break;
        }
        // Render the HTML
        if ( $headline &&  $location && $msgtype) {

            $html = $this->alertHTML($class, $icon, $headline, $classBox);

            // match switch case
            switch ($location) {
                case '1':
                    return $content . $html;
                    break;
                case '2':
                    return $html . $content;
                    break;
                case '3':
                    return $html . $content . $html;
                    break;
                   
                default:
                    return $content;
            }

            return $html;
        }
    }

    function display_admin_notice()
    {
        if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e('Settings saved successfully!', 'alert-message'); ?></p>
            </div>
        <?php
        }
    }

    function alertHTML($class, $icon, $headline, $classBox)
    {
        $html = "<div class='alert-message $classBox wrap'>";
        $html .= "<div class='alert-message-icon $class'>";
        $html .= $icon;
        $html .= '</div>';
        if ($headline) {
            $html .= "<div class = 'alert-message-text'>$headline</div>";
        }
        $html .= '</div>';
        return  $html;
    }




    function languages()
    {
        load_plugin_textdomain('alert-message', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    function enqueue()
    {
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css');
        // assets/css/style.css
        wp_enqueue_style('alert-message', plugin_dir_url(__FILE__) . 'assets/css/style.css');
    }

    // SETTINGS ===========
    //================================================================================================
    function settings()
    {
        //https://developer.wordpress.org/reference/functions/add_settings_section/
        add_settings_section(
            'amsg_first_section',  // (ID) String for use in the 'id' attribute of tags.
            null,  // (Sub-Title) Title of the section.
            null, // (Callback) Generic HTML
            'alert-message-settings',  // (Slug) The slug name of the page on which to display the section.
        );

        // LOCATION

        add_settings_field('amsg_location', __('Display Location', 'alert-message'), array($this, 'locationHTML'), 'alert-message-settings', 'amsg_first_section');
        register_setting("Alert_Message", "amsg_location", array('sanitize_callback' => array($this, 'locationSanitize'), 'default' => ''));

        // HEADLINE TEXT
        add_settings_field('amsg_headline', __('Headline Text', 'alert-message'), array($this, 'amsg_headlineHTML'), 'alert-message-settings', 'amsg_first_section');
        register_setting("Alert_Message", "amsg_headline", array('sanitize_callback' => 'sanitize_text_field', 'default' => ''));

        //IS ACTIVE
        // add_settings_field('amsg_is_active', __('Is Active', 'alert-message'), array($this, 'checkboxHTML'), 'alert-message-settings', 'amsg_first_section', array('theName' => 'amsg_is_active'));
        // register_setting("Alert_Message", "amsg_is_active", array('sanitize_callback' => 'sanitize_text_field', 'default' => '0'));
        // Message Type
        add_settings_field('amsg_msgtype', __('Type of message', 'alert-message'), array($this, 'msgtypeHTML'), 'alert-message-settings', 'amsg_first_section');
        register_setting("Alert_Message", "amsg_msgtype", array('sanitize_callback' => array($this, 'msgtypeSanitize'), 'default' => '1'));
    }

    // CALLBACKS
    //================================================================================================

    // LOCATION
    function locationHTML()
    {

        ?>


        <!-- <input type="radio" name="amsg_location" value="1" <?php checked(get_option('amsg_location'), '1') ?>> <?php _e("Bottom of post", "alert-message"); ?>
        <br>
        <input type="radio" name="amsg_location" value="2" <?php checked(get_option('amsg_location'), '2') ?>> <?php _e("Top of post", "alert-message"); ?>
        <br>
        <input type="radio" name="amsg_location" value="3" <?php checked(get_option('amsg_location'), '3') ?>> <?php _e('Both', "alert-message"); ?> -->
     
        <input type="checkbox" name="amsg_location" value="2" <?php checked(get_option('amsg_location'), '2') ?>> <?php _e("Top of post", "alert-message"); ?>
        <br>
        <input type="checkbox" name="amsg_location" value="1" <?php checked(get_option('amsg_location'), '1') ?>> <?php _e("Bottom of post", "alert-message"); ?>
        <br>
        <input type="checkbox" name="amsg_location" value="3" <?php checked(get_option('amsg_location'), '3') ?>> <?php _e('Both', "alert-message"); ?>

        
    <?php
    }

    function locationSanitize($input)
    {
        if ($input != '1' && $input != '2' && $input != '3') {

            add_settings_error('amsg_location', __('amsg_location_error', 'languages'), __('Invalid value'), 'error');
            return get_option('amsg_location');
        }
        return $input;
    }

    function msgtypeHTML()
    {
    ?>
        <select name="amsg_msgtype">
            <option value="1" <?php selected(get_option('amsg_msgtype'), '1') ?>> <?php _e("Info", "alert-message"); ?></option>
            <option value="2" <?php selected(get_option('amsg_msgtype'), '2') ?>> <?php _e("Warning", "alert-message"); ?></option>
            <option value="3" <?php selected(get_option('amsg_msgtype'), '3') ?>> <?php _e('Danger', "alert-message"); ?></option>
            <option value="4" <?php selected(get_option('amsg_msgtype'), '4') ?>> <?php _e('Success', "alert-message"); ?></option>
        </select>
    <?php
    }

    function msgtypeSanitize($input)
    {
        if ($input != '1' && $input != '2' && $input != '3' && $input != '4') {

            add_settings_error('amsg_msgtype', __('amsg_msgtype_error', 'languages'), __('Invalid value'), 'error');
            return get_option('amsg_msgtype');
        }
        return $input;
    }

    function amsg_headlineHTML()
    {
    ?>
        <textarea name="amsg_headline" rows="3" cols="50" placeholder="Your message here!"><?php echo get_option('amsg_headline'); ?></textarea>
        
    <?php
    }


    // Page
    function admin_page()
    {
        add_menu_page(
            // https://developer.wordpress.org/reference/functions/add_options_page/
            'Alert Message Settings', // On top tab of the browser
            'Alert Message', // On the left menu (Not too long) 
            'manage_options', // Capability - Admins, editors, etc.
            'alert-message-settings', // Slug of the page

            array($this, 'settings_page_HTML'), // Call back function to display the page
            'dashicons-admin-settings', // Icon
            10 // Position on the left  (optional)
        );
    }


    function settings_page_HTML()
    {

    ?>
        <div class="wrap">
            <?php // The .wrap is  a class that comes with WordPress and it's a wrapper for the content
            ?>
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="POST">
                <?php
                settings_fields('Alert_Message');
                do_settings_sections('alert-message-settings');
                submit_button();
                ?>
            </form>

        </div>
        </div>
<?php
    }
}
