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

namespace App\Models;

use App\Observer\SettingObserver;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Prologue\Alerts\Facades\Alert;

class Setting extends BaseModel
{
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'settings';
	
	protected $fakeColumns = ['value'];
	
	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	protected $primaryKey = 'id';
	
	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var boolean
	 */
	public $timestamps = false;
	
	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	protected $guarded = ['id'];
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['id', 'key', 'name', 'value', 'description', 'field', 'parent_id', 'lft', 'rgt', 'depth', 'active'];
	
	/**
	 * The attributes that should be hidden for arrays
	 *
	 * @var array
	 */
	// protected $hidden = [];
	
	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	// protected $dates = [];
	
	protected $casts = [
		'value' => 'array',
	];
	
	/*
	|--------------------------------------------------------------------------
	| FUNCTIONS
	|--------------------------------------------------------------------------
	*/
	protected static function boot()
	{
		parent::boot();
		
		Setting::observe(SettingObserver::class);
	}
	
	public function getNameHtml()
	{
		$out = '';
		
		$url = url(config('larapen.admin.route_prefix', 'admin') . '/setting/' . $this->id . '/edit');
		$out .= '<a href="' . $url . '">' . $this->name . '</a>';
		
		return $out;
	}
	
	/*
	|--------------------------------------------------------------------------
	| SCOPES
	|--------------------------------------------------------------------------
	*/
	public function scopeActive($builder)
	{
		return $builder->where('active', 1);
	}
	
