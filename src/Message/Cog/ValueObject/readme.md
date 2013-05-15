# Value Objects

This component contains some standard value objects that are likely to be used in most web applications using Cog.

## `Authorship`

This class represents the metadata of when a model was created, updated and deleted; and also which user performed these actions.

The following methods can be used to add this metadata:

* `create`
* `update`
* `delete`

If "created" metadata already exists, it cannot be set again. The same restriction exists for "deleted" metadata. There is no restriction on changing the "updated" metadata.

The following methods should be used to access the metadata:

* `createdAt`
* `createdBy`
* `updatedAt`
* `updatedBy`
* `deletedAt`
* `deletedBy`

The following example shows how to set "created" metadata and how to access the data afterwards. Both the "updated" and "deleted" metadata both also work in this same way.

	$authorship = new Authorship;

	$authorship->create(new DateTime('1 Feb 2012'), 'Joe Holdcroft');

	echo $authorship->createdAt()->format('d/m/y');	// 01/02/12
	echo $authorship->createdBy();					// Joe Holdcroft

When setting the created/updated/deleted date, you may pass `null` to use the current date & time:

	$authorship->update(null, 'Danny Hannah');

	echo $authorship->updatedAt()->format('d/m/y'); // today's date

Currently, the `Authorship` object is totally agnostic to what is passed as the user parameter for the `create`, `update` and `delete` methods. It can be an integer, string, object and so on.
In future this may be changed to expect an object that implements the basic user interface.

### Restoring / undeleting

It is possible to remove the "deleted" metadata by calling the `restore` method. This method will throw a `LogicException` if no "deleted" metadata has been set.

	$authorship->delete(null, 'Test User');

	echo $authorship->deletedBy; // Test User

	$authorship->restore();

	echo $authorship->deletedBy; // null

## `Money`

## `DateRange`