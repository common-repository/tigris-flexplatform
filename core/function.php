<?php
/**
 * @package WordPress
 * @subpackage Tigris Flexplatform
 */

// Prevent direct file access
defined( 'ABSPATH' ) or exit;

// Get plugin options
$options = get_option( str_replace( '-', '_', SF_TFP_NAME ) );

/**
 * [sf_tfp_init_translation			CORE: Activate translation hook]
 * @return [hook]						[No return]
 */
function sf_tfp_init_translation() {
	load_plugin_textdomain( SF_TFP_NAME , '', SF_TFP_NAME . '/languages' );
}
add_action( 'plugins_loaded', 'sf_tfp_init_translation' );

/**
 * [sf_tfp_admin_css_js				BACK-OFFICE: Connect CSS styles and Java Script to Back-Office ]
 * @return [hook]                  		[No return]
 */
function sf_tfp_admin_css_js() {
	// CSS
	wp_register_style( 'sf_tfp_stylesheet_back', SF_TFP_ASSETS_URL . 'css/sf-tfp-back-style.css' );
	wp_enqueue_style( 'sf_tfp_stylesheet_back' );

	// JS
	wp_register_script( 'sf_tfp_script_back', SF_TFP_ASSETS_URL . 'js/sf-tfp-back-script.js', array( 'jquery' ) );
	wp_enqueue_script( 'sf_tfp_script_back' );
}
add_action( 'admin_enqueue_scripts', 'sf_tfp_admin_css_js' );

/**
 * [sf_tfp_front_css_js				FRONT-OFFICE: Connect Java Script to Front-Office ]
 * @return [hook]                  		[No return]
 */
function sf_tfp_front_css_js() {
	// CSS
	wp_register_style( 'sf_tfp_stylesheet_back', SF_TFP_ASSETS_URL . 'css/sf-tfp-style.css' );
	wp_enqueue_style( 'sf_tfp_stylesheet_back' );

	// JS
	wp_register_script( 'sf_script', SF_TFP_ASSETS_URL . 'js/sf-tfp-script.js', array( 'jquery' ) );
	wp_localize_script( 'sf_script', 'ajaxurl', admin_url('admin-ajax.php'));
	wp_enqueue_script( 'sf_script' );
}
add_action( 'wp_enqueue_scripts', 'sf_tfp_front_css_js' );

// OAuth
if (class_exists('TigrisFPSaleforceOAuth')) {
	global $sf_tfp_core_already_exists;
	$sf_tfp_core_already_exists = true;
}
else {
	require_once( plugin_dir_path( __FILE__ ) . 'class/TigrisFPSaleforceOAuth.php' );
}

$tigrisFPSaleforceOAuth = new TigrisFPSaleforceOAuth();

add_action( 'template_redirect','sf_tfp_redirect_to_listener');

function sf_tfp_redirect_to_listener() {
	global $options;

	$uri = parse_url($_SERVER['REQUEST_URI']);
	if ( strpos($uri['path'], 'acs-tigris-for-salesforce/listener') === false &&
		 strpos($uri['path'], 'tigris-flexplatform/listener') === false ) {
		return;
	} else {
		$runSaveListener = false;
		if (function_exists('sf_tfp_save_listener')) {
			$runSaveListener = true;
		} else {
			require_once( plugin_dir_path( __FILE__ ) . 'listener.php' );
			$runSaveListener = true;
		}
		if ($runSaveListener)
			sf_tfp_save_listener($options);
		die();
	}

}

/** Vacancy type: BEGIN */

/**
 * [sf_tfp_register_type_vacancy		CORE: Create Vacancy post type and taxonomy]
 * @return [hook]                  		[No return]
 */
function sf_tfp_register_type_vacancy() {
	global $options;

	if (empty($options['vacatures_link']))
		$vacatures_link = 'vacatures';
	else
		$vacatures_link = $options['vacatures_link'];

	if ( isset( $options['category_off'] ) && $options['category_off'] ) {
		// Category dissabled
		$slug = $vacatures_link;
	} else {
		// Vacancy category: tigrisvacancies
		$labels = array(
				'name'              => __( 'Vacancy Categories', SF_TFP_NAME ),
				'singular_name'     => __( 'Vacancies Categories', SF_TFP_NAME ),
				'search_items'      => __( 'Search Categories', SF_TFP_NAME ),
				'all_items'         => __( 'All Vacancies Categories', SF_TFP_NAME ),
				'parent_item'       => __( 'Parent Vacancies Categories', SF_TFP_NAME ),
				'parent_item_colon' => __( 'Parent Vacancies Categories:', SF_TFP_NAME ),
				'edit_item'         => __( 'Edit Category', SF_TFP_NAME ),
				'update_item'       => __( 'Update Category', SF_TFP_NAME ),
				'add_new_item'      => __( 'Add New Category', SF_TFP_NAME ),
				'new_item_name'     => __( 'New Categories', SF_TFP_NAME ),
				'menu_name'         => __( 'Vacancies Categories', SF_TFP_NAME ),
				'popular_items'		=> __( 'Popular Vacancies', SF_TFP_NAME ),
				'view_item'         => __( 'View Vacancy', SF_TFP_NAME ),
				'view_items'		=> __( 'View Vacancy category', SF_TFP_NAME )
			);
		$args	= array(
				'label'                 => __( 'Vacancies', SF_TFP_NAME ),
				'labels'                => $labels,
				'description'           => __( 'Category for Tigris Vacancy', SF_TFP_NAME ),
				'public'                => true,
				'show_in_nav_menus'     => false,
				'show_ui'               => true,
				'show_tagcloud'         => false,
				'hierarchical'          => true,
				'rewrite'               => array(
						'slug'				=> 'vacatures',
						'hierarchical'		=> false,
						'with_front'		=> false,
						'feed'				=> false
					),
				'show_admin_column'     => true
			);

		register_taxonomy( 'tigrisvacancies', array( 'vacatures' ), $args );

		$slug = $vacatures_link . '/%tigrisvacancies%';
	}

	// Post type: vacatures
	$labels = array(
			'name'           	=> __( 'Vacancies', SF_TFP_NAME ),
			'singular_name'  	=> __( 'Vacancy', SF_TFP_NAME ),
			'menu_name'      	=> __( 'Tigris Vacancies', SF_TFP_NAME ),
			'all_items'      	=> __( 'All Vacancies', SF_TFP_NAME ),
			'add_new'        	=> _x( 'Add New', SF_TFP_NAME ),
			'add_new_item'   	=> __( 'Add New Vacancy', SF_TFP_NAME ),
			'edit'           	=> __( 'Edit', SF_TFP_NAME ),
			'edit_item'      	=> __( 'Edit Vacancy', SF_TFP_NAME ),
			'new_item'       	=> __( 'New Vacancy', SF_TFP_NAME ),
			'view_item'       	=> __( 'View Vacancy', SF_TFP_NAME ),
			'search_items'      => __( 'Search Vacancy', SF_TFP_NAME ),
			'not_found'			=> __( 'Not found Vacancies', SF_TFP_NAME ),
			'not_found_in_trash'=> __( 'Not found Vacancies in trash', SF_TFP_NAME ),
			'parent_item_colon' => __( 'Parent Vacancy', SF_TFP_NAME ),
			'parent_item_colon' => __( 'Parent Vacancy', SF_TFP_NAME )
		);
	$args	= array(
			'label'               => __( 'Vacancy', SF_TFP_NAME ),
			'labels'              => $labels,
			'description'         => '',
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_rest'        => false,
			'rest_base'           => '',
			'show_in_menu'        => true,
			'exclude_from_search' => false,
			'menu_position'		  => 4,
			'menu_icon'           => 'dashicons-megaphone',
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'rewrite'             => array(
					'slug'				=> $slug,
					'with_front'		=> false,
					'pages'				=> false,
					'feeds'				=> false,
					'feed'				=> false
				),
			'has_archive'         => 'vacatures',
			'query_var'           => true,
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author' ),
			'taxonomies'          => array( 'tigrisvacancies' ),
		);

	register_post_type( 'vacatures', $args );

	// Vacancy Tags: tigristag
	$labels = array(
	        'name'                       => _x( 'Tags', 'taxonomy general name', 'textdomain' ),
	        'singular_name'              => _x( 'Tag', 'taxonomy singular name', 'textdomain' ),
	        'search_items'               => __( 'Search Tag', 'textdomain' ),
	        'popular_items'              => __( 'Popular Tags', 'textdomain' ),
	        'all_items'                  => __( 'All Tags', 'textdomain' ),
	        'parent_item'                => null,
	        'parent_item_colon'          => null,
	        'edit_item'                  => __( 'Edit Tag', 'textdomain' ),
	        'update_item'                => __( 'Update Tag', 'textdomain' ),
	        'add_new_item'               => __( 'Add New Tag', 'textdomain' ),
	        'new_item_name'              => __( 'New Tag Name', 'textdomain' ),
	        'separate_items_with_commas' => __( 'Separate tags with commas', 'textdomain' ),
	        'add_or_remove_items'        => __( 'Add or remove tags', 'textdomain' ),
	        'choose_from_most_used'      => __( 'Choose from the most used tags', 'textdomain' ),
	        'not_found'                  => __( 'No tags found.', 'textdomain' ),
	        'menu_name'                  => __( 'Tags', 'textdomain' )
	    );
	$args	= array(
	        'hierarchical'          => false,
	        'labels'                => $labels,
	        'show_ui'               => true,
	        'show_admin_column'     => true,
	        'update_count_callback' => '_update_post_term_count',
	        'query_var'             => true,
	        'rewrite'               => array( 'slug' => 'tag' )
	    );

	register_taxonomy( 'tigristag', 'vacatures', $args );
}
add_action( 'init', 'sf_tfp_register_type_vacancy' );

