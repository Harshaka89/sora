<?php
if (!defined('ABSPATH')) exit;

// Set defaults
$hours = isset($hours) ? $hours : array();
$days_config = array(
    'monday' => array('name' => 'Monday', 'icon' => '📅'),
    'tuesday' => array('name' => 'Tuesday', 'icon' => '📅'),
    'wednesday' => array('name' => 'Wednesday', 'icon' => '📅'),
    'thursday' => array('name' => 'Thursday', 'icon' => '📅'),
    'friday' => array('name' => 'Friday', 'icon' => '📅'),
    'saturday' => array('name' => 'Saturday', 'icon' => '🎉'),
    'sunday' => array('name' => 'Sunday', 'icon' => '🌟')
);

// Helper function to safely get hour properties
function yrr_get_hour_value($hour_obj, $property, $default = '') {
    if (is_object($hour_obj) && property_exists($hour_obj, $property)) {
        return $hour_obj->$property;
    } elseif (is_array($hour_obj) && isset($hour_obj[$property])) {
        return $hour_obj[$property];
    }
    return $default;
}

// Helper function to format time for display
function yrr_format_time($time) {
    if (empty($time) || $time === '00:00:00') return '';
    return date('H:i', strtotime($time));
}
?>

<div class="wrap">
    <div style="max-width: 1200px; margin: 20px auto; background: white; padding: 30px; border-radius: 15px;">
        
        <h1 style="text-align: center; color: #2c3e50; margin-bottom: 30px;">🕐 Operating Hours Management</h1>
        
        <form method="post" action="">
            <?php wp_nonce_field('yrr_hours_save', 'hours_nonce'); ?>
            <input type="hidden" name="save_hours" value="1">
            
            <?php foreach ($days_config as $day => $config): ?>
                <?php 
                $day_hours = isset($hours[$day]) ? $hours[$day] : null;
                $is_closed = yrr_get_hour_value($day_hours, 'is_closed', 0);
                $open_time = yrr_format_time(yrr_get_hour_value($day_hours, 'open_time', '10:00:00'));
                $close_time = yrr_format_time(yrr_get_hour_value($day_hours, 'close_time', '22:00:00'));
                ?>
                
                <div style="background: #f8f9fa; padding: 20px; margin-bottom: 15px; border-radius: 10px; border: 2px solid #e9ecef;">
                    <div style="display: grid; grid-template-columns: 150px 1fr 1fr 1fr 120px; gap: 15px; align-items: center;">
                        
                        <!-- Day Name -->
                        <div style="font-weight: bold; font-size: 1.1rem; color: #2c3e50;">
                            <?php echo $config['icon']; ?> <?php echo $config['name']; ?>
                        </div>
                        
                        <!-- Open Time -->
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #155724;">Open Time</label>
                            <input type="time" 
                                   name="<?php echo $day; ?>_open" 
                                   id="<?php echo $day; ?>_open"
                                   value="<?php echo $open_time; ?>" 
                                   <?php echo $is_closed ? 'disabled' : ''; ?>
                                   onchange="updateTimeDisplay('<?php echo $day; ?>')"
                                   style="width: 100%; padding: 8px; border: 2px solid #28a745; border-radius: 5px;">
                        </div>
                        
                        <!-- Close Time -->
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #dc3545;">Close Time</label>
                            <input type="time" 
                                   name="<?php echo $day; ?>_close" 
                                   id="<?php echo $day; ?>_close"
                                   value="<?php echo $close_time; ?>" 
                                   <?php echo $is_closed ? 'disabled' : ''; ?>
                                   onchange="updateTimeDisplay('<?php echo $day; ?>')"
                                   style="width: 100%; padding: 8px; border: 2px solid #dc3545; border-radius: 5px;">
                        </div>
                        
                        <!-- Status Display -->
                        <div id="<?php echo $day; ?>_status" style="text-align: center; font-weight: bold;">
                            <!-- Will be updated by JavaScript -->
                        </div>
                        
                        <!-- Closed Checkbox -->
                        <div style="text-align: center;">
                            <label style="display: flex; align-items: center; gap: 5px; justify-content: center; cursor: pointer;">
                                <input type="checkbox" 
                                       name="<?php echo $day; ?>_closed" 
                                       id="<?php echo $day; ?>_closed"
                                       value="1" 
                                       <?php checked($is_closed, 1); ?>
                                       onchange="updateTimeDisplay('<?php echo $day; ?>')"
                                       style="transform: scale(1.2);">
                                <span style="font-weight: bold; color: #dc3545;">Closed</span>
                            </label>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <!-- Save Button -->
            <div style="text-align: center; margin-top: 30px;">
                <button type="submit" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; padding: 15px 30px; border-radius: 10px; font-size: 1.1rem; font-weight: bold; cursor: pointer;">
                    💾 Save Operating Hours
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function updateTimeDisplay(day) {
    const openInput = document.getElementById(day + '_open');
    const closeInput = document.getElementById(day + '_close');
    const closedCheckbox = document.getElementById(day + '_closed');
    const statusDiv = document.getElementById(day + '_status');
    
    if (!openInput || !closeInput || !closedCheckbox || !statusDiv) return;
    
    if (closedCheckbox.checked) {
        statusDiv.innerHTML = '<span style="color: #dc3545;">🔴 CLOSED</span>';
        openInput.disabled = true;
        closeInput.disabled = true;
        openInput.style.opacity = '0.5';
        closeInput.style.opacity = '0.5';
    } else {
        openInput.disabled = false;
        closeInput.disabled = false;
        openInput.style.opacity = '1';
        closeInput.style.opacity = '1';
        
        const openTime = openInput.value;
        const closeTime = closeInput.value;
        
        if (openTime && closeTime) {
            const openDisplay = formatTime12Hour(openTime);
            const closeDisplay = formatTime12Hour(closeTime);
            statusDiv.innerHTML = `<span style="color: #28a745;">🟢 ${openDisplay} - ${closeDisplay}</span>`;
        } else {
            statusDiv.innerHTML = '<span style="color: #ffc107;">⚠️ Set Times</span>';
        }
    }
}

function formatTime12Hour(time24) {
    const [hours, minutes] = time24.split(':');
    const hour12 = hours % 12 || 12;
    const ampm = hours < 12 ? 'AM' : 'PM';
    return `${hour12}:${minutes} ${ampm}`;
}

// Initialize displays on page load
document.addEventListener('DOMContentLoaded', function() {
    const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    days.forEach(day => {
        updateTimeDisplay(day);
    });
});
</script>
