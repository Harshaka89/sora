<?php
class RRS_Booking_Form {
    
    public function __construct() {
        add_shortcode('restaurant_booking_form', array($this, 'render_form'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_nopriv_submit_reservation', array($this, 'handle_form_submission'));
        add_action('wp_ajax_submit_reservation', array($this, 'handle_form_submission'));
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script('jquery');
        wp_localize_script('jquery', 'rrs_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rrs_booking_nonce')
        ));
    }
    
    public function render_form($atts) {
        $atts = shortcode_atts(array(
            'style' => 'default'
        ), $atts);
        
        ob_start();
        ?>
        <div id="rrs-booking-form-container" style="max-width: 600px; margin: 20px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2 style="text-align: center; color: #333; margin-bottom: 30px;">Make a Reservation</h2>
            
            <!-- Success/Error Messages -->
            <div id="rrs-messages" style="display: none; padding: 15px; margin-bottom: 20px; border-radius: 5px;"></div>
            
            <form id="rrs-booking-form" style="display: grid; gap: 20px;">
                <?php wp_nonce_field('rrs_booking_nonce', 'booking_nonce'); ?>
                
                <!-- Customer Information -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #555;">Full Name *</label>
                        <input type="text" name="customer_name" required style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 5px; font-size: 16px;">
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #555;">Email Address *</label>
                        <input type="email" name="customer_email" required style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 5px; font-size: 16px;">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #555;">Phone Number *</label>
                        <input type="tel" name="customer_phone" required style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 5px; font-size: 16px;">
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #555;">Party Size *</label>
                        <select name="party_size" required style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 5px; font-size: 16px;">
                            <option value="">Select party size</option>
                            <?php for($i = 1; $i <= 12; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?> <?php echo $i == 1 ? 'Guest' : 'Guests'; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Date and Time -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #555;">Preferred Date *</label>
                        <input type="date" name="reservation_date" required min="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d', strtotime('+60 days')); ?>" style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 5px; font-size: 16px;">
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #555;">Preferred Time *</label>
                        <select name="reservation_time" required style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 5px; font-size: 16px;">
                            <option value="">Select time</option>
                            <option value="17:00">5:00 PM</option>
                            <option value="17:30">5:30 PM</option>
                            <option value="18:00">6:00 PM</option>
                            <option value="18:30">6:30 PM</option>
                            <option value="19:00">7:00 PM</option>
                            <option value="19:30">7:30 PM</option>
                            <option value="20:00">8:00 PM</option>
                            <option value="20:30">8:30 PM</option>
                            <option value="21:00">9:00 PM</option>
                            <option value="21:30">9:30 PM</option>
                        </select>
                    </div>
                </div>
                
                <!-- Special Requests -->
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #555;">Special Requests</label>
                    <textarea name="special_requests" rows="4" style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 5px; font-size: 16px; resize: vertical;" placeholder="Dietary requirements, celebrations, accessibility needs, table preferences..."></textarea>
                </div>
                
                <!-- GDPR Consent -->
                <div>
                    <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="gdpr_consent" required style="margin-top: 4px;">
                        <span style="font-size: 14px; color: #666;">I agree to the processing of my personal data for this reservation and consent to being contacted regarding my booking. <a href="<?php echo get_privacy_policy_url(); ?>" target="_blank" style="color: #0073aa;">View Privacy Policy</a></span>
                    </label>
                </div>
                
                <div>
                    <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="marketing_consent" style="margin-top: 4px;">
                        <span style="font-size: 14px; color: #666;">I would like to receive promotional emails and special offers (optional)</span>
                    </label>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" style="width: 100%; padding: 15px; background: #0073aa; color: white; border: none; border-radius: 5px; font-size: 18px; font-weight: bold; cursor: pointer; transition: background 0.3s;" onmouseover="this.style.background='#005a87'" onmouseout="this.style.background='#0073aa'">
                    <span id="submit-text">Make Reservation</span>
                    <span id="loading-text" style="display: none;">Processing...</span>
                </button>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#rrs-booking-form').on('submit', function(e) {
                e.preventDefault();
                
                // Show loading state
                $('#submit-text').hide();
                $('#loading-text').show();
                $('button[type="submit"]').prop('disabled', true);
                
                // Collect form data
                var formData = {
                    action: 'submit_reservation',
                    nonce: rrs_ajax.nonce,
                    customer_name: $('input[name="customer_name"]').val(),
                    customer_email: $('input[name="customer_email"]').val(),
                    customer_phone: $('input[name="customer_phone"]').val(),
                    party_size: $('select[name="party_size"]').val(),
                    reservation_date: $('input[name="reservation_date"]').val(),
                    reservation_time: $('select[name="reservation_time"]').val(),
                    special_requests: $('textarea[name="special_requests"]').val(),
                    gdpr_consent: $('input[name="gdpr_consent"]').is(':checked') ? 1 : 0,
                    marketing_consent: $('input[name="marketing_consent"]').is(':checked') ? 1 : 0
                };
                
                // Submit via AJAX
                $.post(rrs_ajax.ajax_url, formData, function(response) {
                    var messageDiv = $('#rrs-messages');
                    
                    if (response.success) {
                        messageDiv.html('<strong>Success!</strong> Your reservation has been submitted. We will contact you soon to confirm.')
                                  .css({
                                      'background': '#d4edda',
                                      'color': '#155724',
                                      'border': '1px solid #c3e6cb'
                                  })
                                  .show();
                        
                        // Reset form
                        $('#rrs-booking-form')[0].reset();
                        
                        // Scroll to message
                        $('html, body').animate({
                            scrollTop: messageDiv.offset().top - 20
                        }, 500);
                        
                    } else {
                        messageDiv.html('<strong>Error!</strong> ' + response.data)
                                  .css({
                                      'background': '#f8d7da',
                                      'color': '#721c24',
                                      'border': '1px solid #f5c6cb'
                                  })
                                  .show();
                    }
                    
                    // Reset button state
                    $('#submit-text').show();
                    $('#loading-text').hide();
                    $('button[type="submit"]').prop('disabled', false);
                    
                }).fail(function() {
                    $('#rrs-messages')
                        .html('<strong>Error!</strong> Something went wrong. Please try again later.')
                        .css({
                            'background': '#f8d7da',
                            'color': '#721c24',
                            'border': '1px solid #f5c6cb'
                        })
                        .show();
                    
                    // Reset button state
                    $('#submit-text').show();
                    $('#loading-text').hide();
                    $('button[type="submit"]').prop('disabled', false);
                });
            });
        });
        </script>
        <?php
        
        return ob_get_clean();
    }
    
    public function handle_form_submission() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'rrs_booking_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Validate required fields
        $required_fields = ['customer_name', 'customer_email', 'customer_phone', 'party_size', 'reservation_date', 'reservation_time'];
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error('Please fill in all required fields');
            }
        }
        
        // Check GDPR consent
        if (empty($_POST['gdpr_consent'])) {
            wp_send_json_error('You must agree to data processing to make a reservation');
        }
        
        // Prepare data
        $reservation_data = array(
            'reservation_code' => 'RES-' . time() . '-' . rand(100, 999),
            'customer_name' => sanitize_text_field($_POST['customer_name']),
            'customer_email' => sanitize_email($_POST['customer_email']),
            'customer_phone' => sanitize_text_field($_POST['customer_phone']),
            'party_size' => intval($_POST['party_size']),
            'reservation_date' => sanitize_text_field($_POST['reservation_date']),
            'reservation_time' => sanitize_text_field($_POST['reservation_time']),
            'special_requests' => sanitize_textarea_field($_POST['special_requests']),
            'status' => 'pending' // Customer reservations start as pending
        );
        
        // Save to database
        global $wpdb;
        $result = $wpdb->insert($wpdb->prefix . 'rrs_reservations', $reservation_data);
        
        if ($result) {
            // Send confirmation email (you can implement this later)
            do_action('rrs_reservation_created', $wpdb->insert_id, $reservation_data);
            
            wp_send_json_success('Reservation submitted successfully!');
        } else {
            wp_send_json_error('Failed to save reservation. Please try again.');
        }
    }
}
