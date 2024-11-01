<?php
/**
 * @package WordPress
 * @subpackage Tigris Flexplatform
 */

// Prevent direct file access
defined( 'ABSPATH' ) or exit;

// Get global plug-in options
$option_name = str_replace( '-', '_', SF_TFP_NAME ); //tigris_flexplatform
$apl_setting = get_option( $option_name );

/**
 * [sf_tfp_menu 						BACK-OFFICE: Adding menu to Administration panel]
 * @return [hook] 						[No return]
 */
function sf_tfp_menu() {

	if ( ! isset( $admin_page_hooks[ 'sf_tfp_adminpage' ] ) ) {

		$page_title = SF_TFP_DEV . ' ' .  __( 'extension', SF_TFP_NAME );
		$menu_title = __( 'Tigris', SF_TFP_NAME );
		$capability = 'manage_options';
		$menu_slug  = 'sf-tigris-flexplatform';
		$function 	= 'sf_tfp_adminpage';
		$icon_url 	= SF_TFP_ASSETS_URL . 'img/plugin-icon.png';
		$position 	= '80.000001';

		$page_hook = add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );

		add_action( "load-{$page_hook}", 'sf_tfp_add_help_tab' );
	}
}

/**
 * [sf_tfp_register_settings 			CORE: Plug-In settings]
 * @return [NULL] 						[No return]
 */
function sf_tfp_register_settings() {

	$option_group = 'sf-tfp-settings';
	$option_name  = str_replace( '-', '_', SF_TFP_NAME );
	$args 		  = array(
			'type'              => 'string',
			'group'             => $option_group,
			'description'       => '',
			'sanitize_callback' => null,
			'show_in_rest'      => false,
		);
	register_setting( $option_group, $option_name, $args );

	$id 	  = 'sf_tfp_section_id';
	$title 	  = '';
	$callback = '';
	$page 	  = 'sf-tigris-flexplatform';
	add_settings_section( $id, $title, $callback, $page );

	$section  = 'sf_tfp_section_id';

	$args	  = array(
		'type' 	  => 'text',
		'options' => str_replace( '-', '_', SF_TFP_NAME )
	);

	$args['label'] = 'channel';
	$args['desc']  = __( 'Enter the channel ID.', SF_TFP_NAME );
	add_settings_field( 'sf_tfp_section_id_0', __( 'Organization Id', SF_TFP_NAME ), 'sf_tfp_field_input', $page, $section, $args );

	$args['label'] = 'consumer_key';
	$args['desc']  = __( 'Consumer Key.', SF_TFP_NAME );
	add_settings_field( 'sf_tfp_section_id_14', __( 'Consumer Key', SF_TFP_NAME ), 'sf_tfp_field_input', $page, $section, $args );

	$args['label'] = 'consumer_secret';
	$args['desc']  = __( 'Consumer Secret.', SF_TFP_NAME );
	add_settings_field( 'sf_tfp_section_id_15', __( 'Consumer Secret', SF_TFP_NAME ), 'sf_tfp_field_input', $page, $section, $args );

	$args['label'] = 'category';
	$args['desc']  = __( 'Enter the name of the category you want to retrieve from.', SF_TFP_NAME );
	add_settings_field( 'sf_tfp_section_id_3', __( 'Category name', SF_TFP_NAME ), 'sf_tfp_field_input', $page, $section, $args );

	$args['label'] = 'vacatures_link';
	$args['desc']  = sprintf(__( 'Change the URL for the vacancies page. (default "%s")', SF_TFP_NAME ), 'vacatures');
	add_settings_field( 'sf_tfp_section_id_17', __( 'URL for the vacancies page', SF_TFP_NAME ), 'sf_tfp_field_input', $page, $section, $args );

	$args['label'] = 'author';
	$args['desc']  = __( 'Enter the ID of the author whose entries you want to receive.', SF_TFP_NAME );
	add_settings_field( 'sf_tfp_section_id_4', __( 'Author ID', SF_TFP_NAME ), 'sf_tfp_field_input', $page, $section, $args );

	$args['label'] = 'email';
	$args['desc']  = __( 'The form will be sent only if there is a failure in sending to Salesforce.', SF_TFP_NAME );
	add_settings_field( 'sf_tfp_section_id_5', __( 'E-mail for send forms', SF_TFP_NAME ), 'sf_tfp_field_input', $page, $section, $args );

	$args['label'] = 'email_on';
	$args['desc']  = __( 'Enable if you want to receive all forms by e-mail.', SF_TFP_NAME );
	add_settings_field( 'sf_tfp_section_id_6', __( 'Enable sending forms to email', SF_TFP_NAME ), 'sf_tfp_field_checkbox', $page, $section, $args );

	$args['label'] = 'redirect';
	$args['desc']  = __( 'If left blank, then the application will be sent in the background.', SF_TFP_NAME );
	add_settings_field( 'sf_tfp_section_id_7', __( 'Forwarding after submitting the form', SF_TFP_NAME ), 'sf_tfp_field_select', $page, $section, $args );

	$args['label'] = 'location_area';
	$args['desc']  = __( 'Job placement regions for aplication form. Each region is entered through ";".<br>Example: Texas;Alabama;', SF_TFP_NAME );
	add_settings_field( 'sf_tfp_section_id_8', __( 'Job locations', SF_TFP_NAME ), 'sf_tfp_field_textarea', $page, $section, $args );

	$args['label'] = 'def_category';
	$args['desc']  = __( 'Enter the default category name. If you leave empty, default category name will be "no-vacancies".', SF_TFP_NAME );
	add_settings_field( 'sf_tfp_section_id_9', __( 'Default category', SF_TFP_NAME ), 'sf_tfp_field_input', $page, $section, $args );

	$args['label'] = 'category_off';
	$args['desc']  = __( 'Disable categories functionality.', SF_TFP_NAME );
	add_settings_field( 'sf_tfp_section_id_10', __( 'Disable categories', SF_TFP_NAME ), 'sf_tfp_field_checkbox', $page, $section, $args );

	$args['label'] = 'branch_off';
	$args['desc']  = __( 'Disable branches functionality.', SF_TFP_NAME );
	add_settings_field( 'sf_tfp_section_id_11', __( 'Disable branches', SF_TFP_NAME ), 'sf_tfp_field_checkbox', $page, $section, $args );

	$args['label'] = 'roles_off';
	$args['desc']  = __( 'Disable vacancies author functionality.', SF_TFP_NAME );
	add_settings_field( 'sf_tfp_section_id_12', __( 'Disable vacancies author', SF_TFP_NAME ), 'sf_tfp_field_checkbox', $page, $section, $args );

	$args['label'] = 'google_api_key';
	$args['desc']  = __( 'API key for Google Maps.', SF_TFP_NAME );
	add_settings_field( 'sf_tfp_section_id_13', __( 'Google API key', SF_TFP_NAME ), 'sf_tfp_field_input', $page, $section, $args );

	$args['label'] = 'refresh_token';
	add_settings_field( 'sf_tfp_section_id_16', __( 'Connect SF', SF_TFP_NAME ), 'sf_tfp_field_refreshToken', $page, $section, $args );
}

