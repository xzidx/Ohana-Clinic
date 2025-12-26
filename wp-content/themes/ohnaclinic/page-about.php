





















<?php
/*
Template Name: About Page
*/
get_header();
?>

<main>
    <h1><?php the_title(); ?></h1>
    <div class="page-content">
        <?php
        while ( have_posts() ) : the_post();
            the_content();
        endwhile;
        ?>
    </div>
</main>

<?php get_footer(); ?>
