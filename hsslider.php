<?php
/**
 * Plugin Name: HS Slider
 * Plugin URI: http://www.heliossolutions.in
 * Description: HS Slider to integrate z-index jQuery gallery, Add any image that has already been uploaded to add to your slider.
 * Version: 1.1
 * Author: Helios Solutions
 * Author URI: http://www.heliossolutions.in
 */

/* Load the plugin. */
add_action( 'plugins_loaded', 'hsslider_load' );

/* Activate  the plugin. */
register_activation_hook( __FILE__, 'hsslider_plugin_activate' );

function hsslider_plugin_activate() {
    /* Register the custom post type. */
    hsslider_register_custom_post_type();
	
    /* Flush permalinks. */
    flush_rewrite_rules();
    
    hsslider_default_settings();
}

/* Deactivate the plugin. */
register_deactivation_hook( __FILE__, 'hsslider_plugin_deactivate' );

function hsslider_plugin_deactivate() {
    flush_rewrite_rules();
}

/* Uninstall the plugin. */
register_uninstall_hook( __FILE__, 'hslider_plugin_uninstall' );

function hsslider_plugin_uninstall() {
    delete_option( 'hsslider_options' );
    
}

function hsslider_load() {
    /* Plugin directory URL. */
    define( 'HSSLIDER_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );

    /* Register the custom post types for HS Slider. */
    add_action( 'init', 'hsslider_register_custom_post_type' );

    /* Register the shortcodes for HS Slider. */
    add_action( 'init', 'hsslider_register_shortcode' );

    /* Attach the stylesheet. */
    add_action( 'template_redirect', 'hsslider_attach_stylesheets' );

    /* Attach the JavaScript. */
    add_action( 'template_redirect', 'hsslider_attach_scripts' );

    /* Add image sizes */
    add_action( 'init', 'hsslider_image_sizes' );

    /* Add submenu of HS Slider.*/
    add_action('admin_menu', 'hsslider_settings');

    /* Register and define the slider settings. */
    add_action( 'admin_init', 'hsslider_settings_init' );

    /* Add URL box for HS slider. */
    add_action( 'add_meta_boxes', 'hsslider_create_url_metaboxes' );
    
    /* Save URL box data. */
    add_action( 'save_post', 'hsslider_save_urldata', 1, 2 );
    
    /* Custome post type columns for list view.  */
    add_filter( 'manage_edit-hsslider_columns', 'hsslider_columns' );

    /* Add cutom post type value for columns to the list view. */
    add_action( 'manage_posts_custom_column', 'hsslider_add_columns' );

    /* List the HS Sliders by the ascending order. */
    add_filter( 'pre_get_posts', 'hsslider_column_order' );	

    /* Custome field on category list. */
    add_filter('manage_edit-hsslidercategory_columns', 'hsslidercategory_columns');  
    add_filter('manage_hsslidercategory_custom_column', 'hsslidercategory_columns_content', 10, 3);  
    
    /* Create custom taxonomy for category of HS Slider*/
    add_action( 'init', 'create_hsslidercategory_taxonomies', 0 );
    add_action( 'restrict_manage_posts', 'category_add_taxonomy_filters' );
    
    /* Create hidden add/edit field for category to store shortcode. */
    add_action( 'hsslidercategory_add_form_fields', 'custom_taxonomy_add_new_meta_field', 10, 2 );
    add_action( 'hsslidercategory_edit_form_fields', 'custom_taxonomy_edit_meta_field', 10, 2 );
    
    add_action( 'edited_hsslidercategory', 'save_taxonomy_custom_meta', 10, 2 );  
    add_action( 'create_hsslidercategory', 'save_taxonomy_custom_meta', 10, 2 );
}

function hsslider_create_url_metaboxes() {
    add_meta_box( 'hsslider_urlbox', __( 'Link', 'hsslider' ), 'hsslider_urlbox', 'hsslider', 'normal', 'default' );
}

function hsslider_urlbox() {
    global $post;	

    $hsslider_link_url = get_post_meta( $post->ID, '_hsslider_link_url', true ); ?>

    <p>URL: <input type="text" style="width: 90%;" name="hsslider_link_url" value="<?php echo esc_attr( $hsslider_link_url ); ?>" /></p>
    <span class="description"><?php echo _e( 'The URL of post.', 'hsslider' ); ?></span>
	
<?php }

function hsslider_save_urldata( $post_id, $post ) {
    if ( isset( $_POST['hsslider_link_url'] ) ) {
        update_post_meta( $post_id, '_hsslider_link_url', strip_tags( $_POST['hsslider_link_url'] ) );
    }	
}

function hsslidercategory_columns($defaults) {  
    $defaults['shortcodeval']  = 'Short code of Category';  
    return $defaults;  
}  

function hsslidercategory_columns_content($c, $column_name, $term_id) { 
    if ($column_name == 'shortcodeval') {  
        $term_meta =  get_option('taxonomy_' . $term_id);
        echo $term_meta['custom_term_meta'];
    }
}

function category_add_taxonomy_filters() {
    global $typenow;
    
    $taxonomies = array('hsslidercategory');

    if( $typenow == 'hsslider' ){
        foreach ($taxonomies as $tax_slug) {
            $tax_obj = get_taxonomy($tax_slug);
            $tax_name = $tax_obj->labels->name;
            $terms = get_terms($tax_slug);
            if(count($terms) > 0) {
                echo "<select name='$tax_slug' id='$tax_slug' class='postform'>";
                echo "<option value=''>Show All $tax_name</option>";
                foreach ($terms as $term) { 
                        echo '<option value='. $term->slug, $_GET[$tax_slug] == $term->slug ? ' selected="selected"' : '','>' . $term->name .' </option>'; //(' . $term->count .')
                }
                echo "</select>";
            }
        }
    }
}


function create_hsslidercategory_taxonomies() 
{
  $labels = array(
    'name' => _x( 'HS Slider Categories', 'taxonomy general name' ),
    'singular_name' => _x( 'HS Slider Category', 'taxonomy singular name' ),
    'search_items' =>  __( 'HS Slider Search Category' ),
    'all_items' => __( 'HS Slider All Category' ),
    'parent_item' => __( 'HS Slider Parent Category' ),
    'parent_item_colon' => __( 'HS Slider Parent Category:' ),
    'edit_item' => __( 'HS Slider Edit Category' ), 
    'update_item' => __( 'HS Slider Update Category' ),
    'add_new_item' => __( 'HS Slider Add New Category' ),
    'new_item_name' => __( 'HS Slider New Category Name' ),
    'menu_name' => __( 'HS Slider Category' ),
  ); 	

  register_taxonomy('hsslidercategory',array('hsslider'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'hsslidercategory' ),
  ));
}  

