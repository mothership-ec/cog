<?php

namespace Message\Cog\Field;

/**
 * @author Sam Trangmar-Keates <sam@message.co.uk>
 *
 * Class to build Content of a field
 */
class ContentBuilder
{
	/**
	 * The name of the form field added to keep track of sequence within
	 * RepeatableContainers
	 */
	const SEQUENCE_FIELD = '_sequence';

	/**
	 * [buildContent description]
	 * @param  Factory $fieldFactory The field factory with all fields for the 
	 *                               content
	 * @param  array   $data         The content data, this should be specified
	 *                               as ['group' => [$field_1, $field_2, ...], ...]
	 *                               where $field_1 & $field_2 are a stdClass
	 *                               objects containing at least 'field' and 
	 *                               'value' properties
	 * @param  string|ContentInterface $content Content class or class name
	 * @return ContentInterface       Returns the build content
	 */
	public function buildContent(Factory $fieldFactory, $data, $content = '\\Message\\Cog\\Field\\Content')
	{
		if (!$content instanceof ContentInterface) {
			if (!is_string($content)) {
				throw new \InvalidArgumentException('content must be either ContentInterface or string, '.gettype($content) == 'object'?get_class($content):gettype($content). ' given');
			}

			$content = new $content;
		}

		foreach ($fieldFactory as $name => $field) {
			if ($field instanceof Group && $field->isRepeatable()) {
				// add sequence variable
				$field->add($fieldFactory->getField('hidden', self::SEQUENCE_FIELD));

				$field = new RepeatableContainer($field);
			}

			$content->set($name, $field);
		}

		// Loop through the content, grouped by group
		foreach ($data as $groupName => $rows) {
			foreach ($rows as $row) {
				// If this field is in a group
				if ($groupName) {
					$group = $content->get($groupName);

					if (!$group) {
						continue;
					}

					// Get the right group instance if it's a repeatable group
					if ($group instanceof RepeatableContainer) {
						// Ensure the right number of groups are defined
						while (!$group->get($row->sequence)) {
							$group->add();
						}

						$group = $group->get($row->sequence);

						// set sequence field value
						$group->get('_sequence')->setValue($row->sequence);
					}

					// Set the field
					try {
						$field = $group->{$row->field};
					}
					catch (\OutOfBoundsException $e) {
						continue;
					}
				}
				// If not, finding the field is easy
				else {
					$field = $content->get($row->field);
				}

				// Skip the field if we can't find it
				if (!isset($field)) {
					continue;
				}

				// Set the values
				if ($field instanceof MultipleValueField) {
					$field->setValue($row->data_name, $row->value);
				}
				elseif ($field instanceof BaseField) {
					$field->setValue($row->value);
				}
			}
		}

		return $content;
	}
}