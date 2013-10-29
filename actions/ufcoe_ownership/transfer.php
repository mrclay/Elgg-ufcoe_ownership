<?php

namespace UFCOE\Ownership;

use UFCOE\Elgg\Url;

elgg_make_sticky_form(PLUGIN_ID . '/transfer');

$guid = get_input('guid', 0, false);
if ($guid && !is_numeric($guid)) {
	$url_sniffer = new Url(elgg_get_site_url());
	$guid = $url_sniffer->getGuid($guid);
}
$entity = get_entity((int)$guid);

$user = false;
$new_owner_guids = get_input('new_owner_guids', array(), false);
if ($new_owner_guids) {
	$user = get_user((int)$new_owner_guids[0]);
}

$form_url = 'transfer_ownership?' . http_build_query(array(
	'guid' => $entity ? $entity->guid : null,
	'username' => $user ? $user->username : null,
));

if (get_input('need_preview')) {
	forward($form_url);
}

$can_write_to_user = ($user && $user->canWriteToContainer());

$can_edit_entity = ($entity && $entity->canEdit());

if (!$can_edit_entity || !$can_write_to_user) {
	// could be just previewing form, no error
	forward($form_url);
}

if ($entity->owner_guid == $user->guid) {
	register_error(PLUGIN_ID . ':already_owner');
	forward($form_url);
}

$func = Plugin::getTransferFunc($entity);
if (!is_callable($func)) {
	register_error(elgg_echo(PLUGIN_ID . ':no_transfer_func_available'));
	forward($form_url);
}

$success = call_user_func($func, $entity, $user);
if (!$success) {
	register_error(elgg_echo(PLUGIN_ID . ':transfer_failed'));
	forward($form_url);
}

elgg_clear_sticky_form(PLUGIN_ID . '/transfer');

system_message(elgg_echo(PLUGIN_ID . ':ownership_changed'));
forward($entity->getURL());
