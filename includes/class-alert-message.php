<?php
namespace AlertMessage;

if (!defined('ABSPATH')) exit;

class AlertMessage {
    public function __construct() {
        add_action('init', [$this, 'languages']);
        add_filter('the_content', [$this, 'ifWrap']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue']);
    }

    public function languages() {
        load_plugin_textdomain('alert-message', false, dirname(plugin_basename(__FILE__), 2) . '/languages');
    }

    public function enqueue() {
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css');
        wp_enqueue_style('alert-message', plugin_dir_url(__FILE__) . '../assets/css/style.css');
    }

    public function ifWrap($content) {
        if (is_single() && is_main_query()) {
            return $this->createHTML($content);
        }
        return $content;
    }

    private function createHTML($content) {
        $headline = get_option('amsg_headline', __("Don't forget to be awesome!", "alert-message"));
        $location = get_option('amsg_location', '1');
        $msgtype = get_option('amsg_msgtype', '1');

        $icons = [
            '1' => '<i class="fas fa-info-circle"></i>',
            '2' => '<i class="fas fa-exclamation-triangle"></i>',
            '3' => '<i class="fas fa-bomb"></i>',
            '4' => '<i class="fas fa-check-circle"></i>'
        ];

        $classes = [
            '1' => ['alert-info', 'alert-box-info'],
            '2' => ['alert-warning', 'alert-box-warning'],
            '3' => ['alert-bomb', 'alert-box-bomb'],
            '4' => ['alert-success', 'alert-box-success']
        ];

        [$class, $classBox] = $classes[$msgtype];
        $icon = $icons[$msgtype];

        $html = $this->alertHTML($class, $icon, $headline, $classBox);

        switch ($location) {
            case '1': return $content . $html;
            case '2': return $html . $content;
            case '3': return $html . $content . $html;
            case '4': return $content;
            default: return $content;
        }
    }

    private function alertHTML($class, $icon, $headline, $classBox) {
        return "<div class='alert-message $classBox wrap'>
                    <div class='alert-message-icon $class'>$icon</div>
                    <div class='alert-message-text'>$headline</div>
                </div>";
    }
}
