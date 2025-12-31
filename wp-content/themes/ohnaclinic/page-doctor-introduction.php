<?php
/*
Template Name: Doctor Introduction
*/
?>

<?php get_header(); ?>

    <div class="more-space"></div>
    <div class="main">
        <div class="title">
            <h1>Doctor introduction</h1>
            <span>Meet our team of experienced doctors dedicated to providing the highest quality care. <br> Learn about their specialties, qualifications, and commitment to your health.</span>
        </div>
    </div>
    
    <div class="more-space-1"></div>
    
    <div class="card-cover">
        <?php
        // 1. Setup the query to get Doctors from MySQL
        $args = array(
            'post_type'      => 'doctor',
            'posts_per_page' => -1, // Show all doctors
            'orderby'        => 'date',
            'order'          => 'ASC'
        );
        $doctor_query = new WP_Query($args);

        // 2. Start the Loop
        if ($doctor_query->have_posts()) :
            while ($doctor_query->have_posts()) : $doctor_query->the_post();
                
                // Get the custom data we saved in functions.php
                $position     = get_post_meta(get_the_ID(), '_doctor_position', true);
                $achievements = get_post_meta(get_the_ID(), '_doctor_achievements', true);
                
                // Convert the "one per line" text into an array
                $achievement_lines = explode("\n", $achievements);
        ?>

            <div class="profile-card">
                <div class="content-side">
                    <h1 class="name"><?php the_title(); ?></h1>
                    <div class="gold-line"></div>
                    
                    <h3 class="current-title"><?php echo esc_html($position); ?></h3>
                    
                    <ul class="achievements">
                        <?php 
                        foreach ($achievement_lines as $line) {
                            if (!empty(trim($line))) {
                                echo '<li>' . esc_html($line) . '</li>';
                            }
                        }
                        ?>
                    </ul>
                </div>
                
                <div class="image-side">
                    <?php if (has_post_thumbnail()) : ?>
                        <?php the_post_thumbnail('large'); ?>
                    <?php else : ?>
                        <img src="<?php echo get_template_directory_uri(); ?>/images/default-doctor.jpg" alt="Doctor">
                    <?php endif; ?>
                </div>
            </div>

        <?php 
            endwhile;
            wp_reset_postdata(); // Clean up MySQL query
        else : 
        ?>
            <p style="text-align:center;">No doctor profiles found.</p>
        <?php endif; ?>
    </div>

<?php get_footer(); ?>