<?php
/**
 * Reservation Controller - Yenolx Restaurant Reservation v1.5.1
 * Fixed: Removed HTML content and syntax errors
 */

if (!defined('ABSPATH')) exit;

class YRR_Reservation_Controller {
    private $reservation_model;
    
    public function __construct() {
        $this->reservation_model = new YRR_Reservation_Model();
        
        // Hook into WordPress actions
        add_action('wp_ajax_submit_reservation', array($this, 'handle_ajax_reservation'));
        add_action('wp_ajax_nopriv_submit_reservation', array($this, 'handle_ajax_reservation'));
        add_action('init', array($this, 'handle_reservation_submission'));
    }
    
    public function handle_reservation_submission() {
        if (isset($_POST['submit_reservation']) && wp_verify_nonce($_POST['reservation_nonce'], 'submit_reservation')) {
            $this->process_reservation();
        }
    }
    
    public function handle_ajax_reservation() {
        check_ajax_referer('reservation_nonce', 'nonce');
        
        $result = $this->process_reservation();
        
        if ($result) {
            wp_send_json_success(array('message' => 'Reservation submitted successfully!'));
        } else {
            wp_send_json_error(array('message' => 'Failed to submit reservation. Please try again.'));
        }
    }
    
    private function process_reservation() {
        // Validate required fields
        $required_fields = array('customer_name', 'customer_email', 'customer_phone', 'party_size', 'reservation_date', 'reservation_time');
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                return false;
            }
        }
        
        // Prepare reservation data
        $reservation_data = array(
            'reservation_code' => $this->generate_reservation_code(),
            'customer_name' => sanitize_text_field($_POST['customer_name']),
            'customer_email' => sanitize_email($_POST['customer_email']),
            'customer_phone' => sanitize_text_field($_POST['customer_phone']),
            'party_size' => intval($_POST['party_size']),
            'reservation_date' => sanitize_text_field($_POST['reservation_date']),
            'reservation_time' => sanitize_text_field($_POST['reservation_time']),
            'special_requests' => sanitize_textarea_field($_POST['special_requests'] ?? ''),
            'status' => 'pending'
        );
        
        // Create reservation
        $result = $this->reservation_model->create($reservation_data);
        
        if ($result) {
            // Send confirmation email
            $this->send_confirmation_email($reservation_data);
            return $result;
        }
        
        return false;
    }
    
    private function generate_reservation_code() {
        return 'RES-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    }
    
    private function send_confirmation_email($reservation_data) {
        $subject = 'Reservation Confirmation - ' . get_bloginfo('name');
        $message = "Dear {$reservation_data['customer_name']},\n\n";
        $message .= "Your reservation has been received!\n\n";
        $message .= "Details:\n";
        $message .= "Date: {$reservation_data['reservation_date']}\n";
        $message .= "Time: {$reservation_data['reservation_time']}\n";
        $message .= "Party Size: {$reservation_data['party_size']} guests\n";
        $message .= "Confirmation Code: {$reservation_data['reservation_code']}\n\n";
        $message .= "We will confirm your reservation shortly.\n\n";
        $message .= "Thank you!";
        
        wp_mail($reservation_data['customer_email'], $subject, $message);
    }
    
    public function display_reservation_form($atts = array()) {
        $defaults = array(
            'show_time_slots' => true,
            'show_special_requests' => true
        );
        
        $settings = wp_parse_args($atts, $defaults);
        
        ob_start();
        include YRR_PLUGIN_PATH . 'views/public/reservation-form.php';
        return ob_get_clean();
    }
}
?>
