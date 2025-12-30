<?php
/*
Plugin Name: Ohana Doctor Schedule
Description: Multi-date doctor availability with booking status, capacity, and online booking.
Version: 5.2
Author: Ohana Clinic
*/

if (!defined('ABSPATH')) exit;

/* =========================
   1. META BOX
========================= */
add_action('add_meta_boxes', function () {
    add_meta_box(
        'ods_schedule_box',
        'Doctor Availability',
        'ods_schedule_box_html',
        'doctor',
        'normal',
        'high'
    );
});

function ods_schedule_box_html($post) {
    wp_nonce_field('ods_save_schedule', 'ods_nonce');
    $schedules = get_post_meta($post->ID, '_ods_schedules', true);
    if (!is_array($schedules)) $schedules = [];
    ?>

    <div id="ods-schedules">
        <?php foreach ($schedules as $i => $row): ?>
            <div class="ods-row">
                <input type="date" name="ods[<?= $i ?>][date]" value="<?= esc_attr($row['date']) ?>">
                <input type="time" name="ods[<?= $i ?>][start]" value="<?= esc_attr($row['start']) ?>">
                <input type="time" name="ods[<?= $i ?>][end]" value="<?= esc_attr($row['end']) ?>">
                <select name="ods[<?= $i ?>][type]">
                    <option value="first" <?= selected($row['type'], 'first') ?>>First Visit</option>
                    <option value="follow" <?= selected($row['type'], 'follow') ?>>Follow-up</option>
                </select>
                <label>
                    <input type="checkbox" name="ods[<?= $i ?>][full]" value="1" <?= !empty($row['full']) ? 'checked' : '' ?>>
                    Full
                </label>
                <input type="number" name="ods[<?= $i ?>][capacity]" value="<?= esc_attr($row['capacity'] ?? 1) ?>" min="1" style="width:60px;" title="Number of people">
                <button type="button" class="button remove-row">✖</button>
            </div>
        <?php endforeach; ?>
    </div>

    <button type="button" class="button button-primary" id="add-row">+ Add Schedule</button>

    <style>
        .ods-row { display:flex; gap:10px; margin-bottom:10px; align-items:center; }
        .ods-row input[type="number"] { width:60px; }
    </style>

    <script>
    (function(){
        let index = <?= count($schedules) ?>;
        document.getElementById('add-row').onclick = function () {
            const div = document.createElement('div');
            div.className = 'ods-row';
            div.innerHTML = `
                <input type="date" name="ods[${index}][date]">
                <input type="time" name="ods[${index}][start]">
                <input type="time" name="ods[${index}][end]">
                <select name="ods[${index}][type]">
                    <option value="first">First Visit</option>
                    <option value="follow">Follow-up</option>
                </select>
                <label>
                    <input type="checkbox" name="ods[${index}][full]" value="1">
                    Full
                </label>
                <input type="number" name="ods[${index}][capacity]" value="1" min="1" style="width:60px;" title="Number of people">
                <button type="button" class="button remove-row">✖</button>
            `;
            document.getElementById('ods-schedules').appendChild(div);
            index++;
        };

        document.addEventListener('click', function(e){
            if(e.target.classList.contains('remove-row')){
                e.target.parentElement.remove();
            }
        });
    })();
    </script>

<?php }

/* =========================
   2. SAVE META
========================= */
add_action('save_post', function ($post_id) {
    if(!isset($_POST['ods_nonce']) || !wp_verify_nonce($_POST['ods_nonce'],'ods_save_schedule')) return;
    if(!current_user_can('edit_post',$post_id)) return;

    if(isset($_POST['ods']) && is_array($_POST['ods'])){
        $clean=[];
        foreach($_POST['ods'] as $row){
            if(empty($row['date']) || empty($row['start']) || empty($row['end'])) continue;
            $clean[]=[
                'date' => sanitize_text_field($row['date']),
                'start' => sanitize_text_field($row['start']),
                'end' => sanitize_text_field($row['end']),
                'type' => sanitize_text_field($row['type']),
                'full' => !empty($row['full']) ? 1 : 0,
                'capacity' => !empty($row['capacity']) ? intval($row['capacity']) : 1,
            ];
        }
        update_post_meta($post_id,'_ods_schedules',$clean);
    } else {
        delete_post_meta($post_id,'_ods_schedules');
    }
});

/* =========================
   3. BOOKING POST TYPE
========================= */
add_action('init', function(){
    register_post_type('doctor_booking', [
        'labels' => [
            'name' => 'Doctor Bookings',
            'singular_name' => 'Doctor Booking',
        ],
        'public' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-calendar-alt',
        'supports' => ['title', 'custom-fields'],
    ]);
});

/* =========================
   4. AJAX BOOKING HANDLER (Save all customer info)
========================= */
add_action('wp_ajax_ods_book_slot', 'ods_handle_booking');
add_action('wp_ajax_nopriv_ods_book_slot', 'ods_handle_booking');

function ods_handle_booking() {
    $required = ['doctor_id','date','time','people','last-name','first-name','phone','email','birth-year','birth-month','birth-day'];
    foreach($required as $key){
        if(empty($_POST[$key])){
            wp_send_json_error("Missing field: $key");
        }
    }

    $doctor_id = intval($_POST['doctor_id']);
    $date = sanitize_text_field($_POST['date']);
    $time = sanitize_text_field($_POST['time']);
    $people = intval($_POST['people']);
    $last_name = sanitize_text_field($_POST['last-name']);
    $first_name = sanitize_text_field($_POST['first-name']);
    $phone = sanitize_text_field($_POST['phone']);
    $email = sanitize_email($_POST['email']);
    $birth = sanitize_text_field($_POST['birth-year']).'-'.sanitize_text_field($_POST['birth-month']).'-'.sanitize_text_field($_POST['birth-day']);
    $patient_id = sanitize_text_field($_POST['patient-id'] ?? '');
    $remarks = sanitize_textarea_field($_POST['remarks'] ?? '');

    // Create booking post
    $booking_id = wp_insert_post([
        'post_type' => 'doctor_booking',
        'post_title' => "Booking: $first_name $last_name with doctor #$doctor_id on $date $time",
        'post_status' => 'publish',
        'meta_input' => [
            'doctor_id' => $doctor_id,
            'date' => $date,
            'time' => $time,
            'people' => $people,
            'last_name' => $last_name,
            'first_name' => $first_name,
            'phone' => $phone,
            'email' => $email,
            'birth_date' => $birth,
            'patient_id' => $patient_id,
            'remarks' => $remarks,
        ],
    ]);

    if($booking_id){
        // Email notification
        $message = "New booking:\nDoctor ID: $doctor_id\nDate: $date\nTime: $time\nPatient: $first_name $last_name\nPhone: $phone\nEmail: $email\nPeople: $people\nPatient ID: $patient_id\nRemarks: $remarks";
        wp_mail(get_option('admin_email'), 'New Doctor Booking', $message);

        wp_send_json_success('Booking saved!');
    } else {
        wp_send_json_error('Failed to save booking.');
    }
}
