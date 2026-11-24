<?php
/**
 * Title: Competition home page
 * Slug: winrocket/home
 * Categories: winrocket
 * Inserter: true
 * Description: Featured hero, stats, section links, competition rows, how it works and draw results banner.
 *
 * @package winrocket
 */

?>
<!-- wp:winrocket/hero {"align":"full"} /-->

<!-- wp:winrocket/stats {"align":"full"} /-->

<!-- wp:winrocket/section-nav {"align":"full"} /-->

<!-- wp:group {"className":"wr-home-sections","style":{"spacing":{"padding":{"top":"48px","bottom":"24px"},"blockGap":"56px"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group wr-home-sections" style="padding-top:48px;padding-bottom:24px">
	<!-- wp:winrocket/competition-grid {"anchorId":"featured","title":"<?php echo esc_attr__( 'Featured competitions', 'winrocket' ); ?>","subtitle":"<?php echo esc_attr__( 'Start with our most popular prize draws.', 'winrocket' ); ?>","source":"featured","count":4} /-->

	<!-- wp:winrocket/competition-grid {"anchorId":"ending-soon","title":"<?php echo esc_attr__( 'Ending soon', 'winrocket' ); ?>","subtitle":"<?php echo esc_attr__( 'Last chance: these draws are closing first.', 'winrocket' ); ?>","source":"ending","count":8} /-->

	<!-- wp:winrocket/competition-grid {"anchorId":"instant-wins","title":"<?php echo esc_attr__( 'Instant win competitions', 'winrocket' ); ?>","subtitle":"<?php echo esc_attr__( 'Win instant prizes now and still be in the main draw.', 'winrocket' ); ?>","source":"instant","count":4} /-->

	<!-- wp:winrocket/competition-grid {"anchorId":"cars","title":"<?php echo esc_attr__( 'Cars & bikes', 'winrocket' ); ?>","subtitle":"<?php echo esc_attr__( 'Dream cars and motorbikes, or the cash alternative.', 'winrocket' ); ?>","source":"category","category":"cars-bikes","count":4} /-->

	<!-- wp:winrocket/competition-grid {"anchorId":"cash","title":"<?php echo esc_attr__( 'Cash competitions', 'winrocket' ); ?>","subtitle":"<?php echo esc_attr__( 'Tax-free cash prizes at a range of entry prices.', 'winrocket' ); ?>","source":"category","category":"cash","count":4} /-->

	<!-- wp:winrocket/competition-grid {"anchorId":"tech","title":"<?php echo esc_attr__( 'Tech & luxury', 'winrocket' ); ?>","subtitle":"<?php echo esc_attr__( 'Gadgets, gaming and home luxuries.', 'winrocket' ); ?>","source":"category","category":"tech-luxury","count":4} /-->

	<!-- wp:winrocket/competition-grid {"anchorId":"holidays","title":"<?php echo esc_attr__( 'Holidays & experiences', 'winrocket' ); ?>","subtitle":"<?php echo esc_attr__( 'Trips and days out to remember.', 'winrocket' ); ?>","source":"category","category":"holidays-experiences","count":4} /-->

	<!-- wp:group {"className":"wr-how","layout":{"type":"constrained"}} -->
	<div class="wp-block-group wr-how">
		<!-- wp:paragraph {"className":"wr-eyebrow"} -->
		<p class="wr-eyebrow"><?php esc_html_e( 'How it works', 'winrocket' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"className":"wr-section__title"} -->
		<h2 class="wp-block-heading wr-section__title"><?php esc_html_e( 'Three steps to win', 'winrocket' ); ?></h2>
		<!-- /wp:heading -->

		<!-- wp:columns {"className":"wr-steps"} -->
		<div class="wp-block-columns wr-steps">
			<!-- wp:column {"className":"wr-step"} -->
			<div class="wp-block-column wr-step">
				<!-- wp:paragraph {"className":"wr-step__num"} -->
				<p class="wr-step__num">01</p>
				<!-- /wp:paragraph -->
				<!-- wp:heading {"level":3} -->
				<h3 class="wp-block-heading"><?php esc_html_e( 'Choose a competition', 'winrocket' ); ?></h3>
				<!-- /wp:heading -->
				<!-- wp:paragraph -->
				<p><?php esc_html_e( 'Browse prizes, entry prices and draw dates.', 'winrocket' ); ?></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:column -->

			<!-- wp:column {"className":"wr-step"} -->
			<div class="wp-block-column wr-step">
				<!-- wp:paragraph {"className":"wr-step__num"} -->
				<p class="wr-step__num">02</p>
				<!-- /wp:paragraph -->
				<!-- wp:heading {"level":3} -->
				<h3 class="wp-block-heading"><?php esc_html_e( 'Pick your tickets', 'winrocket' ); ?></h3>
				<!-- /wp:heading -->
				<!-- wp:paragraph -->
				<p><?php esc_html_e( 'Choose how many tickets you want and check out securely.', 'winrocket' ); ?></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:column -->

			<!-- wp:column {"className":"wr-step"} -->
			<div class="wp-block-column wr-step">
				<!-- wp:paragraph {"className":"wr-step__num"} -->
				<p class="wr-step__num">03</p>
				<!-- /wp:paragraph -->
				<!-- wp:heading {"level":3} -->
				<h3 class="wp-block-heading"><?php esc_html_e( 'Follow the draw', 'winrocket' ); ?></h3>
				<!-- /wp:heading -->
				<!-- wp:paragraph -->
				<p><?php esc_html_e( 'See your ticket numbers in your account and watch the results.', 'winrocket' ); ?></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:column -->
		</div>
		<!-- /wp:columns -->
	</div>
	<!-- /wp:group -->

	<!-- wp:winrocket/results-cta /-->
</div>
<!-- /wp:group -->
