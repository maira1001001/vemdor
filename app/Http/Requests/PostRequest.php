<?php
/**
 * LaraClassified - Geo Classified Ads Software
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

namespace App\Http\Requests;

use App\Models\CategoryField;
use App\Models\PostValue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostRequest extends Request
{
    protected $customFields = [];
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
		$rules = [
			'category'     => 'required|not_in:0',
			'post_type'    => 'required|not_in:0',
			'title'        => 'required|mb_between:2,150|whitelist_word_title',
			'description'  => 'required|mb_between:5,6000|whitelist_word',
			'contact_name' => 'required|mb_between:2,200',
			'email'        => 'max:100|whitelist_email|whitelist_domain',
			'phone'        => 'max:20',
			'city'         => 'required|not_in:0',
		];
        
        // CREATE
        if (in_array($this->method(), ['POST', 'CREATE'])) {
            $rules['parent'] = 'required|not_in:0';
            
            // Recaptcha
            if (config('settings.security.recaptcha_activation')) {
                $rules['g-recaptcha-response'] = 'required';
            }
        }
        
        // UPDATE
        // if (in_array($this->method(), ['PUT', 'PATCH', 'UPDATE'])) {}
        
        // COMMON
        
        // Location
        if (in_array(config('country.admin_type'), ['1', '2']) && config('country.admin_field_active') == 1) {
            $rules['admin_code'] = 'required|not_in:0';
        }
        
        // Email
        if ($this->filled('email')) {
            $rules['email'] = 'email|' . $rules['email'];
        }
        if (isEnabledField('email')) {
            if (isEnabledField('phone') && isEnabledField('email')) {
                if (Auth::check()) {
                    $rules['email'] = 'required_without:phone|' . $rules['email'];
                } else {
                    // Email address is required for Guests
                    $rules['email'] = 'required|' . $rules['email'];
                }
            } else {
                $rules['email'] = 'required|' . $rules['email'];
            }
        }
        
        // Phone
		if (config('settings.sms.phone_verification') == 1) {
			if ($this->filled('phone')) {
				$countryCode = $this->input('country', config('country.code'));
				if ($countryCode == 'UK') {
					$countryCode = 'GB';
				}
				$rules['phone'] = 'phone:' . $countryCode . ',mobile|' . $rules['phone'];
			}
		}
        if (isEnabledField('phone')) {
            if (isEnabledField('phone') && isEnabledField('email')) {
                $rules['phone'] = 'required_without:email|' . $rules['phone'];
            } else {
                $rules['phone'] = 'required|' . $rules['phone'];
            }
        }
		
		// Get Category nested IDs
		$catNestedIds = (object)[
			'parentId' => $this->input('parent'),
			'id'       => $this->input('category'),
		];
		
		// Custom Fields
        $this->customFields = CategoryField::getFields($catNestedIds);
        if ($this->customFields->count() > 0) {
            foreach ($this->customFields as $field) {
                $cfRules = '';

                // Check if the field is required
                if ($field->required == 1 && $field->type != 'file') {
                    $cfRules = 'required';
                }

                // Check if the field is an upload type
                if ($field->type == 'file') {
                    $fileExists = false;
                    
                    if ($this->filled('post_id')) {
                        $postValue = PostValue::where('post_id', $this->input('post_id'))->where('field_id', $field->tid)->first();
                        if (!empty($postValue)) {
                            if (trim($postValue->value) != '' && Storage::exists($postValue->value)) {
                                $fileExists = true;
                            }
                        }
                    }
                    
                    if ($field->required == 1) {
                        if (!$fileExists) {
                            $cfRules = 'required';
                        }
                    }
                    
                    $cfRules = (!empty($cfRules)) ? $cfRules . '|' : '';
                    $cfRules = $cfRules . 'mimes:' . getUploadFileTypes('file') . '|max:' . (int)config('settings.upload.max_file_size', 1000);
                }

                $rules['cf.' . $field->tid] = $cfRules;
            }
        }
		
		/*
		 * Tags (Only allow letters, numbers, spaces and ',;_-' symbols)
		 *
		 * Explanation:
		 * [] 	=> character class definition
		 * p{L} => matches any kind of letter character from any language
		 * p{N} => matches any kind of numeric character
		 * _- 	=> matches underscore and hyphen
		 * + 	=> Quantifier — Matches between one to unlimited times (greedy)
		 * /u 	=> Unicode modifier. Pattern strings are treated as UTF-16. Also causes escape sequences to match unicode characters
		 */
		if ($this->filled('tags')) {
			$rules['tags'] = 'regex:/^[\p{L}\p{N} ,;_-]+$/u';
		}
        
        return $rules;
    }
    
    /**
     * @return array
     */
    public function messages()
    {
        $messages = [];
        
        if ($this->customFields->count() > 0) {
            foreach ($this->customFields as $field) {
                // If the field is required
                if ($field->required == 1) {
                    $messages['cf.' . $field->tid . '.required'] = t('The :field is required.', ['field' => mb_strtolower($field->name)]);
                }
                
                // If the field is an upload type
                if ($field->type == 'file') {
                    $messages['cf.' . $field->tid . '.mimes'] = t('The file of :field must be in the good format.', ['field' => mb_strtolower($field->name)]);
                    $messages['cf.' . $field->tid . '.max'] = t('The file of :field may not be greater than :max.', [
                        'field' => mb_strtolower($field->name),
                        'max'   => (int)config('settings.upload.max_file_size', 1000),
                    ]);
                }
            }
        }
        
        return $messages;
    }
}
