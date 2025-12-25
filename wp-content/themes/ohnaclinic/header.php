<!DOCTYPE html>
<html <?php language_attributes(); ?>>
    
<head>
    <link rel="stylesheet" href="<?php echo get_stylesheet_uri(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php wp_head(); ?>
    

</head>
<body <?php body_class(); ?>>



<header class="header">
    <div class="header-img"><img src="http://clinic_website.test/wp-content/uploads/2025/12/clinic_logo.jpg" alt=""></div>
    <!-- <h1 class="site-title"><?php bloginfo('name'); ?></h1> -->
    <div class="icon-space"> 
        
    </div>
    
    <div class="menu" id="mobile-menu">
        <?php 
        wp_nav_menu([
            'theme_location' => 'primary',
            'menu_class' => 'menu-list',
            'container' => false
        ]); 
        ?>
        <!-- <i class="fa-solid fa-list phone-only" id="menu-toggle"></i> -->
    </div>
</header>










<script>
const phoneToggle = document.querySelector('.phone-only');
const menu = document.querySelector('.menu');

phoneToggle.addEventListener('click', () => {
    menu.classList.toggle('show');
});

</script>



<main>