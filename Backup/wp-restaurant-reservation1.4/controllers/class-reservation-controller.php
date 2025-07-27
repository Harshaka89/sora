<?php
/**
 * Reservation Controller Class
 * 
 * Handles public reservation functionality and AJAX operations
 * 
 * @package RestaurantReservations
 * @subpackage Controllers
 * @version 1.4.0
 * @since 1.0.0
 * @author Your Name
 * 
 * @class RRS_Reservation_Controller
 * @description Controller for reservation booking and management
 * 
 * Responsibilities:
 * - Public booking form handling
 * - AJAX reservation operations
 * - Reservation validation
 * - Email notifications
 * - Shortcode management
 * 
 * Methods:
 * - display_booking_form() - Render public form
 * - handle_booking_submission() - Process form data
 * - ajax_update_reservation() - AJAX update handler
 * - ajax_delete_reservation() - AJAX delete handler
 * - send_confirmation_email() - Email notifications
 */

if (!defined('ABSPATH')) exit;


class RRS_Reservation_Controller {
    private $reservation_model;
    private $settings_model;
    
    public function __construct() {
        $this->reservation_model = new RRS_Reservation_Model();
        $this->settings_model = new RRS_Settings_Model();
    }
    
    public function display_booking_form($atts) {
        $atts = shortcode_atts(array('theme' => 'default'), $atts);
        
        if (!$this->settings_model->is_restaurant_open()) {
            return $this->load_view_content('public/restaurant-closed');
        }
        
        $message = '';
        if (isset($_POST['rrs_submit']) && wp_verify_nonce($_POST['rrs_nonce'], 'rrs_booking')) {
            $message = $this->handle_booking_submission();
        }
        
        $data = array(
            'settings' => $this->settings_model->get_all(),
            'message' => $message,
            'theme' => $atts['theme']
        );
        
        return $this->load_view_content('public/booking-form', $data);
    }
    
    private function handle_booking_submission() {
        $reservation_data = array(
            'customer_name' => sanitize_text_field($_POST['customer_name']),
            'customer_email' => sanitize_email($_POST['customer_email']),
            'customer_phone' => sanitize_text_field($_POST['customer_phone']),
            'party_size' => intval($_POST['party_size']),
            'reservation_date' => sanitize_text_field($_POST['reservation_date']),
            'reservation_time' => sanitize_text_field($_POST['reservation_time']),
            'special_requests' => sanitize_textarea_field($_POST['special_requests'])
        );
        
        $validation = $this->reservation_model->validate($reservation_data);
        if ($validation !== true) {
            return array(
                'type' => 'error',
                'message' => 'Please fix the following errors: ' . implode(', ', $validation)
            );
        }
        
        $result = $this->reservation_model->create($reservation_data);
        
        if (is_wp_error($result)) {
            return array(
                'type' => 'error',
                'message' => 'Failed to create reservation. Please try again.'
            );
        }
        
        $reservation = $this->reservation_model->get_by_id($result);
        
        return array(
            'type' => 'success',
            'message' => 'Reservation submitted successfully!',
            'reservation_code' => $reservation->reservation_code
        );
    }
    
    private function load_view_content($view, $data = array()) {
        $view_file = RRS_PLUGIN_PATH . 'views/' . $view . '.php';
        
        if (file_exists($view_file)) {
            extract($data);
            ob_start();
            include $view_file;
            return ob_get_clean();
        }
        
        return '<p>View file not found: ' . $view . '</p>';
    }
}
?>