/**
 * [sf_tfp_adminpage 					BACK-OFFICE: Adding settings page in Administration panel]
 * @return [string] 					[Settings page HTML code]
 */
function sf_tfp_adminpage() {
	global $apl_setting;

	include_once 'page-setup.php';
}

/**
 * [sf_tfp_add_help_tab 				BACK-OFFICE: Adding help block to setting page]
 * @return [NULL] 						[No return]
 */
function sf_tfp_add_help_tab(){
	$screen = get_current_screen();

	$help_array = array(
			array(
				'id'      => 'sf_tfp_help_tab_firsst',
				'title'   => __( 'Connect user templates', SF_TFP_NAME ),
				'content' => '<p>'
								. __( '1. Create a new "tigris" folder in your theme.', SF_TFP_NAME ) . '<br />'
								. __( '2. Add your template to this new folder:', SF_TFP_NAME ) . '<br />'
								. __( '- for the page of the list of vacancies (category-tigrisvacancies.php).', SF_TFP_NAME ) . '<br />'
								. __( '- for the loadable AJAX vacancy block (ajax-tigrisvacancy.php).', SF_TFP_NAME ) . '<br />'
								. __( '- for a single job page (single-tigrisvacancy.php).', SF_TFP_NAME ) . '<br />'
								. __( '- for the form of the answer to the vacancy (form-tigrisvacancy.php).', SF_TFP_NAME ) . '<br />'
								. __( '- for a single branch page (single-tigrisbranch.php).', SF_TFP_NAME ) . '<br />'
								. '<br /><b>' . __( 'P.S.:', SF_TFP_NAME ) . '</b> '
								. __( 'The default templates are located in the plugin folder "templates".', SF_TFP_NAME ) . '<br />'
							. '</p>',
			),
			array(
				'id'      => 'sf_tfp_help_tab_second',
				'title'   => __( 'Form connection via shortcode.', SF_TFP_NAME ),
				'content' => '<p>'
								. __( 'To show the application form for work in your theme template, insert a short code.', SF_TFP_NAME ) . '<br /><br />'
								. __( 'Example:', SF_TFP_NAME ) . '<br />'
								. __( '- outside the vacancy loop', SF_TFP_NAME ) .  ': <b>[tigris_form id="0"]</b> ' . '<br />'
								. __( '- inside the vacancy loop',  SF_TFP_NAME ) . ': <b>[tigris_form]</b>' . '<br />'
								. __( '- in php code',  SF_TFP_NAME ) . ': <b>&lt;?php echo do_shortcode( &prime;[tigris_form id="0"]&prime; ); ?&gt;</b><br />'
							. '</p>',
			),
			array(
				'id'      => 'sf_tfp_help_tab_thirdth',
				'title'   => __( 'Attention', SF_TFP_NAME ),
				'content' => '<p>'
								. __( 'Do not forget to re-save permanent links (Settings -> Permalinks).', SF_TFP_NAME ) . '<br /><br />'
								. __( 'Link to the list of vacancies:', SF_TFP_NAME ) . ' (' . __( 'your site permalink', SF_TFP_NAME ) . ')<b>/vacatures</b><br /><br />'
							. '</p>',
			)
		);

	foreach ( $help_array as $value ) {
		$screen->add_help_tab( $value );
	}
}

