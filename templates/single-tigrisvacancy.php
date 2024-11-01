<?php
/**
 * @package WordPress
 * @subpackage Tigris Flexplatform
 */

get_header(); ?>
<!-- Default template from Plug-In single-tigrisvacancy.php: BEGIN -->
	<section class="vacancy-page__block">

		<?php /** Loop begin */ ?>
		<?php while ( have_posts() ) {
			the_post(); ?>

			<section class="vacancy-page__box">

				<div class="vacancy-page__title">
					<h1 class="h1">
						<?php
						/** Display field of vacancy title. */
						the_title(); ?>
					</h1>
				</div>

				<div class="vacancy-page__date">
					<?php
					/** Display field of published date. */
					the_date( 'F j, Y' ); ?>
				</div>

				<div class="vacancy-page__custom-fields">

					<span class="<?php echo mb_strtolower( get_post_field( 'type' ) ); ?>">
						<?php
						/** Display field of type of work. */
						echo get_post_field( 'type' ); ?>
					</span>

					<span class="<?php echo mb_strtolower( get_post_field( 'location' ) ); ?>">
						<?php
						/** Display field of location. */
						echo get_post_field( 'location' ); ?>
					</span>

					<span>
						<?php
						/** Display field of economics sector. */
						echo get_post_field( 'sector' ); ?>
					</span>

				</div>

				<div class="vacancy-page__content">
					<?php
					/** Display field of vacancy description. */
					the_content(); ?>
				</div>

			</section>

		<?php } ?>
		<?php /** Loop end */ ?>
	</section>

	<section class="vacancy-page__feedback">
		<?php
		/** Display feedback form. */
		echo do_shortcode( '[tigris_single_form]' ); ?>
	</section>
<!-- Default template from Plug-In single-tigrisvacancy.php: END -->
<?php get_footer();