<?php

namespace UFCOE\Ownership;

class Plugin {

	public static function routePage($segments) {
		$title = elgg_echo(PLUGIN_ID . ':change_ownership_heading');

		elgg_push_context('ufcoe_ownership');

		$content = elgg_view_form(PLUGIN_ID . '/transfer');

		$body = elgg_view_layout('content', array(
			'content' => $content,
			'title' => $title,
			'filter' => '',
		));

		echo elgg_view_page($title, $body);

		elgg_pop_context();
	}

	/**
	 * Get function that will transfer this entity
	 *
	 * Must return a function that accepts an entity and a user and returns true if ownership
	 * was transferred
	 *
	 * @param \ElggEntity $entity
	 * @return callable
	 */
	public static function getTransferFunc(\ElggEntity $entity) {
		$type = $entity->type . ':' . $entity->getSubtype();
		return elgg_trigger_plugin_hook('ufcoe_ownership:get_transfer_func', $type, null, false);
	}

	/**
	 * Handles the hook "ufcoe_ownership:get_transfer_func", providing a simple backup function.
	 */
	public static function allowTransferHook($hook, $type, $value, $params) {
		if ($value) {
			// already handled
			return;
		}
		$allowed = array(
			'object:comment', // the only thing simple enough right now :)
		);
		if (in_array($type, $allowed)) {
			return __NAMESPACE__ . '\\Plugin::defaultTransferFunc';
		}
	}

	/**
	 * Provides built-in support for transferring ownership of some entities
	 *
	 * @param \ElggEntity $entity
	 * @param \ElggUser $new_owner
	 * @return bool
	 */
	public static function defaultTransferFunc(\ElggEntity $entity, \ElggUser $new_owner) {
		$type = $entity->type . ':' . $entity->getSubtype();

		if ($type === 'object:comment') {
			$entity->owner_guid = $new_owner->guid;
			$entity->save();
			return true;
		}

		return false;
	}

	/**
	 * Update river entries so it looks as if the new owner created the entity
	 *
	 * @param \ElggEntity $obj
	 * @param \ElggUser $new_owner
	 */
	public static function changeRiverCreator(\ElggEntity $obj, \ElggUser $new_owner) {
		$items = elgg_get_river(array(
			'object_guids' => $obj->guid,
			'action_types' => 'create',
			'limit' => 1,
		));
		/* @var \ElggRiverItem[] $items */
		if (!$items) {
			return;
		}
		$dbprefix = elgg_get_config('dbprefix');
		update_data("
			UPDATE {$dbprefix}river
			SET subject_guid = {$new_owner->guid}
			WHERE id = {$items[0]->id}
		");
	}
}
