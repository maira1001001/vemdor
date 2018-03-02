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

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Auth\Traits\VerificationTrait;
use Larapen\Admin\app\Http\Controllers\PanelController;
use App\Models\Gender;
use Illuminate\Support\Facades\Input;
use App\Http\Requests\Admin\UserRequest as StoreRequest;
use App\Http\Requests\Admin\UserRequest as UpdateRequest;

class UserController extends PanelController
{
	use VerificationTrait;
	
	public function setup()
	{
		/*
		|--------------------------------------------------------------------------
		| BASIC CRUD INFORMATION
		|--------------------------------------------------------------------------
		*/
		$this->xPanel->setModel('App\Models\User');
		$this->xPanel->setRoute(config('larapen.admin.route_prefix', 'admin') . '/user');
		$this->xPanel->setEntityNameStrings(__t('user'), __t('users'));
		if (!request()->input('order')) {
			$this->xPanel->orderBy('created_at', 'DESC');
		}
		
		// Filters
		// -----------------------
		$this->xPanel->addFilter([
			'name'  => 'id',
			'type'  => 'text',
			'label' => 'ID',
		],
		false,
		function ($value) {
			$this->xPanel->addClause('where', 'id', '=', $value);
		});
		// -----------------------
		$this->xPanel->addFilter([
			'name'  => 'from_to',
			'type'  => 'date_range',
			'label' => __t('Date range'),
		],
		false,
		function ($value) {
			$dates = json_decode($value);
			$this->xPanel->addClause('where', 'created_at', '>=', $dates->from);
			$this->xPanel->addClause('where', 'created_at', '<=', $dates->to);
		});
		// -----------------------
		$this->xPanel->addFilter([
			'name'  => 'name',
			'type'  => 'text',
			'label' => __t('Name'),
		],
		false,
		function ($value) {
			$this->xPanel->addClause('where', 'name', 'LIKE', "%$value%");
		});
		// -----------------------
		$this->xPanel->addFilter([
			'name'  => 'country',
			'type'  => 'select2',
			'label' => __t('Country'),
		],
		getCountries(),
		function ($value) {
			$this->xPanel->addClause('where', 'country_code', '=', $value);
		});
		// -----------------------
		$this->xPanel->addFilter([
			'name'  => 'status',
			'type'  => 'dropdown',
			'label' => __t('Status'),
		], [
			1 => __t('Unactivated'),
			2 => __t('Activated'),
		], function ($value) {
			if ($value == 1) {
				$this->xPanel->addClause('where', 'verified_email', '=', 0);
				$this->xPanel->addClause('orWhere', 'verified_phone', '=', 0);
			}
			if ($value == 2) {
				$this->xPanel->addClause('where', 'verified_email', '=', 1);
				$this->xPanel->addClause('where', 'verified_phone', '=', 1);
			}
		});
		
		
		/*
		|--------------------------------------------------------------------------
		| COLUMNS AND FIELDS
		|--------------------------------------------------------------------------
		*/
		if (request()->segment(2) != 'account') {
			// COLUMNS
			$this->xPanel->addColumn([
				'name'  => 'id',
				'label' => 'ID',
			]);
			$this->xPanel->addColumn([
				'name'  => 'created_at',
				'label' => __t("Date"),
				'type'  => 'datetime',
			]);
			$this->xPanel->addColumn([
				'name'          => 'name',
				'label'         => __t('Name'),
				'type'          => 'model_function',
				'function_name' => 'getNameHtml',
			]);
			$this->xPanel->addColumn([
				'name'  => 'email',
				'label' => __t("Email"),
			]);
			$this->xPanel->addColumn([
				'label'         => __t('Country'),
				'name'          => 'country_code',
				'type'          => 'model_function',
				'function_name' => 'getCountryHtml',
			]);
			$this->xPanel->addColumn([
				'name'          => 'verified_email',
				'label'         => __t("Verified Email"),
				'type'          => 'model_function',
				'function_name' => 'getVerifiedEmailHtml',
			]);
			$this->xPanel->addColumn([
				'name'          => 'verified_phone',
				'label'         => __t("Verified Phone"),
				'type'          => 'model_function',
				'function_name' => 'getVerifiedPhoneHtml',
			]);
			
			// FIELDS
			$this->xPanel->addField([
				'name'       => 'email',
				'label'      => __t('Email'),
				'type'       => 'email',
				'attributes' => [
					'placeholder' => __t('Email'),
				],
			]);
			$this->xPanel->addField([
				'name'       => 'password',
				'label'      => __t('Password'),
				'type'       => 'password',
				'attributes' => [
					'placeholder' => __t('Password'),
				],
			], 'create');
			$this->xPanel->addField([
				'label'             => __t('Gender'),
				'name'              => 'gender_id',
				'type'              => 'select2_from_array',
				'options'           => $this->gender(),
				'allows_null'       => false,
				'wrapperAttributes' => [
					'class' => 'form-group col-md-6',
				],
			]);
			$this->xPanel->addField([
				'name'              => 'name',
				'label'             => __t('Name'),
				'type'              => 'text',
				'attributes'        => [
					'placeholder' => __t('Name'),
				],
				'wrapperAttributes' => [
					'class' => 'form-group col-md-6',
				],
			]);
			$this->xPanel->addField([
				'name'              => 'phone',
				'label'             => __t('Phone'),
				'type'              => 'text',
				'attributes'        => [
					'placeholder' => __t('Phone'),
				],
				'wrapperAttributes' => [
					'class' => 'form-group col-md-6',
				],
			]);
			$this->xPanel->addField([
				'name'              => 'phone_hidden',
				'label'             => __t("Phone hidden"),
				'type'              => 'checkbox',
				'wrapperAttributes' => [
					'class' => 'form-group col-md-6',
					'style' => 'margin-top: 20px;',
				],
			]);
			$this->xPanel->addField([
				'label'             => __t("Country"),
				'name'              => 'country_code',
				'model'             => 'App\Models\Country',
				'entity'            => 'country',
				'attribute'         => 'asciiname',
				'type'              => 'select2',
				'wrapperAttributes' => [
					'class' => 'form-group col-md-6',
				],
			]);
			
			$this->xPanel->addField([
				'name'              => 'user_type_id',
				'label'             => __t("Type"),
				'model'             => 'App\Models\UserType',
				'entity'            => 'userType',
				'attribute'         => 'name',
				'type'              => 'select2',
				'wrapperAttributes' => [
					'class' => 'form-group col-md-6',
				],
			]);
			$this->xPanel->addField([
				'name'              => 'is_admin',
				'label'             => __t("Is admin"),
				'type'              => 'checkbox',
				'wrapperAttributes' => [
					'class' => 'form-group col-md-6',
					'style' => 'margin-top: 20px;',
				],
			]);
			$this->xPanel->addField([
				'name'              => 'verified_email',
				'label'             => __t("Verified Email"),
				'type'              => 'checkbox',
				'wrapperAttributes' => [
					'class' => 'form-group col-md-6',
					'style' => 'margin-top: 20px;',
				],
			]);
			$this->xPanel->addField([
				'name'              => 'verified_phone',
				'label'             => __t("Verified Phone"),
				'type'              => 'checkbox',
				'wrapperAttributes' => [
					'class' => 'form-group col-md-6',
					'style' => 'margin-top: 20px;',
				],
			]);
			$this->xPanel->addField([
				'name'              => 'blocked',
				'label'             => __t("Blocked"),
				'type'              => 'checkbox',
				'wrapperAttributes' => [
					'class' => 'form-group col-md-6',
					'style' => 'margin-top: 20px;',
				],
			]);
			$this->xPanel->addField([
				'name'       => 'ip_addr',
				'label'      => "IP",
				'type'       => 'text',
				'attributes' => [
					'disabled' => true,
				],
			]);
		}
		
		// Check (Encrypt or Skip) the Password
		if (Input::filled('password')) {
			Input::merge(['password' => bcrypt(Input::get('password'))]);
		} else {
			Input::replace(Input::except(['password']));
		}
	}
	
