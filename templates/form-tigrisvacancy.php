<?php
/**
 * @package WordPress
 * @subpackage Tigris Flexplatform
 */

if ( $GLOBALS['att'] != NULL ) {
	$val = '';
	if (isset($GLOBALS['att']['vacancy']) && $GLOBALS['att']['vacancy'] != 0)
		$val = $GLOBALS['att']['vacancy'];
} else {
	$val = get_the_ID();
} ?>
<!-- Default template from Plug-In form-tigrisvacancy.php: BEGIN -->
<form method="post" class="vacancy-form" enctype="multipart/form-data">

	<div style="display: none;">
		<?php wp_nonce_field(); ?>
		<input type="hidden" name="action" value="formvacancies">
		<input type="hidden" name="vacancyID" value="<?php echo $val; ?>">
	</div>

	<p>
		<label>
			<span><?php _e( 'First name', SF_TFP_NAME ); ?></span>
			<span class="vacancy-form__your-name">
				<input type="text"
					name="firstname"
					value=""
					class="vacancy-form__sf-form"
					required
					data-error="<?php _e( 'Enter your name.', SF_TFP_NAME ) ?>">
			</span>
		</label>
	</p>

	<p>
		<label>
			<span><?php _e( 'Last name', SF_TFP_NAME ); ?></span>
			<span class="vacancy-form__lastname">
				<input
					type="text"
					name="lastname"
					value=""
					class="vacancy-form__sf-form"
					required
					data-error="<?php _e( 'Enter the last name.', SF_TFP_NAME ) ?>">
			</span>
		</label>
	</p>

	<p>
		<label>
			<span><?php _e( 'Email', SF_TFP_NAME ); ?></span>
			<span class="vacancy-form__email">
				<input
					type="email"
					name="email"
					value=""
					class="vacancy-form__sf-form"
					required
					data-error="<?php _e( 'Please enter a valid e-mail.', SF_TFP_NAME ) ?>">
			</span>
		</label>
	</p>

	<p>
		<label>
			<span><?php _e( 'Contact phone', SF_TFP_NAME ); ?></span>
			<span class="vacancy-form__phone">
				<input
					type="tel"
					name="phone"
					value=""
					class="vacancy-form__sf-form"
					required
					data-error="<?php _e( 'Please enter a valid phone number.', SF_TFP_NAME ) ?>">
			</span>
		</label>
	</p>

	<p class="add-select">
		<label>
			<span><?php _e( 'Location', SF_TFP_NAME ); ?></span>
			<span class="vacancy-form__location">
				<select
					name="locations[]"
					size="5"
					multiple="1"
					data-error="<?php _e( 'Choose region.', SF_TFP_NAME ) ?>">
						<option disabled><?php _e( 'Choose a location', SF_TFP_NAME ); ?></option>
						<?php echo sf_tfp_generate_locations_option(); ?>
				</select>
			</span>
		</label>
	</p>

	<p class="add-file">
		<label for="file-cv">
			<span class="vacancy-form__your-cv">
				<input
					type="file"
					name="your-cv"
					size="40"
					class="vacancy-form__sf-form"
					id="file-cv"
					required
					data-error="<?php _e( 'Attach your resume.', SF_TFP_NAME ) ?>">
			</span>
			<span data-multiple-caption="{count} files selected"><?php _e( 'CV', SF_TFP_NAME ); ?></span>
			<i class="transition vacancy-form__button" onclick="this.parentNode.childNodes[1].classList.remove( 'required' );"><?php _e( 'Upload', SF_TFP_NAME ); ?></i>
		</label>
	</p>

	<p class="add-file">
		<label for="file-mb">
			<span class="vacancy-form__your-letter">
				<input
					type="file"
					name="your-letter"
					size="40"
					class="vacancy-form__sf-form"
					id="file-mb"
					data-error="<?php _e( 'Attach a letter of recommendation.', SF_TFP_NAME ) ?>">
			</span>
			<span data-multiple-caption="{count} files selected"><?php _e( 'Motivation letter (optional)', SF_TFP_NAME ); ?></span>
			<i class="transition vacancy-form__button"><?php _e( 'Upload', SF_TFP_NAME ); ?></i>
		</label>
	</p>

	<p class="add-comment">
		<label>
			<span><?php _e( 'Note', SF_TFP_NAME ); ?></span>
			<span class="your-message vacancy-form__your-message">
				<textarea
					name="your-message"
					cols="40"
					rows="10"
					class="vacancy-form__sf-form"
					aria-invalid="false"
					data-error="<?php _e( 'Add your comments.', SF_TFP_NAME ) ?>"></textarea>
			</span>
		</label>
	</p>

	<p class="add-terms">
		<label>
			<span>
				<?php _e( 'I agree to the Terms and Conditions', SF_TFP_NAME ); ?>
			</span>
			<span class="vacancy-form__terms">
				<input
					type="checkbox"
					name="terms"
					value="1"
					class="vacancy-form__sf-form"
					required
					data-error="<?php _e( 'Accept the Terms and Conditions of the agreement.', SF_TFP_NAME ) ?>">
			</span>
		</label>
	</p>

	<div>
		<button
			type="submit"
			id="send-form"
			class="x-btn-tigris-send vacancy-btn-tigris-send"
			data-required="<?php _e( 'Fill in all marked fields.', SF_TFP_NAME ); ?>"
			data-load="<?php _e( 'Upload...', SF_TFP_NAME ); ?>">
			<?php _e( 'Send application', SF_TFP_NAME ); ?>
		</button>

		<div class="vacancy-form__ajax-loader"></div>
	</div>
	<div class="vacancy-form__response-output"></div>
</form>
<script>
	<?php
	$redirect_url = get_option( str_replace( '-', '_', SF_TFP_NAME ) );
	if ( $redirect_url['redirect'] ) {
		$current_url  = site_url( $redirect_url['redirect'] );
		$query_url 	  = parse_url( home_url( $_SERVER['REQUEST_URI'] ), PHP_URL_QUERY );
		$full_url 	  = $current_url . ( $query_url ? '/?' . $query_url : '' );
	} else {
		$full_url 	  = 0;
	}
	?>
	var ajaxurl  = '<?php echo site_url() ?>/wp-admin/admin-ajax.php',
		redirect = '<?php echo $full_url; ?>';
</script>
<!-- Default template from Plug-In form-tigrisvacancy.php: END -->