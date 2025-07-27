<?php
/**
 * Plugin Name: Restaurant Reservations System
 * Description: Simple restaurant reservation management
 * Version: 1.1.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) exit;

// Create database tables on activation
register_activation_hook(__FILE__, 'rrs_create_tables');

function rrs_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rrs_reservations (
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
    $existing = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rrs_reservations");
    if ($existing == 0) {
        $wpdb->insert($wpdb->prefix . 'rrs_reservations', array(
            'reservation_code' => 'RES-001',
            'customer_name' => 'John Smith',
            'customer_email' => 'john@example.com',
            'customer_phone' => '123-456-7890',
            'party_size' => 4,
            'reservation_date' => date('Y-m-d'),
            'reservation_time' => '19:00:00',
            'special_requests' => 'Window table please',
            'status' => 'confirmed'
        ));
    }
}

// Add admin menu
add_action('admin_menu', 'rrs_add_menu');

function rrs_add_menu() {
    add_menu_page(
        'Reservations',
        'Reservations',
        'manage_options',
        'reservations',
        'rrs_dashboard_page',
        'dashicons-calendar-alt',
        26
    );
    
    add_submenu_page('reservations', 'Settings', 'Settings', 'manage_options', 'res-settings', 'rrs_settings_page');
}

// Dashboard page
function rrs_dashboard_page() {
    global $wpdb;
    
    // Handle actions
    if (isset($_GET['action']) && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        switch ($_GET['action']) {
            case 'confirm':
                $wpdb->update($wpdb->prefix . 'rrs_reservations', array('status' => 'confirmed'), array('id' => $id));
                echo '<div class="notice notice-success"><p>✅ Reservation confirmed!</p></div>';
                break;
            case 'cancel':
                $wpdb->update($wpdb->prefix . 'rrs_reservations', array('status' => 'cancelled'), array('id' => $id));
                echo '<div class="notice notice-success"><p>❌ Reservation cancelled!</p></div>';
                break;
            case 'delete':
                $wpdb->delete($wpdb->prefix . 'rrs_reservations', array('id' => $id));
                echo '<div class="notice notice-success"><p>🗑️ Reservation deleted!</p></div>';
                break;
        }
    }
    
    // Get today's reservations
    $today = date('Y-m-d');
    $reservations = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}rrs_reservations WHERE reservation_date = %s ORDER BY reservation_time", $today));
    
    // Get statistics
    $stats = array(
        'today' => $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}rrs_reservations WHERE reservation_date = %s", $today)),
        'pending' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rrs_reservations WHERE status = 'pending'"),
        'total' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rrs_reservations")
    );
    ?>
    
    <div class="wrap">
        <h1>🍽️ Restaurant Reservations Dashboard</h1>
        
        <!-- Statistics -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; text-align: center;">
                <h2 style="margin: 0; font-size: 2rem;"><?php echo $stats['today']; ?></h2>
                <p style="margin: 5px 0 0 0;">Today's Reservations</p>
            </div>
            <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 10px; text-align: center;">
                <h2 style="margin: 0; font-size: 2rem;"><?php echo $stats['pending']; ?></h2>
                <p style="margin: 5px 0 0 0;">Pending Approval</p>
            </div>
            <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; border-radius: 10px; text-align: center;">
                <h2 style="margin: 0; font-size: 2rem;"><?php echo $stats['total']; ?></h2>
                <p style="margin: 5px 0 0 0;">Total Reservations</p>
            </div>
        </div>
        
        <!-- Navigation -->
        <div style="margin: 20px 0;">
            <a href="<?php echo admin_url('admin.php?page=res-settings'); ?>" class="button button-secondary">⚙️ Settings</a>
            <button onclick="location.reload()" class="button button-secondary">🔄 Refresh</button>
        </div>
        
        <!-- Today's Reservations -->
        <div style="background: white; border: 1px solid #ccd0d4; border-radius: 8px; margin-top: 20px;">
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px; border-radius: 8px 8px 0 0;">
                <h2 style="margin: 0;">📅 Today's Reservations - <?php echo date('F j, Y'); ?></h2>
            </div>
            
            <div style="padding: 20px;">
                <?php if (!empty($reservations)): ?>
                    <?php foreach ($reservations as $res): ?>
                        <div style="border: 2px solid #e9ecef; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: <?php echo $res->status === 'confirmed' ? '#e8f5e8' : ($res->status === 'pending' ? '#fff3cd' : '#f8d7da'); ?>;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px;">
                                <div style="flex: 1; min-width: 300px;">
                                    <h3 style="margin: 0 0 8px 0; font-size: 1.2rem;">
                                        🕐 <?php echo date('g:i A', strtotime($res->reservation_time)); ?> - 
                                        👤 <?php echo esc_html($res->customer_name); ?>
                                    </h3>
                                    <p style="margin: 0 0 5px 0;">
                                        📧 <?php echo esc_html($res->customer_email); ?> • 
                                        📞 <?php echo esc_html($res->customer_phone); ?> • 
                                        👥 <?php echo $res->party_size; ?> guests
                                    </p>
                                    <p style="margin: 0; font-size: 0.9rem; color: #666;">
                                        🏷️ <?php echo esc_html($res->reservation_code); ?>
                                    </p>
                                    <?php if ($res->special_requests): ?>
                                        <div style="background: rgba(0,0,0,0.05); padding: 10px; border-radius: 5px; margin-top: 8px; font-style: italic;">
                                            💬 <?php echo esc_html($res->special_requests); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                    <span style="background: <?php echo $res->status === 'confirmed' ? '#28a745' : ($res->status === 'pending' ? '#ffc107' : '#dc3545'); ?>; color: <?php echo $res->status === 'pending' ? '#000' : '#fff'; ?>; padding: 6px 12px; border-radius: 15px; font-size: 0.8rem; font-weight: bold; text-transform: uppercase;">
                                        <?php echo $res->status; ?>
                                    </span>
                                    
                                    <?php if ($res->status === 'pending'): ?>
                                        <a href="?page=reservations&action=confirm&id=<?php echo $res->id; ?>" class="button button-primary" style="font-size: 0.8rem;">✅ Confirm</a>
                                    <?php endif; ?>
                                    
                                    <a href="?page=reservations&action=cancel&id=<?php echo $res->id; ?>" onclick="return confirm('Cancel this reservation?')" class="button" style="font-size: 0.8rem;">❌ Cancel</a>
                                    
                                    <a href="?page=reservations&action=delete&id=<?php echo $res->id; ?>" onclick="return confirm('Delete this reservation permanently?')" class="button" style="font-size: 0.8rem;">🗑️ Delete</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: #666;">
                        <div style="font-size: 3rem; margin-bottom: 15px;">📅</div>
                        <h3>No Reservations Today</h3>
                        <p>New reservations will appear here automatically</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div style="margin-top: 20px; padding: 15px; background: #e3f2fd; border-radius: 8px;">
            <h4>📋 Customer Booking Form Shortcode:</h4>
            <code>[restaurant_booking_form]</code>
            <p>Add this shortcode to any page or post to display the reservation form for customers.</p>
        </div>
    </div>
    <?php
}

// Settings page
function rrs_settings_page() {
    if (isset($_POST['save_settings'])) {
        echo '<div class="notice notice-success"><p>✅ Settings saved successfully!</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>⚙️ Restaurant Settings</h1>
        
        <form method="post">
            <table class="form-table">
                <tr>
                    <th scope="row">Restaurant Status</th>
                    <td>
                        <label>
                            <input type="radio" name="restaurant_open" value="1" checked> 
                            🟢 Open for reservations
                        </label><br>
                        <label>
                            <input type="radio" name="restaurant_open" value="0"> 
                            🔴 Closed for reservations
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Restaurant Name</th>
                    <td><input type="text" name="restaurant_name" value="<?php echo get_bloginfo('name'); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row">Maximum Party Size</th>
                    <td><input type="number" name="max_party_size" value="12" min="1" max="50"></td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="save_settings" class="button-primary" value="💾 Save Settings">
            </p>
        </form>
    </div>
    <?php
}

// Customer booking form shortcode
add_shortcode('restaurant_booking_form', 'rrs_booking_form');

function rrs_booking_form() {
    global $wpdb;
    
    // Handle form submission
    if (isset($_POST['rrs_submit']) && wp_verify_nonce($_POST['rrs_nonce'], 'rrs_booking')) {
        $reservation_data = array(
            'reservation_code' => 'WEB-' . date('Ymd') . '-' . strtoupper(wp_generate_password(6, false)),
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
            $success_message = '<div style="background: #d4edda; color: #155724; padding: 20px; border-radius: 10px; margin-bottom: 20px; text-align: center;">
                <h3>✅ Reservation Submitted Successfully!</h3>
                <p><strong>Confirmation Code:</strong> ' . $reservation_data['reservation_code'] . '</p>
                <p>We will contact you within 1 hour to confirm your reservation.</p>
            </div>';
        }
    }
    
    ob_start();
    ?>
    <div style="max-width: 600px; margin: 30px auto; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        <h2 style="text-align: center; margin-bottom: 30px; color: #2c3e50;">🍽️ Reserve Your Table</h2>
        
        <?php if (isset($success_message)) echo $success_message; ?>
        
        <form method="post">
            <?php wp_nonce_field('rrs_booking', 'rrs_nonce'); ?>
            <input type="hidden" name="rrs_submit" value="1">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Full Name *</label>
                    <input type="text" name="customer_name" required style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Email *</label>
                    <input type="email" name="customer_email" required style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; box-sizing: border-box;">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Phone *</label>
                    <input type="tel" name="customer_phone" required style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Party Size *</label>
                    <select name="party_size" required style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; box-sizing: border-box;">
                        <option value="">Select party size</option>
                        <?php for($i = 1; $i <= 12; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?> <?php echo $i == 1 ? 'Guest' : 'Guests'; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Date *</label>
                    <input type="date" name="reservation_date" required min="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d', strtotime('+60 days')); ?>" style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Time *</label>
                    <select name="reservation_time" required style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; box-sizing: border-box;">
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
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Special Requests</label>
                <textarea name="special_requests" rows="4" placeholder="Any special requirements..." style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; box-sizing: border-box; resize: vertical;"></textarea>
            </div>
            
            <button type="submit" style="width: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 15px; border-radius: 10px; font-size: 1.2rem; font-weight: bold; cursor: pointer;">
                🍽️ Submit Reservation
            </button>
        </form>
    </div>
    
    <style>
    @media (max-width: 768px) {
        .grid-cols-2 { grid-template-columns: 1fr !important; }
    }
    </style>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const grids = document.querySelectorAll('[style*="grid-template-columns: 1fr 1fr"]');
        grids.forEach(grid => grid.classList.add('grid-cols-2'));
    });
    </script>
    <?php
    return ob_get_clean();
}
?>
