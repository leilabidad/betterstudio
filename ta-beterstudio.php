<?php
/**
 * @package leila_bidad_ta
 * @version 1.0
 */
/*
Plugin Name: پلاگین تمرین استودیوبهتر
Description: این پلاگین صرفا برای تست دوم لیلا بیداد در استودیو بهتر می باشد.
Author: لیلا بیداد
Version: 1.0
Author URI: http://leilabidad.com
*/

function tablink_setup() {
	$labels = array(
		'name' => __( 'لینک ها', 'tablink_leilabidad_plugin' ),
		'singular_name' => __( 'لینک ها', 'tablink_leilabidad_plugin' ),
		'add_new_item' => __( 'افزودن لینک جدید', 'tablink_leilabidad_plugin' ),
		'edit_item' => __( 'ویرایش لینک', 'tablink_leilabidad_plugin' ),
		'new_item' => __( 'لینک جدید', 'tablink_leilabidad_plugin' ),
		'not_found' => __( 'لینکی یافت نشد', 'tablink_leilabidad_plugin' ),
		'all_items' => __( 'همه لینک ها', 'tablink_leilabidad_plugin' )
	);
	$args = array(
		'labels' => $labels,
		'public' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'has_archive' => true,
		'map_meta_cap' => true,
		'menu_icon' => 'dashicons-admin-links',		
		'supports' => array( 'title', 'editor', 'thumbnail', 'author' ),
		'taxonomies' => array( 'tablink-categury' )
	);
	register_post_type( 'tablink', $args );
}
add_action( 'init', 'tablink_setup' );

/**  Register taxonomies   */
function tablink_register_taxonomies(){

	$labels = array(
		'name' => __( 'لینک ریدایرکت', 'tablink_leilabidad_plugin' ),
		'label' => __( 'لینک ریدایرکت', 'tablink_leilabidad_plugin' ),
		'add_new_item' => __( 'افزودن لینک جدید', 'tablink_leilabidad_plugin' ),
	);

	$args = array(
		'labels' => $labels,
		'label' => __( 'لینک', 'tablink_leilabidad_plugin' ),
		'show_ui' => true,
		'show_admin_column' => true
	);
	register_taxonomy( 'tablink-categury', array( 'tablink' ), $args );
}
add_action( 'init', 'tablink_register_taxonomies' );

/**
 * Add meta box
 
 */


function tablink_save_meta_box_data( $post_id ){
	// verify meta box nonce
	if ( !isset( $_POST['tablink_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['tablink_meta_box_nonce'], basename( __FILE__ ) ) ){
		return;
	}

	// return if autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
		return;
	}

  // Check the user's permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ){
		return;
	}

	// store custom fields values
}
add_action( 'save_post_tablink', 'tablink_save_meta_box_data' );

/////////////////////////ساخت شورت کد
function getPostViewsTA($postID){
    $count_key = 'post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count==''){
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
        return "بازدید نشده";
    }
    return $count.' بازدید';
}
function setPostViewsTA($postID) {
    $count_key = 'post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count==''){
        $count = 0;
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
    }else{
        $count++;
        update_post_meta($postID, $count_key, $count);
    }
}

// Remove issues with prefetching adding extra views
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0); 



	function wpex_paginationTA() {
		
    $the_query = new WP_Query( array('posts_per_page'=>10,
                                 'post_type'=>'tablink',
                                 'paged' => get_query_var('paged') ? get_query_var('paged') : 1) 
                            ); 
                            ?>
<?php while ($the_query -> have_posts()) : $the_query -> the_post();  ?>
<div class="col-xs-12 file">
<a href="<?php the_permalink(); ?>" class="file-title" target="_blank">
<i class="fa fa-angle-right" aria-hidden="true"></i> <?php echo get_the_title(); ?>
</a>
<div class="file-description"><?php echo getPostViewsTA(get_the_ID()); ?></div>
</div>
<?php
endwhile;

$big = 999999999; // need an unlikely integer
 echo paginate_links( array(
    'base' => str_replace( $big, '%#%', get_pagenum_link( $big ) ),
    'format' => '?paged=%#%',
    'current' => max( 1, get_query_var('paged') ),
    'total' => $the_query->max_num_pages
) );

wp_reset_postdata();

	}
	


 function show_taxonomies($atts) {

wpex_paginationTA();



 }
add_shortcode('show-taxonomies', 'show_taxonomies');

// Add to a column in WP-Admin
add_filter('manage_posts_columns', 'posts_column_viewsTA');
add_action('manage_posts_custom_column', 'posts_custom_column_viewsTA',5,2);
function posts_column_viewsTA($defaults){
    $defaults['post_views'] = __('Views');
    return $defaults;
}
function posts_custom_column_viewsTA($column_name, $id){
    if($column_name === 'post_views'){
        echo getPostViewsTA(get_the_ID());
    }
}

//////////BIDAD REDIRECT FOR BETTER STUDIO/////////////////////
function tab_redirect() {
		
	if($_GET['tablink']!='')
    {
		global $post;
		$term_list = get_the_terms($post->ID, 'tablink-categury');
		foreach($term_list as $term_single) {
			 $url = $term_single->description;
		}
		wp_redirect(  $url  );
		setPostViewsTA($post->ID);
		die;
    }
}
add_action( 'template_redirect', 'tab_redirect' );

//////////END OF BIDAD REDIRECT FOR BETTER STUDIO/////////////////////



