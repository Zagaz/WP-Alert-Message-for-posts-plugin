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
        
    }
    // PLUGIN    ===========
    //================================================================================================

    function ifWrap($content)
    {
        if (is_single() && is_main_query() &&
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
    function createHTML($content){
        $headline       = get_option('amsg_headline') ? get_option('amsg_headline')  : false;
        $location       = get_option('amsg_location') ? get_option('amsg_location')  : false;
        $wordCount      = get_option('amsg_wordcount')? get_option('amsg_wordcount') : false;
        $characterCount = get_option('amsg_character')? get_option('amsg_character') : false;
        $readTime       = get_option('amsg_readtime') ? get_option('amsg_readtime')  : false;

      if ($headline || $location || $wordCount || $characterCount || $readTime) {

        $html = '<div class="alert-message wrap">';
        if ($headline) {
            $html .= "<h2>$headline</h2>";
      
        }
        if ($wordCount) {
            $html .= '<p> '. __ ( 'Word Count' , 'alert-message' ) . ': ' . str_word_count(strip_tags($content)) . ' ' . __('Words', 'alert-message' ) . '</p>';
        }
        if ($characterCount) {
            $html .= '<p>' . __('Character Count' , 'alert-message' ) . ':  ' . strlen(strip_tags($content)) . ' '. __('Characters', 'alert-message' ) . '</p>';
        }
        if ($readTime) {
            $html .= '<p>' . __('Read Time' , 'alert-message' ) . ': ' . round(str_word_count(strip_tags($content)) / 200) . ' ' . __('Minutes', 'alert-message' ) . '</p>';
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

        // Example of a field and settin ==============

        // add_settings_field( $id:string, $title:string, $callback:callable, $page:string, $section:string, $args:array );
        //https://developer.wordpress.org/reference/functions/add_settings_field/

        // register_setting( $option_group:string, $option_name:string, $args:array );
        // https://developer.wordpress.org/reference/functions/register_setting/

        // LOCATION


        add_settings_field('amsg_location', __('Display Location', 'alert-message'), array($this, 'locationHTML'), 'alert-message-settings', 'amsg_first_section');
        register_setting("Alert_Message", "amsg_location", array('sanitize_callback' => array($this, 'locationSanitize'), 'default' => '0'));

        // HEADLINE TEXT
        add_settings_field('amsg_headline', __('Headline Text', 'alert-message'), array($this, 'amsg_headlineHTML'), 'alert-message-settings', 'amsg_first_section');
        register_setting("Alert_Message", "amsg_headline", array('sanitize_callback' => 'sanitize_text_field', 'default' => 'Your Message!'));

        //WORD COUNT
        add_settings_field('amsg_wordcount', __('Display Word Count', 'alert-message'),  array($this, 'checkboxHTML'), 'alert-message-settings', 'amsg_first_section', array('theName' => 'amsg_wordcount'));
        register_setting("Alert_Message", "amsg_wordcount", array('sanitize_callback' => 'sanitize_text_field', 'default' => '0'));

        // CHARACTER COUNT
        add_settings_field('amsg_character', __('Display Character Count', 'alert-message'), array($this, 'checkboxHTML'), 'alert-message-settings', 'amsg_first_section', array('theName' => 'amsg_character'));
        register_setting("Alert_Message", "amsg_character", array('sanitize_callback' => 'sanitize_text_field', 'default' => '0'));

        // READ TIME
        add_settings_field('amsg_readtime', __('Read Time', 'alert-message'), array($this, 'checkboxHTML'), 'alert-message-settings', 'amsg_first_section', array('theName' => 'amsg_readtime'));
        register_setting("Alert_Message", "amsg_readtime", array('sanitize_callback' => 'sanitize_text_field', 'default' => '0'));
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
