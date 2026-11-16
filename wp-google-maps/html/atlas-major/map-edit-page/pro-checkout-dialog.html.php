<?php
if(!defined('ABSPATH'))
	return;
?>
<div class="wpgmza-generic-modal wpgmza-pro-checkout-modal" data-type="pro_checkout" data-am-checkout-state="loading">
	<div class="wpgmza-generic-modal-inner am-checkout" role="dialog" aria-modal="true" aria-labelledby="am-checkout-title">

		<!-- Choose a plan -->
		<div class="am-checkout-step" data-am-checkout-step="plans">
			<div class="am-checkout-head">
				<div class="am-checkout-head-text">
					<span class="am-checkout-badge"><?php _e('PRO', 'wp-google-maps'); ?></span>
					<h2 class="am-checkout-title" id="am-checkout-title"><?php _e('Upgrade to WP Go Maps Pro', 'wp-google-maps'); ?></h2>
					<p class="am-checkout-sub"><?php _e('One-off payment, lifetime licence. Choose how many sites you need.', 'wp-google-maps'); ?></p>
					<a class="am-checkout-rating" href="https://wordpress.org/support/plugin/wp-google-maps/reviews/" target="_blank" rel="noopener">
						<span class="am-checkout-stars" role="img" aria-label="<?php esc_attr_e('Rated 4.8 out of 5', 'wp-google-maps'); ?>">
							<?php for($star = 0; $star < 5; $star++): ?><svg width="15" height="15" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.5l2.9 6 6.6.8-4.9 4.5 1.3 6.5L12 17l-5.9 3.3 1.3-6.5L2.5 9.3l6.6-.8z"/></svg><?php endfor; ?>
						</span>
						<strong>4.8</strong>
						<span><?php _e('3,000+ reviews on WordPress.org', 'wp-google-maps'); ?></span>
						<span class="am-checkout-rating-sep" aria-hidden="true">|</span>
						<span><?php _e('300,000+ active sites', 'wp-google-maps'); ?></span>
					</a>
				</div>
				<button type="button" class="wpgmza-button am-checkout-close" data-action="cancel" aria-label="<?php esc_attr_e('Close', 'wp-google-maps'); ?>">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><line x1="6" y1="6" x2="18" y2="18"/><line x1="18" y1="6" x2="6" y2="18"/></svg>
				</button>
			</div>

			<!-- Regional discount, shown the same way as the wpgmaps.com pricing page -->
			<div class="am-checkout-regional" data-am-checkout-regional hidden>
				<img class="am-checkout-regional-flag" data-am-checkout-regional-flag alt="" />
				<span class="am-checkout-regional-text">
					<strong class="am-checkout-regional-title" data-am-checkout-regional-title></strong>
					<span class="am-checkout-regional-body"><?php _e('We want WP Go Maps Pro to be affordable wherever you are.', 'wp-google-maps'); ?></span>
				</span>
			</div>

			<div class="am-checkout-plans" role="radiogroup" aria-label="<?php esc_attr_e('Plan', 'wp-google-maps'); ?>" data-am-checkout-plans></div>

			<ul class="am-checkout-features">
				<li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg><?php _e('Marker categories and filtering', 'wp-google-maps'); ?></li>
				<li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg><?php _e('Marker listings and store locator', 'wp-google-maps'); ?></li>
				<li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg><?php _e('Directions for your visitors', 'wp-google-maps'); ?></li>
				<li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg><?php _e('Bulk import from CSV, KML and more', 'wp-google-maps'); ?></li>
			</ul>

			<div class="am-checkout-cta-wrap">
				<button type="button" class="am-checkout-cta" data-am-checkout-continue>
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
					<span data-am-checkout-continue-label><?php _e('Continue to secure checkout', 'wp-google-maps'); ?></span>
				</button>
				<p class="am-checkout-note">
					<?php _e('Checkout opens in a new tab. Discount codes can be entered there.', 'wp-google-maps'); ?>
					<a href="https://www.wpgmaps.com/features/?utm_source=plugin&amp;utm_medium=link&amp;utm_campaign=in-plugin-checkout" target="_blank" rel="noopener"><?php _e('See all Pro features', 'wp-google-maps'); ?></a>
				</p>
			</div>
		</div>

		<!-- Loading plans -->
		<div class="am-checkout-step" data-am-checkout-step="loading">
			<div class="am-checkout-head am-checkout-head-end">
				<button type="button" class="wpgmza-button am-checkout-close" data-action="cancel" aria-label="<?php esc_attr_e('Close', 'wp-google-maps'); ?>">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><line x1="6" y1="6" x2="18" y2="18"/><line x1="18" y1="6" x2="6" y2="18"/></svg>
				</button>
			</div>
			<div class="am-checkout-center">
				<span class="am-checkout-spinner" aria-hidden="true"></span>
				<p class="am-checkout-sub"><?php _e('Loading plans...', 'wp-google-maps'); ?></p>
			</div>
		</div>

		<!-- Waiting for payment -->
		<div class="am-checkout-step" data-am-checkout-step="waiting">
			<div class="am-checkout-head am-checkout-head-end">
				<button type="button" class="wpgmza-button am-checkout-close" data-action="cancel" aria-label="<?php esc_attr_e('Close', 'wp-google-maps'); ?>">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><line x1="6" y1="6" x2="18" y2="18"/><line x1="18" y1="6" x2="6" y2="18"/></svg>
				</button>
			</div>
			<div class="am-checkout-center">
				<div class="am-checkout-icon am-checkout-icon-pro">
					<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><line x1="6" y1="15" x2="10" y2="15"/></svg>
				</div>
				<h2 class="am-checkout-title"><?php _e('Complete your payment in the Stripe tab', 'wp-google-maps'); ?></h2>
				<p class="am-checkout-sub">
					<?php _e('You chose', 'wp-google-maps'); ?> <strong data-am-checkout-plan-name></strong>.
					<?php _e('This window updates by itself as soon as your payment is confirmed, so you can leave it open.', 'wp-google-maps'); ?>
				</p>
				<div class="am-checkout-pill">
					<span class="am-checkout-spinner am-checkout-spinner-sm" aria-hidden="true"></span>
					<?php _e('Waiting for payment confirmation', 'wp-google-maps'); ?>
				</div>
				<p class="am-checkout-note">
					<?php _e("Tab didn't open?", 'wp-google-maps'); ?>
					<a href="#" target="_blank" rel="noopener" data-am-checkout-reopen><?php _e('Reopen secure checkout', 'wp-google-maps'); ?></a>
				</p>
				<p class="am-checkout-note">
					<?php _e('Changed your mind?', 'wp-google-maps'); ?>
					<button type="button" class="am-checkout-link-button" data-am-checkout-change-plan><?php _e('Choose a different plan', 'wp-google-maps'); ?></button>
				</p>
			</div>
		</div>

		<!-- Paid -->
		<div class="am-checkout-step" data-am-checkout-step="complete">
			<div class="am-checkout-head">
				<div class="am-checkout-icon am-checkout-icon-ok am-checkout-icon-sm">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
				</div>
				<div class="am-checkout-head-text">
					<h2 class="am-checkout-title"><?php _e("You're all set", 'wp-google-maps'); ?></h2>
					<p class="am-checkout-sub"><?php _e('Payment received for', 'wp-google-maps'); ?> <span data-am-checkout-plan-name></span>. <?php _e('Two quick steps to switch it on.', 'wp-google-maps'); ?></p>
				</div>
				<button type="button" class="wpgmza-button am-checkout-close" data-action="cancel" aria-label="<?php esc_attr_e('Close', 'wp-google-maps'); ?>">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><line x1="6" y1="6" x2="18" y2="18"/><line x1="18" y1="6" x2="6" y2="18"/></svg>
				</button>
			</div>

			<div class="am-checkout-key-wrap" data-am-checkout-licence>
				<label class="am-checkout-label" for="am-checkout-key"><?php _e('Your licence key', 'wp-google-maps'); ?></label>
				<div class="am-checkout-key-row">
					<input type="text" readonly id="am-checkout-key" class="am-checkout-key" data-am-checkout-key />
					<button type="button" class="am-checkout-copy" data-am-checkout-copy>
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/></svg>
						<span data-am-checkout-copy-label><?php _e('Copy', 'wp-google-maps'); ?></span>
					</button>
				</div>
				<p class="am-checkout-licence-saved" data-am-checkout-licence-saved hidden>
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
					<?php _e('Saved on this site. Pro will use it automatically when you activate it.', 'wp-google-maps'); ?>
				</p>
			</div>
			<p class="am-checkout-note am-checkout-pad" data-am-checkout-licence-pending>
				<?php _e('Your licence key is on its way to your email.', 'wp-google-maps'); ?>
			</p>

			<div class="am-checkout-steps">
				<div class="am-checkout-stepcard">
					<span class="am-checkout-stepcard-title"><span class="am-checkout-num">1</span><?php _e('Download Pro', 'wp-google-maps'); ?></span>
					<span class="am-checkout-stepcard-text"><?php _e('Save the Pro add-on zip to your computer.', 'wp-google-maps'); ?></span>
					<a href="#" target="_blank" rel="noopener" class="am-checkout-btn am-checkout-btn-primary" data-am-checkout-download>
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
						<?php _e('Download Pro', 'wp-google-maps'); ?>
					</a>
				</div>
				<div class="am-checkout-stepcard">
					<span class="am-checkout-stepcard-title"><span class="am-checkout-num">2</span><?php _e('Upload and activate', 'wp-google-maps'); ?></span>
					<span class="am-checkout-stepcard-text" data-am-checkout-step-paste><?php _e('Upload the zip, activate it, then paste your licence key.', 'wp-google-maps'); ?></span>
					<span class="am-checkout-stepcard-text" data-am-checkout-step-saved hidden><?php _e('Upload the zip and activate it. Your licence key is already saved.', 'wp-google-maps'); ?></span>
					<a href="#" class="am-checkout-btn am-checkout-btn-soft" data-am-checkout-upload>
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
						<?php _e('Go to Upload Plugin', 'wp-google-maps'); ?>
					</a>
				</div>
			</div>

			<p class="am-checkout-note am-checkout-pad">
				<?php _e('A receipt with your licence key and download link is on its way to your email. You can also find them in', 'wp-google-maps'); ?>
				<a href="#" target="_blank" rel="noopener" data-am-checkout-account><?php _e('My Account', 'wp-google-maps'); ?></a>.
			</p>
		</div>

		<!-- Store unavailable -->
		<div class="am-checkout-step" data-am-checkout-step="error">
			<div class="am-checkout-head am-checkout-head-end">
				<button type="button" class="wpgmza-button am-checkout-close" data-action="cancel" aria-label="<?php esc_attr_e('Close', 'wp-google-maps'); ?>">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><line x1="6" y1="6" x2="18" y2="18"/><line x1="18" y1="6" x2="6" y2="18"/></svg>
				</button>
			</div>
			<div class="am-checkout-center">
				<h2 class="am-checkout-title"><?php _e('The store could not be reached from here', 'wp-google-maps'); ?></h2>
				<p class="am-checkout-sub"><?php _e('You can still buy Pro on our website.', 'wp-google-maps'); ?></p>
				<a href="https://www.wpgmaps.com/purchase-professional-version/?utm_source=plugin&amp;utm_medium=link&amp;utm_campaign=in-plugin-checkout-fallback" target="_blank" rel="noopener" class="am-checkout-btn am-checkout-btn-primary am-checkout-btn-inline"><?php _e('Buy Pro on wpgmaps.com', 'wp-google-maps'); ?></a>
			</div>
		</div>

		<!-- Trust footer -->
		<div class="am-checkout-footer">
			<span class="am-checkout-secure">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
				<?php _e('Secure payment. Your card details never touch your site.', 'wp-google-maps'); ?>
			</span>
			<span class="am-checkout-stripe"><?php _e('Powered by Stripe', 'wp-google-maps'); ?></span>
		</div>
	</div>
</div>
