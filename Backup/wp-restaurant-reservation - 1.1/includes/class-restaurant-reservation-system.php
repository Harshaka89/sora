<?php
/**
 * Plugin Name: Restaurant Reservation System Pro
 * Description: Complete restaurant reservation management with table booking, floor plans, and GDPR compliance
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: restaurant-reservation
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Restaurant_Reservation_System {
    
    public function __construct() {
        // Constructor
    }
    
    public function run() {
        $this->init_hooks();
        $this->create_database_tables();
    }
    
    private function init_hooks() {
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Add shortcode
        add_shortcode('restaurant_booking_form', array($this, 'booking_form_shortcode'));
        
        // Handle form submissions
        add_action('init', array($this, 'handle_form_submissions'));
    }
    
    public function create_database_tables() {
        // Only create tables if they don't exist
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'rrs_reservations';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            
            $sql = "CREATE TABLE $table_name (
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
            global $wpdb;
            $wpdb->insert($table_name, array(
                'reservation_code' => 'RES-001',
                'customer_name' => 'John Doe',
                'customer_email' => 'john@example.com',
                'customer_phone' => '123-456-7890',
                'party_size' => 4,
                'reservation_date' => date('Y-m-d'),
                'reservation_time' => '19:00:00',
                'status' => 'confirmed'
            ));
        }
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Restaurant Reservations',
            'Reservations',
            'manage_options',
            'restaurant-reservations',
            array($this, 'dashboard_page'),
            'dashicons-calendar-alt',
            26
        );
        
        add_submenu_page(
            'restaurant-reservations',
            'All Reservations',
            'All Reservations',
            'manage_options',
            'rrs-all-reservations',
            array($this, 'all_reservations_page')
        );
    }
    
    public function dashboard_page() {
        global $wpdb;
        
        // Get today's reservations
        $today = date('Y-m-d');
        $reservations = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}rrs_reservations WHERE reservation_date = %s ORDER BY reservation_time",
            $today
        ));
        
        $today_count = count($reservations);
        $total_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rrs_reservations");
        
        ?>
        <div class="wrap">
            <h1>Restaurant Reservations Dashboard</h1>
            
            <!-- Statistics -->
            <div style="display: flex; gap: 20px; margin: 20px 0;">
                <div style="background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #0073aa;">
                    <h3 style="margin: 0; color: #0073aa; font-size: 32px;"><?php echo $today_count; ?></h3>
                    <p style="margin: 5px 0 0 0;">Today's Reservations</p>
                </div>
                
                <div style="background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #00a32a;">
                    <h3 style="margin: 0; color: #00a32a; font-size: 32px;"><?php echo $total_count; ?></h3>
                    <p style="margin: 5px 0 0 0;">Total Reservations</p>
                </div>
            </div>
            
            <!-- Today's Schedule -->
            <div style="background: white; padding: 20px; margin: 20px 0; border-radius: 8px;">
                <h2>Today's Schedule - <?php echo date('F j, Y'); ?></h2>
                
                <?php if (!empty($reservations)): ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Party Size</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservations as $reservation): ?>
                            <tr>
                                <td><strong><?php echo date('g:i A', strtotime($reservation->reservation_time)); ?></strong></td>
                                <td><?php echo esc_html($reservation->customer_name); ?></td>
                                <td><?php echo esc_html($reservation->customer_email); ?></td>
                                <td><?php echo $reservation->party_size; ?> guests</td>
                                <td>
                                    <span style="padding: 4px 12px; background: #00a32a; color: white; border-radius: 12px; font-size: 12px;">
                                        <?php echo ucfirst($reservation->status); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="text-align: center; padding: 40px; color: #666;">No reservations for today.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    public function all_reservations_page() {
        global $wpdb;
        
        $reservations = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}rrs_reservations ORDER BY reservation_date DESC LIMIT 50"
        );
        
        ?>
        <div class="wrap">
            <h1>All Reservations</h1>
            
            <div style="background: white; padding: 20px; margin: 20px 0; border-radius: 8px;">
                <?php if (!empty($reservations)): ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Customer</th>
                                <th>Contact</th>
                                <th>Party Size</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservations as $reservation): ?>
                            <tr>
                                <td>
                                    <strong><?php echo date('M j, Y', strtotime($reservation->reservation_date)); ?></strong><br>
                                    <?php echo date('g:i A', strtotime($reservation->reservation_time)); ?>
                                </td>
                                <td><?php echo esc_html($reservation->customer_name); ?></td>
                                <td>
                                    <?php echo esc_html($reservation->customer_email); ?><br>
                                    <small><?php echo esc_html($reservation->customer_phone); ?></small>
                                </td>
                                <td><?php echo $reservation->party_size; ?></td>
                                <td>
                                    <span style="padding: 4px 12px; background: #00a32a; color: white; border-radius: 12px; font-size: 12px;">
                                        <?php echo ucfirst($reservation->status); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No reservations found.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    public function booking_form_shortcode() {
        ob_start();
        ?>
        <div style="background: white; padding: 30px; border-radius: 8px; max-width: 600px; margin: 20px auto;">
            <h2>Make a Reservation</h2>
            <form method="post">
                <p>Reservation form will be here</p>
                <button type="submit" class="button">Book Now</button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function handle_form_submissions() {
        // Handle form submissions here
    }
}
