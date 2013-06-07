<?php

use Symfony\Component\Form\FormConfigInterface;

class FauxConfig implements FormConfigInterface
{
	public function getEventDispatcher(){}
	public function getName(){}
	public function getPropertyPath(){}
	public function getMapped(){}
	public function getByReference(){}
	public function getVirtual(){}
	public function getCompound(){
		return false;
	}
	public function getType(){}
	public function getViewTransformers(){}
	public function getModelTransformers(){}
	public function getDataMapper(){}
	public function getValidators(){}
	public function getRequired(){}
	public function getDisabled(){}
	public function getErrorBubbling(){}
	public function getEmptyData(){}
	public function getAttributes(){}
	public function hasAttribute($name){}
	public function getAttribute($name, $default = null){}
	public function getData(){}
	public function getDataClass(){}
	public function getDataLocked(){}
	public function getFormFactory(){}
	public function getOptions(){}
	public function hasOption($name){}
	public function getOption($name, $default = null){}
}