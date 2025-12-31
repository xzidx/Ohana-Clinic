<?php
/*
Template Name: Booking Page
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log');

get_header();
?>

<div class="booking-page">
<h2>Booking Page</h2>

<?php
// Safe GET handling
$doctor_id = isset($_GET['doctor']) ? intval($_GET['doctor']) : 0;
$date = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : '';
$hour = isset($_GET['hour']) ? intval($_GET['hour']) : '';

$doctor = null;
if ($doctor_id > 0) {
    $doctor = get_post($doctor_id);
}

if (!$doctor instanceof WP_Post) {
    echo '<p style="color:red;">Invalid doctor or missing parameters. Please select a slot again.</p>';
} else {

    echo '<p><strong>Doctor:</strong> ' . esc_html($doctor->post_title) . '</p>';
    echo '<p><strong>Date:</strong> ' . esc_html($date) . '</p>';
    echo '<p><strong>Hour:</strong> ' . esc_html($hour) . ':00</p>';

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name  = isset($_POST['patient_name']) ? sanitize_text_field($_POST['patient_name']) : '';
        $email = isset($_POST['patient_email']) ? sanitize_email($_POST['patient_email']) : '';

        if ($name && $email) {
            // Save booking info to DB if needed
            echo '<p style="color:green;">Thank you, ' . esc_html($name) . '! Your appointment has been recorded.</p>';
        } else {
            echo '<p style="color:red;">Please fill in all fields.</p>';
        }
    }
    ?>

    <form method="post">
        <input type="hidden" name="doctor" value="<?php echo esc_attr($doctor_id); ?>">
        <input type="hidden" name="date" value="<?php echo esc_attr($date); ?>">
        <input type="hidden" name="hour" value="<?php echo esc_attr($hour); ?>">
        <label>Name: <input type="text" name="patient_name" required></label><br>
        <label>Email: <input type="email" name="patient_email" required></label><br>
        <button type="submit">Book Appointment</button>
    </form>

<?php } ?>

</div>

<?php get_footer(); ?>
