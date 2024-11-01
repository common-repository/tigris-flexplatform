<?php
/**
 * @package WordPress
 * @subpackage Tigris Flexplatform
 */

get_header(); ?>
<!-- Default template from Plug-In category-tigrisvacancy.php: BEGIN -->
	<section class="vacancies-page__block">

		<div class="vacancies-page__title">
			<h1 class="h1">
				<?php echo wp_sprintf( __( 'Search our %s vacancies', SF_TFP_NAME ), wp_count_posts( 'vacatures' )->publish ); ?>
			</h1>
		</div>

			<?php /** Loop begin */ ?>

			<?php
			$args = array(
					'post_type' 	 => 'vacatures',
					'posts_per_page' => 4
				);
			if ( isset( $filters['meta_query'] ) ) {
				$args['meta_query'] = $filters['meta_query'];
			}
			$vacancies = new WP_Query( $args );
			?>

			<div class="vacancies-page__vacancies-block">

				<?php while ( $vacancies->have_posts() ) : $vacancies->the_post(); ?>

					<div id="post-<?php the_ID(); ?>" <?php post_class( 'vacancies-page__vacancy-block'); ?>>

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

				<?php endwhile; ?>

				<?php
				/** AJAX vacations loading */
				if (  $vacancies->max_num_pages > 1 ) { ?>
					<div id="vacancy-load">
						<span data-load="<?php _e( 'Loading...', SF_TFP_NAME ); ?>">
							<?php _e( 'Show more', SF_TFP_NAME ); ?>
						</span>
					</div>
					<script>
					var ajaxurl = '<?php echo site_url() ?>/wp-admin/admin-ajax.php';
					var true_posts = '<?php echo json_encode( $vacancies->query_vars ); ?>';
					var current_page = <?php echo ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1; ?>;
					var max_pages = '<?php echo $vacancies->max_num_pages; ?>';
					</script>
				<?php } ?>

				<?php wp_reset_postdata(); ?>
			</div>

			<?php /** Loop end */ ?>

	</section>
<!-- Default template from Plug-In category-tigrisvacancy.php: END -->
<?php get_footer();