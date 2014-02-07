<?php

namespace Message\Cog\Field\Type;

use Message\Cog\Field\Field;

use Message\Mothership\FileManager\File\Type as FileType;

use Message\ImageResize\ResizableInterface;

use Message\Cog\Form\Handler;
use Message\Cog\Filesystem;
use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;

/**
 * A field for a file in the file manager database.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class File extends Field implements ContainerAwareInterface, ResizableInterface
{
	protected $_services;

	protected $_allowedTypes;

	/**
	 * Cast this field to a string.
	 *
	 * This outputs the public path to the file, or a blank string if the file
	 * could not be found.
	 *
	 * @return string Public path to the file
	 */
	public function __toString()
	{
		if ($file = $this->_services['file_manager.file.loader']->getByID((int) $this->_value)) {
			$cogFile = new Filesystem\File($file->url);

			return $cogFile->getPublicUrl();
		}

		return '';
	}

	public function getFieldType()
	{
		return 'file';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getUrl()
	{
		return $this->getFile() ? $this->getFile()->getUrl() : '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAltText()
	{
		return $this->getFile() ? $this->getFile()->getAltText() : '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function setContainer(ContainerInterface $container)
	{
		$this->_services = $container;
	}

	public function getFormField(Handler $form)
	{
		$form->add($this->getName(), 'ms_file', $this->getLabel(), array(
			'attr'          => array('data-help-key' => $this->_getHelpKeys()),
			'choices'       => $this->_getChoices(),
			'allowed_types' => $this->_allowedTypes ?: false,
			'empty_value'   => $this->_services['translator']->trans('ms.file_manager.select.default'),
		));
	}

	public function setAllowedTypes($types)
	{
		if (!is_array($types)) {
			$types = array($types);
		}

		$this->_allowedTypes = $types;

		return $this;
	}

	public function getFile()
	{
		return $this->_services['file_manager.file.loader']->getByID((int) $this->_value);
	}

	// public function getValue()
	// {
	// 	return $this->_services['file_manager.file.loader']->getByID((int) $this->_value);
	// }

	protected function _getChoices()
	{
		$files   = $this->_services['file_manager.file.loader']->getAll();
		$choices = array();

		if (!$files) {
			return array();
		}

		foreach ($files as $file) {
			if ($this->_allowedTypes) {
				if (!in_array($file->typeID, $this->_allowedTypes)) {
					continue;
				}
			}

			$choices[$file->id] = $file->name;
		}

		return $choices;
	}
}