/* Add submenu of HS Slider. */
function hsslider_settings() {
    add_submenu_page( 'edit.php?post_type=hsslider', __( 'HS Slider Settings', 'hsslider' ), __( 'Settings', 'hsslider' ), 'manage_options', 'hsslider-settings', 'hsslider_settings_page' );
}

/*  Create the HS Slider Settings page. */
function hsslider_settings_page() { ?>
    <div class="wrap">
        <?php screen_icon( 'plugins' ); ?>
        <h2><?php _e( 'HS Slider Settings', 'hsslider' ); ?></h2>
        <form method="post" action="options.php">
            <?php settings_fields( 'hsslider_options' ); ?>
            <?php do_settings_sections( 'hsslider-settings' ); ?>
            <br /><p><input type="submit" name="Submit" value="<?php _e( 'Update HS Slider Settings', 'hsslider' ); ?>" class="button-primary" /></p>
        </form>
    </div>
<?php }

function hsslider_default_settings() {
    /* Retrieve exisitng options, if any. */
    $ex_options = get_option( 'hsslider_options' );

    /* Check if options are set. Add default values if not. */ 
    if ( !is_array( $ex_options ) || $ex_options['hsslider_duration'] == '' ) {
        $default_options =  array(	
            'hsslider_width'     => '600',
            'hsslider_height'    => '400',
            'hsslider_effect'    => 'verticle',
            'hsslider_direction' => 'next',
            'hsslider_duration'  => '5000',
            'hsslider_start'     => 0		
        );	

        /* Set the default options. */
        update_option( 'hsslider_options', $default_options );
    }	
}

