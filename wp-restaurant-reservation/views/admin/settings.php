<?php
if (!defined('ABSPATH')) exit;

// Get current settings with defaults
$settings = isset($settings) ? $settings : array();
$time_slot_duration = isset($settings['time_slot_duration']) ? $settings['time_slot_duration'] : 60;
$restaurant_open = isset($settings['restaurant_open']) ? $settings['restaurant_open'] : '1';
$max_advance_booking = isset($settings['max_advance_booking']) ? $settings['max_advance_booking'] : 30;

// ✅ NEW: Get today's hours from hours table automatically
$hours_model = new YRR_Hours_Model();
$today_hours = $hours_model->get_today_hours();
$current_day = strtolower(date('l')); // monday, tuesday, etc.

// Calculate current service duration
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
        $close_minutes += 24 * 60; // Handle overnight
    }
    
    $duration_minutes = $close_minutes - $open_minutes;
    $hours = floor($duration_minutes / 60);
    $minutes = $duration_minutes % 60;
    $service_info['duration'] = $hours . 'h ' . $minutes . 'm';
}

// Handle success messages
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
        case 'validation_error':
            $message = '<div class="notice notice-warning is-dismissible"><p>⚠️ Please check your settings and try again.</p></div>';
            break;
    }
}
?>

<div class="wrap">
    <?php echo $message; ?>
    
    <div style="max-width: 1400px; margin: 20px auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #007cba;">
            <h1 style="font-size: 2.5rem; color: #2c3e50; margin: 0;">⚙️ Restaurant Settings</h1>
            <p style="color: #6c757d; margin: 10px 0 0 0;">Configure time slots and reservation rules - Hours managed separately</p>
        </div>

        <form method="post" action="" onsubmit="return validateSettings()">
            <?php wp_nonce_field('yrr_settings_save', 'settings_nonce'); ?>
            <input type="hidden" name="save_settings" value="1">
            
            <!-- ✅ NEW: Auto Hours Display (Read-Only) -->
            <div style="background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%); padding: 25px; border-radius: 15px; margin-bottom: 30px; border: 2px solid #1976d2;">
                <h3 style="margin: 0 0 20px 0; color: #1976d2; font-size: 1.4rem;">🏪 Current Operating Status</h3>
                
                <!-- Restaurant Status Toggle -->
                <div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; text-align: center;">
                    <label style="display: block; margin-bottom: 15px; font-weight: bold; font-size: 1.2rem;">Restaurant Status</label>
                    <div style="display: flex; justify-content: center; gap: 20px; align-items: center;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 15px 25px; border-radius: 10px; background: #e8f5e8; border: 2px solid #28a745; transition: all 0.3s ease;">
                            <input type="radio" name="restaurant_open" value="1" <?php checked($restaurant_open, '1'); ?> onchange="toggleRestaurantStatus()">
                            <span style="font-weight: bold; color: #155724; font-size: 1.1rem;">🟢 OPEN</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 15px 25px; border-radius: 10px; background: #f8d7da; border: 2px solid #dc3545; transition: all 0.3s ease;">
                            <input type="radio" name="restaurant_open" value="0" <?php checked($restaurant_open, '0'); ?> onchange="toggleRestaurantStatus()">
                            <span style="font-weight: bold; color: #721c24; font-size: 1.1rem;">🔴 CLOSED</span>
                        </label>
                    </div>
                    <div id="status_note" style="margin-top: 15px; padding: 15px; border-radius: 8px; font-size: 1rem; font-weight: bold;">
                        <!-- Will be updated by JavaScript -->
                    </div>
                </div>
                
                <!-- ✅ Auto Daily Hours Display -->
                <div style="background: white; padding: 25px; border-radius: 15px; border: 3px solid #1976d2;">
                    <h4 style="margin: 0 0 20px 0; color: #1976d2; font-size: 1.3rem; text-align: center;">📅 Today's Hours (<?php echo ucfirst($current_day); ?>) - Auto Updated</h4>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; text-align: center;">
                        <div style="background: #e3f2fd; padding: 20px; border-radius: 10px; border: 2px solid #1976d2;">
                            <div style="font-size: 1.1rem; font-weight: bold; color: #1976d2; margin-bottom: 10px;">🌅 Opens At</div>
                            <div style="font-size: 2rem; font-weight: bold; color: #2c3e50;">
                                <?php echo $service_info['is_closed'] ? 'CLOSED' : date('g:i A', strtotime($service_info['open_time'])); ?>
                            </div>
                        </div>
                        
                        <div style="background: #f3e5f5; padding: 20px; border-radius: 10px; border: 2px solid #1976d2;">
                            <div style="font-size: 1.1rem; font-weight: bold; color: #1976d2; margin-bottom: 10px;">🌅 Closes At</div>
                            <div style="font-size: 2rem; font-weight: bold; color: #2c3e50;">
                                <?php echo $service_info['is_closed'] ? 'CLOSED' : date('g:i A', strtotime($service_info['close_time'])); ?>
                            </div>
                        </div>
                        
                        <div style="background: #e8f5e8; padding: 20px; border-radius: 10px; border: 2px solid #1976d2;">
                            <div style="font-size: 1.1rem; font-weight: bold; color: #1976d2; margin-bottom: 10px;">⏰ Service Duration</div>
                            <div style="font-size: 2rem; font-weight: bold; color: #2c3e50;">
                                <?php echo $service_info['is_closed'] ? 'CLOSED' : $service_info['duration']; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div style="text-align: center; margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 10px; border: 2px solid #ffc107;">
                        <span style="color: #856404; font-weight: bold; font-size: 1.1rem;">
                            💡 Hours are managed in the <a href="<?php echo admin_url('admin.php?page=yrr-hours'); ?>" style="color: #856404; text-decoration: underline;">Operating Hours</a> section
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Time Slot Configuration -->
            <div style="background: linear-gradient(135deg, #e8f5e8 0%, #f0fff4 100%); padding: 25px; border-radius: 15px; margin-bottom: 30px; border: 2px solid #28a745;">
                <h3 style="margin: 0 0 20px 0; color: #155724; font-size: 1.4rem;">🕐 Time Slot Configuration</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #155724; font-size: 1.1rem;">Reservation Time Slot Duration</label>
                        <select name="time_slot_duration" id="time_slot_duration" onchange="updateTimeSlotPreview()" 
                                style="width: 100%; padding: 15px; border: 3px solid #28a745; border-radius: 10px; box-sizing: border-box; font-size: 1.2rem; font-weight: bold;">
                            <option value="15" <?php selected($time_slot_duration, 15); ?>>15 minutes</option>
                            <option value="30" <?php selected($time_slot_duration, 30); ?>>30 minutes</option>
                            <option value="45" <?php selected($time_slot_duration, 45); ?>>45 minutes</option>
                            <option value="60" <?php selected($time_slot_duration, 60); ?>>1 hour (Recommended)</option>
                            <option value="90" <?php selected($time_slot_duration, 90); ?>>1.5 hours</option>
                            <option value="120" <?php selected($time_slot_duration, 120); ?>>2 hours</option>
                        </select>
                        <small style="color: #155724; font-weight: bold; display: block; margin-top: 5px;">How long each reservation slot lasts</small>
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #155724; font-size: 1.1rem;">Booking Buffer Time</label>
                        <select name="booking_buffer_minutes" style="width: 100%; padding: 15px; border: 3px solid #28a745; border-radius: 10px; box-sizing: border-box; font-size: 1.2rem; font-weight: bold;">
                            <option value="0" <?php selected($settings['booking_buffer_minutes'] ?? 60, 0); ?>>No buffer (Book until closing)</option>
                            <option value="30" <?php selected($settings['booking_buffer_minutes'] ?? 60, 30); ?>>30 minutes before closing</option>
                            <option value="60" <?php selected($settings['booking_buffer_minutes'] ?? 60, 60); ?>>1 hour before closing</option>
                            <option value="120" <?php selected($settings['booking_buffer_minutes'] ?? 60, 120); ?>>2 hours before closing</option>
                        </select>
                        <small style="color: #155724; font-weight: bold; display: block; margin-top: 5px;">Stop taking reservations before closing</small>
                    </div>
                </div>
                
                <!-- ✅ Auto Time Slot Preview Based on Today's Hours -->
                <div style="background: white; padding: 25px; border-radius: 15px; border: 3px solid #28a745;">
                    <h4 style="margin: 0 0 20px 0; color: #155724; font-size: 1.3rem; text-align: center;">📋 Today's Available Time Slots (Auto Generated)</h4>
                    <div id="time_slots_preview" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 12px; max-height: 250px; overflow-y: auto; padding: 10px;">
                        <!-- Will be populated by JavaScript -->
                    </div>
                    <div id="slots_count" style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 10px; text-align: center; font-weight: bold; font-size: 1.1rem;">
                        <!-- Will show total slots count -->
                    </div>
                </div>
            </div>
            
            <!-- Restaurant Information (Same as before) -->
            <div style="background: linear-gradient(135deg, #fff3cd 0%, #fefefe 100%); padding: 25px; border-radius: 15px; margin-bottom: 30px; border: 2px solid #ffc107;">
                <h3 style="margin: 0 0 20px 0; color: #856404; font-size: 1.4rem;">🍽️ Restaurant Information</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #856404;">Restaurant Name</label>
                        <input type="text" name="restaurant_name" 
                               value="<?php echo esc_attr($settings['restaurant_name'] ?? get_bloginfo('name')); ?>" 
                               style="width: 100%; padding: 15px; border: 2px solid #ffc107; border-radius: 10px; box-sizing: border-box; font-size: 1.1rem;">
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #856404;">Contact Email</label>
                        <input type="email" name="restaurant_email" 
                               value="<?php echo esc_attr($settings['restaurant_email'] ?? get_option('admin_email')); ?>" 
                               style="width: 100%; padding: 15px; border: 2px solid #ffc107; border-radius: 10px; box-sizing: border-box; font-size: 1.1rem;">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #856404;">Phone Number</label>
                        <input type="tel" name="restaurant_phone" 
                               value="<?php echo esc_attr($settings['restaurant_phone'] ?? ''); ?>" 
                               style="width: 100%; padding: 15px; border: 2px solid #ffc107; border-radius: 10px; box-sizing: border-box; font-size: 1.1rem;">
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #856404;">Maximum Party Size</label>
                        <select name="max_party_size" style="width: 100%; padding: 15px; border: 2px solid #ffc107; border-radius: 10px; box-sizing: border-box; font-size: 1.1rem;">
                            <?php for($i = 1; $i <= 20; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php selected($settings['max_party_size'] ?? 12, $i); ?>><?php echo $i; ?> people</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #856404;">Currency Symbol</label>
                        <select name="currency_symbol" style="width: 100%; padding: 15px; border: 2px solid #ffc107; border-radius: 10px; box-sizing: border-box; font-size: 1.1rem;">
                            <option value="$" <?php selected($settings['currency_symbol'] ?? '$', '$'); ?>>$ (Dollar)</option>
                            <option value="€" <?php selected($settings['currency_symbol'] ?? '$', '€'); ?>>€ (Euro)</option>
                            <option value="£" <?php selected($settings['currency_symbol'] ?? '$', '£'); ?>>£ (Pound)</option>
                            <option value="₹" <?php selected($settings['currency_symbol'] ?? '$', '₹'); ?>>₹ (Rupee)</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Booking Rules (Same as before) -->
            <div style="background: linear-gradient(135deg, #f8d7da 0%, #fefefe 100%); padding: 25px; border-radius: 15px; margin-bottom: 30px; border: 2px solid #dc3545;">
                <h3 style="margin: 0 0 20px 0; color: #721c24; font-size: 1.4rem;">📋 Booking Rules & Policies</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #721c24;">Advance Booking Days</label>
                        <input type="number" name="max_advance_booking" min="1" max="365" 
                               value="<?php echo esc_attr($max_advance_booking); ?>" 
                               style="width: 100%; padding: 15px; border: 2px solid #dc3545; border-radius: 10px; box-sizing: border-box; font-size: 1.1rem;">
                        <small style="color: #721c24; font-weight: bold; display: block; margin-top: 5px;">How many days ahead customers can book</small>
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #721c24;">Auto-Confirm Reservations</label>
                        <select name="auto_confirm_reservations" style="width: 100%; padding: 15px; border: 2px solid #dc3545; border-radius: 10px; box-sizing: border-box; font-size: 1.1rem;">
                            <option value="0" <?php selected($settings['auto_confirm_reservations'] ?? 0, 0); ?>>Manual confirmation required</option>
                            <option value="1" <?php selected($settings['auto_confirm_reservations'] ?? 0, 1); ?>>Auto-confirm all reservations</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Save Button -->
            <div style="text-align: center; padding: 30px;">
                <button type="submit" id="save_button" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; padding: 25px 50px; border-radius: 20px; font-size: 1.3rem; font-weight: bold; cursor: pointer; box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4); transition: all 0.3s ease;">
                    💾 Save Settings
                </button>
                <div style="margin-top: 15px; color: #6c757d; font-size: 0.9rem;">
                    Operating hours managed separately in Hours section
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// ✅ Use database hours instead of manual input
const todayHours = {
    open: '<?php echo $service_info['open_time']; ?>',
    close: '<?php echo $service_info['close_time']; ?>',
    isClosed: <?php echo $service_info['is_closed'] ? 'true' : 'false'; ?>
};

