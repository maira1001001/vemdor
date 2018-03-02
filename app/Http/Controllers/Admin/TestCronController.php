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

use Illuminate\Support\Facades\Artisan;
use Larapen\Admin\app\Http\Controllers\Controller;
use Prologue\Alerts\Facades\Alert;

class TestCronController extends Controller
{
	/**
	 * TestCronController constructor.
	 */
	public function __construct()
	{
		$this->middleware('demo');
	}
	
	/**
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function run()
	{
		$errorFound = false;
		
		// Run the Cron Job command manually
		try {
			$exitCode = Artisan::call('ads:clean');
		} catch (\Exception $e) {
			Alert::error($e->getMessage())->flash();
			$errorFound = true;
		}
		
		// Check if error occurred
		if (!$errorFound) {
			$message = __t("The Cron Job command was successfully run.");
			Alert::success($message)->flash();
		}
		
		return redirect()->back();
	}
}
