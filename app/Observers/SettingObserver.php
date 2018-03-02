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

namespace App\Observer;

use App\Models\Post;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingObserver
{
	/**
	 * Listen to the Entry updating event.
	 *
	 * @param  Setting $setting
	 * @return void
	 */
    public function updating(Setting $setting)
	{
		// Get the original object values
		$original = $setting->getOriginal();
		
		if (isset($original['value']) && !empty($original['value'])) {
			if (!is_array($original['value'])) {
				$original['value'] = json_decode($original['value'], true);
			}
			
			// Remove old logo from disk (Don't remove the default logo)
			if (isset($setting->value['logo']) && isset($original['value']['logo'])) {
				if ($setting->value['logo'] != $original['value']['logo']) {
					if (!str_contains($original['value']['logo'], config('larapen.core.logo'))) {
						Storage::delete($original['value']['logo']);
					}
				}
			}
			
			// Remove old favicon from disk (Don't remove the default favicon)
			if (isset($setting->value['favicon']) && isset($original['value']['favicon'])) {
				if ($setting->value['favicon'] != $original['value']['favicon']) {
					if (!str_contains($original['value']['favicon'], config('larapen.core.favicon'))) {
						Storage::delete($original['value']['favicon']);
					}
				}
			}
			
			// Remove old body_background_image from disk
			if (isset($setting->value['body_background_image']) && isset($original['value']['body_background_image'])) {
				if ($setting->value['body_background_image'] != $original['value']['body_background_image']) {
					Storage::delete($original['value']['body_background_image']);
				}
			}
			
			// Enable Posts Approbation by User Admin (Post Review)
			if (isset($setting->value['posts_review_activation'])) {
				// If Post Approbation is enabled, then update all the existing Posts
				if ((int)$setting->value['posts_review_activation'] == 1) {
					Post::where('reviewed', '!=', 1)->update(['reviewed' => 1]);
				}
			}
		}
	}
    
    /**
     * Listen to the Entry saved event.
     *
     * @param  Setting $setting
     * @return void
     */
    public function saved(Setting $setting)
    {
    	// If the Default Country is changed, then clear the 'country_code' from the sessions
		if (isset($setting->value['default_country_code'])) {
			session()->forget('country_code');
			session(['country_code' => $setting->value['default_country_code']]);
		}
	
		// If the Default Listing Mode is changed, then clear the 'listing_display_mode' from the cookies
		// NOTE: The cookie has been set from JavaScript, so we have to provide the good path (may be the good expire time)
		if (isset($setting->value['display_mode'])) {
			$expire = 60 * 24 * 7; // 7 days
			if (isset($_COOKIE['listing_display_mode'])) {
				unset($_COOKIE['listing_display_mode']);
			}
			setcookie('listing_display_mode', $setting->value['display_mode'], $expire, '/');
		}
		
        // Removing Entries from the Cache
        $this->clearCache($setting);
    }
    
    /**
     * Listen to the Entry deleted event.
     *
     * @param  Setting $setting
     * @return void
     */
    public function deleted(Setting $setting)
    {
        // Removing Entries from the Cache
        $this->clearCache($setting);
    }
    
    /**
     * Removing the Entity's Entries from the Cache
     *
     * @param $setting
     */
    private function clearCache($setting)
    {
        Cache::flush();
    }
}
