<?php
/**
 * Settings Controller Class
 * 
 * Handles restaurant settings and configuration management
 * 
 * @package RestaurantReservations
 * @subpackage Controllers
 * @version 1.4.0
 * @since 1.0.0
 * @author Your Name
 * 
 * @class RRS_Settings_Controller
 * @description Controller for settings management and validation
 * 
 * Responsibilities:
 * - Settings form processing
 * - Configuration validation
 * - Default value management
 * - Settings export/import
 * - Cache management
 * 
 * Methods:
 * - get_all_settings() - Retrieve all settings
 * - update_settings() - Update configuration
 * - validate_settings() - Validate setting data
 * - reset_to_defaults() - Reset configuration
 * - export_settings() - Export configuration
 * - import_settings() - Import configuration
 */

if (!defined('ABSPATH')) exit;


class RRS_Settings_Controller {
    private $settings_model;
    
    public function __construct() {
        $this->settings_model = new RRS_Settings_Model();
    }
    
    public function get_all_settings() {
        return $this->settings_model->get_all();
    }
    
    public function update_settings($settings) {
        foreach ($settings as $name => $value) {
            $this->settings_model->set($name, $value);
        }
        return true;
    }
    
    public function validate_settings($settings) {
        $errors = array();
        
        if (isset($settings['max_party_size']) && ($settings['max_party_size'] < 1 || $settings['max_party_size'] > 50)) {
            $errors[] = 'Maximum party size must be between 1 and 50';
        }
        
        if (isset($settings['restaurant_email']) && !is_email($settings['restaurant_email'])) {
            $errors[] = 'Please enter a valid email address';
        }
        
        return empty($errors) ? true : $errors;
    }
}
?>
