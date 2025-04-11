<?php
namespace AlertMessage;

if (!defined('ABSPATH')) exit;

class AlertMessageAdmin {
    public function __construct() {
        add_action('admin_menu', [$this, 'admin_page']);
        add_action('admin_init', [$this, 'settings']);
        add_action('admin_notices', [$this, 'display_admin_notice']);
    }

    public function display_admin_notice() {
        if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved successfully!', 'alert-message') . '</p></div>';
        }
    }

    public function admin_page() {
        add_menu_page(
            'Alert Message Settings',
            'Alert Message',
            'manage_options',
            'alert-message-settings',
            [$this, 'settings_page_HTML'],
            'dashicons-admin-settings',
            10
        );
    }

    public function settings() {
        add_settings_section('amsg_first_section', null, null, 'alert-message-settings');

        register_setting('Alert_Message', 'amsg_location', [
            'sanitize_callback' => [$this, 'locationSanitize'],
            'default' => '1'
        ]);
        add_settings_field('amsg_location', __('Display Location', 'alert-message'), [$this, 'locationHTML'], 'alert-message-settings', 'amsg_first_section');

        register_setting('Alert_Message', 'amsg_headline', [
            'sanitize_callback' => 'sanitize_text_field',
            'default' => __("Don't forget to be awesome!", 'alert-message')
        ]);
        add_settings_field('amsg_headline', __('Headline Text', 'alert-message'), [$this, 'headlineHTML'], 'alert-message-settings', 'amsg_first_section');

        register_setting('Alert_Message', 'amsg_msgtype', [
            'sanitize_callback' => [$this, 'msgtypeSanitize'],
            'default' => '1'
        ]);
        add_settings_field('amsg_msgtype', __('Type of message', 'alert-message'), [$this, 'msgtypeHTML'], 'alert-message-settings', 'amsg_first_section');
    }

    public function settings_page_HTML() {
        ?>
        <div class="wrap">
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

    public function locationHTML() {
        $value = get_option('amsg_location', '1');
        ?>
        <label><input type="checkbox" name="amsg_location" value="2" <?php checked($value, '2'); ?>> <?php _e("Top of post", "alert-message"); ?></label><br>
        <label><input type="checkbox" name="amsg_location" value="1" <?php checked($value, '1'); ?>> <?php _e("Bottom of post", "alert-message"); ?></label><br>
        <label><input type="checkbox" name="amsg_location" value="3" <?php checked($value, '3'); ?>> <?php _e('Both', "alert-message"); ?></label><br>
        <label><input type="checkbox" name="amsg_location" value="4" <?php checked($value, '4'); ?>> <?php _e('None', "alert-message"); ?></label>
        <script>
            const checkboxes = document.querySelectorAll('input[name="amsg_location"]');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', e => {
                    checkboxes.forEach(c => { if (c !== e.target) c.checked = false; });
                });
            });
        </script>
        <?php
    }

    public function locationSanitize($input) {
        return in_array($input, ['1', '2', '3', '4']) ? $input : get_option('amsg_location');
    }

    public function headlineHTML() {
        echo '<textarea name="amsg_headline" rows="3" cols="50" placeholder="Your message here!">' . esc_textarea(get_option('amsg_headline')) . '</textarea>';
    }

    public function msgtypeHTML() {
        $value = get_option('amsg_msgtype', '1');
        ?>
        <select name="amsg_msgtype">
            <option value="1" <?php selected($value, '1'); ?>><?php _e("Info", "alert-message"); ?></option>
            <option value="2" <?php selected($value, '2'); ?>><?php _e("Warning", "alert-message"); ?></option>
            <option value="3" <?php selected($value, '3'); ?>><?php _e("Danger", "alert-message"); ?></option>
            <option value="4" <?php selected($value, '4'); ?>><?php _e("Success", "alert-message"); ?></option>
        </select>
        <?php
    }

    public function msgtypeSanitize($input) {
        return in_array($input, ['1', '2', '3', '4']) ? $input : get_option('amsg_msgtype');
    }
}

new AlertMessageAdmin();
