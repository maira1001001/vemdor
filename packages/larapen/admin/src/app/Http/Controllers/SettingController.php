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

/*
------------------------------------------------------------------------------------
The "field" field value for "settings" table
------------------------------------------------------------------------------------
text            => {"name":"value","label":"Value","type":"text"}
textarea        => {"name":"value","label":"Value","type":"textarea"}
checkbox        => {"name":"value","label":"Activation","type":"checkbox"}
upload (image)  => {"name":"value","label":"Value","type":"image","upload":"true","disk":"uploads","default":"images/logo@2x.png"}
selectbox       => {"name":"value","label":"Value","type":"select_from_array","options":OPTIONS}
                => {"default":"Default","blue":"Blue","yellow":"Yellow","green":"Green","red":"Red"}
                => {"smtp":"SMTP","mailgun":"Mailgun","mandrill":"Mandrill","ses":"Amazon SES","mail":"PHP Mail","sendmail":"Sendmail"}
                => {"sandbox":"sandbox","live":"live"}
------------------------------------------------------------------------------------
*/

namespace Larapen\Admin\app\Http\Controllers;

use App\Http\Requests\Admin\SettingRequest as StoreRequest;
use App\Http\Requests\Admin\SettingRequest as UpdateRequest;

class SettingController extends PanelController
{
	public function __construct()
	{
		parent::__construct();
		
		/*
		|--------------------------------------------------------------------------
		| BASIC CRUD INFORMATION
		|--------------------------------------------------------------------------
		*/
		$this->xPanel->setModel('App\Models\Setting');
		$this->xPanel->setEntityNameStrings(__t('general setting'), __t('general settings'));
		$this->xPanel->setRoute(config('larapen.admin.route_prefix', 'admin') . '/setting');
		$this->xPanel->enableReorder('name', 1);
		$this->xPanel->allowAccess(['reorder']);
		$this->xPanel->denyAccess(['create', 'delete']);
		$this->xPanel->setDefaultPageLength(100);
		if (!request()->input('order')) {
			$this->xPanel->orderBy('lft', 'ASC');
			$this->xPanel->orderBy('id', 'ASC');
		}
		
		/*
		|--------------------------------------------------------------------------
		| COLUMNS AND FIELDS
		|--------------------------------------------------------------------------
		*/
		// COLUMNS
		$this->xPanel->addColumn([
			'name'          => 'name',
			'label'         => "Setting",
			'type'          => "model_function",
			'function_name' => 'getNameHtml',
		]);
		$this->xPanel->addColumn([
			'name'  => 'description',
			'label' => "",
		]);
		
		// FIELDS
		/*
		$this->xPanel->addField([
			'name'       => 'name',
			'label'      => 'Section',
			'type'       => 'text',
			'attributes' => [
				'disabled' => 'disabled',
			],
		]);
		*/
	}
	
	/**
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function index()
	{
		// if view_table_permission is false, abort
		$this->xPanel->hasAccessOrFail('list');
		$this->xPanel->addClause('where', 'active', 1);
		
		$this->data['entries'] = $this->xPanel->getEntries();
		$this->data['xPanel'] = $this->xPanel;
		$this->data['title'] = ucfirst($this->xPanel->entity_name_plural);
		
		return view('admin::panel.list', $this->data);
	}
	
	public function store(StoreRequest $request)
	{
		return parent::storeCrud();
	}
	
	/**
	 * @param $id
	 * @param null $childId
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function edit($id, $childId = null)
	{
		$this->xPanel->hasAccessOrFail('update');
		
		if (!empty($childId)) {
			$id = $childId;
		}
		
		$this->data['entry'] = $this->xPanel->getEntry($id);
		
		// Add the 'field' field
		$fieldColValue = json_decode($this->data['entry']->field, true);
		$this->addField($fieldColValue);
		
		// ...
		$this->data['xPanel'] = $this->xPanel;
		$this->data['saveAction'] = $this->getSaveAction();
		$this->data['fields'] = $this->xPanel->getUpdateFields($id);
		$this->data['title'] = trans('admin::messages.edit') . ' ' . $this->xPanel->entity_name;
		
		$this->data['id'] = $id;
		
		return view('admin::panel.edit', $this->data);
	}
	
	public function update(UpdateRequest $request)
	{
		$this->data['entry'] = $this->xPanel->getEntry($request->input('id'));
		
		// Add the 'field' field
		$fieldColValue = json_decode($this->data['entry']->field, true);
		$this->addField($fieldColValue);
		
		
		return parent::updateCrud();
	}
	
	/**
	 * Add fake fields as array of the default json
	 *
	 * @param $fieldColValue
	 */
	public function addField($fieldColValue)
	{
		// Get the fake feature items
		$fakeFeatureItems = [
			'fake'     => true,
			'store_in' => "value",
		];
		
		// Is a multi-fields settings
		if (isset($fieldColValue['0']) && is_array($fieldColValue['0'])) {
			foreach ($fieldColValue as $key => $fieldColItem) {
				$fieldColItemFull = $fieldColItem + $fakeFeatureItems;
				$this->addField($fieldColItemFull);
			}
		} else {
			// Is a one field settings (with a valid json data)
			if (isset($fieldColValue['name'])) {
				if (isset($fieldColValue['label'])) {
					if (isset($fieldColValue['plugin'])) {
						$fieldColValue['label'] = trans($fieldColValue['plugin'] . '::messages.' . $fieldColValue['label']);
					} else {
						$fieldColValue['label'] = trans('admin::messages.' . $fieldColValue['label']);
					}
				}
				if (isset($fieldColValue['hint'])) {
					$checkClearedHintContent = trim(strip_tags($fieldColValue['hint']));
					if (!empty($checkClearedHintContent)) {
						if (isset($fieldColValue['plugin'])) {
							$fieldColValue['hint'] = trans($fieldColValue['plugin'] . '::messages.' . $fieldColValue['hint']);
						} else {
							$fieldColValue['hint'] = trans('admin::messages.' . $fieldColValue['hint']);
						}
					}
					$fieldColValue['hint'] = str_replace('#admin#', url(config('larapen.admin.route_prefix', 'admin')), $fieldColValue['hint']);
				}
				if (isset($fieldColValue['type']) && $fieldColValue['type'] == 'custom_html') {
					$checkClearedValueContent = trim(strip_tags($fieldColValue['value']));
					if (!empty($checkClearedValueContent)) {
						$fieldColValue['value'] = trans('admin::messages.' . $fieldColValue['value']);
					}
					$fieldColValue['value'] = str_replace('#admin#', url(config('larapen.admin.route_prefix', 'admin')), $fieldColValue['value']);
				}
			} else {
				// Is a one field settings (without a valid json data)
				$fieldColValue = [
					'name'  => 'value',
					'label' => 'Value',
					'type'  => 'text',
				];
			}
			
			// Add the fake field to xPanel
			$this->xPanel->addField($fieldColValue);
		}
	}
}
