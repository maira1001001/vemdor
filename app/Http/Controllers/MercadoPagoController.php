<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use MP;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class MercadoPagoController extends Controller
{
  public function getCreatePreference()
  {
    $preferenceData = [
      'items' => [
        [
          'id' => 12,
          'category_id' => 'phones',
          'title' => 'iPhone 6',
          'description' => 'iPhone 6 de 64gb nuevo',
          'picture_url' => 'http://d243u7pon29hni.cloudfront.net/images/products/iphone-6-dorado-128-gb-red-4g-8-mpx-1256254%20(1)_m.png',
          'quantity' => 1,
          'currency_id' => 'ARS',
          'unit_price' => 14999
        ]
      ],
    ];

    $preference = MP::create_preference($preferenceData);

    return dd($preference);


  }
}
