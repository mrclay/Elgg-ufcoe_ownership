**Note!** This directory must be named `ufcoe_ownership` in your mod directory.

## UFCOE Ownership Transfer

This is a pluggable framework for transferring ownership of entities.

### How it works

As this is something meant to be seldom done, I've provided no menu item to get to this feature. You just enter http://example.org/transfer_ownership. You'll get a form asking for the GUID/URL of an entity, and a user picker.

The form simply resubmits until it's showing you a preview of the change, and the submit button will change to "Change owner".

To be allowed to make the change, the current user must:

* Have access to the entity
* Can edit the entity
* Can write to the container of the new user

...and a plugin must have provided a function designated to the transfer task for that type/subtype of entity.

### Current state

This plugin by itself can only change the ownership of comment entities (Elgg 1.9). I've not had the time to write algorithms for changing the common Elgg object types, as they all involve tricky stuff like moving icons/drafts.

If you want to donate a transfer function I'd be happy to include it with this plugin.

#### Adding a transfer function

First, write a function/method that can transfer ownership of an entity. Your function will be passed:

* The entity as the first argument
* The user entity who will be the new owner as the second argument

Your function should return true if it successfully transferred ownership of the entity, else false.

**Tip:** This plugin provides the function UFCOE\Ownership\Plugin::changeRiverCreator() to help you change river `create` items if you need to do so.

Now register for the hook named `ufcoe_ownership:get_transfer_func`, with `type:subtype` as the hook type. In the handler, return a callback to your transfer function.

