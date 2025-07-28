<?php
if (!defined('ABSPATH')) exit;

// Helper function to safely get object properties
function yrr_get_property_dash($object, $property, $default = '') {
    if (is_object($object) && property_exists($object, $property) && !empty($object->$property)) {
        return $object->$property;
    }
    return $default;
}

// Rest of your dashboard code continues here...
?>

// Check user permissions
$current_user = wp_get_current_user();
$is_super_admin = in_array('administrator', $current_user->roles);
$is_admin = $is_super_admin || in_array('yrr_admin', $current_user->roles);

if (!$is_admin) {
    wp_die('You do not have sufficient permissions to access this page.');
}

// Your existing dashboard content continues here...
?>


<div class="wrap">
    <div style="max-width: 1400px; margin: 20px auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        
        <!-- Header with Role Indicator -->
        <div style="text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #667eea;">
            <h1 style="font-size: 2.5rem; color: #2c3e50; margin: 0;">🏪 <?php echo esc_html($restaurant_name); ?> Dashboard</h1>
            <p style="color: #6c757d; margin: 10px 0 0 0;">Yenolx Restaurant Reservation System v1.5.1</p>
            <div style="margin-top: 15px; display: flex; justify-content: center; gap: 15px; align-items: center;">
                <span style="background: <?php echo $restaurant_status == '1' ? '#28a745' : '#dc3545'; ?>; color: white; padding: 8px 16px; border-radius: 20px; font-weight: bold;">
                    <?php echo $restaurant_status == '1' ? '🟢 OPEN' : '🔴 CLOSED'; ?>
                </span>
                <span style="background: <?php echo $is_super_admin ? '#dc3545' : '#007cba'; ?>; color: white; padding: 8px 16px; border-radius: 20px; font-weight: bold;">
                    <?php echo $is_super_admin ? '👑 SUPER ADMIN' : '👤 ADMIN'; ?>
                </span>
            </div>
        </div>
        
     <!-- Success/Error Messages -->
<?php if (isset($_GET['message'])): ?>
    <div style="padding: 15px; margin: 20px 0; border-radius: 8px; border: 2px solid; <?php
        switch($_GET['message']) {
            case 'reservation_created':
                echo 'background: #d4edda; color: #155724; border-color: #28a745;';
                $msg = '✅ Manual reservation created successfully!';
                break;
            case 'missing_fields':
                echo 'background: #f8d7da; color: #721c24; border-color: #dc3545;';
                $msg = '❌ Missing required fields. Please fill in all required information.';
                break;
            case 'invalid_nonce':
                echo 'background: #f8d7da; color: #721c24; border-color: #dc3545;';
                $msg = '❌ Security check failed. Please try again.';
                break;
            case 'db_error':
                echo 'background: #f8d7da; color: #721c24; border-color: #dc3545;';
                $msg = '❌ Database error occurred. Please check if the reservations table exists.';
                break;
            case 'exception_error':
                echo 'background: #f8d7da; color: #721c24; border-color: #dc3545;';
                $msg = '❌ System error occurred. Please check the error logs for details.';
                break;
            default:
                echo 'background: #f8d7da; color: #721c24; border-color: #dc3545;';
                $msg = '❌ An error occurred. Please check the system logs for details.';
        }
    ?>">
        <h4 style="margin: 0;"><?php echo $msg; ?></h4>
    </div>