function hsslider_settings_init() {
	
    /* Register the HS Slider settings. */
    register_setting( 'hsslider_options', 'hsslider_options', 'hsslider_validate_options' );
    
    /* Add settings section. */
    add_settings_section( 'hsslider_options_main', __( ' ', 'hsslider' ), 'hsslider_section_text', 'hsslider-settings' );
    
    /* Add settings fields. */
    add_settings_field( 'hsslider_width', __( 'Width:', 'hsslider' ), 'hsslider_width', 'hsslider-settings', 'hsslider_options_main' );
    add_settings_field( 'hsslider_height', __( 'Height:', 'hsslider' ), 'hsslider_height', 'hsslider-settings', 'hsslider_options_main' );
    add_settings_field( 'hsslider_effect', __( 'Slide Effect:', 'hsslider' ), 'hsslider_effect', 'hsslider-settings', 'hsslider_options_main' );
    add_settings_field( 'hsslider_direction', __( 'Slide Auto Direction:', 'hsslider' ), 'hsslider_direction', 'hsslider-settings', 'hsslider_options_main' );
    add_settings_field( 'hsslider_duration', __( 'Animation Delay:', 'hsslider' ), 'hsslider_duration', 'hsslider-settings', 'hsslider_options_main' );
    add_settings_field( 'hsslider_start', __( 'Start Automatically:', 'hsslider' ), 'hsslider_start', 'hsslider-settings', 'hsslider_options_main' );		
  		
}

function hsslider_section_text() {
    echo '<p class="description">' . __( 'Note : Setting\'s Width and Height would be the maximum size of the HS Slider container.', 'hsslider' ) . '</p>';
    echo '<p class="hs-slider-hint" style="border: 0px solid;line-height: 24px;padding: 20px;width: 56%;">' . __( 'To use HS Slider in your post or page : <br> Type <code> [hsslider catid=yourcategoryid] </code> where yourcategoryid is ID OF CATEGORY. <BR> To use HS Slider in Template : <br> Type <code> < ?php do_shortcode( [hsslider catid=yourcategoryid] ) ?> </code> where yourcategoryid is ID OF CATEGORY. ', 'hsslider' ) . '</p>';
}

function hsslider_validate_options( $input ) {
    $options = get_option( 'hsslider_options' );

    $options['hsslider_width'] = wp_filter_nohtml_kses( intval( $input['hsslider_width'] ) );
    $options['hsslider_height'] = wp_filter_nohtml_kses( intval( $input['hsslider_height'] ) );
    $options['hsslider_effect'] = wp_filter_nohtml_kses( $input['hsslider_effect'] );
    $options['hsslider_direction'] = wp_filter_nohtml_kses( $input['hsslider_direction'] );
    $options['hsslider_duration'] = wp_filter_nohtml_kses( intval( $input['hsslider_duration'] ) );
    $options['hsslider_start'] = isset( $input['hsslider_start'] ) ? 1 : 0;	

    return $options;
}

function hsslider_image_sizes() {
    $options = get_option( 'hsslider_options' );
    add_image_size( 'hsslider-thumbnail', $options['hsslider_width'], $options['hsslider_height'], true );	
}

function hsslider_width() {
    $options = get_option( 'hsslider_options' );
    $hsslider_width = $options['hsslider_width'];
?>
    <input type="text" id="hsslider_width" title="slider width" name="hsslider_options[hsslider_width]" value="<?php echo $hsslider_width; ?>" /> <span class="description"><?php _e( 'px', 'hsslider' ); ?></span>
    
    
<?php }

function hsslider_height() {
    $options = get_option( 'hsslider_options' );
    $hsslider_height = $options['hsslider_height'];
?>
    <input type="text" id="hsslider_height" title="slider height" name="hsslider_options[hsslider_height]" value="<?php echo $hsslider_height; ?>" /> <span class="description"><?php _e( 'px', 'hsslider' ); ?></span>
<?php }

function hsslider_effect() {
    $options = get_option( 'hsslider_options' );
    $hsslider_effect = $options['hsslider_effect'];

    echo "<select id='hsslider_effect' name='hsslider_options[hsslider_effect]'>";
    echo '<option value="verticle" ' . selected( $hsslider_effect, 'verticle', false ) . ' >' . __( 'vertical', 'hsslider' ) . '</option>';
    echo '<option value="horizontal" ' . selected( $hsslider_effect, 'horizontal', false ) . ' >' . __( 'horizontal', 'hsslider' ) . '</option>';
    echo '</select>';	
}

function hsslider_direction() {
    $options = get_option( 'hsslider_options' );
    $hsslider_direction = $options['hsslider_direction'];

    echo "<select id='hsslider_direction' name='hsslider_options[hsslider_direction]'>";
    echo '<option value="next" ' . selected( $hsslider_direction, 'next', false ) . ' >' . __( 'next', 'hsslider' ) . '</option>';
    echo '<option value="prev" ' . selected( $hsslider_direction, 'prev', false ) . ' >' . __( 'prev', 'hsslider' ) . '</option>';
    echo '</select>';	
}