/**
 * [sf_tfp_permalink_for_vacancy		CORE: Adding taxonomy to the CNC]
 * @param  [string] $permalink			[Current URL]
 * @param  [Object] $post     			[WpQuery]
 * @return [hook]                  		[No return]
 */
function sf_tfp_permalink_for_vacancy( $permalink, $post ) {
	if( strpos( $permalink, '%tigrisvacancies%' ) === false ) {
		return $permalink;
	}

	$terms = get_the_terms( $post, 'tigrisvacancies' );

	if ( ! is_wp_error( $terms ) && ! empty( $terms ) && is_object( $terms[0] ) ) {
		$term_slug = array_pop( $terms )->slug;
	} else {
		global $options;
		if ( isset( $options['def_category'] ) && $options['def_category'] ) {
			$term_slug = $options['def_category'];
		} else {
			$term_slug = 'no-vacancies';
		}
	}

	return str_replace( '%tigrisvacancies%', $term_slug, $permalink );
}
add_filter( 'post_type_link', 'sf_tfp_permalink_for_vacancy', 1, 2 );

/**
 * [sf_tfp_add_fields_for_vacancy		CORE: Adding services fields to Vacancy]
 * @return [hook]                  		[No return]
 */
function sf_tfp_add_fields_for_vacancy() {
	add_meta_box( 'vacancy_fields', __( 'Vacancy description', SF_TFP_NAME ), 'sf_tfp_add_fields_box_for_vacancy', 'vacatures', 'normal', 'high'  );
}
add_action( 'add_meta_boxes', 'sf_tfp_add_fields_for_vacancy', 1 );

/**
 * [sf_tfp_add_fields_box_for_vacancy	BACK-OFFICE: Display fields box for vacancies]
 * @return [string]						[HTML code for services fields to Vacancy]
 */
function sf_tfp_add_fields_box_for_vacancy() {
	global $post;

	$fields = array(
		array(
			'name'	=> 'tigris__plaats__c',
			'label'	=> __( 'Location', SF_TFP_NAME )
		),
		array(
			'name'	=> 'tigris__opleidingsniveau__c',
			'label'	=> __( 'Education level', SF_TFP_NAME )
		),
		array(
			'name'	=> 'tigris__uren_per_week__c',
			'label'	=> __( 'Work week', SF_TFP_NAME )
		),
		array(
			'name'	=> 'tigris__salaris_van__c',
			'label'	=> __( 'Salary level (min)', SF_TFP_NAME )
		),
		array(
			'name'	=> 'tigris__salaris_tot__c',
			'label'	=> __( 'Salary level (max)', SF_TFP_NAME )
		),
		array(
			'name'	=> 'tigris__functiegroep__c',
			'label'	=> __( 'Sector of economy', SF_TFP_NAME )
		),
		array(
			'name'	=> 'tigris__soort_dienstverband__c',
			'label'	=> __( 'Type of employment', SF_TFP_NAME )
		),
		array(
			'name'	=> 'tigris__branche__c',
			'label'	=> __( 'Branch', SF_TFP_NAME )
		),
		array(
			'type'	=> 'checkbox',
			'name'	=> 'tigris__interne_vacature__c',
			'label'	=> __( 'Interior', SF_TFP_NAME ),
			'check' => ( ( $check = get_post_meta( $post->ID, 'tigris__interne_vacature__c', 1 ) ) && ! empty( $check ) ) ? 'checked ' : ''
		),
	);

	foreach ( $fields as $key => $value ) {
		if ( ! isset( $value['type'] ) ) { ?>

			<p>
				<label
					style="display:inline-block;min-width:150px;">
					<?php echo $value['label']; ?>:
				</label>
				<input
					type="text"
					name="vacancy[<?php echo $value['name']; ?>]"
					value="<?php echo get_post_meta( $post->ID, $value['name'], 1 ); ?>"
					style="width:50%" />
			</p>

		<?php }
		if ( isset( $value['type'] ) && $value['type'] == 'checkbox' ) { ?>

			<p>
				<label
					style="display:inline-block;min-width:150px;">
					<?php echo $value['label']; ?>:
				</label>
				<input
					type="<?php echo $value['type']; ?>"
					name="vacancy[<?php echo $value['name']; ?>]"
					value="true" <?php echo $value['check']; ?>"/>

			</p>

		<?php }
	} ?>

	<input type="hidden" name="vacancy_fields_nonce" value="<?php echo wp_create_nonce( __FILE__ ); ?>" />

	<?php
}

/**
 * [sf_tfp_fields_updte_vacancy		CORE: Update data, while saving the post]
 * @param  [integer] $post_id			[ID current post]
 * @return [hook]                  		[No return]
 */
function sf_tfp_fields_updte_vacancy( $post_id ) {

	// Check the availability of data...
	if ( ! isset( $_POST['vacancy_fields_nonce'] ) || ! wp_verify_nonce( $_POST['vacancy_fields_nonce'], __FILE__ ) ) {
		return false;
	}

	// Check autosave of data...
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE  ) {
		return false;
	}

	// Verify user rights...
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return false;
	}

	// Check that the field is set.
	if( ! isset( $_POST['vacancy'] ) ) {
		return false;
	}

	// Get update URL
	$old_url = get_metadata( 'post', $post_id, 'tigris__vacature_url__c', true );
	$new_url = get_permalink( $post_id );

	// Correct
	if ( $old_url != $new_url ) {
		if (!class_exists('Plugin_Tigris_FP_Rest'))
			require_once( plugin_dir_path( __FILE__ ) . 'class/class-plugin-tigris-fp-rest.php' );

		$fieldsToUpdate = [
			'Tigris__Vacature_url__c' => $new_url
		];

		$sf_table = 'Tigris__Vacancy__c';

		$sf_id = get_metadata( 'post', $post_id, 'id', true );

		$rest = new Plugin_Tigris_FP_Rest();
		$r = $rest->update( $fieldsToUpdate, $sf_id, $sf_table );
	}

	// OK!
	// Clear input value
	$_POST['vacancy'] = array_map( 'trim', $_POST['vacancy'] );
	foreach( $_POST['vacancy'] as $key => $value ) {
		$key = sanitize_key( $key );
		if( empty( $value ) ) {
			delete_post_meta( $post_id, $key );
			continue;
		}

		update_post_meta( $post_id, $key, sanitize_text_field($value) );
	}

	return $post_id;
}
add_action( 'save_post', 'sf_tfp_fields_updte_vacancy', 0 );

/** Vacancy type:  END */
/***********************/
/** Branch type: BEGIN */

/**
 * [sf_tfp_register_type_branch 		BACK-OFFICE: Add custom Branch post type]
 * @return [hook]                   	[No return]
 */
function sf_tfp_register_type_branch() {
	// Post type: branch
	$labels = array(
			'name'           	=> __( 'Branches', SF_TFP_NAME ),
			'singular_name'  	=> __( 'Branch', SF_TFP_NAME ),
			'menu_name'      	=> __( 'Tigris Branches', SF_TFP_NAME ),
			'all_items'      	=> __( 'All Branches', SF_TFP_NAME ),
			'add_new'        	=> _x( 'Add New', SF_TFP_NAME ),
			'add_new_item'   	=> __( 'Add New Branch', SF_TFP_NAME ),
			'edit'           	=> __( 'Edit', SF_TFP_NAME ),
			'edit_item'      	=> __( 'Edit Branch', SF_TFP_NAME ),
			'new_item'       	=> __( 'New Branch', SF_TFP_NAME ),
			'view_item'       	=> __( 'View Branch', SF_TFP_NAME ),
			'search_items'      => __( 'Search Branch', SF_TFP_NAME ),
			'not_found'			=> __( 'Not found Branches', SF_TFP_NAME ),
			'not_found_in_trash'=> __( 'Not found Branches in trash', SF_TFP_NAME ),
			'parent_item_colon' => __( 'Parent Branch', SF_TFP_NAME ),
			'parent_item_colon' => __( 'Parent Branch', SF_TFP_NAME )
		);
	$args	= array(
			'label'               => __( 'Branch', SF_TFP_NAME ),
			'labels'              => $labels,
			'description'         => '',
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_rest'        => false,
			'rest_base'           => '',
			'show_in_menu'        => true,
			'exclude_from_search' => false,
			'menu_position'		  => 4,
			'menu_icon'           => 'dashicons-location-alt',
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'rewrite'             => array(
					'slug'				=> 'branch',
					'with_front'		=> false,
					'pages'				=> false,
					'feeds'				=> false,
					'feed'				=> false
				),
			'query_var'           => true,
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author' )
		);

	register_post_type( 'branch', $args );
}
if ( ! isset( $options['branch_off'] ) ) {
	add_action( 'init', 'sf_tfp_register_type_branch' );
}

/**
 * [sf_tfp_add_fields_for_branch		CORE: Adding services fields to branches]
 * @return [hook]                  		[No return]
 */
function sf_tfp_add_fields_for_branch() {
	add_meta_box( 'branch_fields', __( 'Branch options', SF_TFP_NAME ), 'sf_tfp_add_fields_box_for_branch', 'branch', 'normal', 'high'  );
}
if ( ! isset( $options['branch_off'] ) ) {
	add_action( 'add_meta_boxes', 'sf_tfp_add_fields_for_branch', 2 );
}

/**
 * [sf_tfp_add_fields_box_for_branch	BACK-OFFICE: Display fields box for Branch]
 * @return [string]						[HTML code for services fields to branches]
 */
