<?php

return array(
	'Auth' => array(
		'backend' => 'Google',
		'hybrid_auth' => array(
			'providers' => array(
				"Google" => array (
					"enabled" => true,
					"keys"    => array ( "id" => GA_APP_ID, "secret" => GA_SECRET ),
					"scope"           => "https://www.googleapis.com/auth/userinfo.profile ". // optional
					"https://www.googleapis.com/auth/userinfo.email"   , // optional
					"access_type"     => "offline",   // optional
				),
				"Facebook" => array (
					"enabled" => true,
					"keys"    => array ( "id" => FACEBOOK_APP_ID, "secret" => FACEBOOK_SECRET ),
					"scope"   => "email, user_about_me, user_birthday, user_hometown", // optional
				)
			),
			'debug_mode' => false,
			// For some reason Hybrid_Auth doesn't create file in a specified folder, be sure that it exists
			'debug_file' => __DIR__ . '/hybrid_auth.log',
		),
		'session_name' => 'hybridauth',
	)
);