<?php

return [
	'app_id'     => env('MP_APP_ID', env('MERCADOPAGO_CLIENT_ID')),
	'app_secret' => env('MP_APP_SECRET', env('MERCADOPAGO_CLIENT_SECRET'))
];