function sf_tfp_add_fields_box_for_branch() {
	global $post;
	$options = get_option( str_replace( '-', '_', SF_TFP_NAME ) );

	// Get all Tigris author
	$tigris_author = array();
	$args = array(
		'role__in'     => array( 'tigris_author' ),
		'fields'       => array( 'ID', 'display_name' )
	);
	$users = get_users( $args );
	foreach( $users as $user ){
		$tigris_author[$user->ID] = str_replace( '_', ' ', $user->display_name );
	}

	$fields = array(
		array(
			'type'  	=> 'select',
			'name'		=> 'tigris__test_a',
			'label'		=> __( 'Location', SF_TFP_NAME )
		),
		array(
			'name'		=> 'tigris__test_c',
			'label'		=> __( 'Street & house', SF_TFP_NAME )
		),
		array(
			'name'		=> 'tigris__test_c2',
			'label'		=> __( 'ZIP & ​place', SF_TFP_NAME )
		),
		array(
			'name'		=> 'tigris__test_d',
			'label'		=> __( 'E-mail', SF_TFP_NAME )
		),
		array(
			'name'		=> 'tigris__test_e',
			'label'		=> __( 'Phone', SF_TFP_NAME )
		),
		/*
		// Example:
		array(
			'type'  	=> 'select',
			'multiple'	=> TRUE,
			'name'		=> 'tigris__test_f',
			'label'		=> __( 'Employees', SF_TFP_NAME ) . '<br><small>(' . __( 'multiselect', SF_TFP_NAME ) . ')</small>',
			'option'	=> $tigris_author
		),
		array(
			'type'	=> 'checkbox',
			'name'	=> 'tigris__test_b',
			'label'	=> __( 'Interior', SF_TFP_NAME ),
			'check' => ( ( $check = get_post_meta( $post->ID, 'tigris__test_b', 1 ) ) && ! empty( $check ) ) ? 'checked ' : ''
		),
		*/
	);

	if ( ! isset( $options['roles_off'] ) ) {
		$employees = array(
				'type'  	=> 'select',
				'multiple'	=> TRUE,
				'name'		=> 'tigris__test_f',
				'label'		=> __( 'Employees', SF_TFP_NAME ) . '<br><small>(' . __( 'multiselect', SF_TFP_NAME ) . ')</small>',
				'option'	=> $tigris_author
			);
		array_push( $fields, $employees );
	}

	foreach ( $fields as $key => $value ) {
		if ( ! isset( $value['type'] ) ) { ?>

			<p>
				<label
					style="display:inline-block;min-width:150px;">
					<?php echo $value['label']; ?>:
				</label>
				<input
					type="text"
					name="branch[<?php echo $value['name']; ?>]"
					value="<?php echo get_post_meta( $post->ID, $value['name'], 1 ); ?>"
					style="width:50%" />
			</p>

		<?php }
		if ( isset( $value['type'] ) && $value['type'] == 'checkbox' ) { ?>

			<p>
				<label
					style="display:inline-block;min-width:150px;">
					<?php echo $value['label']; ?>:
				</label>
				<input
					type="<?php echo $value['type']; ?>"
					name="branch[<?php echo $value['name']; ?>]"
					value="true" <?php echo $value['check']; ?>"/>
			</p>

		<?php } elseif ( isset( $value['type'] ) && $value['type'] == 'select' ) { ?>

			<p>
				<label
					style="display:inline-block;min-width:150px;">
					<?php echo $value['label']; ?>:
				</label>
				<select
					style="width:50%"
					<?php echo ( isset( $value['multiple'] ) && $value['multiple'] ) ? ' multiple ' : '' ?>
					name="branch[<?php echo $value['name']; ?>]<?php echo ( isset( $value['multiple'] ) && $value['multiple'] ) ? '[]' : '' ?>">
					<option><?php _e( 'Choose...', SF_TFP_NAME ); ?></option>

					<?php
					if ( ! isset( $value['option'] ) || empty( $value['option'] ) ) {
						echo sf_tfp_generate_locations_option( get_post_meta( $post->ID, $value['name'], 1 ) );
					} else {

						$selected = get_post_meta( $post->ID, $value['name'], 1 );
						$selected = unserialize( $selected );

						foreach ( $value['option'] as $option_key => $option_value ) {
							echo '<option value="' . $option_key . '"' . ( in_array( $option_key, $selected ) ? ' selected' : '' ) . '>' . $option_value . '</option>';
						}
					}
					?>

				</select>

			</p>

		<?php }
	} ?>

	<input type="hidden" name="branch_fields_nonce" value="<?php echo wp_create_nonce( __FILE__ ); ?>" />

	<?php
}

/**
 * [sf_tfp_fields_updte_branch			CORE: Update data, while saving the post]
 * @param  [integer] $post_id			[ID current post]
 * @return [hook]                  		[No return]
 */
function sf_tfp_fields_updte_branch( $post_id ) {

	// Check the availability of data...
	if ( ! isset( $_POST['branch_fields_nonce'] ) || ! wp_verify_nonce( $_POST['branch_fields_nonce'], __FILE__ ) ) {
		return false;
	}

	// Check autosave of data...
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE  ) {
		return false;
	}

	// Verify user rights...
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return false;
	}

	// Check that the field is set.
	if( ! isset( $_POST['branch'] ) ) {
		return false;
	}

	// OK!
	// Clear input value
	foreach( $_POST['branch'] as $key => $value ) {
		$key = sanitize_key( $key );
		if( empty( $value ) ) {
			delete_post_meta( $post_id, $key );
			continue;
		}

		if ( is_array( $value ) ) {
			$value = serialize( $value );
		}

		update_post_meta( $post_id, $key, sanitize_text_field( $value ) );
	}

	return $post_id;
}
if ( ! isset( $options['branch_off'] ) ) {
	add_action( 'save_post', 'sf_tfp_fields_updte_branch', 1 );
}

/** Branch type: END */

/**
 * [sf_tfp_change_single_template		FRONT-OFFICE: Connect Plug-In page template]
 * @param  [string] $template			[Patch to WP-select template]
 * @return [string]          			[Patch to template]
 */
