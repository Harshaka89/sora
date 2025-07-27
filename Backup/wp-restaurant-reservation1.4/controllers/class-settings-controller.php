<?php
/**
 * Settings Controller - MVC Pattern
 */

if (!defined('ABSPATH')) exit;

class RRS_Settings_Controller {
    private $settings_model;
    
    public function __construct() {
        $this->settings_model = new RRS_Settings_Model();
        $this->handle_settings_save();
    }
    
    public function settings_page() {
        // Handle form submission
        if (isset($_POST['save_settings']) && wp_verify_nonce($_POST['settings_nonce'], 'rrs_settings_save')) {
            $this->save_settings();
        }
        
        // Get current settings
        $settings = $this->settings_model->get_all();
        
        // Load the settings view
        $this->load_view('admin/settings', array('settings' => $settings));
    }
    
    private function save_settings() {
        global $wpdb;
        
        $settings_to_save = array(
            'restaurant_open',
            'restaurant_name',
            'restaurant_email',
            'max_party_size'
        );
        
        $saved_count = 0;
        
        foreach ($settings_to_save as $setting) {
            if (isset($_POST[$setting])) {
                $value = sanitize_text_field($_POST[$setting]);
                
                // Direct database save
                $result = $wpdb->replace($wpdb->prefix . 'rrs_settings', array(
                    'setting_name' => $setting,
                    'setting_value' => $value,
                    'updated_at' => current_time('mysql')
                ));
                
                if ($result !== false) {
                    $saved_count++;
                }
            }
        }
        
        // Clear any caching
        wp_cache_flush();
        
        // Redirect with success message
        $redirect_url = add_query_arg('message', 'saved', admin_url('admin.php?page=res-settings'));
        wp_redirect($redirect_url);
        exit;
    }
    
    private function handle_settings_save() {
        if (isset($_POST['save_settings']) && wp_verify_nonce($_POST['settings_nonce'], 'rrs_settings_save')) {
            $this->save_settings();
        }
    }
    
    private function load_view($view, $data = array()) {
        extract($data);
        include RRS_PLUGIN_PATH . 'views/' . $view . '.php';
    }
}
?>