	/*
	|--------------------------------------------------------------------------
	| ACCESORS
	|--------------------------------------------------------------------------
	*/
	public function getValueAttribute($value)
	{
		// Hide all these fake field content
		$hiddenValues = [
			'recaptcha_public_key',
			'recaptcha_private_key',
			'mail_password',
			'mailgun_secret',
			'mandrill_secret',
			'ses_key',
			'ses_secret',
			'sparkpost_secret',
			'stripe_secret',
			'paypal_username',
			'paypal_password',
			'paypal_signature',
			'facebook_client_id',
			'facebook_client_secret',
			'google_client_id',
			'google_client_secret',
			'google_maps_key',
			'twitter_client_id',
			'twitter_client_secret',
		];
		
		// Get 'value' field value
		if (!is_array($value)) {
			$value = json_decode($value, true);
		}
		
		// Handle 'value' field value
		if (count($value) > 0) {
			// Get Entered values (Or Default values if the Entry doesn't exist)
			if ($this->key == 'app') {
				foreach ($value as $key => $item) {
					if ($key == 'logo') {
						$value['logo'] = str_replace('uploads/', '', $value['logo']);
						if (!Storage::exists($value['logo'])) {
							$value[$key] = config('larapen.core.logo');
						}
					}
					
					if ($key == 'favicon') {
						if (!Storage::exists($value['favicon'])) {
							$value[$key] = config('larapen.core.favicon');
						}
					}
				}
				if (!isset($value['purchase_code'])) {
					$value['purchase_code'] = env('PURCHASE_CODE', '');
				}
				if (!isset($value['name'])) {
					$value['name'] = config('app.name');
				}
				if (!isset($value['logo'])) {
					$value['logo'] = config('larapen.core.logo');
				}
				if (!isset($value['favicon'])) {
					$value['favicon'] = config('larapen.core.favicon');
				}
				if (!isset($value['default_date_format'])) {
					$value['default_date_format'] = config('larapen.core.defaultDateFormat');
				}
				if (!isset($value['default_datetime_format'])) {
					$value['default_datetime_format'] = config('larapen.core.defaultDatetimeFormat');
				}
				if (!isset($value['default_timezone'])) {
					$value['default_timezone'] = config('app.timezone');
				}
			}
			
			if ($this->key == 'style') {
				foreach ($value as $key => $item) {
					if ($key == 'body_background_image') {
						if (!Storage::exists($value['body_background_image'])) {
							$value[$key] = null;
						}
					}
				}
				if (!isset($value['app_skin'])) {
					$value['app_skin'] = 'skin-default';
				}
				if (!isset($value['admin_skin'])) {
					$value['admin_skin'] = 'skin-blue';
				}
			}
			
			if ($this->key == 'listing') {
				if (!isset($value['display_mode'])) {
					$value['display_mode'] = '.grid-view';
				}
				if (!isset($value['items_per_page'])) {
					$value['items_per_page'] = '12';
				}
				if (!isset($value['search_distance_max'])) {
					$value['search_distance_max'] = '500';
				}
				if (!isset($value['search_distance_default'])) {
					$value['search_distance_default'] = '50';
				}
				if (!isset($value['search_distance_interval'])) {
					$value['search_distance_interval'] = '100';
				}
			}
			
			if ($this->key == 'single') {
				if (!isset($value['pictures_limit'])) {
					$value['pictures_limit'] = '5';
				}
				if (!isset($value['tags_limit'])) {
					$value['tags_limit'] = '15';
				}
				if (!isset($value['guests_can_post_ads'])) {
					$value['guests_can_post_ads'] = '1';
				}
				if (!isset($value['guests_can_contact_seller'])) {
					$value['guests_can_contact_seller'] = '1';
				}
				if (!isset($value['simditor_wysiwyg'])) {
					$value['simditor_wysiwyg'] = '1';
				}
			}
			
			if ($this->key == 'seo') {
				if (!isset($value['posts_permalink'])) {
					$value['posts_permalink'] = '{slug}/{id}';
				}
				if (!isset($value['posts_permalink_ext'])) {
					if (is_null($value['posts_permalink_ext'])) {
						$value['posts_permalink_ext'] = '';
					} else {
						$value['posts_permalink_ext'] = '.html';
					}
				}
			}
			
			if ($this->key == 'upload') {
				if (!isset($value['image_types'])) {
					$value['image_types'] = 'jpg,jpeg,gif,png';
				}
				if (!isset($value['file_types'])) {
					$value['file_types'] = 'pdf,doc,docx,word,rtf,rtx,ppt,pptx,odt,odp,wps,jpeg,jpg,bmp,png';
				}
				if (!isset($value['max_file_size'])) {
					$value['max_file_size'] = '2500';
				}
			}
			
			if ($this->key == 'geo_location') {
				if (!isset($value['country_flag_activation'])) {
					$value['country_flag_activation'] = '1';
				}
			}
			
			if ($this->key == 'security') {
				if (!isset($value['login_open_in_modal'])) {
					$value['login_open_in_modal'] = '1';
				}
				if (!isset($value['login_max_attempts'])) {
					$value['login_max_attempts'] = '5';
				}
				if (!isset($value['login_decay_minutes'])) {
					$value['login_decay_minutes'] = '15';
				}
			}
			
			if ($this->key == 'social_link') {
				if (!isset($value['facebook_page_url'])) {
					$value['facebook_page_url'] = '#';
				}
				if (!isset($value['twitter_url'])) {
					$value['twitter_url'] = '#';
				}
				if (!isset($value['google_plus_url'])) {
					$value['google_plus_url'] = '#';
				}
				if (!isset($value['linkedin_url'])) {
					$value['linkedin_url'] = '#';
				}
				if (!isset($value['pinterest_url'])) {
					$value['pinterest_url'] = '#';
				}
			}
			
			if ($this->key == 'other') {
				if (!isset($value['show_tips_messages'])) {
					$value['show_tips_messages'] = '1';
				}
				if (!isset($value['cookie_expiration'])) {
					$value['cookie_expiration'] = '86400';
				}
				if (!isset($value['cache_expiration'])) {
					$value['cache_expiration'] = '1440';
				}
			}
			
			if ($this->key == 'cron') {
				if (!isset($value['unactivated_posts_expiration'])) {
					$value['unactivated_posts_expiration'] = '30';
				}
				if (!isset($value['activated_posts_expiration'])) {
					$value['activated_posts_expiration'] = '90';
				}
				if (!isset($value['archived_posts_expiration'])) {
					$value['archived_posts_expiration'] = '30';
				}
			}
			
			if ($this->key == 'footer') {
				if (!isset($value['show_payment_plugins_logos'])) {
					$value['show_payment_plugins_logos'] = '1';
				}
				if (!isset($value['show_powered_by'])) {
					$value['show_powered_by'] = '1';
				}
			}
			
			// Demo: Secure some Data (Applied for all Entries)
			if (isFromAdminPanel() && isDemo()) {
				foreach ($value as $key => $item) {
					if (!in_array(Request::segment(2), ['password', 'login'])) {
						if (in_array($key, $hiddenValues)) {
							$value[$key] = '************************';
						}
					}
				}
			}
		} else {
			if (isset($this->key)) {
				// Get Default values
				$value = [];
				if ($this->key == 'app') {
					$value['purchase_code'] = env('PURCHASE_CODE', '');
					$value['name'] = config('app.name');
					$value['logo'] = config('larapen.core.logo');
					$value['favicon'] = config('larapen.core.favicon');
					$value['default_date_format'] = config('larapen.core.defaultDateFormat');
					$value['default_datetime_format'] = config('larapen.core.defaultDatetimeFormat');
					$value['default_timezone'] = config('app.timezone');
				}
				if ($this->key == 'style') {
					$value['app_skin'] = 'skin-default';
					$value['admin_skin'] = 'skin-blue';
				}
				if ($this->key == 'listing') {
					$value['display_mode'] = '.grid-view';
					$value['items_per_page'] = '12';
					$value['search_distance_max'] = '500';
					$value['search_distance_default'] = '50';
					$value['search_distance_interval'] = '100';
				}
				if ($this->key == 'single') {
					$value['pictures_limit'] = '5';
					$value['tags_limit'] = '15';
					$value['guests_can_post_ads'] = '1';
					$value['guests_can_contact_seller'] = '1';
					$value['simditor_wysiwyg'] = '1';
				}
				if ($this->key == 'seo') {
					$value['posts_permalink'] = '{slug}/{id}';
					$value['posts_permalink_ext'] = '.html';
				}
				if ($this->key == 'upload') {
					$value['image_types'] = 'jpg,jpeg,gif,png';
					$value['file_types'] = 'pdf,doc,docx,word,rtf,rtx,ppt,pptx,odt,odp,wps,jpeg,jpg,bmp,png';
					$value['max_file_size'] = '2500';
				}
				if ($this->key == 'geo_location') {
					$value['country_flag_activation'] = '1';
				}
				if ($this->key == 'security') {
					$value['login_open_in_modal'] = '1';
					$value['login_max_attempts'] = '5';
					$value['login_decay_minutes'] = '15';
				}
				if ($this->key == 'social_link') {
					$value['facebook_page_url'] = '#';
					$value['twitter_url'] = '#';
					$value['google_plus_url'] = '#';
					$value['linkedin_url'] = '#';
					$value['pinterest_url'] = '#';
				}
				if ($this->key == 'other') {
					$value['show_tips_messages'] = '1';
					$value['cookie_expiration'] = '86400';
					$value['cache_expiration'] = '1440';
				}
				if ($this->key == 'cron') {
					$value['unactivated_posts_expiration'] = '30';
					$value['activated_posts_expiration'] = '90';
					$value['archived_posts_expiration'] = '30';
				}
				if ($this->key == 'footer') {
					$value['show_payment_plugins_logos'] = '1';
					$value['show_powered_by'] = '1';
				}
			}
		}
		
		return $value;
	}
	
