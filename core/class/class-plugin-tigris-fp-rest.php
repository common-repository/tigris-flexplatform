<?php
/**
 * @package WordPress
 * @subpackage Tigris Flexplatform
 */

class Plugin_Tigris_FP_Rest {
	private $_token = '';
	private $_restUrl = '';

	const API_VERSION = '43.0';

	//
	public function __construct() {
		$options = get_option( str_replace( '-', '_', SF_TFP_NAME ) );
		$expired = false;
		if (isset( $_COOKIE['tigris_oauth_access_token'] ) && $_COOKIE['tigris_oauth_access_token']) {
			$args = array (
					'method' => 'GET',
					'timeout' => 50,
					'headers' => array(
						'Authorization' => 'Bearer ' .  $_COOKIE['tigris_oauth_access_token'],
						'Accept' => 'application/json'
					)
				);
			$this->_restUrl = $_COOKIE['tigris_rest_data_url'];
			$url = str_replace('{version}', self::API_VERSION, $this->_restUrl);
			$rersponse = wp_remote_request($url, $args);

			if ( is_wp_error( $rersponse ) || $rersponse['response']['code'] != 200) {
				$expired = true;
			}

			if (!$expired) {
				$this->_token = $_COOKIE['tigris_oauth_access_token'];

				if (isset( $_COOKIE['tigris_rest_data_url'] ) && $_COOKIE['tigris_rest_data_url'])
					$this->_restUrl = $_COOKIE['tigris_rest_data_url'];
			}
		}
		if (!isset( $_COOKIE['tigris_oauth_access_token'] ) || !$_COOKIE['tigris_oauth_access_token'] || $expired) {
			if (!class_exists('TigrisFPSaleforceOAuth'))
				require_once( plugin_dir_path( __FILE__ ) . 'TigrisFPSaleforceOAuth.php' );
			if (isset($options['refresh_token']) && !empty($options['refresh_token'])) {
				list($this->_token, $this->_restUrl) = TigrisFPSaleforceOAuth::sf_tfp_salesforce_get_token();
			}
		}
	}

	/**
	 * @param $fieldsToInsert
	 * @param $sf_table
	 */
	public function insert($fieldsToInsert, $sf_table){
		try {
			$response = false;
			if (!empty($this->_token) && $this->_token && !empty($this->_restUrl)) {
				$args = array (
					'body' => json_encode($fieldsToInsert),
					'method' => 'POST',
					'timeout' => 50,
					'headers' => array(
						'Authorization' => 'Bearer ' .  $this->_token,
						'Content-Type' => 'application/json'
					)
				);

				$url = str_replace('{version}', self::API_VERSION, $this->_restUrl) . 'sobjects/';
				$url .= $sf_table;
				
				$r = wp_remote_request( $url, $args );
				if(!is_wp_error($r) && !empty($r['response']['code']) && $r['response']['code'] == 201)
					$response = json_decode( $r['body'], true );
			}
			return $response;
		} catch (Exception $e) {
			error_log( 'Salesforce error: '.$e );
			return false;
		}
	}

	/**
	 * @param $fieldsToUpdate
	 * @param $id
	 * @param $sf_table
	 */
	public function update($fieldsToUpdate, $sf_id, $sf_table){
		try {
			if (!empty($this->_token) && $this->_token && !empty($this->_restUrl)) {
				$args = array (
					'body' => json_encode($fieldsToUpdate),
					'method' => 'PATCH',
					'timeout' => 50,
					'headers' => array(
						'Authorization' => 'Bearer ' .  $this->_token,
						'Content-Type' => 'application/json',
					)
				);

				$url = str_replace('{version}', self::API_VERSION, $this->_restUrl) . 'sobjects/';
				$url .= $sf_table . '/';
				$url .= $sf_id;

				$r = wp_remote_request( $url, $args );
				return $r;
			}
		} catch (Exception $e) {
			error_log( 'Salesforce error: '.$e );
		}
	}

	public function insert_file($id, $name, $body, $sf_table){
		try {
			if (!empty($this->_token) && $this->_token && !empty($this->_restUrl)) {

				$fieldsToInsert = [
					'Body' => $body,
					'Description' => 'sf',
					'ParentId' => $id,
					'Name' => $name
				];
				$args = array (
					'body' => json_encode($fieldsToInsert),
					'method' => 'POST',
					'timeout' => 50,
					'headers' => array(
						'Authorization' => 'Bearer ' .  $this->_token,
						'Accept' => 'application/json',
						'Content-Type' => 'application/json',
					)
				);

				$url = str_replace('{version}', self::API_VERSION, $this->_restUrl) . 'sobjects/';
				$url .= 'Attachment' . '/';
				$url .= $sf_id;

				$r = wp_remote_request( $url, $args );
				return $r;
			}
		} catch (Exception $e) {
			error_log( 'Salesforce error FILE: '.$e );
		}
	}

	protected function sendRequest() {

	}
}