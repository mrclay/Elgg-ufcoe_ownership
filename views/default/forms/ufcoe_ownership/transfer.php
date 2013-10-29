<?php

namespace UFCOE\Ownership;

$user = false;
$username = get_input('username', '', false);
if ($username) {
	$user = get_user_by_username($username);
}

$can_write_to_user = ($user && $user->canWriteToContainer());

$entity = false;
$guid = get_input('guid', '', false);
if ($guid) {
	$entity = get_entity((int)$guid);
	if ($entity) {
		if (($entity->type !== 'group' && $entity->type !== 'object')
				|| ($entity->getSubtype() === 'plugin')) {
			$entity = false;
		}
	}
}
/* @var \ElggEntity $entity */

$can_edit_entity = ($entity && $entity->canEdit());

$can_transfer = false;
if ($can_edit_entity && $can_write_to_user) {
	$func = Plugin::getTransferFunc($entity);
	if (is_callable($func, true)) {
		$can_transfer = true;
	} else {
		echo "<p>" . elgg_echo(PLUGIN_ID . ':transfer_unsupported') . "</p>";
	}
}

?>
<div>
	<label for="guid"><?php echo elgg_echo(PLUGIN_ID . ':label_entity') ?></label>
	<?php
	if ($entity) {
		echo '<div class="pal prn elgg-divide-left">';
		echo elgg_view_entity($entity, array(
			'full_view' => false,
		));
		echo '</div>';

		if (!$can_edit_entity) {
			echo "<p>" . elgg_echo(PLUGIN_ID . ':cannot_edit_entity') . "</p>";
		}
	}
	echo elgg_view('input/text', array(
		'name' => 'guid',
		'id' => 'guid',
		'placeholder' => elgg_echo(PLUGIN_ID . ':placeholder_guid'),
		'value' => $guid,
	));
	?>
</div>
<div>
	<label for="username"><?php echo elgg_echo(PLUGIN_ID . ':label_owner') ?></label>
	<?php
	$values = array();
	if ($user) {
		$values[] = $user->guid;
	}
	echo elgg_view('input/userpicker', array(
		'name' => 'new_owner_guids',
		'values' => $values,
		'limit' => 1,
	));

	if ($user && !$can_write_to_user) {
		echo "<p>" . elgg_echo(PLUGIN_ID . ':cannot_transfer_to_user') . "</p>";
	}
	?>
</div>
<div class="elgg-foot">
	<?php
	if ($can_transfer) {
		echo elgg_view('input/submit', array(
			'value' => elgg_echo(PLUGIN_ID . ':submit_label_change_owner'),
			'confirm' => elgg_echo(PLUGIN_ID . ':are_you_sure'),
		));
	} else {
		echo elgg_view('input/submit', array(
			'value' => elgg_echo(PLUGIN_ID . ':submit_label_continue'),
		));
	}
	echo elgg_view('input/hidden', array(
		'name' => 'need_preview',
		'value' => $can_transfer ? '' : '1',
	));
	?>
</div>
<script>
elgg.register_hook_handler('init', 'system', function () {
	$(function () {
		var form = $('form.elgg-form-ufcoe-ownership-transfer').get(0);

		$('input[name="guid"], input.elgg-input-user-picker', form).on('input', function () {
			$('input[name="need_preview"]', form).val('1');
			$('input[type="submit"]', form).val(elgg.echo('<?php echo PLUGIN_ID ?>:submit_label_continue'));
		});
	});
});
</script>