function sf_tfp_change_single_template( $template ) {

	$options = get_option( str_replace( '-', '_', SF_TFP_NAME ) );
	if (empty($options['vacatures_link']))
		$vacatures_link = 'vacatures';
	else
		$vacatures_link = $options['vacatures_link'];
	
	// Get url
	$page_url = parse_url( home_url( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH );
	$url_array = array_diff( explode( '/', $page_url ), array( '' ) );

	// Check plug-in pages
	if ( in_array( $vacatures_link, $url_array ) ) {

		// Enable category
		if( taxonomy_exists( 'tigrisvacancies' ) ) {

			// Get all child category
			$args = array(
				'taxonomy'      => array( 'tigrisvacancies' ),
				'get'           => 'all'
			);
			$terms = get_terms( $args );
			foreach ( $terms as $key => $value) {
				$vacancy_cat[$terms[$key]->term_id] = $terms[$key]->slug;
			}

			if ( end( $url_array ) == $vacatures_link || ( isset( $vacancy_cat ) && in_array( end( $url_array ), $vacancy_cat ) ) ) {
				$category = 1;
			} else {
				$category = 0;
			}
		}

		// Check category page...
		if ( ( isset( $category ) && $category ) || ! is_single() ) {

			// Check template to theme
			if ( $new_template = locate_template( array( 'tigris/category-tigrisvacancies.php' ) ) ) {
				$template = $new_template;
			} else {
				$template = SF_TFP_PLUGIN_DIR . '/templates/category-tigrisvacancies.php';
			}

		// ... or single page
		} else {

			// Check template to theme
			if ( $new_template = locate_template( array( 'tigris/single-tigrisvacancy.php' ) ) ) {
				$template = $new_template;
			} else {
				$template = SF_TFP_PLUGIN_DIR . '/templates/single-tigrisvacancy.php';
			}
		}
	}

	if ( in_array( 'branch', $url_array ) ) {

		// Check template to theme
		if ( $new_template = locate_template( array( 'tigris/single-tigrisbranch.php' ) ) ) {
			$template = $new_template;
		} else {
			$template = SF_TFP_PLUGIN_DIR . '/templates/single-tigrisbranch.php';
		}
	}
	return $template;
}
add_filter( 'template_include', 'sf_tfp_change_single_template', 99 );

/**
 * [sf_tfp_load_vacancy				FRONT-OFFICE: AJAX loading vacations post]
 * @return [hook]                  		[HTML code next vacations posts]
 */
function sf_tfp_load_vacancy() {
	if (!isset($_POST['action']))
		return;

	if (  $_POST['action'] == 'loadvacancies' ) {
		$args = json_decode( stripslashes( $_POST['query'] ), true );
		$args['paged'] = $_POST['page'] + 1;
		$args['post_status'] = 'publish';
		$sf_query = new WP_Query( $args );

		if( $sf_query->have_posts() ) {
			while( $sf_query->have_posts() ) {
				$sf_query->the_post();

				// Check template to theme
				if ( locate_template( array( 'tigris/ajax-tigrisvacancy.php' ) ) ) {
					get_template_part( 'tigris/ajax-tigrisvacancy' );
				} else {
					load_template( SF_TFP_PLUGIN_DIR . '/templates/ajax-tigrisvacancy.php', false );
				}
			}
		}

		wp_reset_postdata();
		die();
	}
}
add_action( 'wp_ajax_loadvacancies', 'sf_tfp_load_vacancy' );
add_action( 'wp_ajax_nopriv_loadvacancies', 'sf_tfp_load_vacancy' );

/**
 * [sf_tfp_add_form_shortcode			FRONT-OFFICE: Connection of feedback form]
 * @param  [array] $atts    			[Shortcode attributes]
 * 										vacancy=0 //Vacancy ID (0-without vacancy)
 */
function sf_tfp_add_form_shortcode( $atts ) {
	global $att;
	$att = $atts;

	// Check template to theme
	if ( locate_template( array( 'tigris/form-tigrisresume.php' ) ) ) {
		get_template_part( 'tigris/form-tigrisresume' );
	} else {
		load_template( SF_TFP_PLUGIN_DIR . '/templates/form-tigrisresume.php', false );
	}
}
add_shortcode( 'tigris_form', 'sf_tfp_add_form_shortcode' );

function sf_tfp_add_single_form_shortcode( $atts ) {
	global $att;
	$att = $atts;

	// Check template to theme
	if ( locate_template( array( 'tigris/form-tigrisvacancy.php' ) ) ) {
		get_template_part( 'tigris/form-tigrisvacancy' );
	} else {
		load_template( SF_TFP_PLUGIN_DIR . '/templates/form-tigrisvacancy.php', false );
	}
}
add_shortcode( 'tigris_single_form', 'sf_tfp_add_single_form_shortcode' );

/**
 * [sf_tfp_load_form					FRONT-OFFICE: AJAX sending a job application]
 * @return [hook]                  		[Result of processing the form]
 */
	function sf_tfp_load_form() {
	$reply = 0;
	if ( isset( $_POST['action'] ) && $_POST['action'] == 'formvacancies' && wp_verify_nonce( $_POST['_wpnonce'] ) ) {
		$allowedFileTypes = array(
			'image/jpeg',
			'image/png',
			'image/gif',
			'image/tiff',
			'image/psd',
			'image/bmp',
			'application/octet-stream',
			'image/jp2',
			'image/iff',
			'image/vnd.wap.wbmp',
			'image/xbm',
			'image/webp',
			'text/plain',
			'application/vnd.oasis.opendocument.text',
			'application/pdf',
			'application/msword',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'application/zip',
			'application/x-rar-compressed',
			'application/x-zip-compressed',
			'multipart/x-zip'
		);

		if (!class_exists('Plugin_Tigris_FP_Rest'))
			require_once( plugin_dir_path( __FILE__ ) . 'class/class-plugin-tigris-fp-rest.php' );

		// Sending to Saleforce

		$options = get_option( str_replace( '-', '_', SF_TFP_NAME ) );

		$locations = '';
		if ( isset( $_POST['locations'] ) && ! empty( $_POST['locations'] ) ) {
			$_POST['locations'] = array_map('sanitize_text_field', $_POST['locations']);
			$locations = implode(', ', $_POST['locations']);
		}
		$locations = substr( $locations, 0, -2 );

		$lastname = ( isset( $_POST['lastname'] ) && ! empty( $_POST['lastname'] ) ) ? sanitize_text_field( $_POST['lastname'] ) : sanitize_text_field(  $_POST['firstname'] );

		$vacancyID = ( isset( $_POST['vacancyID'] ) && $_POST['vacancyID'] != '0' ) ? sanitize_text_field($_POST['vacancyID']) : '';

		$fieldsToInsert = [
			'Tigris__Voornaam_kandidaat__c'   => sanitize_text_field( $_POST['firstname'] ),
			'Tigris__Achternaam_kandidaat__c' => $lastname,
            'Tigris__Email__c' 				  => sanitize_email( $_POST['email'] ),
            'Tigris__Telefoonnummer__c' 	  => sanitize_text_field( $_POST['phone'] ),
            'Tigris__Land__c' 				  => $locations,
            'Tigris__Extern_vacature_ID__c'   => $vacancyID,
            'Tigris__UTM_url__c' 			  => home_url( esc_url_raw( $_POST['_wp_http_referer'] ) ),
            'Tigris__Opmerking__c' 			  => sanitize_text_field( $_POST['your-message'] ),
		];

		$sf_table = 'Tigris__Sollicitatie__c';

		$rest = new Plugin_Tigris_FP_Rest();
		$responce = $rest->insert( $fieldsToInsert, $sf_table );

		$name = $_FILES['your-cv']['name'];
		$size = $_FILES['your-cv']['size'];

		if (in_array($_FILES['your-cv']['type'], $allowedFileTypes)) {
			/*if($size == 0){
				mail($options['email'], 'log-file', print_r($_FILES, 1));
			}*/

			$name_array = explode('.', $name);
			$ext = end($name_array);
			unset($name_array[count($name_array)-1]);
			$name_array_new = implode('.', $name_array);
			$name_new = $name_array_new.'_cv.'.$ext;

			$path = $_FILES['your-cv']['tmp_name'];
			$body = base64_encode( file_get_contents( $_FILES['your-cv']['tmp_name'] ) );
			if (!empty($responce))
				$responce2 = $rest->insert_file( $responce['id'], $name_new, $body, $sf_table );

			$name = $_FILES['your-letter']['name'];
			$size2 = $_FILES['your-letter']['size'];
			if($size2 > 0){
				$body = base64_encode( file_get_contents( $_FILES['your-letter']['tmp_name'] ) );
				if (!empty($responce))
					$responce2 = $rest->insert_file( $responce['id'], $name, $body, $sf_table );
			}

		}
		
		if ( !empty($responce) ) {

			$reply = __( 'The application for the job was sent successfully.', SF_TFP_NAME );

		}
		/*else {
			mail($options['email'], 'LOG', 'log-saleforce: '.print_r($responce, 1).' -------- log-post: '.print_r($_POST, 1).' -------- log-file: '.print_r($_FILES, 1));
		}*/

		if ( ! $responce || ( isset( $options['email_on'] ) && $options['email_on'] ) ) {

			if ( !empty($responce) ) {
				$status = __( 'The vacancies were successfully sent to Tigris.', SF_TFP_NAME );
			} else {
				$status = __( 'There was a failure to send a job application for the Salesforce service.', SF_TFP_NAME );
			}

			// Sending to e-mail
			if ( $options['email'] ) {

				$uploaddir  = (object) wp_upload_dir();
				$vacancytmp = $uploaddir->basedir . '/vacancy_tmp';
				$done_files = array();

				if ( ! is_dir( $vacancytmp ) ) {
					mkdir( $vacancytmp, 0777 );
				}

				foreach( $_FILES as $file ) {

					$tmp_name = $file['name'];

					if ( move_uploaded_file( $file['tmp_name'], "$vacancytmp/$tmp_name" ) ){
						$attachments[] = realpath( "$vacancytmp/$tmp_name" );
					}
				}

				$post = array(
						'title' => get_the_title((int) $_POST['vacancyID'] ),
						'url'	=> get_permalink((int) $_POST['vacancyID'] )
					);
				$to		 = $options['email'];
				$subject = $status;
				$message = '<br>'
						 . '<p>' . __( 'Job vacancy', SF_TFP_NAME ) . ': <a href="' .esc_url($post['url']) . '">' . esc_html($post['title']) . '</a></p>'
						 . '<p>' . __( 'Applicant`s firstname', SF_TFP_NAME ) . ': ' . esc_html( $_POST['firstname'] ) . '</p>'
						 . '<p>' . __( 'Applicant`s lastname', SF_TFP_NAME ) . ': ' . esc_html( $_POST['lastname'] ) . '</p>'
						 . '<p>' . __( 'Applicant`s e-mail', SF_TFP_NAME ) . ': ' . esc_html( $_POST['email'] ) . '</p><br>'
						 . '<p>' . __( 'Applicant`s message', SF_TFP_NAME ) . ': ' . esc_html( $_POST['your-message'] ) . '</p>'
						 . '<br><hr><br>'
						 . '<p>' . __( 'Referer URL', SF_TFP_NAME ) . ': ' . get_home_url() . esc_url( $_POST['_wp_http_referer'] ) . '</p><br>';

				$adminEmail = get_option('admin_email');
				$emailFrom = !empty($adminEmail) ? $adminEmail : 'info@' . str_replace('www.', '', parse_url( get_bloginfo( 'url' ), PHP_URL_HOST ));
				$headers = array(
						'From: ' . get_bloginfo( 'name' ) . ' <' . $emailFrom . '>',
						'content-type: text/html'
					);

				if ( wp_mail( $to, $subject, $message, $headers, $attachments ) || !empty($responce) ) {

					$reply = __( 'The application for the job was sent successfully.', SF_TFP_NAME );

					foreach ( $attachments as $file ) {
						unlink( $file );
					}
				} else {
					$reply = __( 'Send failed. Please try again later.', SF_TFP_NAME );
				}
			}
		}
	}
	die( $reply );
}
add_action( 'wp_ajax_formvacancies', 'sf_tfp_load_form' );
add_action( 'wp_ajax_nopriv_formvacancies', 'sf_tfp_load_form' );

/** Tigris-author role: BEGIN */

/**
 * [sf_tfp_add_tigris_role				CORE: Add Tigris-author role]
 */
function sf_tfp_add_tigris_role() {
	$result = add_role( 'tigris_author', __( 'Tigris author', SF_TFP_NAME ),
		array(
			'edit_published_posts'   => true,
			'upload_files'			 => true,
			'publish_posts'			 => true,
			'delete_published_posts' => true,
			'edit_posts'  			 => true,
			'delete_posts'			 => true,
			'read'        			 => true,
			'edit_post'				 => true,
			'level_2'				 => true,
			'level_1'				 => true,
			'level_0'				 => true,
			'delete_posts'			 => true,
			'delete_published_posts' => true,
			'rich_editing'			 => true
		)
	);
}

/**
 * [sf_tfp_remove_tigris_role			CORE: Remove Tigris-author role]
 */
function sf_tfp_remove_tigris_role() {
	remove_role( 'tigris_author' );
}

/**
 * [sf_tfp_profile_fields 				CORE: Add new field for Tigris-author profile]
 * @param  [object] $user 				[User object]
 */
function sf_tfp_profile_fields( $user ) {

	if ( ! current_user_can( 'upload_files' ) ) {
		return;
	}

	// vars
	$url             = get_the_author_meta( 'cupp_meta', $user->ID );
	$upload_url      = get_the_author_meta( 'cupp_upload_meta', $user->ID );
	$upload_edit_url = get_the_author_meta( 'cupp_upload_edit_meta', $user->ID );
	$button_text     = $upload_url ? __( 'Change Current Image', SF_TFP_NAME ) :  __( 'Upload New Image', SF_TFP_NAME );

	if ( $upload_url ) {
		$upload_edit_url = get_site_url() . $upload_edit_url;
	} ?>

	<h3><?php _e( 'Employee Information', SF_TFP_NAME ); ?></h3>
	<table class="form-table">
		<tr>
			<th>
				<label for="employee_linkedin">
					<?php _e( 'LinkedIn', SF_TFP_NAME ); ?>
				</label>
			</th>
			<td>
				<input
					type="text"
					name="employee_linkedin"
					id="employee_linkedin"
					value="<?php echo esc_attr( get_the_author_meta( 'employee_linkedin', $user->ID ) ); ?>"
					class="regular-text" />
				<br />
			</td>
		</tr>
		<tr>
			<th>
				<label for="employee_phone_office">
					<?php _e( '​Phone number (office)', SF_TFP_NAME ); ?>
				</label>
			</th>
			<td>
				<input
					type="text"
					name="employee_phone_office"
					id="employee_phone_office"
					value="<?php echo esc_attr( get_the_author_meta( 'employee_phone_office', $user->ID ) ); ?>"
					class="regular-text" />
				<br />
			</td>
		</tr>
		<tr>
			<th>
				<label for="employee_phone_mobile">
					<?php _e( '​Phone number (mobile)', SF_TFP_NAME ); ?>
				</label>
			</th>
			<td>
				<input
					type="text"
					name="employee_phone_mobile"
					id="employee_phone_mobile"
					value="<?php echo esc_attr( get_the_author_meta( 'employee_phone_mobile', $user->ID ) ); ?>"
					class="regular-text" />
				<br />
			</td>
		</tr>
		<tr>
			<th>
				<label for="employee_branch">
					<?php _e( 'Branch', SF_TFP_NAME ); ?>
				</label>
			</th>
			<td>
				<input
					type="text"
					name="employee_branch"
					id="employee_branch"
					value="<?php echo esc_attr( get_the_author_meta( 'employee_branch', $user->ID ) ); ?>"
					class="regular-text" />
				<br />
			</td>
		</tr>
		<tr>
			<th>
				<label for="employee_description">
					<?php _e( 'Description', SF_TFP_NAME ); ?>
				</label>
			</th>
			<td>
				<textarea name="employee_description" id="employee_description" rows="5" cols="30"><?php echo esc_attr( get_the_author_meta( 'employee_description', $user->ID ) ); ?></textarea>
				<br />
			</td>
		</tr>
	</table>

	<?php
	// Enqueue the WordPress Media Uploader.
	wp_enqueue_media();
}
if ( ! isset( $options['roles_off'] ) ) {
	add_action( 'show_user_profile', 'sf_tfp_profile_fields' );
	add_action( 'edit_user_profile', 'sf_tfp_profile_fields' );
}

/**
 * [sf_tfp_save_profile_fields 		CORE: Save update value]
 * @param  [integer] $user_id 			[User ID]
 */
function sf_tfp_save_profile_fields( $user_id ) {
	update_user_meta( $user_id, 'employee_linkedin', sanitize_text_field($_POST['employee_linkedin']) );
	update_user_meta( $user_id, 'employee_phone_office', sanitize_text_field($_POST['employee_phone_office']) );
	update_user_meta( $user_id, 'employee_phone_mobile', sanitize_text_field($_POST['employee_phone_mobile']) );
	update_user_meta( $user_id, 'employee_branch', sanitize_text_field($_POST['employee_branch']) );
	update_user_meta( $user_id, 'employee_description', sanitize_text_field($_POST['employee_description']) );
}
if ( ! isset( $options['roles_off'] ) ) {
	add_action( 'personal_options_update', 'sf_tfp_save_profile_fields' );
	add_action( 'edit_user_profile_update', 'sf_tfp_save_profile_fields' );
}

/**
 * [sf_tfp_checked_tigris_author		CORE: Checking and creating the author Tigris]
 * @param  [string] $username			[Author name: field 'tigris__owner_name__c']
 * @return [integer]          			[Author ID]
 */
function sf_tfp_checked_tigris_author( $username ) {

	$username = trim( $username );
	$user_str = str_replace( ' ', '_', $username );

	if ( empty( $user_str ) ) {
		$users = get_users( array(
				'role' => 'administrator'
			) );

		return $users[0]->ID;
	}

	// checked author
	if ( ! $user_id = username_exists( $user_str ) ) {
		$user_arr = explode( ' ', $username, 2 );

		// Creating new user
		$userdata = array(
			'user_pass'       		=> password_hash( $username, PASSWORD_DEFAULT ),
			'user_login'      		=> $user_str,
			'user_nicename'   		=> mb_strtolower( $user_str ),
			'user_url'        		=> '',
			'user_email'      		=> mb_strtolower( $user_str ) . '@' . $_SERVER['HTTP_HOST'],
			'display_name'    		=> $user_str,
			'nickname'        		=> $user_str,
			'first_name'      		=> ( isset( $user_arr[0] ) ? $user_arr[0] : '' ),
			'last_name'       		=> ( isset( $user_arr[1] ) ? $user_arr[1] : '' ),
			'description'     		=> __( 'The user is created automatically.', SF_TFP_NAME ),
			'rich_editing'    		=> 'false',
			'user_registered' 		=> date( 'Y-m-d H:i:s' ),
			'role'            		=> 'tigris_author',
			'employee_linkedin'		=> '',
			'employee_phone_office'	=> '',
			'employee_phone_mobile'	=> '',
			'employee_branch'		=> '',
			'employee_description'	=> ''
		);
		$user_id = wp_insert_user( $userdata );

		// Setting meta data
		update_user_meta( $user_id, 'employee_linkedin', $userdata['employee_linkedin'] );
		update_user_meta( $user_id, 'employee_phone_office', $userdata['employee_phone_office'] );
		update_user_meta( $user_id, 'employee_phone_mobile', $userdata['employee_phone_mobile'] );
		update_user_meta( $user_id, 'employee_branch', $userdata['employee_branch'] );
		update_user_meta( $user_id, 'employee_description', $userdata['employee_description'] );
	}
	return $user_id;
}

/** Tigris-author role: END */

/**
 * [sf_tfp_crate_vacancies_category 	CORE: Checking and creating vacancies category]
 * @param  [string] $category_name 		[Category name: field 'tigris__functiegroep__c']
 * @return [mixed]          			[Category ID or false - when category does not exist.]
 */
function sf_tfp_crate_vacancies_category( $category_name ) {
	global $options;

	if ( isset( $options['category_off'] ) && $options['category_off'] ) {
		// Vacancy category dissabled
		return false;
	} else {
		// Vacancy category enabled

		$tax  = 'tigrisvacancies';
		$slug = mb_strtolower( str_replace( ' ', '-', trim( $category_name ) ) );

		$args = array(
			'type'       => 'vacatures',
			'taxonomy'   => $tax,
			'hide_empty' => 0,
			'slug'       => $slug
		);
		$categories = get_categories( $args );
		$cat_id = 0;
		if ( empty( $categories ) ) {

			$args = compact('name', 'slug', 'parent', 'description');

			$data = wp_insert_term(
				$category_name,
				$tax,
				array(
					'description' => __( 'The category is created automatically.', SF_TFP_NAME ),
					'slug'        => $slug
				)
			);
			if( ! is_wp_error($data) )
				$cat_id = $data['term_id'];

		} else {
			$cat_id = $categories[0]->cat_ID;
		}

		return $cat_id;
	}
}

/**
 * [sf_tfp_added_tigristag 			CORE: Added tags to vacancy]
 * @param  [integer] $postID 			[Vacancy ID]
 * @param  [array] $tags 				[Tags for vacancy: fields 'tigris__soort_vacature__c', 'tigris__interne_vacature__c'  and 'tigris__plaats__c']
 * @return [boolean]          			[Operation status]
 */
function sf_tfp_add_tigristag( $postID, $tags = array() ) {
	if ( empty( $tags ) ) {
		return false;
	}

	$taxonomy = 'tigristag';
	$append   = true;

	return wp_set_post_terms( $postID, $tags, $taxonomy, $append );
}

/**
 * [sf_tfp_update_tigristag 			CORE: Update vacancies tags]
 * @param  [integer] $postID 			[Vacancy ID]
 * @param  [array] $tags 				[Tags for vacancy: fields 'tigris__soort_vacature__c', 'tigris__interne_vacature__c'  and 'tigris__plaats__c']
 * @return [boolean]          			[Operation status]
 */
function sf_tfp_update_tigristag( $postID, $tags = array() ) {

	$taxonomy = 'tigristag';

	$post_tags = get_the_terms( $postID , $taxonomy );

	if ( $post_tags ) {
		foreach( $post_tags as $posttag ) {
			$posttags[] = $posttag->name;
		}
	}

	$array_diff = array_diff( $posttags, $tags );

	if ( ! empty( $array_diff ) ) {

		wp_remove_object_terms( $postID, $array_diff, $taxonomy );

		if ( ! empty( $tags ) ) {
			wp_set_post_terms( $postID, $tags, $taxonomy, true );
		}

		return true;
	} else {
		return false;
	}
}

/**
 * [sf_tfp_generate_locations_option 	FRONT-OFFICE: Generate options for selecting a location]
 * @param  [string] $selected 			[Selected option]
 * @return [mixed]          			[HTML code or false - when locations does not exist.]
 */
function sf_tfp_generate_locations_option( $selected = false ) {
	global $options;

	if ( isset( $options['location_area'] ) && ! empty( $options['location_area'] ) ) {
		$location_area = explode( ';', $options['location_area'] );
		$html = '';

		foreach ( $location_area as $value) {
			if ( trim( $value ) ) {
				$html .= '<option value="' . trim( $value ) . '"' . ( $selected == trim( $value ) ? ' selected' : '' ) . '>' . trim( $value ) . '</option>';
			}
		}

		return $html;
	}

	return false;
}

/**
 * [tigris_vacancy_list					FRONT-OFFICE: Add vacancy list]
 * @param  [array] $atts    			[Shortcode attributes]
 * 										count=4 //number of vacancies (default 4)
 * 										categories=cat1,cat2 //Vacancy Categories names
 * 										location=location1 //Vacancy Location
 * 										level=level1 //Education level
 * 										week=40 //Work week
 * 										salary=100-1000 //Salary level (min-max)
 * 										sector=Sector //Sector of economy
 * 										employment=Employment //Type of employment
 * 										branch=Branch //Branch
 * 										page=1 //Page Number
 */
function sf_tfp_add_vacancy_list_shortcode( $atts ) {
	ob_start();
	global $att;
	if (!is_array($atts)) {
		$atts = array();
	}
	$att = $atts;
	if (!isset($att['is_ajax'])) {
		echo '<div class="js-tigris-vacancies-container">';
	}
	$args = array(
			'post_type' 	 => 'vacatures',
			'posts_per_page' => 2,
			'orderby' => 'post_modified',
			'order' => 'DESC',
		);
	
	if (isset($att['count']) && $att['count'])
		$args['posts_per_page'] = $att['count'];
	else
		$att['count'] = 0;

	if (isset($att['page']) && !empty($att['page'])) {
		$args['offset'] = $att['page'] * $args['posts_per_page'];
	}
	if (isset($att['categories'])) {
		$categories = explode(',', $att['categories']);
		$args['tax_query'] = array('relation' => 'OR');
		foreach ($categories as $category) {
			$term = get_term_by('name', $category, 'tigrisvacancies');
			if ($term) {
				$args['tax_query'][] = array(
					'taxonomy' => 'tigrisvacancies',
		            'terms' => $term->term_id,
		            'include_children' => false
				);
			}
		}
	}
	if (isset($att['location'])) {
		$args['meta_query'][] = array(
			'key' => 'tigris__plaats__c',
			'value' => $att['location']
		);
	}
	if (isset($att['level'])) {
		$args['meta_query'][] = array(
			'key' => 'tigris__opleidingsniveau__c',
			'value' => $att['level']
		);
	}
	if (isset($att['week'])) {
		$args['meta_query'][] = array(
			'key' => 'tigris__uren_per_week__c',
			'value' => $att['week']
		);
	}
	if (isset($att['salary'])) {
		$salary = explode('-', $att['salary']);
		if (isset($salary[0])) {
			$args['meta_query'][] = array(
				'compare' => '>=',
				'key' => 'tigris__salaris_van__c',
				'value' => $salary[0]
			);
		}
		if (isset($salary[1])) {
			$args['meta_query'][] = array(
				'compare' => '<=',
				'key' => 'tigris__salaris_tot__c',
				'value' => $salary[1]
			);
		}
	}
	if (isset($att['sector'])) {
		$args['meta_query'][] = array(
			'key' => 'tigris__functiegroep__c',
			'value' => $att['sector']
		);
	}
	if (isset($att['employment'])) {
		$args['meta_query'][] = array(
			'key' => 'tigris__soort_dienstverband__c',
			'value' => $att['employment']
		);
	}
	if (isset($att['branche'])) {
		$args['meta_query'][] = array(
			'key' => 'tigris__branche__c',
			'value' => $att['branche']
		);
	}
	$vacancies = new WP_Query( $args );
	while ( $vacancies->have_posts() ) {
		 $vacancies->the_post();
		// Check template to theme
		if ( locate_template( array( 'tigris/vacancy_list.php' ) ) ) {
			get_template_part( 'tigris/vacancy_list' );
		} else {
			load_template( SF_TFP_PLUGIN_DIR . '/templates/vacancy_list.php', false );
		}
	}
	if (!isset($att['is_ajax'])) {
		$att['action'] = 'tigris_fp_vacancy_list';
		echo '</div>';
		if ($vacancies->post_count >= $att['count']) {
			if ($att['count'] == 0)
				$countData = 2;
			else
				$countData = $att['count'];
		?>
		<div class="tigris-more-vacancies">
			<a href="#" class="js-tigris-more-vacancies" data-page="1"><?php echo __('More Vacancies', SF_TFP_NAME)?></a>
		</div>
		<script type="text/javascript">
			jQuery(function($) {
				$(document).on('click', '.js-tigris-more-vacancies', function(e){
					e.preventDefault();
					that = $(this);
					var data = {
						<?php 
						foreach ($att as $attKey => $attValue) {
							echo '"' . $attKey . '": "' . $attValue . '",';
						}
						?>
		                'page' :  $(this).data('page')
		              };
		              $.ajax({
		                url: "<?php echo admin_url('admin-ajax.php')?>",
		                data:data,
		                type:'POST',
		                dataType: 'json',
		                success:function(data){
		                  	if( data) {
				                if (data.data) {
				                    $('.js-tigris-vacancies-container').append(data.data);
				                    that.data('page', that.data('page') + 1);
				                }
			                	if (data.count < <?php echo $countData?>) {
			                		that.parent().remove();
			              		}
			              	}
		                }
		              });

				});
			});
		</script>
	<?php
		}
		wp_reset_postdata();
		wp_reset_query();
		$vacancyList = ob_get_clean();
	} else {
		if (isset($att['page']) && !empty($att['page'])) {
			$vCount = $vacancies->max_num_pages - $att['page'];
		} else {
			$vCount = $vacancies->max_num_pages;
		}
		$vacancyList['data'] = ob_get_clean();
		$vacancyList['count'] = $vCount;
		$vacancyList = json_encode($vacancyList);
	}
	
    return $vacancyList;
}
add_shortcode( 'tigris_vacancy_list', 'sf_tfp_add_vacancy_list_shortcode' );

add_action('wp_ajax_tigris_fp_vacancy_list', 'ajax_tigris_fp_vacancy_list');
add_action('wp_ajax_nopriv_tigris_fp_vacancy_list', 'ajax_tigris_fp_vacancy_list');

function ajax_tigris_fp_vacancy_list() {
	$shortCodeAttr = '';
	foreach ($_POST as $key => $value) {
		if ($key == 'action')
			continue;
		$shortCodeAttr .= ' ' . $key . '="' . esc_attr($value) . '"';
	}
	$shortCodeAttr .= ' is_ajax=1';
	echo do_shortcode('[tigris_vacancy_list ' . $shortCodeAttr . ']');
    die();
}


/**
 * [tigris_searchform					FRONT-OFFICE: Add Search form]
 * @param  [array] $atts    			[Shortcode attributes]
 * 										distances=15,20,25 //distance
 */
function sf_tfp_add_searchform_shortcode($atts) {
	global $att;
	global $options;
	$att = $atts;
	$count_posts = wp_count_posts( 'vacatures' );
	$total = $count_posts->publish;
	$get_val = array(
		'tigris_location'  => ( isset( $_GET['tigris_location'] ) && $_GET['tigris_location'] && ! isset( $_GET['business'] ) ) ? sanitize_text_field($_GET['tigris_location']) : '',
		'distance'  => ( isset( $_GET['distance'] ) && $_GET['distance'] && ! isset( $_GET['business'] ) ) ? sanitize_text_field($_GET['distance']) : ''
	);
	$html_code = '<form id="tigris-searchform" role="search" method="get" class="tigris-search-form" action="'.home_url( '/' ).'">
		<div class="keywordshome">
		<input type="hidden" value="vacatures" name="post_type" class="js-search-form-item">
		<label class="tigris-search-form-label-find-job">
			<input
				id="s"
				type="text"
				class="tigris-search-form-find-job search-field js-search-form-item"
				placeholder="'.__('Find a job', SF_TFP_NAME).' …"
				value="'. ((isset($_GET['view-view']) || isset( $_GET['business'])) ? '' : get_search_query()).'"
				name="s"
				autocomplete="off"
				data-provide="typeahead">
			<div class="js-autocomplete-result transition"></div>
		</label>
		</div>
		<div class="locationhome">
		<input type="text" class="js-search-form-item tigris-search-form-location" name="tigris_location" placeholder="'.__('Region, city or zip code', SF_TFP_NAME).'" value="' . $get_val['tigris_location'] . '">';
	if (isset($options['google_api_key']) && !empty($options['google_api_key']) && isset($att['distances']) && !empty($att['distances'])) {
		$html_code .= '<div class="afstandhome"><select name="distance" class="js-search-form-item tigris-search-form-distances">';
		$html_code .= '<option value="0">' . __('Distance', SF_TFP_NAME) . '</option>';
		$distances = explode(',', $att['distances']);
		foreach ($distances as $distance) {
			if ($distance == $get_val['distance'] ) {
				$selected = ' selected';
			} else {
				$selected = '';
			}
			$html_code .= '<option value="' . $distance . '"' . $selected . '>' . $distance . ' '. __('km', SF_TFP_NAME) . '</option>';
		}
		$html_code .= '</select></div>';
	}
	$html_code .= '</div>';
	$html_code .= '<div class="buttonhome"><button type="submit" class="tigris-search-form-submit button-red transition js-tigris-search-form-submit" data-total="'.$total.'">';
	$html_code .= sprintf(__( 'All %s vacancies', SF_TFP_NAME ), '<strong>' . $total . '</strong>');
	$html_code .= '</button></div></form>';
	return $html_code;
}

add_shortcode( 'tigris_searchform', 'sf_tfp_add_searchform_shortcode' );

/**
 * [tigris_filter					FRONT-OFFICE: Add Filter form]
 * @param  [array] $atts    		[Shortcode attributes]
 * 									internal=1 // is internal (1-yes, 0 - no)
 * 									distances=15,20,25 //distance
 */
function sf_tfp_add_filter_shortcode($atts) {
	global $wpdb;
	global $att;
	global $options;
	$att = $atts;
	$table_name = $wpdb->prefix . 'postmeta';
	// get all values meta-data
	$result = $wpdb->get_results("
			SELECT DISTINCT `meta_key`, `meta_value`
			FROM  `$table_name`
			WHERE `meta_key` = 'tigris__opleidingsniveau__c'
			OR `meta_key` = 'tigris__uren_per_week__c'
			OR `meta_key` = 'tigris__functiegroep__c'
			OR `meta_key` = 'tigris__branche__c'
			ORDER BY `meta_value`;
		");
	if (!empty( $result)) {
		$tigris_array = array();
		foreach ($result as $value) {
			$tigris_array[$value->meta_key][] = $value->meta_value;
		}
	}
	$hoursweek = array( '8-16', '16-24', '24-32', '32-40' );
	$get_val = array(
		's'  => (isset( $_GET['s']) && $_GET['s'] && isset($_GET['business'])) ? $_GET['s'] : '',
		'tigris_location'  => ( isset($_GET['tigris_location']) && $_GET['tigris_location'] && isset( $_GET['business'] ) ) ? $_GET['tigris_location'] : '',
		'distance'  => (isset($_GET['distance']) && $_GET['distance'] && isset($_GET['business'])) ? esc_attr($_GET['distance']) : '',
		'education' => (isset($_GET['education']) && $_GET['education']) ? esc_attr($_GET['education']) : '',
		'hoursweek' => (isset($_GET['hoursweek']) && $_GET['hoursweek']) ? esc_attr($_GET['hoursweek']) : '',
		'function'  => (isset($_GET['function']) && $_GET['function']) ? esc_attr($_GET['function']) : '',
		'business'  => (isset($_GET['business']) && $_GET['business']) ? esc_attr($_GET['business']) : ''
	);
	$label = '';
	// internal
	if (isset($att['interns']) && $att['interns'])
		$label = 'internal';
	// Generated HTML structure
	$html_code = '<form id="tigris-filter-form" role="search" method="get" class="tigris-filter-form" action="' . home_url( '/' ) . '">
					<input type="hidden" value="" name="s">
					<input type="hidden" value="vacatures" name="post_type" />
					<input type="hidden" value="' . $label . '" name="post_label" />
					<ul class="tigris-filter-first">
						<li class="transition">
							<i class="transition"></i>
							<span>' . __('Function', SF_TFP_NAME) . '</span>
							<ul class="tigris-filter-second">
								<li>
									<input type="text" class="js-filter-form-item" name="s" placeholder="' . __('Find a job', SF_TFP_NAME) . ' …" value="' . $get_val['s'] . '">
								</li>
							</ul>
						</li>
						<li class="transition">
							<i class="transition"></i>
							<span>' . __('Place', SF_TFP_NAME) . '</span>
							<ul class="tigris-filter-second">
								<li>
									<input type="text" class="js-filter-form-item" name="tigris_location" placeholder="' . __('Region, city or zip code', SF_TFP_NAME) . '" value="' . $get_val['tigris_location'] . '">
								</li>
							</ul>
						</li>';
						if (isset($options['google_api_key']) && !empty($options['google_api_key']) && isset($att['distances']) && !empty($att['distances'])) {
						$html_code .= '
						<li class="transition">
							<i class="transition"></i>
							<span>' . __('Distance', SF_TFP_NAME) . '</span>
							<ul class="tigris-filter-second">
								<li' . (!empty( $get_val['distance'] ) ? ' class="selected"' : '') . '>
									<select name="distance" class="tigris-filter-distance js-filter-form-item">
									<option value="0">' . __('Distance', SF_TFP_NAME) . '</option>';
							$distances = explode(',', $att['distances']);
							foreach ($distances as $distance) {
								if ($distance == $get_val['distance'] ) {
									$selected = ' selected';
								} else {
									$selected = '';
								}
								$html_code .= '<option value="' . $distance . '"' . $selected . '>' . $distance . ' '. __('km', SF_TFP_NAME) . '</option>';
							}
							$html_code .= '</select>
								</li>
							</ul>
						</li>';
						}
						$html_code .= '
						<li class="transition">
							<i class="transition"></i>
							<span>' . __('Education level', SF_TFP_NAME) . '</span>
							<ul class="tigris-filter-second">
								<li' . ( ! empty( $get_val['education'] ) ? ' class="selected"' : '' ) . '>
									<select name="education" class="tigris-filter-education js-filter-form-item">';
										if ( ! empty( $tigris_array['tigris__opleidingsniveau__c'] ) ) {
											$html_code .= '<option value="0">' . __('Choose...', SF_TFP_NAME) . '</option>';
											foreach ( $tigris_array['tigris__opleidingsniveau__c'] as $value ) {
												if ( $value == $get_val['education'] ) {
													$selected = ' selected';
												} else {
													$selected = '';
												}
												$html_code .= '<option value="' . $value . '"' . $selected . '>' . $value . '</option>';
											}
										}
									$html_code .= '</select>
								</li>
							</ul>
						</li>';
		$html_code .= '
						<li class="transition">
							<i class="transition"></i>
							<span>' . __( 'Work week', SF_TFP_NAME) . '</span>
							<ul class="tigris-filter-second">
								<li' . ( ! empty( $get_val['hoursweek'] ) ? ' class="selected"' : '' ) . '>
									<select name="hoursweek" class="tigris-filter-hoursweek js-filter-form-item">';
										$html_code .= '<option value="0">' . __('Choose...', SF_TFP_NAME) . '</option>';
										foreach ( $hoursweek as $value ) {
											if ( $value == $get_val['hoursweek'] ) {
												$selected = ' selected';
											} else {
												$selected = '';
											}
											$html_code .= '<option value="' . $value . '"' . $selected . '>' . $value . '</option>';
										}
									$html_code .= '</select>
								</li>
							</ul>
						</li>';
		$html_code .= '
						<li class="transition">
							<i class="transition"></i>
							<span>' . __('Branch', SF_TFP_NAME) . '</span>
							<ul class="tigris-filter-second">
								<li' . ( ! empty( $get_val['business'] ) ? ' class="selected"' : '' ) . '>
									<select name="business" class="tigris-filter-business js-filter-form-item">';
										if ( ! empty( $tigris_array['tigris__branche__c'] ) ) {
											$html_code .= '<option value="0">' . __('Choose...', SF_TFP_NAME) . '</option>';
											foreach ( $tigris_array['tigris__branche__c'] as $value ) {
												if ( $value == $get_val['business'] ) {
													$selected = ' selected';
												} else {
													$selected = '';
												}
												$html_code .= '<option value="' . $value . '"' . $selected . '>' . $value . '</option>';
											}
										}
									$html_code .= '</select>
								</li>
							</ul>
						</li>

					</ul>
					<button type="submit" id="filtersubmit" class="x-btn-tigris-search js-tigris-filter">' . __('Search', SF_TFP_NAME) . '</button>
					<button type="reset" id="tigris-reset" class="x-btn transition js-tigris-reset">' . __('Clear filters', SF_TFP_NAME) . '</button>
				</form>';
	return $html_code;
}


add_shortcode( 'tigris_filter', 'sf_tfp_add_filter_shortcode' );
/**
 * [tigris_vacansies_search 				Filter requests for search result]
 * @param  [object] $query 					[WP_Query object]
 * @return [hook]                   		[No return]
 */
function sf_tfp_vacansies_search( $query ) {

	if ( isset( $_GET['s'] ) && $_GET['s'] == '' ) {
		$query->is_search == true;
	}
	$meta_query = array();

	if ( $query->is_search && isset( $_GET['post__in'] ) && $_GET['post__in'] ) {
		$post_in = explode( ',', sanitize_text_field($_GET['post__in']) );
		$query->set( 'post__in', $post_in );
	} else {
		// choice interns
		if ( $query->is_search && isset( $_GET['post_label'] ) && $_GET['post_label'] == 'internal' ) {
			array_push( $meta_query, array(
					'key' => 'Tigris__Interne_vacature__c'
				) );
		}

		// choice branch
		if ( $query->is_search && ( isset( $_GET['business'] ) && ! empty( $_GET['business'] ) && $_GET['business'] ) ) {
			array_push( $meta_query, array(
					'key' => 'Tigris__Branche__c',
					'value' => sanitize_text_field($_GET['business'])
				) );
		}

		// choice region
		if ( $query->is_search && ( isset( $_GET['tigris_location'] ) && ! empty( $_GET['tigris_location'] ) && $_GET['tigris_location'] ) && empty( $_GET['distance'] ) ) {
			array_push( $meta_query, array(
					'key' => 'Tigris__Plaats__c',
					'value' => sanitize_text_field($_GET['tigris_location'])
				) );
		}

		// choice education level
		if ( $query->is_search && ( isset( $_GET['education'] ) && ! empty( $_GET['education'] ) && $_GET['education'] ) ) {
			array_push( $meta_query, array(
					'key' => 'Tigris__Opleidingsniveau__c',
					'value' => sanitize_text_field($_GET['education'])
				) );
		}

		// choice hours per week
		if ( $query->is_search && ( isset( $_GET['hoursweek'] ) && ! empty( $_GET['hoursweek'] ) && $_GET['hoursweek'] ) ) {
			array_push( $meta_query, array(
					'key'     => 'Tigris__uren_per_week__c',
					'value'   => explode( '-', sanitize_text_field($_GET['hoursweek']) ),
					'type'    => 'numeric',
					'compare' => 'BETWEEN'
				) );
		}

		// choice category
		if ( $query->is_search && ( isset( $_GET['function'] ) && ! empty( $_GET['function'] ) && $_GET['function'] ) ) {
			array_push( $meta_query, array(
					'key' => 'Tigris__Functiegroep__c',
					'value' => sanitize_text_field($_GET['function'])
				) );
		}

		// choice distance
		if ( $query->is_search && ( isset( $_GET['distance'] ) && ! empty( $_GET['distance'] ) && $_GET['distance'] ) ) {
	        $location = false;
	        if(isset($_GET['tigris_location']) && !empty($_GET['tigris_location'])){
	            $location = sanitize_text_field($_GET['tigris_location']);
	        }
	        $distance = sanitize_text_field($_GET['distance']);
			if($location === false) {
			$center = sf_tfp_get_latlng(false);
			} else {
				$center = sf_tfp_get_latlng($location);
			}
			$post__in = [];
			if( $center !== false){
	            if(isset($args['meta_query'][0]['value']))
					unset($args['meta_query'][0]['value']);
				$query_vacatures = new WP_Query([
					'post_type' => 'vacatures',
					'posts_per_page' => -1,
					'orderby' => 'post_modified',
					'order' => 'DESC'
				]);
		        while ( $query_vacatures->have_posts() ) {
			        $query_vacatures->the_post();
			        $latlng = sf_tfp_get_or_update_latlng(get_the_ID());
			        if($latlng === false) continue;

			        $d = sf_tfp_get_distance($latlng, $center) / 1000;
					$distance = intval($distance);
			        if($d < $distance){
				        array_push($post__in, get_the_ID());
			        }
		        }
	        }
			if(count($post__in) == 0)
				$post__in = array(0);
	        $query->set( 'post__in', $post__in );
		}
		// total search
		if ( $query->is_search && isset( $_GET['view-view'] ) ) {
			$query->set( 'post_type', array( 'post', 'vacatures' ) );
			$query->set( 'order', 'ASC' );
			$query->set( 'orderby', 'name' );
		}

		if ( ! empty( $meta_query ) ) {
			$query->set( 'meta_query', $meta_query );
		}
	}
}
add_action( 'pre_get_posts', 'sf_tfp_vacansies_search' );

/**
 * @param $address | String or false
 * @return mixed | Array or false
 */
function sf_tfp_get_latlng($address){
	global $options;
	if ($address) {
		$prepAddr = str_replace(' ','+',$address);
		$url = 'https://maps.google.com/maps/api/geocode/json?address='. $prepAddr .'&sensor=false&key=' .$options['google_api_key'];
		$geocode =file_get_contents($url);

		$output = json_decode($geocode);
	}

	if( !isset($output->results[0]) || $address === false) {
		if ( is_plugin_active( 'geoip-detect/geoip-detect.php' ) ) {
			$ip = geoip_detect2_get_client_ip();
			$info = geoip_detect2_get_info_from_ip($ip);
			$latlng = [
					'lat' => $info->location->latitude,
					'lng' => $info->location->longitude
			];
		}
		if(!isset($latlng) || is_null($latlng['lat']) || is_null($latlng['lng'])){
			return false;
		}
	} else {
		if(isset($output->results[0])) {
			$latlng = [
					'lat' => $output->results[0]->geometry->location->lat,
					'lng' => $output->results[0]->geometry->location->lng
			];
		} else {
			return false;
		}
	}

	return $latlng;
}

/**
 * @param $post_id
 * @return array
 */
function sf_tfp_get_or_update_latlng($post_id){
	$geo_lat = floatval(get_post_meta($post_id, 'geo_lat', true));
	$geo_lng = floatval(get_post_meta($post_id, 'geo_lng', true));
	if($geo_lat == 0 || $geo_lng == 0){
		// update lat, lng if it's empty
		$address = get_post_meta($post_id, 'tigris__plaats__c');
		if(count($address) == 0)
			return false;
		$latlng = sf_tfp_get_latlng($address[0]);
		if($latlng !== false) {
			update_post_meta($post_id, 'geo_lat', $latlng['lat']);
			update_post_meta($post_id, 'geo_lng', $latlng['lng']);
			return $latlng;
		} else {
			return false;
		}
	} else {
		// return value
		return [
				'lat' => $geo_lat,
				'lng' => $geo_lng,
		];
	}
}

/**
 * @param $point1
 * @param $point2
 * @return int
 */
function sf_tfp_get_distance($point1, $point2){

	$lat1 = $point1['lat'];
	$lon1 = $point1['lng'];

	$lat2 = $point2['lat'];
	$lon2 = $point2['lng'];


	$lat1 *= M_PI / 180;
	$lat2 *= M_PI / 180;
	$lon1 *= M_PI / 180;
	$lon2 *= M_PI / 180;

	$d_lon = $lon1 - $lon2;

	$slat1 = sin($lat1);
	$slat2 = sin($lat2);
	$clat1 = cos($lat1);
	$clat2 = cos($lat2);
	$sdelt = sin($d_lon);
	$cdelt = cos($d_lon);

	$y = pow($clat2 * $sdelt, 2) + pow($clat1 * $slat2 - $slat1 * $clat2 * $cdelt, 2);
	$x = $slat1 * $slat2 + $clat1 * $clat2 * $cdelt;

	return atan2(sqrt($y), $x) * 6372795;
}

/**
 * [sf_tfp_search_correctcount 			FRONT-OFFICE: Vacancy location for search]
 * @return [string] 						[HTML code]
 */
function sf_tfp_search_correctcount() {

	if ($_POST['action'] == 'correctcount') {

		$args = array(
				'numberposts' => -1,
				'nopaging'	  => true,
				'orderby' => 'post_modified',
				'order' => 'DESC',
				'meta_query'  => array(
					array(
						'key'	=> 'tigris__plaats__c',
						'compare' => 'EXISTS'
					)
				)
			);

		$location = false;
		$term_type = 0;
		foreach ( $_POST['query'] as $value ) {
			if ( $value['name'] == 'distance' ) {
				$distance = sanitize_text_field($value['value']);
			}
		}

		foreach ( $_POST['query'] as $key => $value ) {
			$value['name'] = sanitize_key( $value['name'] );
			$value['value'] = sanitize_text_field( $value['value'] );

			if ( $value['name'] == 'term_type' && $value['value'] == 1 ) {
				$term_type = 1;
			}

			if ( $value['name'] == 'post_type') {
				$args['post_type'] = $value['value'];
			}

			if ( $value['name'] == 's' && ! empty( $value['value'] ) ) {
				$args['s'] = $value['value'];
			}

			if ( $value['name'] == 'tigris_location' && ! empty( $value['value'] ) ) {
				$location = $value['value'];
			}

			if ( $value['name'] == 'tigris_location' && ! empty( $value['value'] ) && ! $distance ) {
				$args['meta_query'][0]['value'] = $value['value'];
			}

			if ( $value['name'] == 'distance' && $value['value'] != 0 ) {

				$distance = $value['value'];
				$center = false;
				$args['post__in'] = [];
				$center = sf_tfp_get_latlng($location);

				if( $center !== false){
					if(isset($args['meta_query'][0]['value']))
						unset($args['meta_query'][0]['value']);

					$query_vacatures = new WP_Query([
						'post_type' => 'vacatures',
						'posts_per_page' => -1,
						'orderby' => 'post_modified',
						'order' => 'DESC'
					]);

					while ( $query_vacatures->have_posts() ) {
						$query_vacatures->the_post();
						$latlng = sf_tfp_get_or_update_latlng(get_the_ID());
						if($latlng === false) continue;

						$d = sf_tfp_get_distance($latlng, $center) / 1000;
						$distance = intval($distance);
			        	if($d < $distance){
							array_push($args['post__in'], get_the_ID());
						}
					}
				}
			}
		}

		if ( $term_type ) {
			foreach ( $args['post__in'] as $key => $post_id ) {
				$terms = get_the_terms( $post_id, 'tigrisvacancies' );
				if ( $terms[0]->slug == 'interne' || $terms[0]->slug == 'intern' ) {
					unset( $args['post__in'][$key] );
				}
			}
		}

		$query = new WP_Query( $args );

		$result = array(
				'count'    	 => count( $query->posts ),
				'post__in' 	 => isset( $args['post__in'] ) ? implode( ',', $args['post__in'] ) : 0,
				'argsuments' => $args
			);

		echo json_encode( $result );
	}
	die();
}
add_action( 'wp_ajax_correctcount', 'sf_tfp_search_correctcount' );
add_action( 'wp_ajax_nopriv_correctcount', 'sf_tfp_search_correctcount' );

/**
 * [sf_tfp_search_autocomplete 			FRONT-OFFICE: Autocomlete for search]
 * @return [string] 						[HTML code]
 */
function sf_tfp_search_autocomplete() {

	if ( $_POST['action'] == 'autocomlete' ) {

		$args = array(
			's' 			 => sanitize_text_field($_POST['query']),
			'post_type'		 => 'vacatures',
			'posts_per_page' => -1,
			'orderby' => 'post_modified',
			'order' => 'DESC',
		);

		$query = new WP_Query( $args );

		$results 	 = array();
		$results_tmp = array();

		if( $query->have_posts() ) {
			while( $query->have_posts() ) {
				$query->the_post();
				$results_tmp[] = get_the_title();
			}
			$results = array_unique( $results_tmp );
			$results['count'] = count( $query->posts );
		}


		wp_reset_postdata();
		echo json_encode( $results );
	}
	die();
}
add_action( 'wp_ajax_autocomlete', 'sf_tfp_search_autocomplete' );
add_action( 'wp_ajax_nopriv_autocomlete', 'sf_tfp_search_autocomplete' );