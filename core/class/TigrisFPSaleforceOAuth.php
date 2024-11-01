<?php
/**
 * @package WordPress
 * @subpackage Tigris Flexplatform
 */
class TigrisFPSaleforceOAuth {

	public function __construct() {
		add_action( 'login_form', array( &$this, 'sf_tfp_salesforce_login_form' ) );
		add_action( 'template_redirect', array( &$this, 'sf_tfp_template_redirect' ) );
		add_action( 'wp_logout', array( &$this, 'sf_tfp_tigris_logout' ), 1, 2 );
	}

	/**
	 * wp-login.php with Saleforce specifics 
	 *
	 * @access public
	 * @return void
	 */
	public function sf_tfp_salesforce_login_form() {
		if ( isset( $_GET['tigris-oauth-error'] ) ) {
			$description = !empty($_GET['description']) ? sanitize_text_field($_GET['description']) : __('Error connecting to Saleforce.', SF_TFP_NAME);
			echo '<div style="padding:10px;background-color:#FFDFDD;border:1px solid #ced9ea;border-radius:3px;-webkit-border-radius:3px;-moz-border-radius:3px;"><p style="line-height:1.6em;"><strong>Error!</strong> ' . $description . ' </p></div><br>';
		}
		else if ( isset( $_GET['tigris-domain-error'] ) ) {
			$username = sanitize_text_field($_GET['tigris-oauth-username']);
			echo '<div style="padding:10px;background-color:#FFDFDD;border:1px solid #ced9ea;border-radius:3px;-webkit-border-radius:3px;-moz-border-radius:3px;"><p style="line-height:1.6em;"><strong>Error!</strong> ' . sprintf(__( 'User %s  is not authorised to login.', SF_TFP_NAME ), $username) .' </p></div><br>';
		}
		echo ' <style type="text/css">
			#loginform .sale-force-button {
				padding-bottom: 10px;
			}
			#loginform .sale-force-button a {
				border: 1px solid;
				padding: 10px 10px 10px 25px;
				text-decoration: none;
				background-image: url("'. SF_TFP_ASSETS_URL . 'img/plugin-icon.png");
				background-repeat: no-repeat;
				background-position: left;
			}
			</style>';
		$options = get_option( str_replace( '-', '_', SF_TFP_NAME ) );

		$redirectUrl = home_url( 'tigrisoauth-callback.php' );
		$login_button_text = __('Login with SaleForce', SF_TFP_NAME);
		$saleForceLoginUrl = 'https://login.salesforce.com/services/oauth2/authorize?response_type=code&client_id=' . $options['consumer_key'] . '&redirect_uri=' . $redirectUrl;
		echo '<p class="sale-force-button"><a href="' . $saleForceLoginUrl . '">' . esc_html($login_button_text) . '</a></p>';
	}

	public function sf_tfp_template_redirect() {
		$uri = parse_url($_SERVER['REQUEST_URI']);
		if( strpos( $uri['path'], 'tigrisoauth-callback.php' ) !== false) {
			if ( isset( $_GET['error'] ) ) {
				wp_redirect( wp_login_url() . "?tigris-oauth-error=1&description=" . sanitize_text_field($_GET['error_description'] ));
			}

			$options = get_option( str_replace( '-', '_', SF_TFP_NAME ) );

			$client_id =  $options['consumer_key'];
			$client_secret =  $options['consumer_secret'];
			$redirect_url = home_url( 'tigrisoauth-callback.php' );

			$code = isset($_GET['code']) ? sanitize_text_field($_GET['code']) : null;

			if ($code) {
					//make oauth call
					$oauth_result = wp_remote_post( "https://login.salesforce.com/services/oauth2/token", array(
							'headers' => array('Accept' => 'application/json'),
							'body' => array(
								'grant_type' => 'authorization_code',
								'code' => $code,
								'client_id' => $client_id,
								'client_secret' => $client_secret,
								'redirect_uri' => $redirect_url,
							)
					));
					
					if ( ! is_wp_error( $oauth_result ) && $oauth_result['response']['code'] == 200) {
						$oauth_response = json_decode( $oauth_result['body'], true );
					}
					else {
						wp_redirect( wp_login_url() . "?tigris-oauth-error=1&description=" . sanitize_text_field($_GET['error_description']) );
					}
					if ( isset( $oauth_response['access_token'] ) ) {
						list($oauth_username, $tigris_rest_data_url) = self::validateToken($oauth_response);
						if (!$oauth_username && !is_user_logged_in())
							wp_redirect( wp_login_url() . "?tigris-oauth-error=1" );
						if (is_user_logged_in()) {
							$user = wp_get_current_user();
							wp_redirect('/wp-admin/admin.php?page=sf-tigris-flexplatform');
						} else {
							$user = get_user_by('login', $oauth_username);
							
							if (! isset( $user->ID ) ) {
								$new_user_id = $this->sf_tfp_try_create_user($oauth_username);
								$user = get_user_by('id', $new_user_id);
							}

							if(isset($user->ID)) {
								$is_tigris_oauth_meta_exists = (get_user_meta($user->ID, 'tigris-oauth-user', true) != '');
								if ( ! $is_tigris_oauth_meta_exists ) {
									add_user_meta( $user->ID, 'tigris-oauth-user', true, true);
								}
								
								wp_set_auth_cookie( $user->ID, false );
								wp_redirect( home_url() );
							} else {
								wp_redirect( wp_login_url() . "?tigris-domain-error=1&tigris-oauth-username=" . urlencode($oauth_username) );
							}
						}
					}
					else {
						wp_redirect( wp_login_url() . "?tigris-oauth-error=1" );
					}
			}
			else {
				wp_redirect( wp_login_url() . "?tigris-oauth-error=1" );
			}
		}
	}

	protected function  sf_tfp_try_create_user($username) {
		$user = get_userdatabylogin($username);
		$random_password = wp_generate_password( 12, false );
		$userdata = array(
		    'user_login' =>  $username,
		    'user_email' => $username,
		    'role'   =>  'tigris_author',
		    'user_pass'  =>  $random_password
		);
		$user_id = wp_insert_user( $userdata ) ;
		add_user_meta( $user_id, 'tigris-oauth-user', true, true);
		return $user_id;
	}

	/**
	 * logout method - called from wp_logout action
	 *
	 * @access public
	 * @return void
	 */
	public function sf_tfp_tigris_logout() {
		setcookie( 'tigris_oauth_access_token', '0', 0, COOKIEPATH, COOKIE_DOMAIN );
		setcookie( 'tigris_rest_data_url', '0', 0, COOKIEPATH, COOKIE_DOMAIN );
	}

	public static function sf_tfp_salesforce_get_token() {
		$optionName = str_replace( '-', '_', SF_TFP_NAME );
		$options = get_option( $optionName );
		$result = array();
		$oauth_result = wp_remote_post( "https://login.salesforce.com/services/oauth2/token", array(
				'body' => array(
					'grant_type' => 'refresh_token',
					'refresh_token' => $options['refresh_token'],
					'format' => 'json',
					'client_id' => $options['consumer_key'],
					'client_secret' => $options['consumer_secret'],
				)
		));
		if ( ! is_wp_error( $oauth_result ) && $oauth_result['response']['code'] == 200) {
			$oauth_response = json_decode( $oauth_result['body'], true );
			if ( isset( $oauth_response['access_token'] ) ) {
				list($oauth_username, $tigris_rest_data_url) = self::validateToken($oauth_response);
				$result = array($oauth_response['access_token'], $tigris_rest_data_url);
				if (isset($oauth_response['refresh_token'])) {
					$options['refresh_token'] = $oauth_response['refresh_token'];
					update_option($optionName, $options);
				}
			}
		} else {
			$options['refresh_token'] = '';
			update_option($optionName, $options);
		}
		return $result;
	}

	public static function validateToken($oauth_response) {
		$optionName = str_replace( '-', '_', SF_TFP_NAME );
		$options = get_option( $optionName );
		$oauth_username = $tigris_rest_data_url = false;
		if (isset($oauth_response['id']))
			$idtoken_validation_result = wp_remote_get($oauth_response['id'] . '?format=json', array(
				'headers' => array(
						'Authorization' => 'Bearer ' .  $oauth_response['access_token']
					)
				)
			);
		if( !empty($idtoken_validation_result) && !is_wp_error($idtoken_validation_result) && $idtoken_validation_result['response']['code'] == 200) {
			$idtoken_response = json_decode($idtoken_validation_result['body'], true);
			$oauth_username = $idtoken_response['email'];
			$tigris_rest_data_url = $idtoken_response['urls']['rest'];
			setcookie( 'tigris_oauth_access_token', $oauth_response['access_token'], (time() + ( 86400 * 7)), COOKIEPATH, COOKIE_DOMAIN );
			setcookie( 'tigris_rest_data_url', $tigris_rest_data_url, (time() + ( 86400 * 7)), COOKIEPATH, COOKIE_DOMAIN );
			if (isset($oauth_response['refresh_token'])) {
				$options['refresh_token'] = $oauth_response['refresh_token'];
				update_option($optionName, $options);
			}
			$valid = true;
		}
		return array($oauth_username, $tigris_rest_data_url);
	}
}
?>