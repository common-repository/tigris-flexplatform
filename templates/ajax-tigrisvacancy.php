<?php
/**
 * @package WordPress
 * @subpackage Tigris Flexplatform
 */
?>
<!-- Default template from Plug-In ajax-tigrisvacancy.php: BEGIN -->
<div id="post-<?php the_ID(); ?>" <?php post_class( 'vacancies-page__vacancy-block hidden'); ?>>

	<a href="<?php the_permalink() ?>" class="vacancies-page__vacancy-header">
		<h4 class="h4">
			<?php
			/** Display field of vacancy title. */
			the_title(); ?>
		</h4>
	</a>
	<p class="vacancies-page__vacancy-content">
		<?php
		/** Display field of vacancy description. */
		$content = get_the_content();
		$trimmed_content = wp_trim_words( $content, 40, '<a href="'. get_permalink() .'">...</a>' );
		echo $trimmed_content;
		?>
	</p>

	<div class="vacancies-page__vacancy-footer">

		<span>
			<?php
			/** Display field of vacancy description. */
			echo get_post_field( 'sector' ); ?>
		</span>
		<span>
			<?php
			/** Display field of type of work. */
			echo get_post_field( 'type' ); ?>
		</span>
		<span>
			<?php
			/** Display field of published date. */
			echo get_the_date( 'F j, Y' ); ?>
		</span>

	</div>
</div>
<!-- Default template from Plug-In ajax-tigrisvacancy.php: END -->
<?php
/**
 * END: 67;
 */