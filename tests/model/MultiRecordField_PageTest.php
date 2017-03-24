<?php

class MultiRecordField_PageTest extends Page implements TestOnly
{
    private static $has_many = array(
        'HasManyRelation' => 'MultiRecordField_HasManyTest',
    );

    public function getCMSFields() {
        $fields = parent::getCMSFields();
        $fields->addFieldToTab('Root.Main', MultiRecordField::create('HasManyRelation', 'Has Many', $this->HasManyRelation()));
        return $fields;
    }
}

class MultiRecordField_PageTest_Controller extends Page_Controller implements TestOnly
{
}