<?php
/**
 * LaraClassified - Geo Classified Ads CMS
 * Copyright (c) BedigitCom. All Rights Reserved
 *
 * Website: http://www.bedigit.com
 *
 * LICENSE
 * -------
 * This software is furnished under a license and may be used and copied
 * only in accordance with the terms of such license and with the inclusion
 * of the above copyright notice. If you Purchased from Codecanyon,
 * Please read the full License from here - http://codecanyon.net/licenses/standard
 */

namespace App\Providers;

use App\Models\Language;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Jenssegers\Date\Date;

class AppServiceProvider extends ServiceProvider
{
	private $cacheExpiration = 1440; // Cache for 1 day (60 * 24)
	
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		try {
			// Specified key was too long error
			Schema::defaultStringLength(191);
		} catch (\Exception $e) {
			//...
		}
		
		// Force HTTPS protocol
		$this->forceHttps();
		
		// Create setting config var for the default language
		$this->getDefaultLanguage();
		
		// Create config vars from settings table
		$this->createConfigVars();
		
		// Update the config vars
		$this->setConfigVars();
	}
	
	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}
	
	// Force HTTPS protocol
	private function forceHttps()
	{
		if (config('larapen.core.forceHttps') == true) {
			URL::forceScheme('https');
		}
	}
	
	// Create setting config var for the default language
	private function getDefaultLanguage()
	{
		try {
			// Get the DB default language
			$defaultLang = Cache::remember('language.default', $this->cacheExpiration, function () {
				$defaultLang = Language::where('default', 1)->first();
				
				return $defaultLang;
			});
			
			if (!empty($defaultLang)) {
				// Create DB default language settings
				Config::set('applang', $defaultLang->toArray());
				
				// Set dates default locale
				Date::setLocale(config('applang.abbr'));
				setlocale(LC_ALL, config('applang.locale'));
			} else {
				Config::set('applang.abbr', config('app.locale'));
			}
		} catch (\Exception $e) {
			Config::set('applang.abbr', config('app.locale'));
		}
	}
	
	// Create config vars from settings table
	private function createConfigVars()
	{
		// Get some default values
		Config::set('settings.app.purchase_code', config('larapen.core.purchaseCode'));
		Config::set('settings.app.default_date_format', config('larapen.core.defaultDateFormat'));
		Config::set('settings.app.default_datetime_format', config('larapen.core.defaultDatetimeFormat'));
		
		// Check DB connection and catch it
		try {
			// Get all settings from the database
			$settings = Cache::remember('settings.active', $this->cacheExpiration, function () {
				$settings = Setting::where('active', 1)->get();
				
				return $settings;
			});
			
			// Bind all settings to the Laravel config, so you can call them like
			if ($settings->count() > 0) {
				foreach ($settings as $key => $setting) {
					if (count($setting->value) > 0) {
						foreach ($setting->value as $key => $value) {
							if (!empty($value)) {
								Config::set('settings.' . $setting->key . '.' . $key, $value);
							}
						}
					}
				}
			}
		} catch (\Exception $e) {
			Config::set('settings.error', true);
			Config::set('settings.app.logo', config('larapen.core.logo'));
		}
	}
	
	// Update the config vars
	private function setConfigVars()
	{
		// App
		Config::set('app.name', config('settings.app.name'));
		Config::set('app.timezone', config('settings.app.default_timezone', config('app.timezone')));
		// reCAPTCHA
		Config::set('recaptcha.public_key', env('RECAPTCHA_PUBLIC_KEY', config('settings.security.recaptcha_public_key')));
		Config::set('recaptcha.private_key', env('RECAPTCHA_PRIVATE_KEY', config('settings.security.recaptcha_private_key')));
		// Mail
		Config::set('mail.driver', env('MAIL_DRIVER', config('settings.mail.driver')));
		Config::set('mail.host', env('MAIL_HOST', config('settings.mail.host')));
		Config::set('mail.port', env('MAIL_PORT', config('settings.mail.port')));
		Config::set('mail.encryption', env('MAIL_ENCRYPTION', config('settings.mail.encryption')));
		Config::set('mail.username', env('MAIL_USERNAME', config('settings.mail.username')));
		Config::set('mail.password', env('MAIL_PASSWORD', config('settings.mail.password')));
		Config::set('mail.from.address', env('MAIL_FROM_ADDRESS', config('settings.mail.email_sender')));
		Config::set('mail.from.name', env('MAIL_FROM_NAME', config('settings.app.name')));
		// Mailgun
		Config::set('services.mailgun.domain', env('MAILGUN_DOMAIN', config('settings.mail.mailgun_domain')));
		Config::set('services.mailgun.secret', env('MAILGUN_SECRET', config('settings.mail.mailgun_secret')));
		// Mandrill
		Config::set('services.mandrill.secret', env('MANDRILL_SECRET', config('settings.mail.mandrill_secret')));
		// Amazon SES
		Config::set('services.ses.key', env('SES_KEY', config('settings.mail.ses_key')));
		Config::set('services.ses.secret', env('SES_SECRET', config('settings.mail.ses_secret')));
		Config::set('services.ses.region', env('SES_REGION', config('settings.mail.ses_region')));
		// Sparkpost
		Config::set('services.sparkpost.secret', env('SPARKPOST_SECRET', config('settings.mail.sparkpost_secret')));
		// Facebook
		Config::set('services.facebook.client_id', env('FACEBOOK_CLIENT_ID', config('settings.social_auth.facebook_client_id')));
		Config::set('services.facebook.client_secret', env('FACEBOOK_CLIENT_SECRET', config('settings.social_auth.facebook_client_secret')));
		// Google
		Config::set('services.google.client_id', env('GOOGLE_CLIENT_ID', config('settings.social_auth.google_client_id')));
		Config::set('services.google.client_secret', env('GOOGLE_CLIENT_SECRET', config('settings.social_auth.google_client_secret')));
		Config::set('services.googlemaps.key', env('GOOGLE_MAPS_API_KEY', config('settings.other.googlemaps_key')));
		// Meta-tags
		Config::set('meta-tags.title', config('settings.app.slogan'));
		Config::set('meta-tags.open_graph.site_name', config('settings.app.name'));
		Config::set('meta-tags.twitter.creator', config('settings.seo.twitter_username'));
		Config::set('meta-tags.twitter.site', config('settings.seo.twitter_username'));
		
		// Fix unknown public folder (for elFinder)
		Config::set('elfinder.roots.0.path', public_path('uploads'));
		
		// Admin panel
		Config::set('larapen.admin.skin', config('settings.style.admin_skin'));
		Config::set('larapen.admin.default_date_format', config('settings.app.default_date_format'));
		Config::set('larapen.admin.default_datetime_format', config('settings.app.default_datetime_format'));
		if (str_contains(config('settings.show_powered_by'), 'fa')) {
			Config::set('larapen.admin.show_powered_by', str_contains(config('settings.footer.show_powered_by'), 'fa-check-square-o') ? 1 : 0);
		} else {
			Config::set('larapen.admin.show_powered_by', config('settings.footer.show_powered_by'));
		}
	}
}
