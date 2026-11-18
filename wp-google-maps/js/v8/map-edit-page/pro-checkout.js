/**
 * @namespace WPGMZA
 * @module ProCheckout
 * @requires WPGMZA.GenericModal
 */
jQuery(function($) {

	/**
	 * In-plugin Pro checkout (Atlas Major map editor, testing).
	 *
	 * Lists the Pro plans from the WP Go Maps store API, opens Stripe's hosted checkout in a new tab
	 * (no iframe, per the WordPress.org guidelines), then polls the store for the order result and shows
	 * the licence key and download link. Pro is never installed by this plugin; the user uploads it.
	 *
	 * @class WPGMZA.ProCheckout
	 * @param {HTMLElement} element The .wpgmza-pro-checkout-modal element
	 */
	WPGMZA.ProCheckout = function(element){
		this.element = $(element);
		this.apiBase = (this.element.attr('data-store-api') || '').replace(/\/?$/, '/');

		/* Closing the dialog does not stop an order check in progress: it keeps checking in the background
		 * and opens again with the licence key once the payment completes */
		this.modal = WPGMZA.GenericModal.createInstance(this.element);

		this.plans = [];
		this.currency = 'usd';
		this.selectedPlan = false;
		this.plansLoaded = false;
		this.pollTimer = false;
		this.pollStarted = 0;
		this.sessionId = false;

		this.bindEvents();
	}

	WPGMZA.ProCheckout.POLL_INTERVAL = 4000;
	WPGMZA.ProCheckout.POLL_TIMEOUT = 45 * 60 * 1000;
	WPGMZA.ProCheckout.DEFAULT_PLAN = '5-site';

	WPGMZA.ProCheckout.createInstance = function(element){
		return new WPGMZA.ProCheckout(element);
	}

	WPGMZA.ProCheckout.prototype.bindEvents = function(){
		var self = this;

		$(document.body).on('click', '[data-wpgmza-pro-checkout]', function(event){
			event.preventDefault();
			/* Each button that opens the checkout names itself, e.g. data-wpgmza-pro-checkout="editor-toolbar" */
			self.entryPoint = $(this).attr('data-wpgmza-pro-checkout') || '';
			self.open();
		});

		this.element.on('click', '[data-am-checkout-plan]', function(event){
			event.preventDefault();
			self.selectPlan($(this).attr('data-am-checkout-plan'));
		});

		this.element.on('click', '[data-am-checkout-continue]', function(event){
			event.preventDefault();
			if(self.selectedPlan){
				self.buy(self.selectedPlan);
			}
		});

		this.element.on('click', '[data-am-checkout-change-plan]', function(event){
			event.preventDefault();
			self.changePlan();
		});

		this.element.on('click', '[data-am-checkout-copy]', function(event){
			event.preventDefault();
			self.copyKey($(this));
		});

		/* Check straight away when the user comes back from the Stripe tab */
		$(window).on('focus', function(){
			if(self.pollTimer){
				self.pollOrder();
			}
		});
	}

	WPGMZA.ProCheckout.prototype.setStep = function(step){
		this.element.attr('data-am-checkout-state', step);
		this.element.find('[data-am-checkout-step]').hide();
		this.element.find('[data-am-checkout-step="' + step + '"]').show();
	}

	WPGMZA.ProCheckout.prototype.open = function(){
		this.modal.show();

		if(this.sessionId){
			/* A checkout is already in progress, keep showing it */
			this.setStep('waiting');
			return;
		}

		if(this.plansLoaded){
			this.setStep('plans');
			return;
		}

		this.loadPlans();
	}

	WPGMZA.ProCheckout.prototype.request = function(method, endpoint, data){
		var options = {
			method: method,
			url: this.apiBase + endpoint,
			dataType: 'json',
			timeout: 20000,
			xhrFields: { withCredentials: false }
		};

		if(method === 'POST'){
			options.contentType = 'application/json';
			options.data = JSON.stringify(data || {});
		} else if(data){
			options.data = data;
		}

		return $.ajax(options);
	}

	WPGMZA.ProCheckout.prototype.loadPlans = function(){
		var self = this;

		this.setStep('loading');

		this.request('GET', 'plans').done(function(response){
			if(!response || !response.plans || !response.plans.length){
				self.setStep('error');
				return;
			}

			self.plans = response.plans;
			self.currency = response.currency || 'usd';
			self.regional = self.parseRegional(response.regional);
			self.plansLoaded = true;
			self.renderRegional();

			var preferred = self.getPlan(WPGMZA.ProCheckout.DEFAULT_PLAN) ? WPGMZA.ProCheckout.DEFAULT_PLAN : self.plans[0].id;
			self.renderPlans();
			self.selectPlan(preferred);
			self.setStep('plans');
		}).fail(function(){
			self.setStep('error');
		});
	}

	WPGMZA.ProCheckout.prototype.getPlan = function(id){
		for(var i = 0; i < this.plans.length; i++){
			if(this.plans[i].id === id){
				return this.plans[i];
			}
		}
		return false;
	}

	/* Short, translatable card names by plan id (the store's product names are long, e.g. "WP Go Maps v10 - LTD - 1 Site License") */
	WPGMZA.ProCheckout.prototype.getPlanLabel = function(plan){
		var labels = {
			'1-site': WPGMZA.localized_strings.pro_checkout_plan_1site,
			'5-site': WPGMZA.localized_strings.pro_checkout_plan_5site,
			'unlimited': WPGMZA.localized_strings.pro_checkout_plan_unlimited
		};
		return labels[plan.id] || plan.name || plan.id;
	}

	/* Regional discount details from the store, only when a discount applies and the flag is a secure link */
	WPGMZA.ProCheckout.prototype.parseRegional = function(regional){
		if(!regional || !(parseInt(regional.discount, 10) > 0)){
			return false;
		}
		return {
			name: String(regional.name || regional.country || ''),
			discount: parseInt(regional.discount, 10),
			flag: (regional.flag_url && /^https:\/\//.test(regional.flag_url)) ? regional.flag_url : false
		};
	}

	WPGMZA.ProCheckout.prototype.fill = function(template, values){
		var i = 0;
		return String(template).replace(/%(\d)\$s|%s/g, function(match, position){
			return position ? values[parseInt(position, 10) - 1] : values[i++];
		}).replace(/%%/g, '%');
	}

	/* Banner at the top of the plans, matching the wpgmaps.com pricing page */
	WPGMZA.ProCheckout.prototype.renderRegional = function(){
		var banner = this.element.find('[data-am-checkout-regional]');

		if(!this.regional){
			banner.prop('hidden', true);
			return;
		}

		var flag = banner.find('[data-am-checkout-regional-flag]');
		if(this.regional.flag){
			flag.attr({ src: this.regional.flag, alt: this.regional.name }).show().off('error').on('error', function(){ $(this).hide(); });
		} else {
			flag.hide();
		}

		banner.find('[data-am-checkout-regional-title]').text(this.fill(WPGMZA.localized_strings.pro_checkout_regional_title, [this.regional.discount, this.regional.name]));
		banner.prop('hidden', false);
	}

	/* The price the customer pays: the regional price when it is lower than the list price */
	WPGMZA.ProCheckout.prototype.getPayCents = function(plan){
		var list = parseInt(plan.price_cents, 10) || 0;
		var regional = parseInt(plan.regional_price_cents, 10) || 0;
		return (regional && regional < list) ? regional : list;
	}

	WPGMZA.ProCheckout.prototype.formatPrice = function(cents){
		var amount = (parseInt(cents, 10) || 0) / 100;
		try {
			return new Intl.NumberFormat(undefined, { style: 'currency', currency: (this.currency || 'USD').toUpperCase(), minimumFractionDigits: amount % 1 ? 2 : 0 }).format(amount);
		} catch(e) {
			return '$' + amount.toFixed(amount % 1 ? 2 : 0);
		}
	}

	/* Line under each price: per-site value for 5 sites, rounded up to a whole amount so it never overstates the saving */
	WPGMZA.ProCheckout.prototype.getPlanSubtitle = function(plan, payCents){
		if(plan.id === '5-site' && payCents > 0){
			return this.fill(WPGMZA.localized_strings.pro_checkout_per_site, [this.formatPrice((Math.floor(payCents / 5 / 100) + 1) * 100)]);
		}
		if(plan.id === 'unlimited'){
			return WPGMZA.localized_strings.pro_checkout_unlimited_sub;
		}
		return WPGMZA.ProCheckout.strings.one_off;
	}

	WPGMZA.ProCheckout.prototype.renderPlans = function(){
		var self = this;
		var container = this.element.find('[data-am-checkout-plans]').empty();

		this.plans.forEach(function(plan){
			var pay = self.getPayCents(plan);
			var list = parseInt(plan.price_cents, 10) || 0;

			/* Text is set with .text() so nothing from the API is ever treated as HTML */
			var card = $('<button type="button" role="radio" aria-checked="false" class="am-checkout-plan"></button>').attr('data-am-checkout-plan', plan.id);

			if(plan.id === '5-site'){
				card.append($('<span class="am-checkout-plan-popular"></span>').text(WPGMZA.localized_strings.pro_checkout_most_popular));
			}

			var top = $('<span class="am-checkout-plan-top"></span>');
			top.append($('<span class="am-checkout-plan-name"></span>').text(self.getPlanLabel(plan)));
			top.append('<span class="am-checkout-plan-dot" aria-hidden="true"></span>');
			card.append(top);

			var price = $('<span class="am-checkout-plan-price"></span>');
			if(pay < list){
				price.append($('<s></s>').text(self.formatPrice(list)));
				price.append(' ');
			}
			price.append($('<span></span>').text(self.formatPrice(pay)));
			card.append(price);

			card.append($('<span class="am-checkout-plan-billing"></span>').text(self.getPlanSubtitle(plan, pay)));

			/* The banner already names the discount, so each card just says what it saves */
			if(pay < list){
				card.append($('<span class="am-checkout-plan-save"></span>').text(self.fill(WPGMZA.localized_strings.pro_checkout_you_save, [self.formatPrice(list - pay)])));
			}

			container.append(card);
		});
	}

	WPGMZA.ProCheckout.prototype.selectPlan = function(id){
		var plan = this.getPlan(id);
		if(!plan){
			return;
		}

		this.selectedPlan = id;

		this.element.find('[data-am-checkout-plan]').each(function(){
			var on = $(this).attr('data-am-checkout-plan') === id;
			$(this).toggleClass('is-selected', on).attr('aria-checked', on ? 'true' : 'false');
		});

		this.element.find('[data-am-checkout-continue-label]').text(WPGMZA.ProCheckout.strings.continue_to + ' · ' + this.formatPrice(this.getPayCents(plan)));
		this.element.find('[data-am-checkout-plan-name]').text(WPGMZA.ProCheckout.strings.pro_prefix + ' ' + this.getPlanLabel(plan));
	}

	WPGMZA.ProCheckout.prototype.buy = function(planId){
		var self = this;

		/* Open the tab inside the click, so popup blockers allow it, then point it at Stripe once we have the URL */
		var stripeTab = window.open('', '_blank');
		var button = this.element.find('[data-am-checkout-continue]').prop('disabled', true);

		this.request('POST', 'checkout', {
			plan: planId,
			site_url: window.location.origin,
			return_url: window.location.href,
			source_build: this.element.attr('data-source-build') || '',
			entry_point: this.entryPoint || '',
			plugin_version: this.element.attr('data-plugin-version') || ''
		}).done(function(response){
			/* Only open a secure checkout address (same rule as the download and My Account links) */
			if(!response || !response.url || !response.session_id || !/^https:\/\//i.test(response.url)){
				if(stripeTab){ stripeTab.close(); }
				self.setStep('error');
				return;
			}

			self.sessionId = response.session_id;
			self.stripeTab = stripeTab;
			self.element.find('[data-am-checkout-reopen]').attr('href', response.url);

			if(stripeTab){
				stripeTab.location = response.url;
			}

			self.setStep('waiting');
			self.startPolling();
		}).fail(function(){
			if(stripeTab){ stripeTab.close(); }
			self.setStep('error');
		}).always(function(){
			button.prop('disabled', false);
		});
	}

	/* Leaves the waiting screen so a different plan can be chosen. Checks the order once more first, so a
	 * payment that has just gone through is shown rather than dropped. An abandoned Stripe checkout simply
	 * expires on Stripe's side */
	WPGMZA.ProCheckout.prototype.changePlan = function(){
		var self = this;
		var sessionId = this.sessionId;
		var button = this.element.find('[data-am-checkout-change-plan]').prop('disabled', true);

		var backToPlans = function(){
			self.stopPolling();
			self.sessionId = false;

			try {
				if(self.stripeTab && !self.stripeTab.closed){
					self.stripeTab.close();
				}
			} catch(e) {}
			self.stripeTab = false;

			self.setStep(self.plansLoaded ? 'plans' : 'loading');
			if(!self.plansLoaded){
				self.loadPlans();
			}
		};

		if(!sessionId){
			button.prop('disabled', false);
			backToPlans();
			return;
		}

		this.request('GET', 'order', { session_id: sessionId }).done(function(response){
			if(response && response.status === 'complete'){
				self.stopPolling();
				self.showComplete(response);
				return;
			}
			backToPlans();
		}).fail(backToPlans).always(function(){
			button.prop('disabled', false);
		});
	}

	WPGMZA.ProCheckout.prototype.startPolling = function(){
		var self = this;
		this.stopPolling();
		this.pollStarted = Date.now();
		this.pollTimer = setInterval(function(){
			self.pollOrder();
		}, WPGMZA.ProCheckout.POLL_INTERVAL);
	}

	WPGMZA.ProCheckout.prototype.stopPolling = function(){
		if(this.pollTimer){
			clearInterval(this.pollTimer);
			this.pollTimer = false;
		}
	}

	WPGMZA.ProCheckout.prototype.pollOrder = function(){
		var self = this;

		if(!this.sessionId){
			return;
		}

		if(Date.now() - this.pollStarted > WPGMZA.ProCheckout.POLL_TIMEOUT){
			/* Give up quietly, the receipt email still carries the key and download link */
			this.stopPolling();
			return;
		}

		if(this.pollInFlight){
			return;
		}

		this.pollInFlight = true;

		this.request('GET', 'order', { session_id: this.sessionId }).done(function(response){
			if(response && response.status === 'complete'){
				self.stopPolling();
				self.showComplete(response);
			}
		}).always(function(){
			self.pollInFlight = false;
		});
	}

	WPGMZA.ProCheckout.prototype.showComplete = function(order){
		var hasKey = !!order.api_key;

		this.element.find('[data-am-checkout-licence]').toggle(hasKey);
		this.element.find('[data-am-checkout-licence-pending]').toggle(!hasKey);
		this.element.find('[data-am-checkout-key]').val(hasKey ? order.api_key : '');

		var download = this.element.find('[data-am-checkout-download]');
		if(order.download_url && /^https:\/\//.test(order.download_url)){
			download.attr('href', order.download_url).show();
		} else {
			download.hide();
		}

		var account = this.element.find('[data-am-checkout-account]');
		if(order.my_account_url && /^https:\/\//.test(order.my_account_url)){
			account.attr('href', order.my_account_url);
		} else {
			account.attr('href', 'https://www.wpgmaps.com/my-account/');
		}

		this.element.find('[data-am-checkout-upload]').attr('href', (WPGMZA.adminurl || '') + 'plugin-install.php?tab=upload');

		this.showLicenceSaved(false);
		if(hasKey){
			this.saveLicence(order.api_key);
		}

		this.sessionId = false;
		this.setStep('complete');
		this.modal.show();
	}

	/* Stores the licence key on this site, so Pro finds it when it is activated. If it can't be
	 * saved (for example the site already has a different key), the screen keeps asking the
	 * customer to paste it, exactly as before */
	WPGMZA.ProCheckout.prototype.saveLicence = function(key){
		var self = this;

		if(!WPGMZA.restAPI){
			return;
		}

		WPGMZA.restAPI.call('/pro-checkout-licence/', {
			method: 'POST',
			data: { licence_key: key },
			success: function(response){
				self.showLicenceSaved(!!(response && response.saved));
			},
			error: function(){
				self.showLicenceSaved(false);
			}
		});
	}

	WPGMZA.ProCheckout.prototype.showLicenceSaved = function(saved){
		this.element.find('[data-am-checkout-licence-saved]').prop('hidden', !saved);
		this.element.find('[data-am-checkout-step-saved]').prop('hidden', !saved);
		this.element.find('[data-am-checkout-step-paste]').prop('hidden', saved);
	}

	WPGMZA.ProCheckout.prototype.copyKey = function(button){
		var input = this.element.find('[data-am-checkout-key]');
		var label = button.find('[data-am-checkout-copy-label]');
		var value = input.val();
		var done = function(){
			label.text(WPGMZA.ProCheckout.strings.copied);
			setTimeout(function(){ label.text(WPGMZA.ProCheckout.strings.copy); }, 1500);
		};

		if(navigator.clipboard && navigator.clipboard.writeText){
			navigator.clipboard.writeText(value).then(done, function(){
				input.trigger('select');
			});
		} else {
			input.trigger('select');
			try { document.execCommand('copy'); done(); } catch(e) {}
		}
	}

	/* Translatable UI strings (WPGMZA\Strings) */
	WPGMZA.ProCheckout.strings = {
		one_off: WPGMZA.localized_strings.pro_checkout_one_off,
		continue_to: WPGMZA.localized_strings.pro_checkout_continue,
		pro_prefix: WPGMZA.localized_strings.pro_checkout_pro_prefix,
		copy: WPGMZA.localized_strings.pro_checkout_copy,
		copied: WPGMZA.localized_strings.pro_checkout_copied
	};

	$(function(){
		var element = $('.wpgmza-pro-checkout-modal');
		if(element.length && element.attr('data-store-api')){
			WPGMZA.proCheckout = WPGMZA.ProCheckout.createInstance(element.get(0));
		}
	});

});
