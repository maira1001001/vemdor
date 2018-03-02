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

namespace App\Http\Controllers\Admin;

use App\Models\Language;
use Larapen\Admin\app\Http\Controllers\PanelController;
use App\Http\Requests\Admin\Request as StoreRequest;
use App\Http\Requests\Admin\Request as UpdateRequest;

class HomeSectionController extends PanelController
{
	public function setup()
	{
		/*
		|--------------------------------------------------------------------------
		| BASIC CRUD INFORMATION
		|--------------------------------------------------------------------------
		*/
		$this->xPanel->setModel('App\Models\HomeSection');
		$this->xPanel->setRoute(config('larapen.admin.route_prefix', 'admin') . '/homepage_section');
		$this->xPanel->setEntityNameStrings(__t('homepage section'), __t('homepage sections'));
		$this->xPanel->denyAccess(['create', 'delete']);
		$this->xPanel->allowAccess(['reorder']);
		$this->xPanel->enableReorder('name', 1);
		if (!request()->input('order')) {
			$this->xPanel->orderBy('lft', 'ASC');
		}
		
		/*
		|--------------------------------------------------------------------------
		| COLUMNS AND FIELDS
		|--------------------------------------------------------------------------
		*/
		// COLUMNS
		$this->xPanel->addColumn([
			'name'          => 'name',
			'label'         => __t("Section"),
			'type'          => 'model_function',
			'function_name' => 'getNameHtml',
		]);
		$this->xPanel->addColumn([
			'name'          => 'active',
			'label'         => __t("Active"),
			'type'          => 'model_function',
			'function_name' => 'getActiveHtml',
		]);
		
		// FIELDS
		$this->xPanel->addField([
			'name'       => 'name',
			'label'      => __t("Section"),
			'type'       => 'text',
			'attributes' => [
				'placeholder' => __t("Section"),
				'disabled'    => 'disabled',
			],
		]);
		
		$section = $this->xPanel->model->find(request()->segment(3));
		if (!empty($section)) {
			// getSearchForm
			if (in_array($section->method, ['getSearchForm'])) {
				$enableCustomFormField = [
					'name'     => 'enable_form_area_customization',
					'label'    => __t("Enable search form area customization"),
					'fake'     => true,
					'store_in' => 'options',
					'type'     => 'checkbox',
					'hint'     => __t("NOTE: The options below require to enable the search form area customization."),
				];
				$this->xPanel->addField($enableCustomFormField);
				
				// Separator
				$this->xPanel->addField([
					'name'  => 'separator_1',
					'type'  => 'custom_html',
					'value' => '<h3>' . __t('Background') . '</h3>',
				]);
				
				$backgroundColorField = [
					'name'                => 'background_color',
					'label'               => __t("Background Color"),
					'fake'                => true,
					'store_in'            => 'options',
					'type'                => 'color_picker',
					'colorpicker_options' => [
						'customClass' => 'custom-class',
					],
					'attributes'          => [
						'placeholder' => "#444",
					],
					'hint'                => __t("Enter a RGB color code."),
				];
				$this->xPanel->addField($backgroundColorField);
				
				$backgroundImageField = [
					'name'     => 'background_image',
					'label'    => __t("Background Image"),
					'fake'     => true,
					'store_in' => 'options',
					'type'     => 'image',
					'upload'   => true,
					'disk'     => 'uploads',
					'hint'     => __t('Choose a picture from your computer.') . '<br>' .
						__t('You can set a background image by country in Settings -> International -> Countries -> [Edit] -> Background Image'),
				];
				$this->xPanel->addField($backgroundImageField);
				
				$heightField = [
					'name'              => 'height',
					'label'             => __t("Height"),
					'fake'              => true,
					'store_in'          => 'options',
					'attributes'        => [
						'placeholder' => "450px",
					],
					'hint'              => __t('Enter a value greater than 50px.') . ' (' . __t('Example: 400px') . ')',
					'wrapperAttributes' => [
						'class' => 'form-group col-md-6',
					],
				];
				$this->xPanel->addField($heightField);
				
				$parallaxField = [
					'name'              => 'parallax',
					'label'             => __t("Enable Parallax Effect"),
					'fake'              => true,
					'store_in'          => 'options',
					'type'              => 'checkbox',
					'wrapperAttributes' => [
						'class' => 'form-group col-md-6',
						'style' => 'margin-top: 20px;',
					],
				];
				$this->xPanel->addField($parallaxField);
				
				// Separator
				$this->xPanel->addField([
					'name'  => 'separator_2',
					'type'  => 'custom_html',
					'value' => '<h3>' . __t('Search Form') . '</h3>',
				]);
				
				$hideFormField = [
					'name'     => 'hide_form',
					'label'    => __t("Hide the Form"),
					'fake'     => true,
					'store_in' => 'options',
					'type'     => 'checkbox',
				];
				$this->xPanel->addField($hideFormField);
				
				$formBorderColorField = [
					'name'                => 'form_border_color',
					'label'               => __t("Form Border Color"),
					'fake'                => true,
					'store_in'            => 'options',
					'type'                => 'color_picker',
					'colorpicker_options' => [
						'customClass' => 'custom-class',
					],
					'attributes'          => [
						'placeholder' => "#333",
					],
					'hint'                => __t("Enter a RGB color code."),
					'wrapperAttributes'   => [
						'class' => 'form-group col-md-3',
					],
				];
				$this->xPanel->addField($formBorderColorField);
				
				$formBorderSizeField = [
					'name'              => 'form_border_width',
					'label'             => __t("Form Border Width"),
					'fake'              => true,
					'store_in'          => 'options',
					'attributes'        => [
						'placeholder' => "5px",
					],
					'hint'              => 'Enter a number with unit (eg. 5px)',
					'wrapperAttributes' => [
						'class' => 'form-group col-md-3',
					],
				];
				$this->xPanel->addField($formBorderSizeField);
				
				$formBtnBackgroundColorField = [
					'name'                => 'form_btn_background_color',
					'label'               => __t("Form Button Background Color"),
					'fake'                => true,
					'store_in'            => 'options',
					'type'                => 'color_picker',
					'colorpicker_options' => [
						'customClass' => 'custom-class',
					],
					'attributes'          => [
						'placeholder' => "#4682B4",
					],
					'hint'                => __t("Enter a RGB color code."),
					'wrapperAttributes'   => [
						'class' => 'form-group col-md-3',
					],
				];
				$this->xPanel->addField($formBtnBackgroundColorField);
				
				$formBtnTextColorField = [
					'name'                => 'form_btn_text_color',
					'label'               => __t("Form Button Text Color"),
					'fake'                => true,
					'store_in'            => 'options',
					'type'                => 'color_picker',
					'colorpicker_options' => [
						'customClass' => 'custom-class',
					],
					'attributes'          => [
						'placeholder' => "#FFF",
					],
					'hint'                => __t("Enter a RGB color code."),
					'wrapperAttributes'   => [
						'class' => 'form-group col-md-3',
					],
				];
				$this->xPanel->addField($formBtnTextColorField);
				
				// Separator
				$this->xPanel->addField([
					'name'  => 'separator_3',
					'type'  => 'custom_html',
					'value' => '<h3>' . __t('Titles') . '</h3>',
				]);
				
				$hideTitlesField = [
					'name'     => 'hide_titles',
					'label'    => __t("Hide Titles"),
					'fake'     => true,
					'store_in' => 'options',
					'type'     => 'checkbox',
				];
				$this->xPanel->addField($hideTitlesField);
				
				// Separator
				$this->xPanel->addField([
					'name'  => 'separator_3_1',
					'type'  => 'custom_html',
					'value' => '<h4>' . __t('Titles Content') . '</h4>',
				]);
				
				$this->xPanel->addField([
					'name'  => 'separator_3_2',
					'type'  => 'custom_html',
					'value' => 'NOTE: ' . __t("You can use dynamic variables such as {app_name}, {country}, {count_ads} and {count_users}."),
				]);
				
				$languages = Language::active()->get();
				if ($languages->count() > 0) {
					foreach ($languages as $language) {
						${'titleField' . $language->abbr} = [
							'name'              => 'title_' . $language->abbr,
							'label'             => __t("Title") . ' (' . $language->name . ')',
							'fake'              => true,
							'store_in'          => 'options',
							'attributes'        => [
								'placeholder' => t('Sell and buy near you', [], 'global', $language->abbr),
							],
							'wrapperAttributes' => [
								'class' => 'form-group col-md-6',
							],
						];
						$this->xPanel->addField(${'titleField' . $language->abbr});
						
						${'subTitleField' . $language->abbr} = [
							'name'              => 'sub_title_' . $language->abbr,
							'label'             => __t("Sub Title") . ' (' . $language->name . ')',
							'fake'              => true,
							'store_in'          => 'options',
							'attributes'        => [
								'placeholder' => t('Simple, fast and efficient', [], 'global', $language->abbr),
							],
							'wrapperAttributes' => [
								'class' => 'form-group col-md-6',
							],
						];
						$this->xPanel->addField(${'subTitleField' . $language->abbr});
					}
				}
				
				// Separator
				$this->xPanel->addField([
					'name'  => 'separator_3_3',
					'type'  => 'custom_html',
					'value' => '<h4>' . __t('Titles Color') . '</h4>',
				]);
				
				$bigTitleColorField = [
					'name'                => 'big_title_color',
					'label'               => __t("Big Title Color"),
					'fake'                => true,
					'store_in'            => 'options',
					'type'                => 'color_picker',
					'colorpicker_options' => [
						'customClass' => 'custom-class',
					],
					'attributes'          => [
						'placeholder' => "#FFF",
					],
					'hint'                => __t("Enter a RGB color code."),
					'wrapperAttributes'   => [
						'class' => 'form-group col-md-6',
					],
				];
				$this->xPanel->addField($bigTitleColorField);
				
				$subTitleColorField = [
					'name'                => 'sub_title_color',
					'label'               => __t("Sub Title Color"),
					'fake'                => true,
					'store_in'            => 'options',
					'type'                => 'color_picker',
					'colorpicker_options' => [
						'customClass' => 'custom-class',
					],
					'attributes'          => [
						'placeholder' => "#FFF",
					],
					'hint'                => __t("Enter a RGB color code."),
					'wrapperAttributes'   => [
						'class' => 'form-group col-md-6',
					],
				];
				$this->xPanel->addField($subTitleColorField);
			}
			
			// getCategories, getSponsoredPosts & getLatestPosts
			if (in_array($section->method, ['getCategories', 'getSponsoredPosts', 'getLatestPosts'])) {
				$maxItemsField = [
					'name'              => 'max_items',
					'label'             => __t("Max Items"),
					'fake'              => true,
					'store_in'          => 'options',
					'wrapperAttributes' => [
						'class' => 'form-group col-md-6',
					],
				];
				$this->xPanel->addField($maxItemsField);
			}
			
			// getSponsoredPosts & getLatestPosts
			if (in_array($section->method, ['getSponsoredPosts', 'getLatestPosts'])) {
				$orderByField = [
					'name'              => 'order_by',
					'label'             => __t("Order By"),
					'fake'              => true,
					'store_in'          => 'options',
					'type'              => 'select2_from_array',
					'options'           => [
						'date'   => __t("Date"),
						'random' => __t("Random"),
					],
					'allows_null'       => false,
					'wrapperAttributes' => [
						'class' => 'form-group col-md-6',
					],
				];
				$this->xPanel->addField($orderByField);
			}
			
			// getLocations
			if ($section->method == 'getLocations') {
				// Separator
				$this->xPanel->addField([
					'name'  => 'separator_4',
					'type'  => 'custom_html',
					'value' => '<h3>' . __t('Locations') . '</h3>',
				]);
				
				$showCitiesField = [
					'name'              => 'show_cities',
					'label'             => __t("Show the Country Cities"),
					'fake'              => true,
					'store_in'          => 'options',
					'type'              => 'checkbox',
					'wrapperAttributes' => [
						'class' => 'form-group col-md-6',
						//'style' => 'margin-top: 20px;',
					],
				];
				$this->xPanel->addField($showCitiesField);
				
				$showPostBtnField = [
					'name'              => 'show_post_btn',
					'label'             => __t("Show the bottom button"),
					'fake'              => true,
					'store_in'          => 'options',
					'type'              => 'checkbox',
					'wrapperAttributes' => [
						'class' => 'form-group col-md-6',
						//'style' => 'margin-top: 20px;',
					],
				];
				$this->xPanel->addField($showPostBtnField);
				
				$backgroundColorField = [
					'name'                => 'background_color',
					'label'               => __t("Background Color"),
					'fake'                => true,
					'store_in'            => 'options',
					'type'                => 'color_picker',
					'colorpicker_options' => [
						'customClass' => 'custom-class',
					],
					'attributes'          => [
						'placeholder' => "#c7c5c1",
					],
					'wrapperAttributes'   => [
						'class' => 'form-group col-md-6',
					],
				];
				$this->xPanel->addField($backgroundColorField);
				
				$borderWidthField = [
					'name'              => 'border_width',
					'label'             => __t("Border Width"),
					'fake'              => true,
					'store_in'          => 'options',
					'attributes'        => [
						'placeholder' => '1px',
					],
					'wrapperAttributes' => [
						'class' => 'form-group col-md-6',
					],
				];
				$this->xPanel->addField($borderWidthField);
				
				$borderColorField = [
					'name'                => 'border_color',
					'label'               => __t("Border Color"),
					'fake'                => true,
					'store_in'            => 'options',
					'type'                => 'color_picker',
					'colorpicker_options' => [
						'customClass' => 'custom-class',
					],
					'attributes'          => [
						'placeholder' => "#c7c5c1",
					],
					'wrapperAttributes'   => [
						'class' => 'form-group col-md-6',
					],
				];
				$this->xPanel->addField($borderColorField);
				
				$textColorField = [
					'name'                => 'text_color',
					'label'               => __t("Text Color"),
					'fake'                => true,
					'store_in'            => 'options',
					'type'                => 'color_picker',
					'colorpicker_options' => [
						'customClass' => 'custom-class',
					],
					'attributes'          => [
						'placeholder' => "#c7c5c1",
					],
					'wrapperAttributes'   => [
						'class' => 'form-group col-md-6',
					],
				];
				$this->xPanel->addField($textColorField);
				
				$linkColorField = [
					'name'                => 'link_color',
					'label'               => __t("Links Color"),
					'fake'                => true,
					'store_in'            => 'options',
					'type'                => 'color_picker',
					'colorpicker_options' => [
						'customClass' => 'custom-class',
					],
					'attributes'          => [
						'placeholder' => "#c7c5c1",
					],
					'wrapperAttributes'   => [
						'class' => 'form-group col-md-6',
					],
				];
				$this->xPanel->addField($linkColorField);
				
				$linkColorHoverField = [
					'name'                => 'link_color_hover',
					'label'               => __t("Links Color (Hover)"),
					'fake'                => true,
					'store_in'            => 'options',
					'type'                => 'color_picker',
					'colorpicker_options' => [
						'customClass' => 'custom-class',
					],
					'attributes'          => [
						'placeholder' => "#c7c5c1",
					],
					'wrapperAttributes'   => [
						'class' => 'form-group col-md-6',
					],
				];
				$this->xPanel->addField($linkColorHoverField);
				
				$maxItemsField = [
					'name'              => 'max_items',
					'label'             => __t("Max Cities"),
					'fake'              => true,
					'store_in'          => 'options',
					'attributes'        => [
						'placeholder' => 12,
					],
					'wrapperAttributes' => [
						'class' => 'form-group col-md-6',
					],
				];
				$this->xPanel->addField($maxItemsField);
				
				$cacheExpirationField = [
					'name'              => 'cache_expiration',
					'label'             => __t("Cache Expiration Time for this section"),
					'fake'              => true,
					'store_in'          => 'options',
					'attributes'        => [
						'placeholder' => __t("In minutes (e.g. 60 for 1h, 0 or empty value to disable the cache)"),
					],
					'hint'              => __t("In minutes (e.g. 60 for 1h, 0 or empty value to disable the cache)"),
					'wrapperAttributes' => [
						'class' => 'form-group col-md-6',
					],
				];
				$this->xPanel->addField($cacheExpirationField);
				
				// Separator
				$this->xPanel->addField([
					'name'  => 'separator_4_1',
					'type'  => 'custom_html',
					'value' => '<h3>' . __t('Country Map') . '</h3>',
				]);
				
				$showMapField = [
					'name'     => 'show_map',
					'label'    => __t("Show the Country Map"),
					'fake'     => true,
					'store_in' => 'options',
					'type'     => 'checkbox',
				];
				$this->xPanel->addField($showMapField);
				
				$mapBackgroundColorField = [
					'name'                => 'map_background_color',
					'label'               => __t("Map's Background Color"),
					'fake'                => true,
					'store_in'            => 'options',
					'type'                => 'color_picker',
					'colorpicker_options' => [
						'customClass' => 'custom-class',
					],
					'attributes'          => [
						'placeholder' => "transparent",
					],
					'hint'                => __t("Enter a RGB color code or the word 'transparent'."),
					'wrapperAttributes'   => [
						'class' => 'form-group col-md-6',
					],
				];
				$this->xPanel->addField($mapBackgroundColorField);
				
				$mapBorderField = [
					'name'                => 'map_border',
					'label'               => __t("Map's Border"),
					'fake'                => true,
					'store_in'            => 'options',
					'type'                => 'color_picker',
					'colorpicker_options' => [
						'customClass' => 'custom-class',
					],
					'attributes'          => [
						'placeholder' => "#c7c5c1",
					],
					'hint'                => '<br>',
					'wrapperAttributes'   => [
						'class' => 'form-group col-md-6',
					],
				];
				$this->xPanel->addField($mapBorderField);
				
				$mapHoverBorderField = [
					'name'                => 'map_hover_border',
					'label'               => __t("Map's Hover Border"),
					'fake'                => true,
					'store_in'            => 'options',
					'type'                => 'color_picker',
					'colorpicker_options' => [
						'customClass' => 'custom-class',
					],
					'attributes'          => [
						'placeholder' => "#c7c5c1",
					],
					'wrapperAttributes'   => [
						'class' => 'form-group col-md-6',
					],
				];
				$this->xPanel->addField($mapHoverBorderField);
				
				$mapBorderWidthField = [
					'name'              => 'map_border_width',
					'label'             => __t("Map's Border Width"),
					'fake'              => true,
					'store_in'          => 'options',
					'attributes'        => [
						'placeholder' => 4,
					],
					'wrapperAttributes' => [
						'class' => 'form-group col-md-6',
					],
				];
				$this->xPanel->addField($mapBorderWidthField);
				
				$mapColorField = [
					'name'                => 'map_color',
					'label'               => __t("Map's Color"),
					'fake'                => true,
					'store_in'            => 'options',
					'type'                => 'color_picker',
					'colorpicker_options' => [
						'customClass' => 'custom-class',
					],
					'attributes'          => [
						'placeholder' => "#f2f0eb",
					],
					'wrapperAttributes'   => [
						'class' => 'form-group col-md-6',
					],
				];
				$this->xPanel->addField($mapColorField);
				
				$mapHoverField = [
					'name'                => 'map_hover',
					'label'               => __t("Map's Hover"),
					'fake'                => true,
					'store_in'            => 'options',
					'type'                => 'color_picker',
					'colorpicker_options' => [
						'customClass' => 'custom-class',
					],
					'attributes'          => [
						'placeholder' => "#4682B4",
					],
					'wrapperAttributes'   => [
						'class' => 'form-group col-md-6',
					],
				];
				$this->xPanel->addField($mapHoverField);
				
				$mapWidthField = [
					'name'              => 'map_width',
					'label'             => __t("Map's Width"),
					'fake'              => true,
					'store_in'          => 'options',
					'attributes'        => [
						'placeholder' => "300px",
					],
					'wrapperAttributes' => [
						'class' => 'form-group col-md-6',
					],
				];
				$this->xPanel->addField($mapWidthField);
				
				$mapHeightField = [
					'name'              => 'map_height',
					'label'             => __t("Map's Height"),
					'fake'              => true,
					'store_in'          => 'options',
					'attributes'        => [
						'placeholder' => "300px",
					],
					'wrapperAttributes' => [
						'class' => 'form-group col-md-6',
					],
				];
				$this->xPanel->addField($mapHeightField);
			}
			
			// getSponsoredPosts
			if ($section->method == 'getSponsoredPosts') {
				$carouselAutoplayField = [
					'name'     => 'autoplay',
					'label'    => __t("Carousel's Autoplay"),
					'fake'     => true,
					'store_in' => 'options',
					'type'     => 'checkbox',
				];
				$this->xPanel->addField($carouselAutoplayField);
				
				$carouselAutoplayTimeout = [
					'name'              => 'autoplay_timeout',
					'label'             => __t("Carousel's Autoplay Timeout"),
					'fake'              => true,
					'store_in'          => 'options',
					'attributes'        => [
						'placeholder' => 1500,
					],
					'wrapperAttributes' => [
						'class' => 'form-group col-md-6',
					],
				];
				$this->xPanel->addField($carouselAutoplayTimeout);
			}
			
			// getLatestPosts
			if ($section->method == 'getLatestPosts') {
				$showViewMoreBtnField = [
					'name'     => 'show_view_more_btn',
					'label'    => __t("Show 'View More' Button"),
					'fake'     => true,
					'store_in' => 'options',
					'type'     => 'checkbox',
				];
				$this->xPanel->addField($showViewMoreBtnField);
			}
			
			// getSponsoredPosts, getLatestPosts & getCategories
			if (in_array($section->method, ['getSponsoredPosts', 'getLatestPosts', 'getCategories'])) {
				$cacheExpirationField = [
					'name'              => 'cache_expiration',
					'label'             => __t("Cache Expiration Time for this section"),
					'fake'              => true,
					'store_in'          => 'options',
					'attributes'        => [
						'placeholder' => __t("In minutes (e.g. 60 for 1h, 0 or empty value to disable the cache)"),
					],
					'hint'              => __t("In minutes (e.g. 60 for 1h, 0 or empty value to disable the cache)"),
					'wrapperAttributes' => [
						'class' => 'form-group col-md-6',
					],
				];
				$this->xPanel->addField($cacheExpirationField);
			}
		}
		
		// Separator
		$this->xPanel->addField([
			'name'  => 'separator_last',
			'type'  => 'custom_html',
			'value' => '<hr>',
		]);
		
		$activeField = [
			'name'  => 'active',
			'label' => __t("Active"),
			'type'  => 'checkbox',
		];
		if (!empty($section) && $section->method == 'getTopAdvertising') {
			$activeField['hint'] = __t('To enable this feature, you have to configure the top advertisement in the Admin panel -> Setup -> Advertising -> top (Edit)');
		}
		if (!empty($section) && $section->method == 'getBottomAdvertising') {
			$activeField['hint'] = __t('To enable this feature, you have to configure the bottom advertisement in the Admin panel -> Setup -> Advertising -> bottom (Edit)');
		}
		$this->xPanel->addField($activeField);
	}
	
	public function store(StoreRequest $request)
	{
		return parent::storeCrud();
	}
	
	public function update(UpdateRequest $request)
	{
		return parent::updateCrud();
	}
}
