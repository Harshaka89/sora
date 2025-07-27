<?php
if (!defined('ABSPATH')) exit;

// Helper function for safe property access
function yrr_get_property_dash($object, $property, $default = '') {
    return (property_exists($object, $property) && !empty($object->$property)) ? $object->$property : $default;
}
?>

<div class="wrap">
    <div style="max-width: 1400px; margin: 20px auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #667eea;">
            <h1 style="font-size: 2.5rem; color: #2c3e50; margin: 0;">🏪 <?php echo esc_html($restaurant_name); ?> Dashboard</h1>
            <p style="color: #6c757d; margin: 10px 0 0 0;">Yenolx Restaurant Reservation System v1.5</p>
            <div style="margin-top: 15px;">
                <span style="background: <?php echo $restaurant_status == '1' ? '#28a745' : '#dc3545'; ?>; color: white; padding: 8px 16px; border-radius: 20px; font-weight: bold;">
                    <?php echo $restaurant_status == '1' ? '🟢 OPEN' : '🔴 CLOSED'; ?>
                </span>
            </div>
        </div>
        
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
        
        <!-- Quick Actions -->
        <div style="margin-bottom: 30px; padding: 25px; background: #f8f9fa; border-radius: 15px; border: 3px solid #e9ecef;">
            <h3 style="margin: 0 0 20px 0; color: #2c3e50;">🚀 Quick Actions</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <a href="<?php echo admin_url('admin.php?page=yrr-all-reservations'); ?>" 
                   style="background: linear-gradient(135deg, #007cba 0%, #004d7a 100%); color: white; padding: 15px 20px; text-decoration: none; border-radius: 10px; text-align: center; font-weight: bold;">
                    📋 All Reservations
                </a>
                <a href="<?php echo admin_url('admin.php?page=yrr-tables'); ?>" 
                   style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 15px 20px; text-decoration: none; border-radius: 10px; text-align: center; font-weight: bold;">
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
                <a href="<?php echo admin_url('admin.php?page=yrr-settings'); ?>" 
                   style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: white; padding: 15px 20px; text-decoration: none; border-radius: 10px; text-align: center; font-weight: bold;">
                    ⚙️ Settings
                </a>
            </div>
        </div>
        
        <!-- Today's Reservations -->
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
                        <p>No reservations scheduled for today. Check back later!</p>
                        <a href="<?php echo admin_url('admin.php?page=yrr-all-reservations'); ?>" 
                           style="background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; margin-top: 15px; display: inline-block;">
                            📋 View All Reservations
                        </a>
                    </div>
                    
                <?php endif; ?>
            </div>
        </div>
        
        <!-- System Health Check -->
        <div style="margin-top: 30px; padding: 20px; background: #e8f5e8; border-radius: 10px; border-left: 5px solid #28a745;">
            <h4 style="margin: 0 0 15px 0; color: #155724;">🔧 System Health Status</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; font-size: 0.9rem;">
                <?php
                global $wpdb;
                $tables = array('yrr_settings', 'yrr_reservations', 'yrr_tables', 'yrr_operating_hours', 'yrr_pricing_rules');
                $all_good = true;
                
                foreach ($tables as $table) {
                    $full_table_name = $wpdb->prefix . $table;
                    $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") == $full_table_name;
                    if (!$exists) $all_good = false;
                    echo '<div>' . ($exists ? '✅' : '❌') . ' ' . ucfirst(str_replace('yrr_', '', $table)) . '</div>';
                }
                ?>
                <div><?php echo $all_good ? '✅' : '⚠️'; ?> Overall Status: <?php echo $all_good ? 'Healthy' : 'Needs Attention'; ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal (reuse from all-reservations page) -->
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

// Close modal when clicking outside
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
}

button:hover, a[style*="background:"]:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
}
</style>
