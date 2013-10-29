<?php

namespace UFCOE\Ownership;

const PLUGIN_ID = 'ufcoe_ownership';

// fix autoloading for 1.8 (place this in plugin start.php)
if (!function_exists('elgg_get_version')) {
	spl_autoload_register(function ($class) {
		$pieces = explode('\\', ltrim($class, '\\'));
		$pieces[count($pieces) - 1] = strtr($pieces[count($pieces) - 1], '_', '/');
		$file = __DIR__ . '/classes/' . implode('/', $pieces) . '.php';
		is_readable($file) && (require $file);
	});
}

function init() {
	elgg_register_page_handler('transfer_ownership', __NAMESPACE__ . '\\Plugin::routePage');

	// handler for built-in transfer functions
	elgg_register_plugin_hook_handler('ufcoe_ownership:get_transfer_func', 'all',
		__NAMESPACE__ . '\\Plugin::allowTransferHook');

	elgg_register_action(PLUGIN_ID . '/transfer', __DIR__ . '/actions/' . PLUGIN_ID . '/transfer.php');
}

elgg_register_event_handler('init', 'system', __NAMESPACE__ . '\\init');