/**
 * [sf_tfp_field_input					FRONT-OFFICE: Generated type input setting field]
 * @param  [array] $args 				[Field parameters]
 * @return [string] 					[Print settings fields]
 */
function sf_tfp_field_input( $args ) {
	$val = get_option( $args['options'] );

	switch ( $args['label'] ) {
		case 'password': $type = 'password';
			break;
		case 'email': $type = 'email';
			break;
		case 'phone': $type = 'tel';
			break;
		case 'hidden': $type = 'hidden';
			break;

		default: $type = esc_attr( $args['type'] );
			break;
	}
	?>

	<input
		type="<?php echo $type; ?>"
		name="<?php echo esc_attr( $args['options'] . '[' . $args['label'] . ']' ); ?>"
		value="<?php echo isset( $val[ $args['label'] ] ) ? $val[ $args['label']] :  '' ; ?>"
		size="60"
	/>

	<p class="description" style="font-size: .7em;">
		<?php echo esc_attr( $args['desc'] ); ?>
	</p>

	<?php
}

/**
 * [sf_tfp_field_select				FRONT-OFFICE: Generated type select setting field]
 * @param  [array] $args 				[Field parameters]
 * @return [string] 					[Print settings fields]
 */
function sf_tfp_field_select( $args ) {
	$val = get_option( $args['options'] );
	$pages = get_pages( $args ); ?>

	<select name="<?php echo esc_attr( $args['options'] . '[' . $args['label'] . ']' ); ?>" style="width: 400px">
		<option value="0">— <?php _e( 'Select', SF_TFP_NAME ); ?> —</option>

		<?php foreach ( $pages as $key => $value ) {

			$link = str_replace( '/', '', parse_url( get_permalink( $value->ID ), PHP_URL_PATH ) );
			$select = '';

			if ( $val['redirect'] == $link && $val['redirect'] != NULL ) {
				$select = ' selected';
			} ?>

			<option value="<?php echo $link; ?>"<?php echo $select; ?>><?php echo $value->post_title; ?></option>

		<?php } ?>

	</select>

	<p class="description" style="font-size: .7em;">
		<?php echo esc_attr( $args['desc'] ); ?>
	</p>

	<?php
}

/**
 * [sf_tfp_field_checkbox				FRONT-OFFICE: Generated type checkbox setting field]
 * @param  [array] $args 				[Field parameters]
 * @return [string] 					[Print settings fields]
 */
function sf_tfp_field_checkbox( $args ) {
	$val = get_option( $args['options'] );
	$checked = '';
	if ( isset( $val[ $args['label'] ] ) && $val[ $args['label'] ] ) {
		$checked = 'checked';
	} ?>

	<input
		type="checkbox"
		name="<?php echo esc_attr( $args['options'] . '[' . $args['label'] . ']' ); ?>"
		value="1"
		<?php echo $checked; ?>
	/>

	<p class="description" style="font-size: .7em;">
		<?php echo esc_attr( $args['desc'] ); ?>
	</p>

	<?php
}

/**
 * [sf_tfp_field_textarea				FRONT-OFFICE: Generated textarea setting field]
 * @param  [array] $args 				[Field parameters]
 * @return [string] 					[Print settings fields]
 */
function sf_tfp_field_textarea( $args ) {
	$val = get_option( $args['options'] ); ?>

	<textarea
		name="<?php echo esc_attr( $args['options'] . '[' . $args['label'] . ']' ); ?>"
		rows="5"
		cols="60"><?php echo isset( $val[ $args['label'] ] ) ? $val[ $args['label']] :  '' ; ?></textarea>

	<p class="description" style="font-size: .7em;">
		<?php echo html_entity_decode( $args['desc'] ); ?>
	</p>

	<?php
}