<?php endif; ?>


        
        <!-- Statistics Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 15px; text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 10px;">📊</div>
                <div style="font-size: 2.5rem; font-weight: bold; margin-bottom: 5px;"><?php echo intval($statistics['total'] ?? 0); ?></div>
                <div style="font-size: 1.1rem; opacity: 0.9;">Total Reservations</div>
            </div>
            
            <div style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 25px; border-radius: 15px; text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 10px;">✅</div>
                <div style="font-size: 2.5rem; font-weight: bold; margin-bottom: 5px;"><?php echo intval($statistics['confirmed'] ?? 0); ?></div>
                <div style="font-size: 1.1rem; opacity: 0.9;">Confirmed Today</div>
            </div>
            
            <div style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white; padding: 25px; border-radius: 15px; text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 10px;">⏳</div>
                <div style="font-size: 2.5rem; font-weight: bold; margin-bottom: 5px;"><?php echo intval($statistics['pending'] ?? 0); ?></div>
                <div style="font-size: 1.1rem; opacity: 0.9;">Pending Approval</div>
            </div>
            
            <div style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white; padding: 25px; border-radius: 15px; text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 10px;">📅</div>
                <div style="font-size: 2.5rem; font-weight: bold; margin-bottom: 5px;"><?php echo intval($statistics['today'] ?? 0); ?></div>
                <div style="font-size: 1.1rem; opacity: 0.9;">Today's Bookings</div>
            </div>
        </div>
        
        <!-- Quick Actions with Manual Reservation -->
        <div style="margin-bottom: 30px; padding: 25px; background: #f8f9fa; border-radius: 15px; border: 3px solid #e9ecef;">
            <h3 style="margin: 0 0 20px 0; color: #2c3e50;">🚀 Quick Actions</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <!-- Manual Reservation Button - NEW -->
                <button onclick="showManualReservationModal()" 
                   style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 15px 20px; border: none; border-radius: 10px; text-align: center; font-weight: bold; cursor: pointer; font-size: 1rem;">
                    ➕ Create Manual Reservation
                </button>
                
                <a href="<?php echo admin_url('admin.php?page=yrr-all-reservations'); ?>" 
                   style="background: linear-gradient(135deg, #007cba 0%, #004d7a 100%); color: white; padding: 15px 20px; text-decoration: none; border-radius: 10px; text-align: center; font-weight: bold;">
                    📋 All Reservations
                </a>
                
                <?php if ($is_super_admin): ?>
                <a href="<?php echo admin_url('admin.php?page=yrr-tables'); ?>" 
                   style="background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%); color: white; padding: 15px 20px; text-decoration: none; border-radius: 10px; text-align: center; font-weight: bold;">
                    🍽️ Manage Tables
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=yrr-hours'); ?>" 
                   style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: white; padding: 15px 20px; text-decoration: none; border-radius: 10px; text-align: center; font-weight: bold;">
                    ⏰ Operating Hours
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=yrr-pricing'); ?>" 
                   style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 15px 20px; text-decoration: none; border-radius: 10px; text-align: center; font-weight: bold;">
                    💰 Pricing Rules
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=yrr-coupons'); ?>" 
                   style="background: linear-gradient(135deg, #fd7e14 0%, #e8590c 100%); color: white; padding: 15px 20px; text-decoration: none; border-radius: 10px; text-align: center; font-weight: bold;">
                    🎫 Discount Coupons
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=yrr-settings'); ?>" 
                   style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: white; padding: 15px 20px; text-decoration: none; border-radius: 10px; text-align: center; font-weight: bold;">
                    ⚙️ Settings
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Today's Reservations (same as before) -->
        <div style="background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px;">
                <h3 style="margin: 0; font-size: 1.8rem;">📅 Today's Reservations (<?php echo date('M j, Y'); ?>)</h3>
            </div>
            
            <div style="padding: 20px;">
                <?php if (!empty($today_reservations) && is_array($today_reservations)): ?>
                    
                    <div style="display: grid; gap: 15px;">
                        <?php foreach ($today_reservations as $reservation): ?>
                            <?php if (!is_object($reservation)) continue; ?>
                            
                            <div style="padding: 20px; border: 2px solid #e9ecef; border-radius: 10px; background: white; display: grid; grid-template-columns: auto 1fr auto auto; gap: 20px; align-items: center;">
                                
                                <!-- Status Badge -->
                                <div>
                                    <?php 
                                    $status = yrr_get_property_dash($reservation, 'status', 'pending');
                                    $status_colors = array(
                                        'confirmed' => '#28a745',
                                        'pending' => '#ffc107',
                                        'cancelled' => '#dc3545'
                                    );
                                    $text_color = $status === 'pending' ? '#000' : '#fff';
                                    ?>
                                    <span style="background: <?php echo $status_colors[$status] ?? '#6c757d'; ?>; color: <?php echo $text_color; ?>; padding: 10px 15px; border-radius: 20px; font-size: 0.9rem; font-weight: bold; text-transform: uppercase;">
                                        <?php echo esc_html($status); ?>
                                    </span>
                                </div>
                                
                                <!-- Customer Info -->
                                <div>
                                    <div style="font-weight: bold; font-size: 1.2rem; color: #2c3e50; margin-bottom: 5px;">
                                        👤 <?php echo esc_html(yrr_get_property_dash($reservation, 'customer_name', 'Unknown Customer')); ?>
                                    </div>
                                    <div style="color: #6c757d; font-size: 0.9rem; margin-bottom: 3px;">
                                        📧 <?php echo esc_html(yrr_get_property_dash($reservation, 'customer_email', 'No email')); ?>
                                    </div>
                                    <div style="color: #6c757d; font-size: 0.9rem;">
                                        📞 <?php echo esc_html(yrr_get_property_dash($reservation, 'customer_phone', 'No phone')); ?>
                                    </div>
                                </div>
                                
                                <!-- Reservation Details -->
                                <div style="text-align: center;">
                                    <div style="font-weight: bold; font-size: 1.3rem; color: #007cba; margin-bottom: 5px;">
                                        <?php echo date('g:i A', strtotime(yrr_get_property_dash($reservation, 'reservation_time', '00:00:00'))); ?>
                                    </div>
                                    <div style="background: #e3f2fd; color: #1976d2; padding: 5px 10px; border-radius: 10px; font-weight: bold;">
                                        👥 <?php echo intval(yrr_get_property_dash($reservation, 'party_size', 1)); ?> guests
                                    </div>
                                    <?php 
                                    $table_id = yrr_get_property_dash($reservation, 'table_id');
                                    if ($table_id): ?>
                                        <div style="margin-top: 5px; color: #6c757d; font-size: 0.9rem;">
                                            🍽️ Table <?php echo esc_html($table_id); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Actions -->
                                <div style="display: flex; gap: 8px; flex-direction: column;">
                                    <?php $reservation_id = yrr_get_property_dash($reservation, 'id'); ?>
                                    <?php if ($reservation_id && $status === 'pending'): ?>
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=yenolx-reservations&action=confirm&id=' . $reservation_id), 'reservation_action'); ?>" 
                                           style="background: #28a745; color: white; padding: 8px 12px; text-decoration: none; border-radius: 5px; font-size: 0.8rem; font-weight: bold; text-align: center;">
                                            ✅ Confirm
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($reservation_id): ?>
                                        <button onclick="editReservation(<?php echo htmlspecialchars(json_encode($reservation)); ?>)" 
                                                style="background: #17a2b8; color: white; border: none; padding: 8px 12px; border-radius: 5px; font-size: 0.8rem; font-weight: bold; cursor: pointer;">
                                            ✏️ Edit
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Special Requests -->
                            <?php 
                            $special_requests = yrr_get_property_dash($reservation, 'special_requests');
                            if ($special_requests): ?>
                                <div style="padding: 10px 20px; background: rgba(0,123,186,0.05); border-radius: 8px; margin-top: -5px; font-size: 0.9rem; border-left: 4px solid #007cba;">
                                    <strong>💬 Special Requests:</strong> <?php echo esc_html($special_requests); ?>
                                </div>
                            <?php endif; ?>
                            
                        <?php endforeach; ?>
                    </div>
                    
                <?php else: ?>
                    
                    <!-- No Reservations Today -->
                    <div style="text-align: center; padding: 40px; color: #6c757d;">
                        <div style="font-size: 4rem; margin-bottom: 20px; opacity: 0.3;">📅</div>
                        <h3 style="margin: 0 0 15px 0;">No Reservations Today</h3>
                        <p>No reservations scheduled for today. Create a manual reservation or check back later!</p>
                        <button onclick="showManualReservationModal()" 
                                style="background: #28a745; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; margin-top: 15px; cursor: pointer;">
                            ➕ Create Manual Reservation
                        </button>
                    </div>
                    
                <?php endif; ?>
            </div>
        </div>
        
        <!-- System Health Check (same as before) -->
        <div style="margin-top: 30px; padding: 20px; background: #e8f5e8; border-radius: 10px; border-left: 5px solid #28a745;">
            <h4 style="margin: 0 0 15px 0; color: #155724;">🔧 System Health Status</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; font-size: 0.9rem;">
                <?php
                global $wpdb;
                $tables = array('yrr_settings', 'yrr_reservations', 'yrr_tables', 'yrr_operating_hours', 'yrr_pricing_rules', 'yrr_coupons');
                $all_good = true;
                
                foreach ($tables as $table) {
                    $full_table_name = $wpdb->prefix . $table;
                    $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") == $full_table_name;
                    if (!$exists) $all_good = false;
                    echo '<div>' . ($exists ? '✅' : '❌') . ' ' . ucfirst(str_replace('yrr_', '', $table)) . '</div>';
                }
                ?>
                <div><?php echo $all_good ? '✅' : '⚠️'; ?> Overall Status: <?php echo $all_good ? 'Healthy' : 'Needs Attention'; ?></div>
                <div>👤 Access Level: <?php echo $is_super_admin ? 'Super Admin' : 'Admin'; ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Manual Reservation Modal -->
