<?php
if (!defined('ABSPATH')) exit;

// Get current settings
$settings = isset($settings) ? $settings : array();
$time_slot_duration = isset($settings['time_slot_duration']) ? $settings['time_slot_duration'] : 60;
$restaurant_open = isset($settings['restaurant_open']) ? $settings['restaurant_open'] : '1';

// Get today's hours from database
$hours_model = new YRR_Hours_Model();
$today_hours = $hours_model->get_today_hours();
$current_day = strtolower(date('l'));

// Calculate service info
$service_info = array(
    'open_time' => $today_hours ? date('H:i', strtotime($today_hours->open_time)) : '10:00',
    'close_time' => $today_hours ? date('H:i', strtotime($today_hours->close_time)) : '22:00',
    'is_closed' => $today_hours ? $today_hours->is_closed : 0,
    'duration' => ''
);

if (!$service_info['is_closed']) {
    $open_minutes = (intval(substr($service_info['open_time'], 0, 2)) * 60) + intval(substr($service_info['open_time'], 3, 2));
    $close_minutes = (intval(substr($service_info['close_time'], 0, 2)) * 60) + intval(substr($service_info['close_time'], 3, 2));
    
    if ($close_minutes <= $open_minutes) {
        $close_minutes += 24 * 60;
    }
    
    $duration_minutes = $close_minutes - $open_minutes;
    $hours = floor($duration_minutes / 60);
    $minutes = $duration_minutes % 60;
    $service_info['duration'] = $hours . 'h ' . $minutes . 'm';
}

// Success messages
$message = '';
if (isset($_GET['message'])) {
    switch ($_GET['message']) {
        case 'saved':
            $count = isset($_GET['count']) ? intval($_GET['count']) : 0;
            $message = '<div class="notice notice-success is-dismissible"><p>✅ Settings saved successfully! (' . $count . ' settings updated)</p></div>';
            break;
        case 'error':
            $message = '<div class="notice notice-error is-dismissible"><p>❌ Error saving settings. Please try again.</p></div>';
            break;
    }
}
?>