function hsslider_duration() {
    $options = get_option( 'hsslider_options' );
    $hsslider_duration = $options['hsslider_duration'];

?>
    <input type="text" id="hsslider_duration" name="hsslider_options[hsslider_duration]" value="<?php echo $hsslider_duration; ?>" /> <span class="description"><?php _e( 'milliseconds', 'hsslider' ); ?></span>
<?php }




function hsslider_start() {
    $options = get_option( 'hsslider_options' );
    $hsslider_start = $options['hsslider_start'];

    echo "<input type='checkbox' id='hsslider_start' name='hsslider_options[hsslider_start]' value='1' " . checked( $hsslider_start, 1, false ) . " />";	
}

function hsslider_attach_scripts() {
    wp_enqueue_script( 'hsslider_zindex-slider', HSSLIDER_URL . 'js/hsflip.js', array( 'jquery' ), 0, true );
    
    $options = get_option( 'hsslider_options' );

    /* variables for Script. */
    wp_localize_script ( 'hsslider_zindex-slider', 'hsslider', array(
        'effect'    => $options['hsslider_effect'],
        'direction'    => $options['hsslider_direction'],
        'duration'  => $options['hsslider_duration'],
        'start'     => $options['hsslider_start']		
    ) );
}

function hsslider_attach_stylesheets() {
    wp_enqueue_style( 'main', HSSLIDER_URL . 'css/hsflip.css', false, 0, 'all' );
}

function hsslider_register_shortcode() {
    add_shortcode( 'hsslider', 'hsslider_shortcode' );
}
function hsslider_shortcode($attr) {	
    $slider = hsslider($attr);
    return $slider;
}

function hsslider($attr) {
    global $wpdb;
    $idCategory = $attr['catid'];
    $sliderwidth = $attr['width'];
    if ( !$sliderwidth ) $sliderwidth = $options['hsslider_width'];
    $sliderheight = $attr['height'];
    if ( !$sliderheight ) $sliderheight = $options['hsslider_height'];
    $querystr =	"SELECT p.* from $wpdb->posts p, $wpdb->terms t, $wpdb->term_taxonomy tt, $wpdb->term_relationships tr WHERE p.id = tr.object_id AND t.term_id = tt.term_id AND tr.term_taxonomy_id = tt.term_taxonomy_id AND (tt.taxonomy = 'hsslidercategory' AND tt.term_id = t.term_id AND t.term_id = '$idCategory')";
    $pageposts = $wpdb->get_results($querystr);
    $options = get_option( 'hsslider_options' );
    if ($pageposts):
    ob_start();    
?>
    
<div class="gallery" style="width:<?php echo $sliderwidth; ?>px; height:<?php echo $sliderheight; ?>px">
    <div id="pictures" style="height:<?php echo $sliderheight; ?>px;">
     <?php foreach ($pageposts as $post): ?>
        <a href="<?php echo get_post_meta( $post->ID, "_hsslider_link_url", true ); ?>" title="<?php the_title_attribute(); ?>" >
            <?php echo get_the_post_thumbnail($post->ID,'hsslider-thumbnail'); ?>
        </a>
     <?php endforeach; ?>
    </div>
    
    
    <div class="mainlinkClass">
    <div class="prev">
        <a href="javascript:void(0)"><?php echo $options['hsslider_prevtext'];?></a>
    </div>
    <div class="next">
        <a href="javascript:void(0)"><?php echo $options['hsslider_nexttext'];?></a>
    </div>
    </div>
</div><!-- #featured-content -->
<?php 
    $content = ob_get_clean();
    endif;
    wp_reset_query();
    return $content;
}