	/*
	|--------------------------------------------------------------------------
	| MUTATORS
	|--------------------------------------------------------------------------
	*/
	public function setValueAttribute($value)
	{
		if (!is_array($value)) {
			$value = json_decode($value, true);
		}
		
		// Logo
		if (isset($value['logo'])) {
			$logo = [
				'attribute' => 'logo',
				'path'      => 'app/logo',
				'default'   => config('larapen.core.logo'),
				'width'     => 454,
				'height'    => 80,
				'upsize'    => true,
				'quality'   => 100,
				'filename'  => 'logo-',
				'orientate' => false,
			];
			$value = $this->upload($value, $logo);
		}
		
		// Favicon
		if (isset($value['favicon'])) {
			$favicon = [
				'attribute' => 'favicon',
				'path'      => 'app/ico',
				'default'   => config('larapen.core.favicon'),
				'width'     => 32,
				'height'    => 32,
				'upsize'    => false,
				'quality'   => 100,
				'filename'  => 'ico-',
				'orientate' => false,
			];
			$value = $this->upload($value, $favicon);
		}
		
		// Body Background Image
		if (isset($value['body_background_image'])) {
			$bodyBackgroundImage = [
				'attribute' => 'body_background_image',
				'path'      => 'app/logo',
				'default'   => null,
				'width'     => 2000,
				'height'    => 2000,
				'upsize'    => true,
				'quality'   => 100,
				'filename'  => 'body-background-',
				'orientate' => false,
			];
			$value = $this->upload($value, $bodyBackgroundImage);
		}
		
		// Check and Get Plugins settings vars
		$value = plugin_set_setting_value($value, $this);
		
		$this->attributes['value'] = json_encode($value);
	}
	
