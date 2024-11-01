<?php
/**
 * @package WordPress
 * @subpackage Tigris Flexplatform
 */
?>
<div id="post-<?php the_ID(); ?>" <?php post_class( 'tigris-vacancies-page-vacancy-block'); ?>>

	<a href="<?php the_permalink() ?>" class="tigris-vacancies-page-vacancy-header">
		<h4 class="h4">
			<?php
			/** Display field of vacancy title. */
			the_title(); ?>
		</h4>
	</a>
	<p class="tigris-vacancies-page-vacancy-content">
		<?php
		/** Display field of vacancy description. */
		$content = get_the_content();
		$trimmed_content = wp_trim_words( $content, 40, '<a href="'. get_permalink() .'">...</a>' );
		echo $trimmed_content;
		?>
	</p>
	<a href="<?php the_permalink() ?>" class="tigris-vacancies-page-vacancy-header">
		<i class="tigris-vacancies-page-vacancy-view"></i>
		<span><?php _e('View job', SF_TFP_NAME)?></span>
	</a>
	<div class="tigris-vacancies-page-vacancy-footer">
		<span>
			<?php
			/** Display field of economics sector. */
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