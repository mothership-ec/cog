<?php

namespace Message\Cog\ValueObject;

use DateTime;

/**
 * Represents the created, updated and deleted metadata for a model.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 * @author Danny Hannah <danny@message.co.uk>
 */
class Authorship
{
	protected $_createdAt;
	protected $_createdBy;
	protected $_updatedAt;
	protected $_updatedBy;
	protected $_deletedAt;
	protected $_deletedBy;

	/**
	 * Get the date & time of creation.
	 *
	 * @return DateTime|null The date & time of creation, null if not set
	 */
	public function createdAt()
	{

	}

	/**
	 * Get the user responsible for creation.
	 *
	 * @return mixed The user that created the model, null if not set
	 */
	public function createdBy()
	{

	}

	/**
	 * Get the date & time of last update.
	 *
	 * @return DateTime|null The date & time of last update, null if not set
	 */
	public function updatedAt()
	{

	}

	/**
	 * Get the user responsible for the last edit.
	 *
	 * @return mixed The user that last edited the model, null if not set
	 */
	public function updatedBy()
	{

	}

	/**
	 * Get the date & time of deletion.
	 *
	 * @return DateTime|null The date & time of deletion, null if not set
	 */
	public function deletedAt()
	{

	}

	/**
	 * Get the user responsible for deletion.
	 *
	 * @return mixed The user that deleted the model, null if not set
	 */
	public function deletedBy()
	{

	}

	/**
	 * Check if the model has been deleted.
	 *
	 * @return boolean True if a deleted timestamp and/or user is set
	 */
	public function isDeleted()
	{

	}

	/**
	 * Set the created metadata.
	 *
	 * @param  DateTime|null $datetime The date & time of creation, null to use
	 *                                 current date & time
	 * @param  mixed $user             The user responsible
	 *
	 * @return Authorship              Returns $this for chainability
	 *
	 * @throws \LogicException         If the created metadata already exists
	 */
	public function create(DateTime $datetime = null, $user = null)
	{

	}

	/**
	 * Set the updated metadata.
	 *
	 * @param  DateTime|null $datetime The date & time of the update, null to use
	 *                                 current date & time
	 * @param  mixed $user             The user responsible
	 *
	 * @return Authorship              Returns $this for chainability
	 */
	public function update(DateTime $datetime = null, $user = null)
	{

	}

	/**
	 * Set the deleted metadata.
	 *
	 * @param  DateTime|null $datetime The date & time of deletion, null to use
	 *                                 current date & time
	 * @param  mixed $user             The user responsible
	 *
	 * @return Authorship              Returns $this for chainability
	 *
	 * @throws \LogicException         If the deleted metadata already exists
	 */
	public function delete(DateTime $datetime = null, $user = null)
	{

	}

	/**
	 * Restore the model by removing the deleted metadata.
	 *
	 * @return Authorship      Returns $this for chainability
	 *
	 * @throws \LogicException If the model hasn't been deleted yet
	 */
	public function restore()
	{

	}

	/**
	 * Print out the authorship metadata as a string.
	 *
	 * @return string The authorship metadata represented as a string
	 */
	public function __toString()
	{

	}
}