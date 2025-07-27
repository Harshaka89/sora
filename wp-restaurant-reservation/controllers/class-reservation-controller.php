<?php
/**
 * Reservation Controller - Yenolx Restaurant Reservation v1.5
 */

if (!defined('ABSPATH')) exit;

class YRR_Reservation_Controller {
    private $reservation_model;
    private $tables_model;
    private $pricing_model;
    
    public function __construct() {
        $this->reservation_model = new YRR_Reservation_Model();
        $this->tables_model = new YRR_Tables_Model();
        $this->pricing_model = new YRR_Pricing_Model();
    }
    
    public function display_booking_form($atts) {
        $atts = shortcode_atts(array(
            'show_tables' => 'true',
            'show_pricing' => 'true'
        ), $atts);
        
        ob_start();
        include YRR_PLUGIN_PATH . 'views/public/booking-form.php';
        return ob_get_clean();
    }
    
    public function ajax_update_reservation() {
        check_ajax_referer('yrr_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $id = intval($_POST['id']);
        $data = array(
            'status' => sanitize_text_field($_POST['status'])
        );
        
        $result = $this->reservation_model->update($id, $data);
        
        wp_send_json_success(array(
            'message' => 'Reservation updated successfully',
            'result' => $result
        ));
    }
    
    public function ajax_delete_reservation() {
        check_ajax_referer('yrr_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $id = intval($_POST['id']);
        $result = $this->reservation_model->delete($id);
        
        wp_send_json_success(array(
            'message' => 'Reservation deleted successfully',
            'result' => $result
        ));
    }
}

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
</script>

?>