<div class="wrap">
    <?php echo $message; ?>
    
    <div style="max-width: 1200px; margin: 20px auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #007cba;">
            <h1 style="font-size: 2.5rem; color: #2c3e50; margin: 0;">⚙️ Restaurant Settings</h1>
            <p style="color: #6c757d; margin: 10px 0 0 0;">Configure time slots and reservation rules</p>
        </div>

        <form method="post" action="">
            <?php wp_nonce_field('yrr_settings_save', 'settings_nonce'); ?>
            <input type="hidden" name="save_settings" value="1">
            
            <!-- Current Operating Status -->
            <div style="background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%); padding: 25px; border-radius: 15px; margin-bottom: 30px; border: 2px solid #1976d2;">
                <h3 style="margin: 0 0 20px 0; color: #1976d2; font-size: 1.4rem;">🏪 Current Operating Status</h3>
                
                <!-- Restaurant Status Toggle -->
                <div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; text-align: center;">
                    <label style="display: block; margin-bottom: 15px; font-weight: bold; font-size: 1.2rem;">Restaurant Status</label>
                    <div style="display: flex; justify-content: center; gap: 20px; align-items: center;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 15px 25px; border-radius: 10px; background: #e8f5e8; border: 2px solid #28a745;">
                            <input type="radio" name="restaurant_open" value="1" <?php checked($restaurant_open, '1'); ?>>
                            <span style="font-weight: bold; color: #155724; font-size: 1.1rem;">🟢 OPEN</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 15px 25px; border-radius: 10px; background: #f8d7da; border: 2px solid #dc3545;">
                            <input type="radio" name="restaurant_open" value="0" <?php checked($restaurant_open, '0'); ?>>
                            <span style="font-weight: bold; color: #721c24; font-size: 1.1rem;">🔴 CLOSED</span>
                        </label>
                    </div>
                </div>
                
                <!-- Today's Hours Display -->
                <div style="background: white; padding: 25px; border-radius: 15px; border: 3px solid #1976d2;">
                    <h4 style="margin: 0 0 20px 0; color: #1976d2; font-size: 1.3rem; text-align: center;">📅 Today's Hours (<?php echo ucfirst($current_day); ?>) - Auto Updated</h4>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; text-align: center;">
                        <div style="background: #e3f2fd; padding: 20px; border-radius: 10px;">
                            <div style="font-size: 1.1rem; font-weight: bold; color: #1976d2; margin-bottom: 10px;">🌅 Opens At</div>
                            <div style="font-size: 2rem; font-weight: bold; color: #2c3e50;">
                                <?php echo $service_info['is_closed'] ? 'CLOSED' : date('g:i A', strtotime($service_info['open_time'])); ?>
                            </div>
                        </div>
                        
                        <div style="background: #f3e5f5; padding: 20px; border-radius: 10px;">
                            <div style="font-size: 1.1rem; font-weight: bold; color: #1976d2; margin-bottom: 10px;">🌅 Closes At</div>
                            <div style="font-size: 2rem; font-weight: bold; color: #2c3e50;">
                                <?php echo $service_info['is_closed'] ? 'CLOSED' : date('g:i A', strtotime($service_info['close_time'])); ?>
                            </div>
                        </div>
                        
                        <div style="background: #e8f5e8; padding: 20px; border-radius: 10px;">
                            <div style="font-size: 1.1rem; font-weight: bold; color: #1976d2; margin-bottom: 10px;">⏰ Service Duration</div>
                            <div style="font-size: 2rem; font-weight: bold; color: #2c3e50;">
                                <?php echo $service_info['is_closed'] ? 'CLOSED' : $service_info['duration']; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div style="text-align: center; margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 10px;">
                        <span style="color: #856404; font-weight: bold;">
                            💡 Hours are managed in the <a href="<?php echo admin_url('admin.php?page=yrr-hours'); ?>" style="color: #856404;">Operating Hours</a> section
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Time Slot Configuration -->
            <div style="background: linear-gradient(135deg, #e8f5e8 0%, #f0fff4 100%); padding: 25px; border-radius: 15px; margin-bottom: 30px; border: 2px solid #28a745;">
                <h3 style="margin: 0 0 20px 0; color: #155724; font-size: 1.4rem;">🕐 Time Slot Configuration</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #155724;">Reservation Time Slot Duration</label>
                        <select name="time_slot_duration" id="time_slot_duration" style="width: 100%; padding: 15px; border: 3px solid #28a745; border-radius: 10px; font-size: 1.2rem; font-weight: bold;">
                            <option value="15" <?php selected($time_slot_duration, 15); ?>>15 minutes</option>
                            <option value="30" <?php selected($time_slot_duration, 30); ?>>30 minutes</option>
                            <option value="45" <?php selected($time_slot_duration, 45); ?>>45 minutes</option>
                            <option value="60" <?php selected($time_slot_duration, 60); ?>>1 hour (Recommended)</option>
                            <option value="90" <?php selected($time_slot_duration, 90); ?>>1.5 hours</option>
                            <option value="120" <?php selected($time_slot_duration, 120); ?>>2 hours</option>
                        </select>
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #155724;">Booking Buffer Time</label>
                        <select name="booking_buffer_minutes" style="width: 100%; padding: 15px; border: 3px solid #28a745; border-radius: 10px; font-size: 1.2rem; font-weight: bold;">
                            <option value="0" <?php selected($settings['booking_buffer_minutes'] ?? 60, 0); ?>>No buffer</option>
                            <option value="30" <?php selected($settings['booking_buffer_minutes'] ?? 60, 30); ?>>30 minutes before closing</option>
                            <option value="60" <?php selected($settings['booking_buffer_minutes'] ?? 60, 60); ?>>1 hour before closing</option>
                            <option value="120" <?php selected($settings['booking_buffer_minutes'] ?? 60, 120); ?>>2 hours before closing</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Restaurant Information -->
            <div style="background: linear-gradient(135deg, #fff3cd 0%, #fefefe 100%); padding: 25px; border-radius: 15px; margin-bottom: 30px; border: 2px solid #ffc107;">
                <h3 style="margin: 0 0 20px 0; color: #856404; font-size: 1.4rem;">🍽️ Restaurant Information</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Restaurant Name</label>
                        <input type="text" name="restaurant_name" 
                               value="<?php echo esc_attr($settings['restaurant_name'] ?? get_bloginfo('name')); ?>" 
                               style="width: 100%; padding: 15px; border: 2px solid #ffc107; border-radius: 10px; font-size: 1.1rem;">
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Maximum Party Size</label>
                        <select name="max_party_size" style="width: 100%; padding: 15px; border: 2px solid #ffc107; border-radius: 10px; font-size: 1.1rem;">
                            <?php for($i = 1; $i <= 20; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php selected($settings['max_party_size'] ?? 12, $i); ?>><?php echo $i; ?> people</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">Advance Booking Days</label>
                    <input type="number" name="max_advance_booking" min="1" max="365" 
                           value="<?php echo esc_attr($settings['max_advance_booking'] ?? 30); ?>" 
                           style="width: 200px; padding: 15px; border: 2px solid #ffc107; border-radius: 10px;">
                    <small style="margin-left: 10px; color: #856404;">How many days ahead customers can book</small>
                </div>
            </div>
            
            <!-- Save Button -->
            <div style="text-align: center; padding: 20px;">
                <button type="submit" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; padding: 20px 40px; border-radius: 15px; font-size: 1.2rem; font-weight: bold; cursor: pointer; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);">
                    💾 Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
