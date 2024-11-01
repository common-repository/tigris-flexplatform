<?php
/**
 * @package WordPress
 * @subpackage Tigris Flexplatform
 */
$branch_ID = get_the_ID();
get_header(); ?>
<!-- Default template from Plug-In single-tigrisbranch.php: BEGIN -->
	<section class="branch-page__block">

		<?php /** Loop begin */ ?>
		<?php while ( have_posts() ) {
			the_post(); ?>

			<section class="branch-page__box">

				<div class="branch-page__box-left">

					<div class="branch-page__box-title">
						<h1 class="h1">
							<?php
							/** Display field of branch title. */
							the_title(); ?>
						</h1>
					</div>

					<div class="branch-page__box-content">
						<?php
						/** Display field of vacancy description. */
						the_content(); ?>
					</div>

				</div>

				<div class="branch-page__box-right">
					<div class="branch-page__box-wrapper">
						<div class="branch-page__box-image">
							<?php if ( $thumbnail = get_the_post_thumbnail_url( $branch_ID, 'full' ) ) { ?>
								<img src="<?php echo $thumbnail; ?>">
							<?php } ?>
						</div>
						<div class="branch-page__box-address">
							<span class="branch-page__box-zip"></span>
							<p>
								<span class="branch-page__box-email">
									<i></i>
									<?php echo get_post_meta( $branch_ID, 'tigris__test_d', 1 ); ?>
								</span>
								<span class="branch-page__box-phone">
									<i></i>
									<?php echo get_post_meta( $branch_ID, 'tigris__test_e', 1 ); ?>
								</span>
							</p>
						</div>
					</div>
				</div>

			</section>

		<?php } ?>
		<?php /** Loop end */ ?>
	</section>
<!-- Default template from Plug-In single-tigrisbranch.php: END -->
<?php get_footer();