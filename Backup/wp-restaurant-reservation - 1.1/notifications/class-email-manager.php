<?php
/**
 * Email Manager Class
 * Handles all email notifications for reservations
 */

if (!defined('ABSPATH')) {
    exit;
}

class RRS_Email_Manager {
    
    private $settings;
    private $template_path;
    
    public function __construct() {
        $this->settings = RRS_Database_Manager::get_settings();
        $this->template_path = RRS_PLUGIN_DIR . 'notifications/templates/email/';
        
        // Hook into reservation events
        add_action('rrs_reservation_created', array($this, 'send_reservation_confirmation'), 10, 2);
        add_action('rrs_reservation_confirmed', array($this, 'send_admin_notification'), 10, 1);
        add_action('rrs_reservation_cancelled', array($this, 'send_cancellation_notification'), 10, 2);
        add_action('rrs_reservation_cancelled_by_customer', array($this, 'send_customer_cancellation_confirmation'), 10, 2);
        add_action('rrs_reservation_submitted', array($this, 'send_new_reservation_admin_alert'), 10, 2);
        add_action('rrs_send_reservation_reminder', array($this, 'send_reservation_reminder'), 10, 1);
        
        // Schedule reminders
        add_action('init', array($this, 'schedule_reminder_emails'));
        add_action('rrs_send_daily_reminders', array($this, 'process_daily_reminders'));
        
        // Custom email settings
        add_filter('wp_mail_from', array($this, 'set_email_from_address'));
        add_filter('wp_mail_from_name', array($this, 'set_email_from_name'));
        add_filter('wp_mail_content_type', array($this, 'set_email_content_type'));
        
        // Create email templates if they don't exist
        $this->create_email_templates();
    }
    
    public function schedule_reminder_emails() {
        if (!wp_next_scheduled('rrs_send_daily_reminders')) {
            wp_schedule_event(time(), 'daily', 'rrs_send_daily_reminders');
        }
    }
    
    public function process_daily_reminders() {
        global $wpdb;
        
        $reminder_hours = isset($this->settings['email_settings']['reminder_hours']) ? 
                         $this->settings['email_settings']['reminder_hours'] : 24;
        
        $reminder_time = date('Y-m-d H:i:s', strtotime("+{$reminder_hours} hours"));
        
        // Get reservations that need reminders
        $reservations = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}rrs_reservations 
            WHERE status = 'confirmed' 
            AND CONCAT(reservation_date, ' ', reservation_time) BETWEEN %s AND %s
            AND id NOT IN (
                SELECT object_id FROM {$wpdb->prefix}rrs_activity_logs 
                WHERE action = 'reminder_sent' 
                AND object_type = 'reservation'
                AND created_at > DATE_SUB(NOW(), INTERVAL 25 HOUR)
            )
        ", date('Y-m-d H:i:s'), $reminder_time));
        
