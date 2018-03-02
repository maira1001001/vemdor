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

use App\Models\Scopes\VerifiedScope;
use App\Models\Traits\CountryTrait;
use App\Observer\UserObserver;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Route;
use Jenssegers\Date\Date;
use Prologue\Alerts\Facades\Alert;

class User extends BaseUser
{
	use CountryTrait, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';
    
    /**
     * The primary key for the model.
     *
     * @var string
     */
    // protected $primaryKey = 'id';
    protected $appends = ['created_at_ta'];
    
    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = true;
    
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'country_code',
        'user_type_id',
        'gender_id',
        'name',
        'about',
        'phone',
        'phone_hidden',
        'email',
        'username',
        'password',
        'remember_token',
        'is_admin',
		'can_be_impersonate',
        'disable_comments',
        'receive_newsletter',
        'receive_advice',
        'ip_addr',
        'provider',
        'provider_id',
		'email_token',
		'phone_token',
		'verified_email',
		'verified_phone',
        'blocked',
        'closed',
    ];
    
    /**
     * The attributes that should be hidden for arrays
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];
    
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'last_login_at', 'deleted_at'];
    
    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    protected static function boot()
    {
        parent::boot();
	
		User::observe(UserObserver::class);

        // Don't apply the ActiveScope when:
        // - User forgot its Password
        // - User changes its Email or Phone
        if (
            !str_contains(Route::currentRouteAction(), 'Auth\ForgotPasswordController') &&
            !str_contains(Route::currentRouteAction(), 'Auth\ResetPasswordController') &&
            !session()->has('emailOrPhoneChanged') &&
			!str_contains(Route::currentRouteAction(), 'Impersonate\Controllers\ImpersonateController')
        ) {
            static::addGlobalScope(new VerifiedScope());
        }
    }

    public function routeNotificationForMail()
    {
        return $this->email;
    }

    public function routeNotificationForNexmo()
    {
		$phone = phoneFormatInt($this->phone, $this->country_code);
		$phone = setPhoneSign($phone, 'nexmo');
	
		return $phone;
    }

    public function routeNotificationForTwilio()
    {
        $phone = phoneFormatInt($this->phone, $this->country_code);
		$phone = setPhoneSign($phone, 'twilio');
        
        return $phone;
    }

    public function sendPasswordResetNotification($token)
    {
        if (Input::filled('email') || Input::filled('phone')) {
            if (Input::filled('email')) {
                $field = 'email';
            } else {
                $field = 'phone';
            }
        } else {
            if (!empty($this->email)) {
                $field = 'email';
            } else {
                $field = 'phone';
            }
        }

        try {
            $this->notify(new ResetPasswordNotification($this, $token, $field));
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }
	
	/**
	 * @return bool
	 */
	public function canImpersonate()
	{
		// Cannot impersonate from Demo website,
		// Non admin users cannot impersonate
		if (isDemo() || $this->is_admin != 1) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @return bool
	 */
	public function canBeImpersonated()
	{
		// Cannot be impersonated from Demo website,
		// Admin users cannot be impersonated,
		// Users with the 'can_be_impersonated' attribute != 1 cannot be impersonated
		if (isDemo() || $this->is_admin == 1 || $this->can_be_impersonated != 1) {
			return false;
		}
		
		return true;
	}

    public function getNameHtml()
    {
    	// Get all the User's attributes
    	$user = self::findOrFail($this->getKey());
    	
		$out = '';
		$out .= '<span style="float:left;">' . $user->name . '</span>';
		$out .= ' ';
		$out .= '<span style="float:right;">';
		if ($user->getKey() == auth()->user()->getAuthIdentifier()) {
			$tooltip = '" data-toggle="tooltip" title="' . t('Cannot impersonate yourself') . '"';
			$out .= '<a class="btn btn-xs btn-danger" ' . $tooltip . '><i class="fa fa-btn fa-lock"></i></a>';
		} else if ($user->is_admin == 1) {
			$tooltip = '" data-toggle="tooltip" title="' . t('Cannot impersonate admin users') . '"';
			$out .= '<a class="btn btn-xs btn-danger" ' . $tooltip . '><i class="fa fa-btn fa-lock"></i></a>';
		} else if (!isVerifiedUser($user)) {
			$tooltip = '" data-toggle="tooltip" title="' . t('Cannot impersonate unactivated users') . '"';
			$out .= '<a class="btn btn-xs btn-danger" ' . $tooltip . '><i class="fa fa-btn fa-lock"></i></a>';
		} else {
			$tooltip = '" data-toggle="tooltip" title="' . t('Impersonate this user') . '"';
			$impersonateUrl = route('impersonate', $this->getKey());
			$out .= '<a class="btn btn-xs btn-default" href="' . $impersonateUrl . '" ' . $tooltip . '><i class="fa fa-btn fa-sign-in"></i></a>';
		}
		$out .= '</span>';
	
		return $out;
    }

    public function getCountryHtml()
    {
        $iconPath = 'images/flags/16/' . strtolower($this->country_code) . '.png';
        if (file_exists(public_path($iconPath))) {
            $out = '';
            $out .= '<a href="' . url('/') . '?d=' . $this->country_code . '" target="_blank">';
            $out .= '<img src="' . url($iconPath) . getPictureVersion() . '" data-toggle="tooltip" title="' . $this->country_code . '">';
            $out .= '</a>';
            return $out;
        } else {
            return $this->country_code;
        }
    }
    
    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id');
    }
    
    public function gender()
    {
        return $this->belongsTo(Gender::class, 'gender_id');
    }
    
    public function messages()
    {
        return $this->hasManyThrough(Message::class, Post::class, 'user_id', 'post_id');
    }
    
    public function savedPosts()
    {
        return $this->belongsToMany(Post::class, 'saved_posts', 'user_id', 'post_id');
    }
    
    public function savedSearch()
    {
        return $this->hasMany(SavedSearch::class, 'user_id');
    }
    
    public function userType()
    {
        return $this->belongsTo(UserType::class, 'user_type_id');
    }
    
    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */
	public function scopeVerified($builder)
	{
		$builder->where(function($query) {
			$query->where('verified_email', 1)->where('verified_phone', 1);
		});
		
		return $builder;
	}
	
	public function scopeUnverified($builder)
	{
		$builder->where(function($query) {
			$query->where('verified_email', 0)->orWhere('verified_phone', 0);
		});
		
		return $builder;
	}
    
    /*
    |--------------------------------------------------------------------------
    | ACCESORS
    |--------------------------------------------------------------------------
    */
    public function getCreatedAtAttribute($value)
    {
        $value = Date::parse($value);
        if (config('timezone.id')) {
            $value->timezone(config('timezone.id'));
        }
        //echo $value->format('l d F Y H:i:s').'<hr>'; exit();
        //echo $value->formatLocalized('%A %d %B %Y %H:%M').'<hr>'; exit(); // Multi-language

        return $value;
    }
    
    public function getUpdatedAtAttribute($value)
    {
        $value = Date::parse($value);
        if (config('timezone.id')) {
            $value->timezone(config('timezone.id'));
        }

        return $value;
    }
    
    public function getLastLoginAtAttribute($value)
    {
        $value = Date::parse($value);
        if (config('timezone.id')) {
            $value->timezone(config('timezone.id'));
        }

        return $value;
    }
    
    public function getDeletedAtAttribute($value)
    {
        $value = Date::parse($value);
        if (config('timezone.id')) {
            $value->timezone(config('timezone.id'));
        }

        return $value;
    }
    
    public function getCreatedAtTaAttribute($value)
    {
        if (!isset($this->attributes['created_at']) and is_null($this->attributes['created_at'])) {
            return null;
        }
	
		Date::setLocale(app()->getLocale());
        $value = Date::parse($this->attributes['created_at']);
        if (config('timezone.id')) {
            $value->timezone(config('timezone.id'));
        }
        $value = $value->ago();

        return $value;
    }

    public function getEmailAttribute($value)
    {
        if (
			isDemo() &&
            Request::segment(2) != 'password'
        ) {
            if (auth()->check()) {
                if (auth()->user()->id != 1) {
                    $value = hideEmail($value);
                }
            }

            return $value;
        } else {
            return $value;
        }
    }
    
    public function getPhoneAttribute($value)
    {
        $countryCode = config('country.code');
        if (isset($this->country_code) && !empty($this->country_code)) {
            $countryCode = $this->country_code;
        }
        
        $value = phoneFormatInt($value, $countryCode);
        
        return $value;
    }
    
    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
