<?php
function mytheme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');

    register_nav_menus([
        'primary' => 'Primary Menu',
    ]);
}
add_action('after_setup_theme', 'mytheme_setup');

function mytheme_assets() {
    wp_enqueue_style(
        'mytheme-style',
        get_stylesheet_uri(),
        [],
        filemtime(get_stylesheet_directory() . '/style.css')
    );
}
add_action('wp_enqueue_scripts', 'mytheme_assets');


function mytheme_register_products() {
    register_post_type('product', [
        'labels' => ['name'=>'Products','singular_name'=>'Product'],
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-cart',
        'supports' => ['title','editor','thumbnail']
    ]);
}
add_action('init','mytheme_register_products');

function mytheme_register_product_category() {
    register_taxonomy('product_category','product',[
        'label'=>'Product Categories',
        'hierarchical'=>true
    ]);
}
add_action('init','mytheme_register_product_category');

function mytheme_product_meta() {
    add_meta_box('product_details','Product Details','mytheme_product_meta_callback','product');
}
add_action('add_meta_boxes','mytheme_product_meta');

function mytheme_product_meta_callback($post){
    $price    = get_post_meta($post->ID,'_price',true);
    $stock    = get_post_meta($post->ID,'_stock',true);
    $featured = get_post_meta($post->ID,'_featured',true);
    ?>
    <p>
        <label>Price ($):</label><br>
        <input type="number" name="price" value="<?php echo esc_attr($price); ?>">
    </p>

    <p>
        <label>Stock:</label><br>
        <input type="number" name="stock" value="<?php echo esc_attr($stock); ?>">
    </p>

    <p>
        <label>
            <input type="checkbox" name="featured" value="1" <?php checked($featured, '1'); ?>>
            Featured Product
        </label>
    </p>
    <?php
}


function mytheme_save_product_meta($post_id){

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (get_post_type($post_id) !== 'product') return;

    update_post_meta($post_id,'_price', $_POST['price'] ?? '');
    update_post_meta($post_id,'_stock', $_POST['stock'] ?? '');
    update_post_meta($post_id,'_featured', isset($_POST['featured']) ? '1' : '0');
}
add_action('save_post','mytheme_save_product_meta');
