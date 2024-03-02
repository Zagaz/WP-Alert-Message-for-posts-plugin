<?php
/*
Plugin Name: Alert Message
Author: André Ranulfo
Aurhor URI: https://linkedin.com/in/andre-ranulfo
Version: 1.0
Description: Just for fun!
language Domain: alert-message
Domain Path: /languages
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
    }
    // PLUGIN    ===========
    //================================================================================================

    function ifWrap($content)
    {
        if (
            is_single() && is_main_query() &&
            (
                get_option('amsg_wordcount', '1')  ||
                get_option('amsg_character', '1')  ||
                get_option('amsg_readtime', '1')
            )
        ) {
            return $this->createHTML($content);
        }
        return $content;
    }
    function createHTML($content)
    {
        $headline       = get_option('amsg_headline') ? get_option('amsg_headline')  : false;
        $location       = get_option('amsg_location') ? get_option('amsg_location')  : false;
        $is_active     = get_option('amsg_is_active') ? get_option('amsg_is_active')  : false;

        $msgtype = get_option('amsg_msgtype') ? get_option('amsg_msgtype')  : '4';

        $icon_info = '<i class="fas fa-info-circle"></i>';
        $icon_waring = '<i class="fas fa-exclamation-triangle"></i>';
        $icon_bomb  = '<i class="fas fa-bomb"></i>';
        $icon_success = '<i class="fas fa-check-circle"></i>';

        $icon = '';
        $class = '';



        switch ($msgtype) {

            case '1':
                $icon = $icon_info;
                $class = 'alert-info';
                break;
            case '2':
                $icon = $icon_waring;
                $class = 'alert-warning';
                break;
            case '3':
                $icon = $icon_bomb;
                $class = 'alert-bomb';
                break;
            case '4':
                $icon = $icon_success;
                $class = 'alert-success';
                break;
        }




        if ($is_active  && $headline &&  $location && $msgtype) {


            $html = '<div class="alert-message wrap">';
            $html .= "<div class='alert-message-icon $class'>";
            $html .= $icon;
            $html .= '</div>';
            if ($headline) {
                $html .= "<div class = 'alert-message-text'>$headline</div>";
            }

            $html .= '</div>';

            // match switch case
            switch ($location) {
                case '0':
                    return $content . $html;
                    break;
                case '1':
                    return $html . $content;
                    break;
                case '2':
                    return $html . $content . $html;
                    break;
            }

            return $content;
        }
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
        register_setting("Alert_Message", "amsg_location", array('sanitize_callback' => array($this, 'locationSanitize'), 'default' => '0'));

        // HEADLINE TEXT
        add_settings_field('amsg_headline', __('Headline Text', 'alert-message'), array($this, 'amsg_headlineHTML'), 'alert-message-settings', 'amsg_first_section');
        register_setting("Alert_Message", "amsg_headline", array('sanitize_callback' => 'sanitize_text_field', 'default' => 'Your Message!'));

        //IS ACTIVE
        add_settings_field('amsg_is_active', __('Is Active', 'alert-message'), array($this, 'checkboxHTML'), 'alert-message-settings', 'amsg_first_section', array('theName' => 'amsg_is_active'));
        register_setting("Alert_Message", "amsg_is_active", array('sanitize_callback' => 'sanitize_text_field', 'default' => '0'));
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
        <select name="amsg_location">
            <option value="0" <?php selected(get_option('amsg_location'), '0') ?>> <?php _e("Bottom of post", "alert-message"); ?></option>
            <option value="1" <?php selected(get_option('amsg_location'), '1') ?>> <?php _e("Top of post", "alert-message"); ?></option>
            <option value="2" <?php selected(get_option('amsg_location'), '2') ?>> <?php _e('Both', "alert-message"); ?></option>
        </select>
    <?php
    }

    function locationSanitize($input)
    {
        if ($input != '0' && $input != '1' && $input != '2') {

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
        <input type="text" name="amsg_headline" value="<?php echo esc_attr(get_option('amsg_headline')); ?>">
    <?php
    }

    function checkboxHTML($args)
    {
        $name = $args['theName'];
    ?>
        <input type="checkbox" name="<?php echo $name; ?>" value="1" <?php checked(get_option($name), '1'); ?>>
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
<?php
    }
}