	public function store(StoreRequest $request)
	{
		return parent::storeCrud();
	}
	
	public function update(UpdateRequest $request)
	{
		return parent::updateCrud();
	}
	
	public function account()
	{
		// FIELDS
		$this->xPanel->addField([
			'label'             => __t("Gender"),
			'name'              => 'gender_id',
			'type'              => 'select2_from_array',
			'options'           => $this->gender(),
			'allows_null'       => false,
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'name',
			'label'             => __t("Name"),
			'type'              => 'text',
			'placeholder'       => __t("Name"),
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'email',
			'label'             => __t("Email"),
			'type'              => 'email',
			'placeholder'       => __t("Email"),
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'password',
			'label'             => __t("Password"),
			'type'              => 'password',
			'placeholder'       => __t("Password"),
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'phone',
			'label'             => __t("Phone"),
			'type'              => 'text',
			'placeholder'       => "Phone",
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'phone_hidden',
			'label'             => __t("Phone hidden"),
			'type'              => 'checkbox',
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
				'style' => 'margin-top: 20px;',
			],
		]);
		$this->xPanel->addField([
			'label'             => __t("Country"),
			'name'              => 'country_code',
			'model'             => 'App\Models\Country',
			'entity'            => 'country',
			'attribute'         => 'asciiname',
			'type'              => 'select2',
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'user_type_id',
			'label'             => __t("Type"),
			'model'             => 'App\Models\UserType',
			'entity'            => 'userType',
			'attribute'         => 'name',
			'type'              => 'select2',
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		
		// Get logged user
		if (auth()->check()) {
			return $this->edit(auth()->user()->id);
		} else {
			abort(403, 'Not allowed.');
		}
	}
	
	public function gender()
	{
		$entries = Gender::trans()->get();
		
		return $this->getTranslatedArray($entries);
	}
}
