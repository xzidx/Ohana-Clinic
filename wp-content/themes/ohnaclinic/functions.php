<?php
// -------------------------
// THEME SETUP
// -------------------------
function mytheme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');

    register_nav_menus([
        'primary' => 'Primary Menu',
    ]);
}
add_action('after_setup_theme', 'mytheme_setup');


// -------------------------
// ENQUEUE STYLES
// -------------------------
function mytheme_assets() {

    // Google Fonts
    wp_enqueue_style(
        'mytheme-google-fonts',
        'https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible+Mono:ital,wght@0,200..800;1,200..800&family=Stack+Sans+Headline:wght@200..700&display=swap',
        [],
        null
    );

    // Main theme CSS
    wp_enqueue_style(
        'mytheme-style',
        get_stylesheet_uri(),
        ['mytheme-google-fonts'],
        filemtime(get_stylesheet_directory() . '/style.css')
    );

    // Front page CSS
    if (is_front_page()) {
        wp_enqueue_style(
            'mytheme-front-style',
            get_stylesheet_directory_uri() . '/css/front-page.css',
            ['mytheme-style'],
            filemtime(get_stylesheet_directory() . '/css/front-page.css')
        );
    }

    // About page CSS
    if (is_page('about')) { 
        wp_enqueue_style(
            'mytheme-about-style',
            get_stylesheet_directory_uri() . '/css/about.css',
            ['mytheme-style'],
            filemtime(get_stylesheet_directory() . '/css/about.css')
        );
    }

    // Contact page CSS
    if (is_page('contact')) { 
        wp_enqueue_style(
            'mytheme-contact-style',
            get_stylesheet_directory_uri() . '/css/contact.css',
            ['mytheme-style'],
            filemtime(get_stylesheet_directory() . '/css/contact.css')
        );
    }

    // Inquiry page CSS
    if (is_page('inquiry')) { 
        wp_enqueue_style(
            'mytheme-inquiry-style',
            get_stylesheet_directory_uri() . '/css/inquiry.css',
            ['mytheme-style'],
            filemtime(get_stylesheet_directory() . '/css/inquiry.css')
        );
    }

    // Doctor Introduction page CSS
    if (is_page('doctor-introduction')) { 
        wp_enqueue_style(
            'mytheme-doctor-intro-style',
            get_stylesheet_directory_uri() . '/css/doctor-introduction.css',
            ['mytheme-style'],
            filemtime(get_stylesheet_directory() . '/css/doctor-introduction.css')
        );
    }

    // Doctor Schedule page CSS
    if (is_page('doctor-schedule')) { 
        wp_enqueue_style(
            'mytheme-doctor-schedule-style',
            get_stylesheet_directory_uri() . '/css/doctor-schedule.css',
            ['mytheme-style'],
            filemtime(get_stylesheet_directory() . '/css/doctor-schedule.css')
        );
    }
}
add_action('wp_enqueue_scripts', 'mytheme_assets');


// -------------------------
// CUSTOM POST TYPE: DOCTORS
// -------------------------
function mytheme_register_doctors() {
    register_post_type('doctor', [
        'labels' => [
            'name' => 'Doctors',
            'singular_name' => 'Doctor',
            'add_new' => 'Add New Doctor',
            'add_new_item' => 'Add New Doctor Profile'
        ],
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-businessman',
        'supports' => ['title', 'editor', 'thumbnail']
    ]);
}
add_action('init', 'mytheme_register_doctors');


// -------------------------
// DOCTOR META BOXES
// -------------------------
function mytheme_doctor_meta() {
    add_meta_box('doctor_details', 'Doctor Profile Details', 'mytheme_doctor_meta_callback', 'doctor');
}
add_action('add_meta_boxes', 'mytheme_doctor_meta');

function mytheme_doctor_meta_callback($post) {
    $position     = get_post_meta($post->ID, '_doctor_position', true);
    $achievements = get_post_meta($post->ID, '_doctor_achievements', true);
    ?>
    <p>
        <label><strong>Current Title / Position:</strong></label><br>
        <input type="text" name="doctor_position" value="<?php echo esc_attr($position); ?>" style="width:100%;" placeholder="e.g. Keiai Clinic Ethics Committee Chairman">
    </p>

    <p>
        <label><strong>Achievements & History (One per line):</strong></label><br>
        <textarea name="doctor_achievements" rows="8" style="width:100%;" placeholder="Former Cabinet Secretariat Advisor..."><?php echo esc_textarea($achievements); ?></textarea>
    </p>
    <?php
}

function mytheme_save_doctor_meta($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (get_post_type($post_id) !== 'doctor') return;

    if (isset($_POST['doctor_position'])) {
        update_post_meta($post_id, '_doctor_position', sanitize_text_field($_POST['doctor_position']));
    }
    if (isset($_POST['doctor_achievements'])) {
        update_post_meta($post_id, '_doctor_achievements', sanitize_textarea_field($_POST['doctor_achievements']));
    }
}
add_action('save_post', 'mytheme_save_doctor_meta');


// -------------------------
// ALLOW SVG UPLOADS
// -------------------------
function custom_mime_types($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'custom_mime_types');


// -------------------------
// DOCTOR BOOKING AJAX HANDLER
// -------------------------
add_action('wp_ajax_ohana_book_slot', 'ohana_book_slot');
add_action('wp_ajax_nopriv_ohana_book_slot', 'ohana_book_slot');

function ohana_book_slot() {
    $doctor_id = intval($_POST['doctor_id'] ?? 0);
    $date      = sanitize_text_field($_POST['date'] ?? '');
    $start     = sanitize_text_field($_POST['start'] ?? '');
    $end       = sanitize_text_field($_POST['end'] ?? '');
    $type      = sanitize_text_field($_POST['type'] ?? '');
    $people    = intval($_POST['people'] ?? 1);

    // Validate required fields
    if (!$doctor_id || !$date || !$start || !$end) {
        wp_send_json(['success' => false, 'message' => 'Invalid data']);
    }

    // Save booking as a custom post type
    $booking_id = wp_insert_post([
        'post_title'  => "Booking: $date $start-$end",
        'post_type'   => 'doctor_booking',
        'post_status' => 'publish',
        'meta_input'  => [
            'doctor_id' => $doctor_id,
            'date'      => $date,
            'start'     => $start,
            'end'       => $end,
            'type'      => $type,
            'people'    => $people,
        ]
    ]);

    if ($booking_id) {
        wp_send_json(['success' => true, 'message' => 'Booking confirmed!']);
    } else {
        wp_send_json(['success' => false, 'message' => 'Booking failed']);
    }
}