function sf_tfp_field_refreshToken( $args ) {
	$val = get_option( $args['options'] );
	if (isset( $val[ $args['label'] ] ) && $val[ $args['label'] ] != '') {
		echo __( 'Salesforce account is connected', SF_TFP_NAME );
	} else {
		$options = get_option( str_replace( '-', '_', SF_TFP_NAME ) );
		$redirectUrl = home_url( 'tigrisoauth-callback.php' );
		$login_button_text = __('Login with SaleForce', SF_TFP_NAME);
		$saleForceLoginUrl = 'https://login.salesforce.com/services/oauth2/authorize?response_type=code&client_id=' . $options['consumer_key'] . '&redirect_uri=' . $redirectUrl;
		echo '<p class="sale-force-button"><a href="' . $saleForceLoginUrl . '">' . esc_html($login_button_text) . '</a></p>';
	}
}

/**
 * [sf_tfp_add_region_column 			CORE: Update columns list]
 * @param [array] $columns 				[All columns]
 * @return [array] 						[Update columns]
 */
function sf_tfp_add_region_column( $columns ) {
	$num = 4;

	$new_columns = array(
		'region'	=> __( 'Vacancy Region', SF_TFP_NAME ),
		'education'	=> __( 'Vacancy Education', SF_TFP_NAME ),
		'type'		=> __( 'Vacancy Type', SF_TFP_NAME ),
	);

	return array_slice( $columns, 0, $num ) + $new_columns + array_slice( $columns, $num );
}
add_filter( 'manage_vacatures_posts_columns', 'sf_tfp_add_region_column', 4 );

/**
 * [sf_tfp_add_region_value 			BACK-OFFICE: Adding data to columns]
 * @param [string] $colname 			[Current column name]
 * @param [integer] $post_id     		[ID current post]
 * @return [hook]                  		[No return]
 */
function sf_tfp_add_region_value( $colname, $post_id ) {

		$meta_values = get_post_meta( $post_id );

		if ( $colname == 'region' ) {

			if ( isset( $meta_values['tigris__plaats__c'] ) && ! empty( $region = trim( $meta_values['tigris__plaats__c'][0] ) ) ) {
				echo $region;
			} else {
				echo '—';
			}
		}

		if ( $colname == 'type' ) {

			if( isset( $meta_values['tigris__soort_dienstverband__c'] ) && ! empty( $type = trim( $meta_values['tigris__soort_dienstverband__c'][0] ) ) ) {
				echo $type;
			} else {
				echo '—';
			}
		}

		if ( $colname == 'education' ) {

			if( isset( $meta_values['tigris__opleidingsniveau__c'] ) && ! empty( $education = trim( $meta_values['tigris__opleidingsniveau__c'][0] ) ) ) {
				echo $education;
			} else {
				echo '—';
			}
		}
}
add_filter( 'manage_vacatures_posts_custom_column', 'sf_tfp_add_region_value', 5, 2 );

/**
 * [sf_tfp_add_sortable_column 		BACK-OFFICE: Adding sortable to column]
 * @param  [array] $sortable_columns 	[Sortable columns]
 * @return [array] $sortable_columns   	[Update sortable columns]
 */
function sf_tfp_add_sortable_column( $sortable_columns ) {
	$sortable_columns['region'] 	= array( 'views_region', false );
	$sortable_columns['education'] 	= array( 'views_education', false );
	$sortable_columns['type'] 		= array( 'views_type', false );

	return $sortable_columns;
}
add_filter('manage_edit-vacatures_sortable_columns', 'sf_tfp_add_sortable_column');

/**
 * [sf_tfp_add_sortable_column_request BACK-OFFICE: Correcting request to sortable columns]
 * @param  [array] $vars 				[Query options]
 * @return [array] $vars   				[Updated query]
 */
function sf_tfp_add_sortable_column_request( $vars ) {

	if ( is_admin() && isset( $vars['orderby'] ) ) {

		switch ( $vars['orderby'] ) {
			case 'views_region':
				$vars['meta_key'] = 'tigris__plaats__c';
				$vars['orderby']  = 'meta_value';
				break;

			case 'views_education':
				$vars['meta_key'] = 'tigris__opleidingsniveau__c';
				$vars['orderby']  = 'meta_value';
				break;

			case 'views_type':
				$vars['meta_key'] = 'tigris__soort_dienstverband__c';
				$vars['orderby']  = 'meta_value';
				break;

			default:
				break;
		}
	}
	return $vars;
}

add_filter( 'pre_update_option_' . $option_name, function( $value, $old_value ) {
	if (!empty($old_value['refresh_token'])
		&& $old_value['channel'] == $value['channel']
		&& $old_value['consumer_key'] == $value['consumer_key']
		&& $old_value['consumer_secret'] == $value['consumer_secret']) {
			$value['refresh_token'] = $old_value['refresh_token'];
	}
	return $value;
}, 10, 2);
add_filter( 'request', 'sf_tfp_add_sortable_column_request' );