<div id="manualReservationModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 20px; width: 90%; max-width: 700px; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #e9ecef;">
            <h3 style="margin: 0;">➕ Create Manual Reservation</h3>
            <button onclick="closeManualReservationModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6c757d;">×</button>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field('create_manual_reservation', 'manual_reservation_nonce'); ?>
            <input type="hidden" name="create_manual_reservation" value="1">
            
            <!-- Customer Information -->
            <div style="background: #e3f2fd; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                <h4 style="margin: 0 0 15px 0; color: #1976d2;">👤 Customer Information</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Customer Name *</label>
                        <input type="text" name="customer_name" required 
                               style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Email Address *</label>
                        <input type="email" name="customer_email" required 
                               style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                    </div>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">Phone Number *</label>
                    <input type="tel" name="customer_phone" required 
                           style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                </div>
            </div>
            
            <!-- Reservation Details -->
            <div style="background: #e8f5e8; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                <h4 style="margin: 0 0 15px 0; color: #155724;">📅 Reservation Details</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Date *</label>
                        <input type="date" name="reservation_date" required min="<?php echo date('Y-m-d'); ?>"
                               style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Time *</label>
                        <input type="time" name="reservation_time" required 
                               style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Party Size *</label>
                        <select name="party_size" required style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                            <?php for($i = 1; $i <= 20; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?> <?php echo $i == 1 ? 'Guest' : 'Guests'; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">Special Requests</label>
                    <textarea name="special_requests" rows="3" placeholder="Any special requirements..." 
                              style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;"></textarea>
                </div>
            </div>
            
            <!-- Admin Options -->
            <div style="background: #fff3cd; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                <h4 style="margin: 0 0 15px 0; color: #856404;">⚙️ Admin Options</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Initial Status</label>
                        <select name="initial_status" style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                            <option value="confirmed">✅ Confirmed</option>
                            <option value="pending">⏳ Pending</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Admin Notes</label>
                        <input type="text" name="admin_notes" placeholder="Internal notes..." 
                               style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                    </div>
                </div>
            </div>
            
            <div style="text-align: right; padding-top: 20px; border-top: 2px solid #e9ecef;">
                <button type="button" onclick="closeManualReservationModal()" 
                        style="background: #6c757d; color: white; border: none; padding: 12px 24px; border-radius: 8px; margin-right: 15px; cursor: pointer; font-weight: bold;">
                    Cancel
                </button>
                <button type="submit" 
                        style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-weight: bold;">
                    ➕ Create Reservation
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal (existing code) -->
<div id="editModal" class="yrr-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 20px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #e9ecef;">
            <h3 style="margin: 0;">✏️ Edit Reservation</h3>
            <button onclick="closeModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6c757d;">×</button>
        </div>
        
        <form method="post" action="<?php echo admin_url('admin.php?page=yenolx-reservations'); ?>">
            <?php wp_nonce_field('edit_reservation', 'edit_nonce'); ?>
            <input type="hidden" id="edit_id" name="reservation_id">
            <input type="hidden" name="edit_reservation" value="1">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">Customer Name *</label>
                    <input type="text" id="edit_name" name="customer_name" required style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">Email Address *</label>
                    <input type="email" id="edit_email" name="customer_email" required style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">Phone Number *</label>
                    <input type="tel" id="edit_phone" name="customer_phone" required style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">Party Size *</label>
                    <select id="edit_party" name="party_size" required style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                        <?php for($i = 1; $i <= 20; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?> <?php echo $i == 1 ? 'Guest' : 'Guests'; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">Reservation Date *</label>
                    <input type="date" id="edit_date" name="reservation_date" required style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">Reservation Time *</label>
                    <input type="time" id="edit_time" name="reservation_time" required style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                </div>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 8px; font-weight: bold;">Special Requests</label>
                <textarea id="edit_requests" name="special_requests" rows="3" placeholder="Any special requirements..." style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;"></textarea>
            </div>
            
            <div style="text-align: right; padding-top: 20px; border-top: 2px solid #e9ecef;">
                <button type="button" onclick="closeModal()" style="background: #6c757d; color: white; border: none; padding: 12px 24px; border-radius: 8px; margin-right: 15px; cursor: pointer; font-weight: bold;">Cancel</button>
                <button type="submit" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-weight: bold;">💾 Update Reservation</button>
            </div>
        </form>
    </div>
</div>

<script>
// Manual Reservation Modal Functions
function showManualReservationModal() {
    document.getElementById('manualReservationModal').style.display = 'flex';
}

function closeManualReservationModal() {
    document.getElementById('manualReservationModal').style.display = 'none';
}

// Edit Modal Functions (existing)
function editReservation(res) {
    document.getElementById('edit_id').value = res.id || '';
    document.getElementById('edit_name').value = res.customer_name || '';
    document.getElementById('edit_email').value = res.customer_email || '';
    document.getElementById('edit_phone').value = res.customer_phone || '';
    document.getElementById('edit_party').value = res.party_size || '1';
    document.getElementById('edit_date').value = res.reservation_date || '';
    document.getElementById('edit_time').value = res.reservation_time || '';
    document.getElementById('edit_requests').value = res.special_requests || '';
    
    document.getElementById('editModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Close modals when clicking outside
document.getElementById('manualReservationModal').addEventListener('click', function(e) {
    if (e.target === this) closeManualReservationModal();
});

document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

<style>
@media (max-width: 1200px) {
    div[style*="grid-template-columns: auto 1fr auto auto"] {
        grid-template-columns: 1fr !important;
        gap: 10px !important;
    }
}

@media (max-width: 768px) {
    div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
    
    div[style*="grid-template-columns: 1fr 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}

button:hover, a[style*="background:"]:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
}
</style>




<!-- Enhanced Edit Modal with Table Assignment -->
<div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 20px; width: 90%; max-width: 700px; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #e9ecef;">
            <h3 style="margin: 0;">✏️ Edit Reservation</h3>
            <button onclick="closeModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6c757d;">×</button>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field('edit_reservation', 'edit_nonce'); ?>
            <input type="hidden" id="edit_id" name="reservation_id">
            <input type="hidden" name="edit_reservation" value="1">
            
            <!-- Customer Information -->
            <div style="background: #e3f2fd; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                <h4 style="margin: 0 0 15px 0; color: #1976d2;">👤 Customer Information</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Customer Name *</label>
                        <input type="text" id="edit_name" name="customer_name" required style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Email *</label>
                        <input type="email" id="edit_email" name="customer_email" required style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Phone *</label>
                        <input type="tel" id="edit_phone" name="customer_phone" required style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Party Size *</label>
                        <select id="edit_party" name="party_size" required style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                            <?php for($i = 1; $i <= 20; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?> <?php echo $i == 1 ? 'Guest' : 'Guests'; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Reservation Details -->
            <div style="background: #e8f5e8; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                <h4 style="margin: 0 0 15px 0; color: #155724;">📅 Reservation Details</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Date *</label>
                        <input type="date" id="edit_date" name="reservation_date" required style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Time *</label>
                        <input type="time" id="edit_time" name="reservation_time" required style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">🍽️ Table</label>
                        <select id="edit_table" name="table_id" style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                            <option value="">No Table</option>
                            <?php 
                            global $wpdb;
                            $tables = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}yrr_tables ORDER BY table_number");
                            if ($tables) {
                                foreach ($tables as $table): ?>
                                    <option value="<?php echo $table->id; ?>">
                                        <?php echo esc_html($table->table_number); ?> (<?php echo $table->capacity; ?> seats - <?php echo esc_html($table->location); ?>)
                                    </option>
                                <?php endforeach;
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Additional Information -->
            <div style="background: #fff3cd; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                <h4 style="margin: 0 0 15px 0; color: #856404;">📝 Additional Information</h4>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">Special Requests</label>
                    <textarea id="edit_requests" name="special_requests" rows="3" style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;"></textarea>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">Admin Notes</label>
                    <textarea id="edit_notes" name="notes" rows="2" style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;"></textarea>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div style="text-align: right; padding-top: 20px; border-top: 2px solid #e9ecef;">
                <button type="button" onclick="closeModal()" style="background: #6c757d; color: white; border: none; padding: 12px 24px; border-radius: 8px; margin-right: 15px; cursor: pointer; font-weight: bold;">Cancel</button>
                <button type="submit" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-weight: bold;">💾 Update Reservation</button>
            </div>
        </form>
    </div>
</div>


<!-- Confirm with Table Assignment Modal -->
<div id="confirmTableModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 20px; width: 90%; max-width: 700px; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #e9ecef;">
            <h3 style="margin: 0;">✅ Confirm Reservation & Assign Table</h3>
            <button onclick="closeConfirmTableModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6c757d;">×</button>
        </div>
        
        <!-- Customer Info Display -->
        <div style="background: #e3f2fd; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
            <h4 style="margin: 0 0 15px 0; color: #1976d2;">👤 Customer Reservation Details</h4>
            <div id="confirm_customer_info">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field('confirm_with_table', 'confirm_table_nonce'); ?>
            <input type="hidden" id="confirm_reservation_id" name="reservation_id">
            <input type="hidden" name="confirm_with_table_action" value="1">
            
            <!-- Available Tables Grid -->
            <div style="margin-bottom: 20px;">
                <h4 style="margin: 0 0 15px 0; color: #2c3e50;">🍽️ Select Table to Assign</h4>
                <div id="confirm_tables_grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px; max-height: 300px; overflow-y: auto;">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>
            
            <!-- Selected Table Display -->
            <div id="confirm_selected_table" style="display: none; background: #e8f5e8; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                <h4 style="margin: 0 0 10px 0; color: #155724;">✅ Selected Table</h4>
                <div id="confirm_selected_details">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div style="text-align: right; padding-top: 20px; border-top: 2px solid #e9ecef;">
                <button type="button" onclick="closeConfirmTableModal()" style="background: #6c757d; color: white; border: none; padding: 12px 24px; border-radius: 8px; margin-right: 15px; cursor: pointer; font-weight: bold;">Cancel</button>
                <button type="submit" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-weight: bold;" disabled id="confirmTableButton">
                    ✅ Confirm Reservation
                </button>
            </div>
        </form>
    </div>
</div>



<!-- Table Assignment Modal -->
<div id="tableAssignmentModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 20px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #e9ecef;">
            <h3 style="margin: 0;">🍽️ Assign Table to Reservation</h3>
            <button onclick="closeTableAssignmentModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6c757d;">×</button>
        </div>
        
        <!-- Customer Info Display -->
        <div style="background: #e3f2fd; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
            <h4 style="margin: 0 0 10px 0; color: #1976d2;">👤 Customer Details</h4>
            <div id="assignment_customer_info">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field('assign_table', 'assign_table_nonce'); ?>
            <input type="hidden" id="assign_reservation_id" name="reservation_id">
            <input type="hidden" name="assign_table_action" value="1">
            
            <!-- Available Tables -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #2c3e50;">🍽️ Select Table</label>
                <div id="available_tables_grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>
            
            <!-- Selected Table Display -->
            <div id="selected_table_info" style="display: none; background: #e8f5e8; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                <h4 style="margin: 0 0 10px 0; color: #155724;">✅ Selected Table</h4>
                <div id="selected_table_details">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>
            
            <div style="text-align: right; padding-top: 20px; border-top: 2px solid #e9ecef;">
                <button type="button" onclick="closeTableAssignmentModal()" style="background: #6c757d; color: white; border: none; padding: 12px 24px; border-radius: 8px; margin-right: 15px; cursor: pointer; font-weight: bold;">Cancel</button>
                <button type="submit" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-weight: bold;">🍽️ Assign Table</button>
            </div>
        </form>
    </div>
</div>


<script>
function showConfirmWithTableModal(reservationId, customerName, partySize, date, time) {
    document.getElementById('confirm_reservation_id').value = reservationId;
    
    // Display customer info
    document.getElementById('confirm_customer_info').innerHTML = `
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div><strong>Customer:</strong><br>${customerName}</div>
            <div><strong>Party Size:</strong><br>👥 ${partySize} guests</div>
            <div><strong>Date:</strong><br>📅 ${date}</div>
            <div><strong>Time:</strong><br>🕐 ${time}</div>
        </div>
    `;
    
    // Load available tables
    loadTablesForConfirmation(partySize, date, time);
    
    document.getElementById('confirmTableModal').style.display = 'flex';
}

function closeConfirmTableModal() {
    document.getElementById('confirmTableModal').style.display = 'none';
    document.getElementById('confirm_selected_table').style.display = 'none';
    document.getElementById('confirmTableButton').disabled = true;
}

function loadTablesForConfirmation(partySize, date, time) {
    const tablesGrid = document.getElementById('confirm_tables_grid');
    
    // Show loading
    tablesGrid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 20px; color: #007cba;">🔄 Loading available tables...</div>';
    
    // Load tables from PHP data
    <?php
    global $wpdb;
    $all_tables = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}yrr_tables ORDER BY table_number");
    if ($all_tables):
    ?>
        const tables = <?php echo json_encode($all_tables); ?>;
        displayTablesForConfirmation(tables, partySize);
    <?php else: ?>
        tablesGrid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 20px; color: #dc3545;">❌ No tables found</div>';
    <?php endif; ?>
}

function displayTablesForConfirmation(tables, partySize) {
    const tablesGrid = document.getElementById('confirm_tables_grid');
    
    let tablesHTML = '';
    tables.forEach(table => {
        const isSuitable = parseInt(table.capacity) >= parseInt(partySize);
        const bgColor = isSuitable ? '#e8f5e8' : '#f8d7da';
        const borderColor = isSuitable ? '#28a745' : '#dc3545';
        const textColor = isSuitable ? '#155724' : '#721c24';
        
        tablesHTML += `
            <div onclick="${isSuitable ? `selectTableForConfirmation(${table.id}, '${table.table_number}', ${table.capacity}, '${table.location}')` : ''}" 
                 style="background: ${bgColor}; border: 2px solid ${borderColor}; padding: 15px; border-radius: 10px; text-align: center; cursor: ${isSuitable ? 'pointer' : 'not-allowed'}; color: ${textColor}; transition: all 0.3s ease;">
                <div style="font-size: 2rem; margin-bottom: 8px;">🍽️</div>
                <div style="font-weight: bold; margin-bottom: 5px;">${table.table_number}</div>
                <div style="font-size: 0.9rem; margin-bottom: 3px;">${table.capacity} seats</div>
                <div style="font-size: 0.8rem;">${table.location}</div>
                ${!isSuitable ? '<div style="font-size: 0.8rem; margin-top: 5px; font-weight: bold;">Too Small</div>' : ''}
            </div>
        `;
    });
    
    tablesGrid.innerHTML = tablesHTML;
}

function selectTableForConfirmation(tableId, tableName, capacity, location) {
    // Remove existing selection
    const existingInput = document.getElementById('confirm_selected_table_id');
    if (existingInput) existingInput.remove();
    
    // Add hidden input
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.id = 'confirm_selected_table_id';
    hiddenInput.name = 'table_id';
    hiddenInput.value = tableId;
    document.querySelector('#confirmTableModal form').appendChild(hiddenInput);
    
    // Show selected table info
    document.getElementById('confirm_selected_details').innerHTML = `
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
            <div><strong>Table:</strong><br>🍽️ ${tableName}</div>
            <div><strong>Capacity:</strong><br>👥 ${capacity} seats</div>
            <div><strong>Location:</strong><br>📍 ${location}</div>
        </div>
    `;
    document.getElementById('confirm_selected_table').style.display = 'block';
    
    // Enable confirm button
    document.getElementById('confirmTableButton').disabled = false;
    
    // Highlight selected table
    document.querySelectorAll('#confirm_tables_grid > div').forEach(div => {
        div.style.boxShadow = 'none';
        div.style.transform = 'scale(1)';
    });
    event.currentTarget.style.boxShadow = '0 0 0 3px #007cba';
    event.currentTarget.style.transform = 'scale(1.05)';
}
</script>



<script>






function editReservation(res) {
    document.getElementById('edit_id').value = res.id || '';
    document.getElementById('edit_name').value = res.customer_name || '';
    document.getElementById('edit_email').value = res.customer_email || '';
    document.getElementById('edit_phone').value = res.customer_phone || '';
    document.getElementById('edit_party').value = res.party_size || '1';
    document.getElementById('edit_date').value = res.reservation_date || '';
    
    const time = res.reservation_time || '';
    document.getElementById('edit_time').value = time.length > 5 ? time.substring(0, 5) : time;
    
    document.getElementById('edit_requests').value = res.special_requests || '';
    document.getElementById('edit_notes').value = res.notes || '';
    
    if (res.table_id) {
        document.getElementById('edit_table').value = res.table_id;
    }
    
    document.getElementById('editModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editModal');
    if (editModal) {
        editModal.addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    }
});
</script>