function hsslider_register_custom_post_type() {
    $labels = array(
        'name'                 => __( 'HS Sliders', 'hsslider' ),
        'singular_name'        => __( 'HS Slider', 'hsslider' ),
        'all_items'            => __( 'All HS Sliders', 'hsslider' ),
        'add_new'              => __( 'Add New HS Slider', 'hsslider' ),
        'add_new_item'         => __( 'Add New HS Slider', 'hsslider' ),
        'edit_item'            => __( 'Edit HS Slider', 'hsslider' ),
        'new_item'             => __( 'New HS Slider', 'hsslider' ),
        'view_item'            => __( 'View HS Slider', 'hsslider' ),
        'search_items'         => __( 'Search HS Sliders', 'hsslider' ),
        'not_found'            => __( 'No HS Slider found', 'hsslider' ),
        'not_found_in_trash'   => __( 'No HS Slider found in Trash', 'hsslider' ), 
        'parent_item_colon'    => ''
    );

    $args = array(
        'labels'               => $labels,
        'public'               => true,
        'publicly_queryable'   => true,
        '_builtin'             => false,
        'show_ui'              => true, 
        'query_var'            => true,
        'rewrite'              => array( "slug" => "hsslider" ),
        'capability_type'      => 'post',
        'hierarchical'         => false,
        'menu_position'        => 20,
        'supports'             => array( 'title','thumbnail', 'page-attributes' ),
        'has_archive'          => true,
        'show_in_nav_menus'    => false
    );

    register_post_type( 'hsslider', $args );
}
 
/* Add custom post type column. */
function hsslider_columns( $columns ) {
    $columns = array(
        'cb'       => '<input type="checkbox" />',
        'image'    => __( 'Image', 'hsslider' ),
        'title'    => __( 'Title', 'hsslider' ),
        'category' => __( 'Category', 'hsslider' ),
        'order'    => __( 'Order', 'hsslider' ),
        'link'     => __( 'Link', 'hsslider' ),
        'date'     => __( 'Date', 'hsslider' )
    );

    return $columns;
}

/* Add custom post type column value for list view. */
function hsslider_add_columns( $column ) {
    global $post;

    $term_list = wp_get_post_terms($post->ID, 'hsslidercategory', array("fields" => "all"));
    $cateName = $term_list[0]->name;

    $postLinkUrl = get_edit_post_link( $post->ID );

    if ( $column == 'image' )		
        echo '<a href="' . $postLinkUrl . '" title="' . $post->post_title . '">' . get_the_post_thumbnail( $post->ID, array( 60, 60 ), array( 'title' => trim( strip_tags(  $post->post_title ) ) ) ) . '</a>';

    if ( $column == 'category' )		
        echo $cateName;

    if ( $column == 'order' )		
        echo '<a href="' . $postLinkUrl . '">' . $post->menu_order . '</a>';

    if ( $column == 'link' )		
        echo '<a href="' . get_post_meta( $post->ID, "_hsslider_link_url", true ) . '" target="_blank" >' . get_post_meta( $post->ID, "_hsslider_link_url", true ) . '</a>';		
}

function hsslider_column_order($wp_query) {
    if( is_admin() ) {
        $post_type = $wp_query->query['post_type'];
        if( $post_type == 'hsslider' ) {
            $wp_query->set( 'orderby', 'menu_order' );
            $wp_query->set( 'order', 'ASC' );
        }
    }	
}

// Add field for Category
function custom_taxonomy_add_new_meta_field() {
?>
    <div class="form-field">
        <input type="hidden" name="term_meta[custom_term_meta]" id="term_meta[custom_term_meta]" value="">
    </div>
<?php
}

// Edit term field.
function custom_taxonomy_edit_meta_field($term) {
    $t_id = $term->term_id;
    $term_meta = get_option( "taxonomy_$t_id" ); ?>
    <tr class="form-field">
    <th scope="row" valign="top">
        <td>
            <input type="hidden" name="term_meta[custom_term_meta]" id="term_meta[custom_term_meta]" value="<?php echo esc_attr( $term_meta['custom_term_meta'] ) ? esc_attr( $term_meta['custom_term_meta'] ) : ''; ?>">
        </td>
    </tr>
<?php
}

// Save extra taxonomy fields callback function.
function save_taxonomy_custom_meta( $term_id ) {
    if ( isset( $_POST['term_meta'] ) ) {
        $t_id = $term_id;
        $term_meta = get_option( "taxonomy_$t_id" );
        $cat_keys = array_keys( $_POST['term_meta'] );
        foreach ( $cat_keys as $key ) {
            if ( isset ( $_POST['term_meta'][$key] ) ) {
                //$term_meta[$key] = $_POST['term_meta'][$key];
                $term_meta[$key] = '[hsslider catid='.$term_id.']';
            }
        }
        
        update_option( "taxonomy_$t_id", $term_meta );
    }
}  
?>