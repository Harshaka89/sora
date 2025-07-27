<?php
if (!defined('ABSPATH')) exit;
?>

<div class="wrap">
    <div style="max-width: 900px; margin: 20px auto; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        
        <div style="text-align: center; margin-bottom: 40px; padding-bottom: 25px; border-bottom: 4px solid #667eea;">
            <h1 style="font-size: 2.5rem; color: #2c3e50; margin: 0;">⚙️ Restaurant Settings v1.4</h1>
            <p style="color: #6c757d; margin: 15px 0 0 0; font-size: 1.1rem;">Complete operational configuration</p>
        </div>
        
        <?php if (isset($_GET['message']) && $_GET['message'] == 'saved'): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; margin: 20px 0; border-radius: 8px; border: 2px solid #28a745;">
                <h3 style="margin: 0 0 10px 0;">✅ Settings Saved Successfully!</h3>
                <p style="margin: 0;">
                    <?php echo isset($_GET['count']) ? intval($_GET['count']) : 0; ?> settings have been saved to the database.
                    <br><strong>Data Updated:</strong> <?php echo date('Y-m-d H:i:s'); ?>
                </p>
            </div>
        <?php endif; ?>
        
        <!-- Current Settings Display -->
        <div style="background: #e3f2fd; padding: 20px; border-radius: 10px; margin-bottom: 30px; border-left: 5px solid #2196f3;">
            <h3 style="margin: 0 0 15px 0; color: #1976d2;">📊 Current Settings Status</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div>
                    <strong>Status:</strong> <?php echo ($settings['restaurant_open'] ?? '1') == '1' ? '🟢 OPEN' : '🔴 CLOSED'; ?>
                </div>
                <div>
                    <strong>Restaurant:</strong> <?php echo esc_html($settings['restaurant_name'] ?? get_bloginfo('name')); ?>
                </div>
                <div>
                    <strong>Email:</strong> <?php echo esc_html($settings['restaurant_email'] ?? get_option('admin_email')); ?>
                </div>
                <div>
                    <strong>Phone:</strong> <?php echo esc_html($settings['restaurant_phone'] ?? 'Not set'); ?>
                </div>
                <div>
                    <strong>Max Party:</strong> <?php echo esc_html($settings['max_party_size'] ?? '12'); ?> guests
                </div>
            </div>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field('rrs_settings_save', 'settings_nonce'); ?>
            
            <!-- Restaurant Status -->
            <div style="margin-bottom: 40px; padding: 30px; background: #f8f9fa; border-radius: 15px; border: 3px solid #e9ecef;">
                <h2 style="color: #007cba; font-size: 1.6rem; margin: 0 0 25px 0; border-bottom: 3px solid #007cba; padding-bottom: 15px;">
                    🔄 Restaurant Status
                </h2>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <label style="display: flex; align-items: center; gap: 15px; font-size: 1.2rem; font-weight: bold; padding: 20px; border-radius: 10px; cursor: pointer; color: #28a745; background: white; border: 3px solid #28a745;">
                        <input type="radio" name="restaurant_open" value="1" <?php checked(($settings['restaurant_open'] ?? '1'), '1'); ?> style="transform: scale(2);">
                        <span>🟢 OPEN - Accept Reservations</span>
                    </label>
                    
                    <label style="display: flex; align-items: center; gap: 15px; font-size: 1.2rem; font-weight: bold; padding: 20px; border-radius: 10px; cursor: pointer; color: #dc3545; background: white; border: 3px solid #dc3545;">
                        <input type="radio" name="restaurant_open" value="0" <?php checked(($settings['restaurant_open'] ?? '1'), '0'); ?> style="transform: scale(2);">
                        <span>🔴 CLOSED - Stop Reservations</span>
                    </label>
                </div>
            </div>
            
            <!-- Restaurant Information -->
            <div style="margin-bottom: 40px; padding: 30px; background: #f8f9fa; border-radius: 15px; border: 3px solid #e9ecef;">
                <h2 style="color: #007cba; font-size: 1.6rem; margin: 0 0 25px 0; border-bottom: 3px solid #007cba; padding-bottom: 15px;">
                    🏪 Restaurant Information
                </h2>
                
                <!-- Restaurant Name -->
                <div style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 10px; font-weight: bold; font-size: 1.1rem; color: #2c3e50;">Restaurant Name *</label>
                    <input type="text" name="restaurant_name" value="<?php echo esc_attr($settings['restaurant_name'] ?? get_bloginfo('name')); ?>" 
                           style="width: 100%; padding: 15px; border: 3px solid #e9ecef; border-radius: 10px; font-size: 1.1rem; box-sizing: border-box;" required>
                </div>
                
                <!-- Contact Information Grid -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px;">
                    <div>
                        <label style="display: block; margin-bottom: 10px; font-weight: bold; font-size: 1.1rem; color: #2c3e50;">📧 Contact Email *</label>
                        <input type="email" name="restaurant_email" value="<?php echo esc_attr($settings['restaurant_email'] ?? get_option('admin_email')); ?>" 
                               style="width: 100%; padding: 15px; border: 3px solid #e9ecef; border-radius: 10px; font-size: 1.1rem; box-sizing: border-box;" required>
                        <small style="color: #6c757d;">For reservation notifications</small>
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 10px; font-weight: bold; font-size: 1.1rem; color: #2c3e50;">📞 Phone Number</label>
                        <input type="tel" name="restaurant_phone" value="<?php echo esc_attr($settings['restaurant_phone'] ?? ''); ?>" 
                               placeholder="+1 (123) 456-7890"
                               style="width: 100%; padding: 15px; border: 3px solid #e9ecef; border-radius: 10px; font-size: 1.1rem; box-sizing: border-box;">
                        <small style="color: #6c757d;">Customer contact number</small>
                    </div>
                </div>
                
                <!-- Address Field -->
                <div style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 10px; font-weight: bold; font-size: 1.1rem; color: #2c3e50;">📍 Restaurant Address</label>
                    <input type="text" name="restaurant_address" value="<?php echo esc_attr($settings['restaurant_address'] ?? ''); ?>" 
                           placeholder="123 Main Street, City, State 12345"
                           style="width: 100%; padding: 15px; border: 3px solid #e9ecef; border-radius: 10px; font-size: 1.1rem; box-sizing: border-box;">
                    <small style="color: #6c757d;">Full restaurant address</small>
                </div>
                
                <!-- Max Party Size -->
                <div style="max-width: 300px;">
                    <label style="display: block; margin-bottom: 10px; font-weight: bold; font-size: 1.1rem; color: #2c3e50;">👥 Maximum Party Size *</label>
                    <input type="number" name="max_party_size" value="<?php echo esc_attr($settings['max_party_size'] ?? '12'); ?>" 
                           min="1" max="50" required
                           style="width: 100%; padding: 15px; border: 3px solid #e9ecef; border-radius: 10px; text-align: center; font-weight: bold; font-size: 1.3rem; box-sizing: border-box;">
                    <small style="color: #6c757d;">Largest group you can serve</small>
                </div>
            </div>
            
            <!-- Save Button -->
            <div style="text-align: center; padding-top: 40px; border-top: 4px solid #e9ecef;">
                <button type="submit" name="save_settings" value="1" 
                        style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; padding: 25px 60px; border-radius: 15px; font-size: 1.4rem; font-weight: bold; cursor: pointer; transition: transform 0.3s ease;">
                    💾 Save All Settings
                </button>
                <p style="margin-top: 15px; color: #6c757d;">
                    All changes will be saved immediately to the database.
                </p>
            </div>
        </form>
        
        <!-- Database Debug Info -->
        <div style="margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 10px; border: 2px solid #dee2e6;">
            <h4 style="margin: 0 0 15px 0; color: #495057;">🔧 Database Status</h4>
            <?php
            global $wpdb;
            $table_name = $wpdb->prefix . 'rrs_settings';
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
            $settings_count = $table_exists ? $wpdb->get_var("SELECT COUNT(*) FROM $table_name") : 0;
            ?>
            <p style="margin: 5px 0; font-family: monospace;">
                ✅ Settings Table: <?php echo $table_exists ? 'EXISTS' : 'MISSING'; ?><br>
                📊 Settings Count: <?php echo $settings_count; ?><br>
                🕒 Last Check: <?php echo date('Y-m-d H:i:s'); ?><br>
                📱 Phone Support: ENABLED
            </p>
        </div>
    </div>
</div>

<style>
@media (max-width: 768px) {
    div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
    div[style*="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr))"] {
        grid-template-columns: 1fr 1fr !important;
    }
}

button:hover {
    transform: translateY(-3px) !important;
    box-shadow: 0 8px 25px rgba(40,167,69,0.4) !important;
}

input:focus {
    border-color: #667eea !important;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
    outline: none !important;
}
</style>
