<?php
if (!defined('ABSPATH')) exit;

// ✅ FIX: Define all missing variables at the top
$current_user = wp_get_current_user();
$is_super_admin = isset($is_super_admin) ? $is_super_admin : in_array('administrator', $current_user->roles);
$is_admin = isset($is_admin) ? $is_admin : ($is_super_admin || in_array('yrr_admin', $current_user->roles));

// Helper function to safely get object properties
function yrr_get_property_dash($object, $property, $default = '') {
    if (is_object($object) && property_exists($object, $property) && !empty($object->$property)) {
        return $object->$property;
    }
    return $default;
}

// Set default values
$statistics = isset($statistics) ? $statistics : array();
$today_reservations = isset($today_reservations) ? $today_reservations : array();
$restaurant_status = isset($restaurant_status) ? $restaurant_status : '1';
$restaurant_name = isset($restaurant_name) ? $restaurant_name : get_bloginfo('name');

// Your existing dashboard content continues here...
?>


<div class="wrap">
    <?php echo $message; ?>
    
    <!-- Dashboard Header -->
    <div style="max-width: 1400px; margin: 20px auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        
        <!-- Header Section -->
        <div style="text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #007cba;">
            <h1 style="font-size: 2.5rem; color: #2c3e50; margin: 0;">🍽️ <?php echo esc_html($restaurant_name); ?> Dashboard</h1>
            <p style="color: #6c757d; margin: 10px 0 0 0;">Restaurant Management System - Today: <?php echo date('F j, Y'); ?></p>
            
            <!-- Restaurant Status Toggle -->
            <div style="margin-top: 15px;">
                <span style="background: <?php echo $restaurant_status == '1' ? '#28a745' : '#dc3545'; ?>; color: white; padding: 8px 20px; border-radius: 20px; font-weight: bold;">
                    <?php echo $restaurant_status == '1' ? '🟢 Restaurant Open' : '🔴 Restaurant Closed'; ?>
                </span>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
            
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 15px; text-align: center;">
                <div style="font-size: 2.5rem; font-weight: bold; margin-bottom: 10px;">
                    <?php echo isset($statistics['total']) ? intval($statistics['total']) : 0; ?>
                </div>
                <div style="font-size: 1.1rem;">Total Reservations</div>
            </div>
            
            <div style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 25px; border-radius: 15px; text-align: center;">
                <div style="font-size: 2.5rem; font-weight: bold; margin-bottom: 10px;">
                    <?php echo isset($statistics['today']) ? intval($statistics['today']) : 0; ?>
                </div>
                <div style="font-size: 1.1rem;">Today's Reservations</div>
            </div>
            
            <div style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: white; padding: 25px; border-radius: 15px; text-align: center;">
                <div style="font-size: 2.5rem; font-weight: bold; margin-bottom: 10px;">
                    <?php echo isset($statistics['pending']) ? intval($statistics['pending']) : 0; ?>
                </div>
                <div style="font-size: 1.1rem;">Pending Approval</div>
            </div>
            
            <div style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white; padding: 25px; border-radius: 15px; text-align: center;">
                <div style="font-size: 2.5rem; font-weight: bold; margin-bottom: 10px;">
                    <?php echo isset($statistics['this_week']) ? intval($statistics['this_week']) : 0; ?>
                </div>
                <div style="font-size: 1.1rem">This Week</div>
            </div>
        </div>

        <!-- Quick Actions Section -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
            
            <!-- Manual Reservation Creation -->
            <div style="background: #f8f9fa; padding: 25px; border-radius: 15px; border: 2px solid #e9ecef;">
                <h3 style="margin: 0 0 20px 0; color: #2c3e50; font-size: 1.3rem;">➕ Create Manual Reservation</h3>
                
                <form method="post" action="">
                    <?php wp_nonce_field('create_manual_reservation', 'manual_reservation_nonce'); ?>
                    <input type="hidden" name="create_manual_reservation" value="1">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: bold;">Customer Name *</label>
                            <input type="text" name="customer_name" required style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: bold;">Email *</label>
                            <input type="email" name="customer_email" required style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: bold;">Phone *</label>
                            <input type="tel" name="customer_phone" required style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: bold;">Party Size *</label>
                            <select name="party_size" required style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                                <?php for($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?> <?php echo $i == 1 ? 'guest' : 'guests'; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: bold;">Date *</label>
                            <input type="date" name="reservation_date" required value="<?php echo date('Y-m-d'); ?>" style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: bold;">Time *</label>
                            <input type="time" name="reservation_time" required style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: bold;">Table (Optional)</label>
                            <select name="table_id" style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                                <option value="">No Table</option>
                                <?php 
                                global $wpdb;
                                $tables = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}yrr_tables ORDER BY table_number");
                                if ($tables) {
                                    foreach ($tables as $table): ?>
                                        <option value="<?php echo $table->id; ?>">
                                            <?php echo esc_html($table->table_number); ?> (<?php echo $table->capacity; ?> seats)
                                        </option>
                                    <?php endforeach;
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Special Requests</label>
                        <textarea name="special_requests" rows="2" style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;"></textarea>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: bold;">Initial Status</label>
                            <select name="initial_status" style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                                <option value="confirmed">Confirmed</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: bold;">Admin Notes</label>
                            <input type="text" name="admin_notes" placeholder="Internal notes..." style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                        </div>
                    </div>
                    
                    <div style="text-align: center;">
                        <button type="submit" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; padding: 15px 30px; border-radius: 10px; font-size: 1.1rem; font-weight: bold; cursor: pointer;">
                            ➕ Create Reservation
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Quick Actions & Navigation -->
            <div style="background: #f8f9fa; padding: 25px; border-radius: 15px; border: 2px solid #e9ecef;">
                <h3 style="margin: 0 0 20px 0; color: #2c3e50; font-size: 1.3rem;">🚀 Quick Actions</h3>
                
                <div style="display: grid; gap: 15px;">
                    <a href="<?php echo admin_url('admin.php?page=yrr-all-reservations'); ?>" 
                       style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px; text-decoration: none; border-radius: 10px; text-align: center; font-weight: bold;">
                        📋 View All Reservations
                    </a>
                    
                    <a href="<?php echo admin_url('admin.php?page=yrr-weekly-reservations'); ?>" 
                       style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white; padding: 15px; text-decoration: none; border-radius: 10px; text-align: center; font-weight: bold;">
                        📅 Weekly View
                    </a>
                    
                    <a href="<?php echo admin_url('admin.php?page=yrr-table-schedule'); ?>" 
                       style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 15px; text-decoration: none; border-radius: 10px; text-align: center; font-weight: bold;">
                        🍽️ Table Schedule
                    </a>
                    
                    <?php if ($is_super_admin): ?>
                        <a href="<?php echo admin_url('admin.php?page=yrr-tables'); ?>" 
                           style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: white; padding: 15px; text-decoration: none; border-radius: 10px; text-align: center; font-weight: bold;">
                            🛠️ Manage Tables
                        </a>
                        
                        <a href="<?php echo admin_url('admin.php?page=yrr-settings'); ?>" 
                           style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: white; padding: 15px; text-decoration: none; border-radius: 10px; text-align: center; font-weight: bold;">
                            ⚙️ Settings
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Today's Reservations -->
        <div style="background: #f8f9fa; padding: 25px; border-radius: 15px; border: 2px solid #e9ecef;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin: 0; color: #2c3e50; font-size: 1.3rem;">📅 Today's Reservations (<?php echo date('F j, Y'); ?>)</h3>
                <span style="background: #007cba; color: white; padding: 5px 15px; border-radius: 15px; font-weight: bold;">
                    <?php echo count($today_reservations); ?> Total
                </span>
            </div>
            
            <?php if (empty($today_reservations)): ?>
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <div style="font-size: 3rem; margin-bottom: 15px;">📅</div>
                    <h4 style="margin: 0 0 10px 0;">No reservations for today</h4>
                    <p style="margin: 0;">Use the form above to create a manual reservation or wait for online bookings.</p>
                </div>
            <?php else: ?>
                <div style="display: grid; gap: 15px;">
                    <?php foreach ($today_reservations as $reservation): ?>
                        
                        <!-- Enhanced Reservation Card with Table Assignment -->
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
                                <div style="color: #6c757d; font-size: 0.9rem; margin-bottom: 3px;">
                                    📞 <?php echo esc_html(yrr_get_property_dash($reservation, 'customer_phone', 'No phone')); ?>
                                </div>
                                <div style="color: #007cba; font-weight: bold;">
                                    📅 <?php echo esc_html(yrr_get_property_dash($reservation, 'reservation_date')); ?> at <?php echo date('g:i A', strtotime(yrr_get_property_dash($reservation, 'reservation_time', '00:00:00'))); ?>
                                </div>
                            </div>
                            
                            <!-- Reservation Details with Table Assignment -->
                            <div style="text-align: center;">
                                <div style="background: #e3f2fd; color: #1976d2; padding: 10px 15px; border-radius: 10px; font-weight: bold; margin-bottom: 10px;">
                                    👥 <?php echo intval(yrr_get_property_dash($reservation, 'party_size', 1)); ?> guests
                                </div>
                                
                                <!-- Table Assignment Display -->
                                <?php 
                                $table_id = yrr_get_property_dash($reservation, 'table_id');
                                if ($table_id): 
                                    $table_info = $wpdb->get_row($wpdb->prepare(
                                        "SELECT table_number, capacity, location FROM {$wpdb->prefix}yrr_tables WHERE id = %d",
                                        $table_id
                                    ));
                                ?>
                                    <div style="background: #28a745; color: white; padding: 8px 12px; border-radius: 10px; font-size: 0.9rem; font-weight: bold; margin-bottom: 10px;">
                                        🍽️ <?php echo $table_info ? esc_html($table_info->table_number) : 'Table ' . $table_id; ?>
                                        <?php if ($table_info): ?>
                                            <br><small>(<?php echo $table_info->capacity; ?> seats - <?php echo esc_html($table_info->location); ?>)</small>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div style="background: #dc3545; color: white; padding: 8px 12px; border-radius: 10px; font-size: 0.9rem; font-weight: bold; margin-bottom: 10px;">
                                        ❌ No Table Assigned
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div style="display: flex; gap: 8px; flex-direction: column;">
                                <?php $reservation_id = yrr_get_property_dash($reservation, 'id'); ?>
                                
                                <?php if ($reservation_id && $status === 'pending'): ?>
                                    <!-- Confirm with Table Assignment Button -->
                                    <button onclick="showConfirmWithTableModal(<?php echo $reservation_id; ?>, '<?php echo esc_js($reservation->customer_name); ?>', <?php echo $reservation->party_size; ?>, '<?php echo $reservation->reservation_date; ?>', '<?php echo $reservation->reservation_time; ?>')" 
                                            style="background: #28a745; color: white; border: none; padding: 10px 15px; border-radius: 5px; font-size: 0.9rem; font-weight: bold; cursor: pointer;">
                                        ✅ Confirm & Assign Table
                                    </button>
                                <?php endif; ?>
                                
                                <?php if ($reservation_id): ?>
                                    <button onclick="editReservation(<?php echo htmlspecialchars(json_encode($reservation)); ?>)" 
                                            style="background: #ffc107; color: #000; border: none; padding: 8px 12px; border-radius: 5px; font-size: 0.8rem; font-weight: bold; cursor: pointer;">
                                        ✏️ Edit
                                    </button>
                                    
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=yenolx-reservations&action=cancel&id=' . $reservation_id), 'reservation_action'); ?>" 
                                       style="background: #dc3545; color: white; padding: 8px 12px; text-decoration: none; border-radius: 5px; font-size: 0.8rem; font-weight: bold; text-align: center;"
                                       onclick="return confirm('Cancel this reservation?')">
                                        ❌ Cancel
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
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

<!-- Edit Reservation Modal -->
<div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 20px; width: 90%; max-width: 600px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0;">✏️ Edit Reservation</h3>
            <button onclick="closeModal()" style="background: none; border: none; font-size: 24px; cursor: pointer;">×</button>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field('edit_reservation', 'edit_nonce'); ?>
            <input type="hidden" id="edit_id" name="reservation_id">
            <input type="hidden" name="edit_reservation" value="1">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">Customer Name *</label>
                    <input type="text" id="edit_name" name="customer_name" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">Email *</label>
                    <input type="email" id="edit_email" name="customer_email" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">Phone *</label>
                    <input type="tel" id="edit_phone" name="customer_phone" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">Party Size *</label>
                    <select id="edit_party" name="party_size" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">
                        <?php for($i = 1; $i <= 12; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?> guests</option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">Date *</label>
                    <input type="date" id="edit_date" name="reservation_date" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">Time *</label>
                    <input type="time" id="edit_time" name="reservation_time" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">
                </div>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 8px; font-weight: bold;">Special Requests</label>
                <textarea id="edit_requests" name="special_requests" rows="3" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;"></textarea>
            </div>
            
            <div style="text-align: right;">
                <button type="button" onclick="closeModal()" style="background: #666; color: white; border: none; padding: 10px 20px; border-radius: 5px; margin-right: 10px; cursor: pointer;">Cancel</button>
                <button type="submit" style="background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">Update Reservation</button>
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
    
    document.getElementById('editModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Close modals when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    ['editModal', 'confirmTableModal'].forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    if (modalId === 'editModal') closeModal();
                    else if (modalId === 'confirmTableModal') closeConfirmTableModal();
                }
            });
        }
    });
});
</script>

<style>
@media (max-width: 1200px) {
    div[style*="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr))"] {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) !important;
    }
}

@media (max-width: 768px) {
    div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
    
    div[style*="grid-template-columns: auto 1fr auto auto"] {
        grid-template-columns: 1fr !important;
        text-align: center;
    }
}
</style>
