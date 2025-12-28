<?php
/*
Plugin Name: Ohana Doctor Schedule
Description: Multi-date doctor availability with booking status and capacity.
Version: 5.0
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
?>
