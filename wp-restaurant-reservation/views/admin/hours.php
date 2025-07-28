<?php
if (!defined('ABSPATH')) exit;

// Handle success messages
$message = '';
if (isset($_GET['message'])) {
    switch ($_GET['message']) {
        case 'hours_saved':
            $count = isset($_GET['count']) ? intval($_GET['count']) : 0;
            $message = '<div class="notice notice-success is-dismissible"><p>✅ Operating hours saved successfully! (' . $count . ' days updated)</p></div>';
            break;
    }
}

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

function yrr_get_hour_value($hour_obj, $property, $default = '') {
    if (is_object($hour_obj) && property_exists($hour_obj, $property)) {
        return $hour_obj->$property;
    }
    return $default;
}

function yrr_format_time($time) {
    if (empty($time) || $time === '00:00:00') return '';
    return date('H:i', strtotime($time));
}
?>

<div class="wrap">
    <?php echo $message; ?>
    
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
                
                <div style="background: #f8f9fa; padding: 20px; margin-bottom: 15px; border-radius: 10px;">
                    <div style="display: grid; grid-template-columns: 150px 1fr 1fr 1fr 120px; gap: 15px; align-items: center;">
                        
                        <div style="font-weight: bold; color: #2c3e50;">
                            <?php echo $config['icon']; ?> <?php echo $config['name']; ?>
                        </div>
                        
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Open Time</label>
                            <input type="time" 
                                   name="<?php echo $day; ?>_open" 
                                   value="<?php echo $open_time; ?>" 
                                   <?php echo $is_closed ? 'disabled' : ''; ?>
                                   style="width: 100%; padding: 8px; border: 2px solid #28a745; border-radius: 5px;">
                        </div>
                        
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Close Time</label>
                            <input type="time" 
                                   name="<?php echo $day; ?>_close" 
                                   value="<?php echo $close_time; ?>" 
                                   <?php echo $is_closed ? 'disabled' : ''; ?>
                                   style="width: 100%; padding: 8px; border: 2px solid #dc3545; border-radius: 5px;">
                        </div>
                        
                        <div style="text-align: center;">
                            <?php if ($is_closed): ?>
                                <span style="color: #dc3545; font-weight: bold;">🔴 CLOSED</span>
                            <?php else: ?>
                                <span style="color: #28a745; font-weight: bold;">🟢 OPEN</span>
                            <?php endif; ?>
                        </div>
                        
                        <div style="text-align: center;">
                            <label style="cursor: pointer;">
                                <input type="checkbox" 
                                       name="<?php echo $day; ?>_closed" 
                                       value="1" 
                                       <?php checked($is_closed, 1); ?>
                                       style="transform: scale(1.2);">
                                <span style="font-weight: bold; color: #dc3545;">Closed</span>
                            </label>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div style="text-align: center; margin-top: 30px;">
                <button type="submit" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; padding: 15px 30px; border-radius: 10px; font-size: 1.1rem; font-weight: bold; cursor: pointer;">
                    💾 Save Operating Hours
                </button>
            </div>
        </form>
    </div>
</div>
