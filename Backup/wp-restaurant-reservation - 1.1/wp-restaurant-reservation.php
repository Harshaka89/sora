<?php
/**
 * Plugin Name: Restaurant Reservation System Pro
 * Description: Complete restaurant reservation management system
 * Version: 1.1.0
 * Author: Your Name
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('RRS_VERSION', '1.1.0');
define('RRS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RRS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Create database tables on activation
register_activation_hook(__FILE__, 'rrs_create_tables');

function rrs_create_tables() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // Simple reservations table
    $sql = "CREATE TABLE {$wpdb->prefix}rrs_reservations (
        id int(11) NOT NULL AUTO_INCREMENT,
        reservation_code varchar(20) NOT NULL,
        customer_name varchar(100) NOT NULL,
        customer_email varchar(100) NOT NULL,
        customer_phone varchar(20) NOT NULL,
        party_size int(11) NOT NULL,
        reservation_date date NOT NULL,
        reservation_time time NOT NULL,
        special_requests text,
        status varchar(20) DEFAULT 'pending',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Insert sample data
    $sample_data = array(
        array(
            'reservation_code' => 'RES-001',
            'customer_name' => 'John Smith',
            'customer_email' => 'john@example.com',
            'customer_phone' => '123-456-7890',
            'party_size' => 4,
            'reservation_date' => date('Y-m-d'),
            'reservation_time' => '19:00:00',
            'special_requests' => 'Window table please',
            'status' => 'confirmed'
        ),
        array(
            'reservation_code' => 'RES-002',
            'customer_name' => 'Sarah Johnson',
            'customer_email' => 'sarah@example.com',
            'customer_phone' => '987-654-3210',
            'party_size' => 2,
            'reservation_date' => date('Y-m-d'),
            'reservation_time' => '20:30:00',
            'special_requests' => 'Anniversary dinner',
            'status' => 'pending'
        )
    );
    
    foreach ($sample_data as $reservation) {
        $wpdb->insert($wpdb->prefix . 'rrs_reservations', $reservation);
    }
}

// Add admin menu
add_action('admin_menu', 'rrs_add_admin_menu');

function rrs_add_admin_menu() {
    add_menu_page(
        'Restaurant Reservations',
        'Reservations',
        'manage_options',
        'restaurant-reservations',
        'rrs_admin_page',
        'dashicons-calendar-alt',
        26
    );
}

// Admin page
function rrs_admin_page() {
    global $wpdb;
    
    // Handle actions
    if (isset($_GET['action']) && $_GET['action'] === 'confirm' && isset($_GET['id'])) {
        $wpdb->update(
            $wpdb->prefix . 'rrs_reservations',
            array('status' => 'confirmed'),
            array('id' => intval($_GET['id']))
        );
        echo '<div class="notice notice-success"><p>Reservation confirmed!</p></div>';
    }
    
    // Get statistics
    $today = date('Y-m-d');
    $stats = array(
        'today' => $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}rrs_reservations WHERE reservation_date = %s", $today)),
        'pending' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rrs_reservations WHERE status = 'pending'"),
        'total' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rrs_reservations")
    );
    
    // Get today's reservations
    $reservations = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}rrs_reservations WHERE reservation_date = %s ORDER BY reservation_time",
        $today
    ));
    
    ?>
    <div class="wrap">
        <h1>🍽️ Restaurant Reservations Pro v1.1 
            <span style="background: #0073aa; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; margin-left: 10px;">ACTIVE</span>
        </h1>
        
        <!-- Statistics Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0;">
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 12px; text-align: center;">
                <h2 style="margin: 0; font-size: 36px;"><?php echo $stats['today']; ?></h2>
                <p style="margin: 8px 0 0 0;">Today's Reservations</p>
            </div>
            <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 12px; text-align: center;">
                <h2 style="margin: 0; font-size: 36px;"><?php echo $stats['pending']; ?></h2>
                <p style="margin: 8px 0 0 0;">Pending Approval</p>
            </div>
            <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; border-radius: 12px; text-align: center;">
                <h2 style="margin: 0; font-size: 36px;"><?php echo $stats['total']; ?></h2>
                <p style="margin: 8px 0 0 0;">Total Reservations</p>
            </div>
        </div>
        
        <!-- Today's Schedule -->
        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h2>📅 Today's Schedule - <?php echo date('F j, Y'); ?></h2>
            
            <?php if (!empty($reservations)): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>Party Size</th>
                            <th>Status</th>
                            <th>Requests</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $res): ?>
                        <tr>
                            <td><strong><?php echo date('g:i A', strtotime($res->reservation_time)); ?></strong></td>
                            <td><?php echo esc_html($res->customer_name); ?></td>
                            <td>
                                <?php echo esc_html($res->customer_email); ?><br>
                                <small><?php echo esc_html($res->customer_phone); ?></small>
                            </td>
                            <td style="text-align: center;">
                                <span style="background: #e3f2fd; padding: 4px 8px; border-radius: 12px; font-weight: bold;">
                                    <?php echo $res->party_size; ?>
                                </span>
                            </td>
                            <td>
                                <span style="background: <?php echo $res->status === 'confirmed' ? '#4caf50' : '#ff9800'; ?>; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold;">
                                    <?php echo ucfirst($res->status); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html($res->special_requests ?: 'None'); ?></td>
                            <td>
                                <?php if ($res->status === 'pending'): ?>
                                    <a href="<?php echo admin_url('admin.php?page=restaurant-reservations&action=confirm&id=' . $res->id); ?>" 
                                       style="background: #28a745; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-size: 12px;">
                                        ✅ Confirm
                                    </a>
                                <?php else: ?>
                                    <span style="color: #28a745; font-weight: bold;">✅ Confirmed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; padding: 40px; color: #666;">No reservations for today.</p>
            <?php endif; ?>
        </div>
        
        <!-- Quick Actions -->
        <div style="margin-top: 20px;">
            <h3>🚀 Quick Actions</h3>
            <a href="<?php echo admin_url('admin.php?page=restaurant-reservations'); ?>" 
               style="background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; margin-right: 10px;">
                🔄 Refresh Dashboard
            </a>
            <button onclick="alert('Customer booking form shortcode: [restaurant_booking_form]')" 
                    style="background: #17a2b8; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer;">
                📋 Get Shortcode
            </button>
        </div>
    </div>
    <?php
}

// Customer booking form shortcode
add_shortcode('restaurant_booking_form', 'rrs_booking_form');

function rrs_booking_form() {
    // Handle form submission
    if (isset($_POST['rrs_submit']) && wp_verify_nonce($_POST['rrs_nonce'], 'rrs_booking')) {
        global $wpdb;
        
        $reservation_data = array(
            'reservation_code' => 'WEB-' . time(),
            'customer_name' => sanitize_text_field($_POST['customer_name']),
            'customer_email' => sanitize_email($_POST['customer_email']),
            'customer_phone' => sanitize_text_field($_POST['customer_phone']),
            'party_size' => intval($_POST['party_size']),
            'reservation_date' => sanitize_text_field($_POST['reservation_date']),
            'reservation_time' => sanitize_text_field($_POST['reservation_time']),
            'special_requests' => sanitize_textarea_field($_POST['special_requests']),
            'status' => 'pending'
        );
        
        $result = $wpdb->insert($wpdb->prefix . 'rrs_reservations', $reservation_data);
        
        if ($result) {
            $success_message = '<div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;"><strong>Success!</strong> Your reservation has been submitted!</div>';
        } else {
            $error_message = '<div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;"><strong>Error!</strong> Please try again.</div>';
        }
    }
    
    ob_start();
    ?>
    <div style="max-width: 600px; margin: 20px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h2 style="text-align: center; margin-bottom: 30px;">🍽️ Make a Reservation</h2>
        
        <?php if (isset($success_message)) echo $success_message; ?>
        <?php if (isset($error_message)) echo $error_message; ?>
        
        <form method="post" style="display: grid; gap: 20px;">
            <?php wp_nonce_field('rrs_booking', 'rrs_nonce'); ?>
            <input type="hidden" name="rrs_submit" value="1">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Full Name *</label>
                    <input type="text" name="customer_name" required style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 5px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Email *</label>
                    <input type="email" name="customer_email" required style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 5px;">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Phone *</label>
                    <input type="tel" name="customer_phone" required style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 5px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Party Size *</label>
                    <select name="party_size" required style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 5px;">
                        <?php for($i = 1; $i <= 12; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?> <?php echo $i == 1 ? 'Guest' : 'Guests'; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Date *</label>
                    <input type="date" name="reservation_date" required min="<?php echo date('Y-m-d'); ?>" style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 5px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Time *</label>
                    <select name="reservation_time" required style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 5px;">
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
                    </select>
                </div>
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Special Requests</label>
                <textarea name="special_requests" rows="3" style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 5px;" placeholder="Any special requirements or requests..."></textarea>
            </div>
            
            <button type="submit" style="background: #28a745; color: white; border: none; padding: 15px; border-radius: 5px; font-size: 18px; font-weight: bold; cursor: pointer;">
                🍽️ Submit Reservation
            </button>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

// Success message
add_action('admin_notices', function() {
    if (get_current_screen()->id === 'toplevel_page_restaurant-reservations') {
        echo '<div class="notice notice-success is-dismissible"><p><strong>Restaurant Reservation System Pro v1.1</strong> is now active and working!</p></div>';
    }
});
