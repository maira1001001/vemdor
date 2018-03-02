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

class SendMessageRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name'                 => 'required|mb_between:2,200',
            'email'                => 'max:100',
            'phone'                => 'max:20',
            'message'              => 'required|mb_between:20,500',
            'post'                 => 'required|numeric',
            'g-recaptcha-response' => (config('settings.security.recaptcha_activation')) ? 'required' : '',
        ];
    
        // Check 'resume' is required
        if ($this->filled('parentCatType') && in_array($this->input('parentCatType'), ['job-offer'])) {
            $rules['filename'] = 'required|mimes:' . getUploadFileTypes('file') . '|max:' . (int)config('settings.upload.max_file_size', 1000);
        }
    
        // Email
        if ($this->filled('email')) {
            $rules['email'] = 'email|' . $rules['email'];
        }
        if (isEnabledField('email')) {
            if (isEnabledField('phone') && isEnabledField('email')) {
                $rules['email'] = 'required_without:phone|' . $rules['email'];
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
        
        return $rules;
    }
    
    /**
     * @return array
     */
    public function messages()
    {
        $messages = [];
        
        return $messages;
    }
}