function toggleRestaurantStatus() {
    const isOpen = document.querySelector('input[name="restaurant_open"]:checked').value === '1';
    const statusNote = document.getElementById('status_note');
    
    if (isOpen) {
        statusNote.innerHTML = '<span style="color: #155724;">🟢 Restaurant is OPEN and accepting reservations</span>';
        statusNote.style.background = '#e8f5e8';
        statusNote.style.border = '2px solid #28a745';
    } else {
        statusNote.innerHTML = '<span style="color: #721c24;">🔴 Restaurant is CLOSED - No new reservations will be accepted</span>';
        statusNote.style.background = '#f8d7da';
        statusNote.style.border = '2px solid #dc3545';
    }
}

function updateTimeSlotPreview() {
    const duration = parseInt(document.getElementById('time_slot_duration').value);
    
    if (todayHours.isClosed) {
        document.getElementById('time_slots_preview').innerHTML = '<div style="grid-column: 1/-1; text-align: center; color: #dc3545; padding: 20px; font-weight: bold;">🔴 Restaurant is closed today</div>';
        document.getElementById('slots_count').innerHTML = '<span style="color: #dc3545;">Restaurant closed today</span>';
        return;
    }
    
    const slots = generateTimeSlots(todayHours.open, todayHours.close, duration);
    displayTimeSlots(slots);
}

