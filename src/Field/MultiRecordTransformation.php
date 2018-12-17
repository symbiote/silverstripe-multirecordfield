<?php

namespace Symbiote\MultiRecordField\Field;




use Exception;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\GridField\GridField;
use Symbiote\GridFieldExtensions\GridFieldAddNewMultiClass;
use SilverStripe\Forms\FormTransformation;



class MultiRecordTransformation extends FormTransformation {
	public function transform(FormField $field) {
		if (!$field instanceof GridField)
		{
			throw new Exception(__CLASS__.' requires GridField FormField type.');
		}
		$title = $field->Title();
		$list = $field->getList();
		$config = $field->getConfig();
		$result = MultiRecordField::create($field->getName(), $title, $list);
		
		// Support: GridFieldExtensions (https://github.com/symbiote/silverstripe-gridfieldextensions)
		$gridFieldAddNewMultiClass = $config->getComponentsByType(GridFieldAddNewMultiClass::class)->first();
		if ($gridFieldAddNewMultiClass) {
			$classes = $gridFieldAddNewMultiClass->getClasses($field);
			$result->setModelClasses($classes);
		}
		return $result;
	}
}