	// Set Upload
	private function upload($value, $params)
	{
		$attribute_name = $params['attribute'];
		$destination_path = $params['path'];
		
		// If 'logo' value doesn't exist, don't make the upload and save data
		if (!isset($value[$attribute_name])) {
			return $value;
		}
		
		// If the image was erased
		if (empty($value[$attribute_name])) {
			// Delete the image from disk
			if (isset($this->value) && isset($this->value[$attribute_name])) {
				if (!empty($params['default'])) {
					if (!str_contains($this->value[$attribute_name], $params['default'])) {
						Storage::delete($this->value[$attribute_name]);
					}
				} else {
					Storage::delete($this->value[$attribute_name]);
				}
			}
			
			// Set null in the database column
			$value[$attribute_name] = null;
			
			return $value;
		}
		
		// If a base64 was sent, store it in the db
		if (starts_with($value[$attribute_name], 'data:image')) {
			try {
				// Get file extension
				$extension = (is_png($value[$attribute_name])) ? 'png' : 'jpg';
				
				// Check if 'Auto Orientate' is enabled
				$autoOrientateIsEnabled = false;
				if (isset($params['orientate']) && $params['orientate']) {
					$autoOrientateIsEnabled = exifExtIsEnabled();
				}
				
				// Make the Image
				if ($autoOrientateIsEnabled) {
					$image = Image::make($value[$attribute_name])->orientate()->resize($params['width'], $params['height'], function ($constraint) use ($params) {
						$constraint->aspectRatio();
						if ($params['upsize']) {
							$constraint->upsize();
						}
					})->encode($extension, $params['quality']);
				} else {
					$image = Image::make($value[$attribute_name])->resize($params['width'], $params['height'], function ($constraint) use ($params) {
						$constraint->aspectRatio();
						if ($params['upsize']) {
							$constraint->upsize();
						}
					})->encode($extension, $params['quality']);
				}
			} catch (\Exception $e) {
				Alert::error($e->getMessage())->flash();
				
				$value[$attribute_name] = null;
				
				return $value;
			}
			
			// Generate a filename.
			$filename = uniqid($params['filename']) . '.' . $extension;
			
			// Store the image on disk.
			Storage::put($destination_path . '/' . $filename, $image->stream());
			
			// Save the path to the database
			$value[$attribute_name] = $destination_path . '/' . $filename;
		} else {
			// Check if value is default file
			if (!empty($params['default'])) {
				$isDefaultFile = str_contains($value[$attribute_name], $params['default']);
			} else {
				$isDefaultFile = $value[$attribute_name] == url('/');
			}
			
			// Get, Transform and Save the path to the database
			if ($isDefaultFile) {
				$value[$attribute_name] = null;
			} else {
				$value[$attribute_name] = $destination_path . last(explode($destination_path, $value[$attribute_name]));
			}
		}
		
		return $value;
	}
}
