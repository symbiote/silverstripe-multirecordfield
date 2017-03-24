<?php

class MultiRecordField_HasManyTest extends DataObject implements TestOnly
{
	private static $db = array(
		'Title' => 'Varchar(255)',
		'Content' => 'HTMLText',
	);

    private static $has_one = array(
    	'Image' => 'Image',
        'Parent' => 'Page',
    );

    public function getCMSFields() {
        $fields = parent::getCMSFields();
        $fields->removeByName('ParentID');
        return $fields;
    }
}