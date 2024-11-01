<?php

/**
 * @package WordPress
 * @subpackage Tigris Flexplatform
 */


function sf_tfp_replace_entity($content,$tag)
{
	preg_match('/<'.$tag.'>(.*?)<\/'.$tag.'>/s', $content, $matches);
	if (isset($matches[1]))
	{
		$new_text=htmlspecialchars($matches[1]);
		$content = str_replace( $matches[1] , $new_text, $content);
	}
	return $content;
}
function sf_tfp_save_listener($options)
{
	$secure = false;
	if (!isset($options['channel'])){
		sf_tfp_respond_sf('false');
		//file_put_contents( 'log-channel.txt', date('r') . " : " . time(). ': Cannot save (Channel) ', FILE_APPEND );
		die('cannot save');
	}

	// Get raw post data from Salesforce

	$content = file_get_contents('php://input');



	$content=sf_tfp_replace_entity($content,'Tigris__Vacature_omschrijving__c');
	$content=sf_tfp_replace_entity($content,'Tigris__Introductie__c');
	$content=sf_tfp_replace_entity($content,'Tigris__Geboden_wordt__c');
	$content=sf_tfp_replace_entity($content,'Tigris__Gevraagd_wordt__c');
	$content=sf_tfp_replace_entity($content,'Tigris__Bedrijfsomschrijving__c');
	$content=sf_tfp_replace_entity($content,'Tigris__Valuta__c');

	$xml = simplexml_load_string($content);
	$p = xml_parser_create();
	xml_parse_into_struct($p, $content, $vals, $index);
	xml_parser_free($p);

	$save = false;
	$data = array();

	//Parse XML
	foreach ($vals as $key => $val){

		if (strtolower($val['tag']) == 'organizationid' && strpos($val['value'], $options['channel']) !== false){
			$secure = true;
			$data['organizationid'] = $val['value'];
		}

		if($val['tag'] == 'NOTIFICATION' && $val['type'] == 'open'){
			$id = $key;
		}

		if($val['tag'] == 'NOTIFICATION' && $val['type'] == 'close'){
			unset($id);
		}

		if (isset($id) && $val['tag'] != 'NOTIFICATION'&& $val['tag'] != 'SOBJECT' && $val['type'] != 'cdata'){
			$name = str_replace('sf:', '', strtolower($val['tag']));

			if(isset($id)){
				$data['data'][$id][$name] = $val['value'];
			}

		}
	}

	if ($secure === false){
		sf_tfp_respond_sf('false');
		error_log( 'Access denided' );
		//file_put_contents( 'log-denided.txt', date('r') . " : " . time(). ': Access denided', FILE_APPEND );
		die('cannot save');
	}

	if (!class_exists('Plugin_Tigris_FP_Rest'))
		require_once( plugin_dir_path( __FILE__ ) . 'class/class-plugin-tigris-fp-rest.php' );

	if ($secure) {

		foreach ($data['data'] as $key => $value){

			$value['sf_id'] = $value['id'];

			if ($value['actiontype'] == 'insert') {

				foreach ($value as $k => $v) {
					if($v == 'false' || $v == '')unset($value[$k]);
				}

				// Array for WP
				$post_content = '';
				$post_content .= isset( $value['tigris__vacature_omschrijving__c'] ) ? '<p data-field="tigris__vacature_omschrijving__c">' . $value['tigris__vacature_omschrijving__c']  . '</p>' : '';
				$post_content .= isset( $value['tigris__gevraagd_wordt__c'] ) ? '<p data-field="tigris__gevraagd_wordt__c">' . $value['tigris__gevraagd_wordt__c']  . '</p>' : '';
				$post_content .= isset( $value['tigris__geboden_wordt__c'] ) ? '<p data-field="tigris__geboden_wordt__c">' . $value['tigris__geboden_wordt__c']  . '</p>' : '';

				$post_data = array(
					'post_title'    => wp_strip_all_tags( $value['name'] ),
					'post_content'  => $post_content,
					'post_status'   => 'publish',
					'post_type'     => 'vacatures',
				);

				// update or new Author in Database
				if(isset($value['tigris__owner_name__c']) && !isset( $options['roles_off'] )){
					$post_data['post_author'] = sf_tfp_checked_tigris_author( $value['tigris__owner_name__c'] );
				}
				elseif (!isset( $options['roles_off'])) {
					$post_data['post_author'] = $options['author'];
				}

				if(isset($value['tigris__office__c']) && isset( $options['branch_off'] )){
					unset($value['tigris__office__c']);
				}

				$post_data['meta_input'] = $value;

				// Insert to BD
				$post_id = wp_insert_post( $post_data );

				//check category
				if(isset($value['tigris__branche__c']) && !isset( $options['category_off'] )){
					$cat_name = sf_tfp_crate_vacancies_category( $value['tigris__branche__c'] );
					wp_set_object_terms($post_id, $cat_name, 'tigrisvacancies' );
				}

				$tags = array();

				if(isset($value['tigris__soort_vacature__c'])){
					$tags[] = __( $value['tigris__soort_vacature__c'] , SF_TFP_NAME );
				}
				if(isset($value['tigris__interne_vacature__c'])){
					$tags[] = __( 'interne', SF_TFP_NAME );
				}
				if(isset($value['tigris__plaats__c'])){
					$tags[] = __( $value['tigris__plaats__c'], SF_TFP_NAME );
				}

				if (!empty($tags)){
					sf_tfp_add_tigristag( $post_id, $tags );
				}

				$link =  get_permalink( $post_id);

				$fieldsToUpdate = [
					'Tigris__Vacature_url__c' 		=> $link,
					'Tigris__Extern_vacature_ID__c' => $post_id,
					'Tigris__Geplaatst__c' 			=> true
				];

				$sf_table = 'Tigris__Vacancy__c';

				$rest = new Plugin_Tigris_FP_Rest();
				$rest->update( $fieldsToUpdate, $value['id'], $sf_table );

				$value['vacancy_id'] = $post_id;
				do_action( 'sf_hook_tigris_insert', $value );
			}

			if ($value['actiontype'] == 'update'){
				$args = array(
					'post_type' => 'vacatures',
					'meta_query' => array(
						'relation' => 'OR',
						array(
							'key' => 'sf_id',
							'value' => $value['id']
						)
					)
				);
				$get_post = get_posts( $args );

				if(empty($get_post)){
					foreach ($value as $k => $v) {
						if($v == 'false' || $v == '')unset($value[$k]);
					}
					//new post
					//If the entry was deleted on the site, then we create a new one
					$post_content = '';
					$post_content .= isset( $value['tigris__vacature_omschrijving__c'] ) ? '<p data-field="tigris__vacature_omschrijving__c">' . $value['tigris__vacature_omschrijving__c']  . '</p>' : '';
					$post_content .= isset( $value['tigris__gevraagd_wordt__c'] ) ? '<p data-field="tigris__gevraagd_wordt__c">' . $value['tigris__gevraagd_wordt__c']  . '</p>' : '';
					$post_content .= isset( $value['tigris__geboden_wordt__c'] ) ? '<p data-field="tigris__geboden_wordt__c">' . $value['tigris__geboden_wordt__c']  . '</p>' : '';


					$post_data = array(
						'post_title'    => wp_strip_all_tags( $value['name'] ),
						'post_content'  => $post_content,
						'post_status'   => 'publish',
						'post_type'     => 'vacatures',
					);

					// update or new Author in Database
					if(isset($value['tigris__owner_name__c']) && !isset( $options['roles_off'] )){
						$post_data['post_author'] = sf_tfp_checked_tigris_author( $value['tigris__owner_name__c'] );
					}
					elseif (!isset( $options['roles_off'])) {
						$post_data['post_author'] = $options['author'];
					}


					if(isset($value['tigris__office__c']) && isset( $options['branch_off'] )){
						unset($value['tigris__office__c']);
					}

					$post_data['meta_input'] = $value;

					// Insert to DB
					$post_id = wp_insert_post( $post_data );

					//check category
					if(isset($value['tigris__branche__c']) && !isset( $options['category_off'] )){
						$cat_name = sf_tfp_crate_vacancies_category( $value['tigris__branche__c'] );
						wp_set_object_terms($post_id, $cat_name, 'tigrisvacancies' );
					}

					$tags = array();

					if(isset($value['tigris__soort_vacature__c'])){
						$tags[] = __( $value['tigris__soort_vacature__c'] , SF_TFP_NAME );
					}
					if(isset($value['tigris__interne_vacature__c'])){
						$tags[] = __( 'interne', SF_TFP_NAME );
					}
					if(isset($value['tigris__plaats__c'])){
						$tags[] = __( $value['tigris__plaats__c'], SF_TFP_NAME );
					}

					if (!empty($tags)){
						sf_tfp_add_tigristag( $post_id, $tags );
					}

					$link =  get_permalink( $post_id);

					$fieldsToUpdate = [
						'Tigris__Vacature_url__c' 		=> $link,
						'Tigris__Extern_vacature_ID__c' => $post_id,
						'Tigris__Geplaatst__c' 			=> true
					];

					$sf_table = 'Tigris__Vacancy__c';

					$rest = new Plugin_Tigris_FP_Rest();
					$r = $rest->update( $fieldsToUpdate, $value['id'], $sf_table );
				}
				elseif(!empty($get_post) && isset($value['tigris__geplaatst__c']) && $value['tigris__geplaatst__c'] == 'false'){
					//delete post if checkbox "Op website geplaatst" is false

					$r = wp_delete_post( $get_post[0]->ID, true );
					$meta_values = get_post_meta( $get_post[0]->ID, '' );
					foreach ( $meta_values as $meta_key => $value ) {
						delete_post_meta( $get_post[0]->ID, $meta_key );
					}
				}
				elseif(!empty($get_post) && isset($value['tigris__offline__c']) && $value['tigris__offline__c'] == 'true'){
					//delete post if status offline

					$r = wp_delete_post( $get_post[0]->ID, true );
					$meta_values = get_post_meta( $get_post[0]->ID, '' );
					foreach ( $meta_values as $meta_key => $value ) {
						delete_post_meta( $get_post[0]->ID, $meta_key );
					}
				}
				else {
					foreach ($value as $k => $v) {
						if($v == 'false' || $v == ''){
							delete_post_meta( $get_post[0]->ID, $k );
							unset($value[$k]);
						}
					}

					$post_content = '';
					// $post_content .= isset( $value['tigris__introductie__c'] ) ? '<p data-field="tigris__introductie__c">' . $value['tigris__introductie__c']  . '</p>' : '';
					$post_content .= isset( $value['tigris__vacature_omschrijving__c'] ) ? '<p data-field="tigris__vacature_omschrijving__c">' . $value['tigris__vacature_omschrijving__c']  . '</p>' : '';
					$post_content .= isset( $value['tigris__gevraagd_wordt__c'] ) ? '<p data-field="tigris__gevraagd_wordt__c">' . $value['tigris__gevraagd_wordt__c']  . '</p>' : '';
					$post_content .= isset( $value['tigris__geboden_wordt__c'] ) ? '<p data-field="tigris__geboden_wordt__c">' . $value['tigris__geboden_wordt__c']  . '</p>' : '';

					$post_data_new = array(
						'post_title'    => wp_strip_all_tags( $value['name'] ),
						'post_content'  => $post_content,
						'post_status'   => 'publish',
						'post_type'     => 'vacatures',
					);

					// update or new Author in Database
					if(isset($value['tigris__owner_name__c']) && !isset( $options['roles_off'] )){
						$post_data_new['post_author'] = sf_tfp_checked_tigris_author( $value['tigris__owner_name__c'] );
					}
					elseif (!isset( $options['roles_off'])) {
						$post_data_new['post_author'] = $options['author'];
					}

					//check category
					if(isset($value['tigris__branche__c'])){
						$cat_name = sf_tfp_crate_vacancies_category( $value['tigris__branche__c'] );
					}

					if(isset($cat_name) && !isset( $options['category_off'] )){
						wp_set_object_terms($get_post[0]->ID, $cat_name, 'tigrisvacancies' );
					}
					elseif(isset($cat_name) && isset( $options['category_off'] )) {
						wp_remove_object_terms($get_post[0]->ID, $cat_name, 'tigrisvacancies' );
					}

					if(isset($value['tigris__office__c']) && isset( $options['branch_off'] )){
						unset($value['tigris__office__c']);
					}

					$post_data_new['meta_input'] = $value;

					$post_data_new['ID'] = $get_post[0]->ID;

					$r = wp_update_post( wp_slash( $post_data_new ) );

					$tags = array();

					if(isset($value['tigris__soort_vacature__c'])){
						$tags[] = __( $value['tigris__soort_vacature__c'] , SF_TFP_NAME );
					}
					if(isset($value['tigris__interne_vacature__c'])){
						$tags[] = __( 'interne', SF_TFP_NAME );
					}
					if(isset($value['tigris__plaats__c'])){
						$tags[] = __( $value['tigris__plaats__c'], SF_TFP_NAME );
					}

					sf_tfp_update_tigristag( $get_post[0]->ID, $tags );

					$fieldsToUpdate = [
						'Tigris__Vacature_url__c' => get_permalink( $get_post[0]->ID),
						'Tigris__Extern_vacature_ID__c' => $get_post[0]->ID
					];

					$sf_table = 'Tigris__Vacancy__c';

					$rest = new Plugin_Tigris_FP_Rest();
					$r = $rest->update( $fieldsToUpdate, $value['id'], $sf_table );
				}

				$value['vacancy_id'] = $get_post[0]->ID;
				do_action( 'sf_hook_tigris_update', $value );

			}

			if ($value['actiontype'] == 'delete'){
				$args = array(
					'post_type' => 'vacatures',
					'meta_query' => array(
						'relation' => 'OR',
						array(
							'key' => 'sf_id',
							'value' => $value['id']
						)
					)
				);

				do_action( 'sf_hook_tigris_delete', $value);

				$get_post = get_posts( $args );
				foreach ($get_post as $value){
					wp_delete_post( $value->ID, true );
					$meta_values = get_post_meta( $get_post[0]->ID, '' );
					foreach ( $meta_values as $meta_key => $value ) {
						delete_post_meta( $get_post[0]->ID, $meta_key );
					}
				}
			}
		}
		$retVal = true;
	}

	// Respond
	if ($retVal) {
		sf_tfp_respond_sf('true');
	}else{
		sf_tfp_respond_sf('false');
	}
}

function sf_tfp_respond_sf($tf) {
if ($tf == 'true')
	header("HTTP/1.1 200 OK");

	print '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
  <soapenv:Body>
    <notificationsResponse xmlns="http://soap.sforce.com/2005/09/outbound">
      <Ack>' . $tf . '</Ack>
    </notificationsResponse>
  </soapenv:Body>
</soapenv:Envelope>';
}