function generateTimeSlots(openTime, closeTime, durationMinutes) {
    const slots = [];
    const openMinutes = timeToMinutes(openTime);
    let closeMinutes = timeToMinutes(closeTime);
    
    if (closeMinutes <= openMinutes) {
        closeMinutes += 24 * 60;
    }
    
    for (let currentMinutes = openMinutes; currentMinutes < closeMinutes; currentMinutes += durationMinutes) {
        const hour = Math.floor(currentMinutes / 60) % 24;
        const minute = currentMinutes % 60;
        
        const timeString = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
        const displayTime = formatTime12Hour(hour, minute);
        slots.push({
            value: timeString,
            display: displayTime
        });
    }
    
    return slots;
}

function timeToMinutes(time) {
    const [hours, minutes] = time.split(':').map(Number);
    return hours * 60 + minutes;
}

function formatTime12Hour(hour, minute) {
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour % 12 || 12;
    return `${displayHour}:${minute.toString().padStart(2, '0')} ${ampm}`;
}

function displayTimeSlots(slots) {
    const container = document.getElementById('time_slots_preview');
    const countDiv = document.getElementById('slots_count');
    
    if (slots.length === 0) {
        container.innerHTML = '<div style="grid-column: 1/-1; text-align: center; color: #dc3545; padding: 20px; font-weight: bold;">❌ No valid time slots</div>';
        countDiv.innerHTML = '<span style="color: #dc3545;">No available slots</span>';
        return;
    }
    
    let slotsHTML = '';
    slots.forEach((slot, index) => {
        const bgColor = index % 2 === 0 ? '#e3f2fd' : '#f3e5f5';
        slotsHTML += `
            <div style="background: ${bgColor}; padding: 12px; border-radius: 8px; text-align: center; font-size: 1rem; font-weight: bold; border: 2px solid #1976d2; transition: all 0.2s ease;">
                ${slot.display}
            </div>
        `;
    });
    
    container.innerHTML = slotsHTML;
    countDiv.innerHTML = `<span style="color: #28a745;">✅ ${slots.length} time slots available today</span>`;
}

function validateSettings() {
    const restaurantName = document.querySelector('input[name="restaurant_name"]').value.trim();
    if (!restaurantName) {
        alert('Please enter a restaurant name.');
        return false;
    }
    
    return true;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleRestaurantStatus();
    updateTimeSlotPreview();
    
    document.getElementById('time_slot_duration').addEventListener('change', updateTimeSlotPreview);
});
</script>