        foreach ($reservations as $reservation) {
            $this->send_reservation_reminder($reservation->id);
        }
    }
    
    public function send_reservation_confirmation($reservation_id, $reservation_data) {
        if (!$this->should_send_email('customer_notifications')) {
            return;
        }
        
        $reservation = $this->get_reservation_details($reservation_id);
        if (!$reservation) {
            return;
        }
        
        $subject = $this->get_email_subject('confirmation', $reservation);
        $template = $this->get_email_template('confirmation', $reservation);
        $headers = $this->get_email_headers();
        
        $sent = wp_mail($reservation['customer_email'], $subject, $template, $headers);
        
        if ($sent) {
            $this->log_email_sent('confirmation', $reservation_id, $reservation['customer_email']);
        }
        
        return $sent;
    }
    
    public function send_admin_notification($reservation_id) {
        if (!$this->should_send_email('admin_notifications')) {
            return;
        }
        
        $reservation = $this->get_reservation_details($reservation_id);
        if (!$reservation) {
            return;
        }
        
        $admin_email = isset($this->settings['email_settings']['admin_email']) ? 
                      $this->settings['email_settings']['admin_email'] : get_option('admin_email');
        
        $subject = sprintf(__('Reservation Confirmed - %s', 'restaurant-reservation'), $reservation['reservation_code']);
        $template = $this->get_email_template('admin_notification', $reservation);
        $headers = $this->get_email_headers();
        
        $sent = wp_mail($admin_email, $subject, $template, $headers);
        
        if ($sent) {
            $this->log_email_sent('admin_notification', $reservation_id, $admin_email);
        }
        
        return $sent;
    }
    
    public function send_new_reservation_admin_alert($reservation_id, $reservation_data) {
        if (!$this->should_send_email('admin_notifications')) {
            return;
        }
        
        $reservation = $this->get_reservation_details($reservation_id);
        if (!$reservation) {
            return;
        }
        
        $admin_email = isset($this->settings['email_settings']['admin_email']) ? 
                      $this->settings['email_settings']['admin_email'] : get_option('admin_email');
        
        $subject = sprintf(__('New Reservation Received - %s', 'restaurant-reservation'), $reservation['reservation_code']);
        $template = $this->get_email_template('new_reservation_alert', $reservation);
        $headers = $this->get_email_headers();
        
        $sent = wp_mail($admin_email, $subject, $template, $headers);
        
        if ($sent) {
            $this->log_email_sent('new_reservation_alert', $reservation_id, $admin_email);
        }
        
        return $sent;
    }
    
    public function send_cancellation_notification($reservation_id, $reason = '') {
        $reservation = $this->get_reservation_details($reservation_id);
        if (!$reservation) {
            return;
        }
        
        // Send to customer
        if ($this->should_send_email('customer_notifications')) {
            $subject = $this->get_email_subject('cancellation', $reservation);
            $template = $this->get_email_template('cancellation', $reservation, array('reason' => $reason));
            $headers = $this->get_email_headers();
            
            wp_mail($reservation['customer_email'], $subject, $template, $headers);
        }
        
        // Send to admin
        if ($this->should_send_email('admin_notifications')) {
            $admin_email = isset($this->settings['email_settings']['admin_email']) ? 
                          $this->settings['email_settings']['admin_email'] : get_option('admin_email');
            
            $subject = sprintf(__('Reservation Cancelled - %s', 'restaurant-reservation'), $reservation['reservation_code']);
            $template = $this->get_email_template('admin_cancellation', $reservation, array('reason' => $reason));
            $headers = $this->get_email_headers();
            
            wp_mail($admin_email, $subject, $template, $headers);
        }
    }
    
    public function send_customer_cancellation_confirmation($reservation_id, $reservation) {
        if (!$this->should_send_email('customer_notifications')) {
            return;
        }
        
        $reservation_details = is_array($reservation) ? $reservation : $this->get_reservation_details($reservation_id);
        
        $subject = sprintf(__('Reservation Cancelled - %s', 'restaurant-reservation'), $reservation_details['reservation_code']);
        $template = $this->get_email_template('customer_cancellation_confirmation', $reservation_details);
        $headers = $this->get_email_headers();
        
        $sent = wp_mail($reservation_details['customer_email'], $subject, $template, $headers);
        
        if ($sent) {
            $this->log_email_sent('customer_cancellation_confirmation', $reservation_id, $reservation_details['customer_email']);
        }
        
        return $sent;
    }
    
    public function send_reservation_reminder($reservation_id) {
        if (!$this->should_send_email('reminder_notifications')) {
            return;
        }
        
        $reservation = $this->get_reservation_details($reservation_id);
        if (!$reservation) {
            return;
        }
        
        $subject = $this->get_email_subject('reminder', $reservation);
        $template = $this->get_email_template('reminder', $reservation);
        $headers = $this->get_email_headers();
        
        $sent = wp_mail($reservation['customer_email'], $subject, $template, $headers);
        
        if ($sent) {
            $this->log_email_sent('reminder', $reservation_id, $reservation['customer_email']);
        }
        
        return $sent;
    }
    
    private function get_reservation_details($reservation_id) {
        global $wpdb;
        
        $reservation = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}rrs_reservations WHERE id = %d",
            $reservation_id
        ), ARRAY_A);
        
        if ($reservation) {
            // Format date and time for display
            $reservation['formatted_date'] = date('F j, Y', strtotime($reservation['reservation_date']));
            $reservation['formatted_time'] = date('g:i A', strtotime($reservation['reservation_time']));
            $reservation['formatted_datetime'] = $reservation['formatted_date'] . ' at ' . $reservation['formatted_time'];
            
            // Add restaurant info
            $reservation['restaurant_name'] = get_bloginfo('name');
            $reservation['restaurant_phone'] = '+1234567890'; // Should come from settings
            $reservation['restaurant_email'] = 'info@restaurant.com'; // Should come from settings
            $reservation['restaurant_address'] = '123 Restaurant Street, City, State 12345'; // Should come from settings
        }
        
        return $reservation;
    }
    
    private function get_email_subject($type, $reservation) {
        $subjects = array(
            'confirmation' => sprintf(__('Reservation Confirmation - %s', 'restaurant-reservation'), $reservation['reservation_code']),
            'cancellation' => sprintf(__('Reservation Cancelled - %s', 'restaurant-reservation'), $reservation['reservation_code']),
            'reminder' => sprintf(__('Reservation Reminder - Tomorrow at %s', 'restaurant-reservation'), $reservation['formatted_time']),
        );
        
        $subject = isset($subjects[$type]) ? $subjects[$type] : __('Reservation Update', 'restaurant-reservation');
        
        return apply_filters('rrs_email_subject', $subject, $type, $reservation);
    }
    
    private function get_email_template($type, $reservation, $extra_data = array()) {
        $template_file = $this->template_path . $type . '.php';
        
        if (file_exists($template_file)) {
            ob_start();
            extract($reservation);
            extract($extra_data);
            include $template_file;
            return ob_get_clean();
        }
        
        // Fallback to built-in templates
        return $this->get_built_in_template($type, $reservation, $extra_data);
    }
    
    private function get_built_in_template($type, $reservation, $extra_data = array()) {
        switch ($type) {
            case 'confirmation':
                return $this->build_confirmation_template($reservation);
                
            case 'new_reservation_alert':
                return $this->build_new_reservation_alert_template($reservation);
                
            case 'cancellation':
                return $this->build_cancellation_template($reservation, $extra_data);
                
            case 'customer_cancellation_confirmation':
                return $this->build_customer_cancellation_template($reservation);
                
            case 'reminder':
                return $this->build_reminder_template($reservation);
                
            case 'admin_notification':
                return $this->build_admin_notification_template($reservation);
                
            case 'admin_cancellation':
                return $this->build_admin_cancellation_template($reservation, $extra_data);
                
            default:
                return $this->build_default_template($reservation);
        }
    }
    
    private function build_confirmation_template($reservation) {
        $template = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #0073aa; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .reservation-details { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
                .detail-row { padding: 5px 0; border-bottom: 1px solid #eee; }
                .label { font-weight: bold; display: inline-block; width: 120px; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
                .button { display: inline-block; padding: 10px 20px; background: #0073aa; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Reservation Confirmation</h1>
                    <p>Thank you for choosing ' . esc_html($reservation['restaurant_name']) . '!</p>
                </div>
                
                <div class="content">
                    <p>Dear ' . esc_html($reservation['customer_name']) . ',</p>
                    
                    <p>Your reservation has been received and is currently <strong>' . ucfirst($reservation['status']) . '</strong>. Here are your reservation details:</p>
                    
                    <div class="reservation-details">
                        <div class="detail-row">
                            <span class="label">Confirmation Code:</span>
                            <strong>' . esc_html($reservation['reservation_code']) . '</strong>
                        </div>
                        <div class="detail-row">
                            <span class="label">Date & Time:</span>
                            ' . esc_html($reservation['formatted_datetime']) . '
                        </div>
                        <div class="detail-row">
                            <span class="label">Party Size:</span>
                            ' . esc_html($reservation['party_size']) . ' ' . (_n('guest', 'guests', $reservation['party_size'], 'restaurant-reservation')) . '
                        </div>
                        <div class="detail-row">
                            <span class="label">Name:</span>
                            ' . esc_html($reservation['customer_name']) . '
                        </div>
                        <div class="detail-row">
                            <span class="label">Email:</span>
                            ' . esc_html($reservation['customer_email']) . '
                        </div>
                        <div class="detail-row">
                            <span class="label">Phone:</span>
                            ' . esc_html($reservation['customer_phone']) . '
                        </div>';
        
        if (!empty($reservation['special_requests'])) {
            $template .= '
                        <div class="detail-row">
                            <span class="label">Special Requests:</span>
                            ' . esc_html($reservation['special_requests']) . '
                        </div>';
        }
        
        $template .= '
                    </div>
                    
                    <p><strong>Important Information:</strong></p>
                    <ul>
                        <li>Please arrive on time for your reservation</li>
                        <li>If you need to cancel or modify your reservation, please do so at least 2 hours in advance</li>
                        <li>For parties of 6 or more, a service charge may apply</li>
                    </ul>
                    
                    <div style="text-align: center; margin: 20px 0;">
                        <a href="' . home_url() . '?rrs_action=view&reservation_code=' . $reservation['reservation_code'] . '" class="button">View Reservation</a>
                        <a href="' . home_url() . '?rrs_action=cancel&reservation_code=' . $reservation['reservation_code'] . '" class="button" style="background: #d63638;">Cancel Reservation</a>
                    </div>
                    
                    <p>We look forward to serving you!</p>
                    
                    <p>Best regards,<br>
                    The ' . esc_html($reservation['restaurant_name']) . ' Team</p>
                </div>
                
                <div class="footer">
                    <p>' . esc_html($reservation['restaurant_name']) . '<br>
                    ' . esc_html($reservation['restaurant_address']) . '<br>
                    Phone: ' . esc_html($reservation['restaurant_phone']) . ' | Email: ' . esc_html($reservation['restaurant_email']) . '</p>
                </div>
            </div>
        </body>
        </html>';
        
        return $template;
    }
    
    private function build_new_reservation_alert_template($reservation) {
        return '
        <html>
        <body style="font-family: Arial, sans-serif;">
            <h2>New Reservation Alert</h2>
            
            <p>A new reservation has been submitted and requires your attention:</p>
            
            <table border="1" cellpadding="10" style="border-collapse: collapse;">
                <tr><td><strong>Confirmation Code:</strong></td><td>' . esc_html($reservation['reservation_code']) . '</td></tr>
                <tr><td><strong>Customer:</strong></td><td>' . esc_html($reservation['customer_name']) . '</td></tr>
                <tr><td><strong>Email:</strong></td><td>' . esc_html($reservation['customer_email']) . '</td></tr>
                <tr><td><strong>Phone:</strong></td><td>' . esc_html($reservation['customer_phone']) . '</td></tr>
                <tr><td><strong>Date & Time:</strong></td><td>' . esc_html($reservation['formatted_datetime']) . '</td></tr>
                <tr><td><strong>Party Size:</strong></td><td>' . esc_html($reservation['party_size']) . '</td></tr>
                <tr><td><strong>Status:</strong></td><td>' . ucfirst($reservation['status']) . '</td></tr>
                <tr><td><strong>Special Requests:</strong></td><td>' . esc_html($reservation['special_requests'] ?: 'None') . '</td></tr>
            </table>
            
            <p><a href="' . admin_url('admin.php?page=restaurant-reservations') . '">View in Admin Dashboard</a></p>
        </body>
        </html>';
    }
    
    private function build_reminder_template($reservation) {
        return '
        <html>
        <body style="font-family: Arial, sans-serif;">
            <h2>Reservation Reminder</h2>
            
            <p>Dear ' . esc_html($reservation['customer_name']) . ',</p>
            
            <p>This is a friendly reminder that you have a reservation at ' . esc_html($reservation['restaurant_name']) . ' tomorrow:</p>
            
            <p><strong>Date & Time:</strong> ' . esc_html($reservation['formatted_datetime']) . '<br>
            <strong>Party Size:</strong> ' . esc_html($reservation['party_size']) . ' ' . (_n('guest', 'guests', $reservation['party_size'], 'restaurant-reservation')) . '<br>
            <strong>Confirmation Code:</strong> ' . esc_html($reservation['reservation_code']) . '</p>
            
            <p>We look forward to seeing you!</p>
            
            <p>If you need to cancel or modify your reservation, please contact us at ' . esc_html($reservation['restaurant_phone']) . '.</p>
            
            <p>Best regards,<br>
            ' . esc_html($reservation['restaurant_name']) . '</p>
        </body>
        </html>';
    }
    
    private function build_cancellation_template($reservation, $extra_data) {
        $reason = isset($extra_data['reason']) ? $extra_data['reason'] : '';
        
        return '
        <html>
        <body style="font-family: Arial, sans-serif;">
            <h2>Reservation Cancelled</h2>
            
            <p>Dear ' . esc_html($reservation['customer_name']) . ',</p>
            
            <p>Your reservation has been cancelled:</p>
            
            <p><strong>Confirmation Code:</strong> ' . esc_html($reservation['reservation_code']) . '<br>
            <strong>Date & Time:</strong> ' . esc_html($reservation['formatted_datetime']) . '<br>
            <strong>Party Size:</strong> ' . esc_html($reservation['party_size']) . '</p>
            
            ' . ($reason ? '<p><strong>Reason:</strong> ' . esc_html($reason) . '</p>' : '') . '
            
            <p>We apologize for any inconvenience. We hope to serve you in the future.</p>
            
            <p>Best regards,<br>
            ' . esc_html($reservation['restaurant_name']) . '</p>
        </body>
        </html>';
    }
    
    private function build_customer_cancellation_template($reservation) {
        return '
        <html>
        <body style="font-family: Arial, sans-serif;">
            <h2>Cancellation Confirmation</h2>
            
            <p>Dear ' . esc_html($reservation['customer_name']) . ',</p>
            
            <p>Your reservation has been successfully cancelled:</p>
            
            <p><strong>Confirmation Code:</strong> ' . esc_html($reservation['reservation_code']) . '<br>
            <strong>Date & Time:</strong> ' . esc_html($reservation['formatted_datetime']) . '<br>
            <strong>Party Size:</strong>
