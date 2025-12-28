<?php
/*
Template Name: Already Booked Page
*/

// Enable error display for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log');

get_header();
?>

<div class="already-booked">
<h2>Appointment Already Booked</h2>

<?php
// Safe GET handling
$doctor_id = isset($_GET['doctor']) ? intval($_GET['doctor']) : 0;
$date      = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : '';
$hour      = isset($_GET['hour']) ? intval($_GET['hour']) : '';

// Validate doctor
$doctor = null;
if ($doctor_id > 0) {
    $doctor = get_post($doctor_id);
}

// Stop if invalid doctor
if (!$doctor instanceof WP_Post) {
    echo '<p style="color:red;">Invalid doctor or missing parameters. Please select a slot again.</p>';
    get_footer();
    exit; // Stop execution to avoid fatal errors
}

// Display booking info
echo '<p>The slot you selected is already full.</p>';
echo '<p><strong>Doctor:</strong> ' . esc_html($doctor->post_title) . '</p>';
echo '<p><strong>Date:</strong> ' . esc_html($date) . '</p>';
echo '<p><strong>Hour:</strong> ' . esc_html($hour) . ':00</p>';
echo '<p>Please select another available slot.</p>';
?>

</div>

<?php get_footer(); ?>
