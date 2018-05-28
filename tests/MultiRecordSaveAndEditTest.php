<?php

class MultiRecordSaveAndEditTest extends MultiRecordTest
{
    protected $usesDatabase = true;
    
    protected static $disable_themes = true;

    protected static $fixture_file = 'MultiRecordSaveAndEditTest.yml';

    protected $extraDataObjects = array(
        'MultiRecordField_PageTest',
        'MultiRecordField_HasManyTest'
    );

    public function testSaveAndEditNewRecords_NoSort()
    {
        $this->logInAs('admin');

        $page = MultiRecordField_PageTest::create();
        $page->Title = 'New page';
        $page->write();

        // Upload file through MultiRecordField
        $this->get($page->CMSEditLink());
        $_FILES = array('Image' => $this->getUploadFile('tmpfile.jpg'));
        $response = $this->post(
            "admin/pages/edit/EditForm/field/"."HasManyRelation"."/addinlinerecord/MultiRecordField_HasManyTest/new/field/".key($_FILES)."/upload",
            $_FILES
        );
        $this->assertEquals(200, $response->getStatusCode());
        $uploadResponseData = json_decode($response->getBody(), true);
        $this->assertTrue(isset($uploadResponseData[0]['id']), 'Cannot upload file on UploadField inside MultiRecordField');
        $fileID = $uploadResponseData[0]['id'];
        $this->assertNotEquals(0, $fileID);
        $this->get($page->CMSEditLink());

        // Add relations
        $response = $this->submitForm('Form_EditForm', 'action_publish', array(
            'ID' => $page->ID,
            'Title' => 'New page, new name',
        ), array(
            'HasManyRelation__MultiRecordField__MultiRecordField_HasManyTest__new_1__Title' => 'Test A Title',
            'HasManyRelation__MultiRecordField__MultiRecordField_HasManyTest__new_1__Content' => '<p>Test A Content</p>',
            'HasManyRelation__MultiRecordField__MultiRecordField_HasManyTest__new_2__Title' => 'Test B Title',
            'HasManyRelation__MultiRecordField__MultiRecordField_HasManyTest__new_2__Content' => '<p>Test B Content</p>',
            'HasManyRelation__MultiRecordField__MultiRecordField_HasManyTest__new_2__Image' => array('Files' => array($fileID)),
        ));
        $this->assertEquals(200, $response->getStatusCode());
        $page = Page::get()->byID($page->ID);
        $this->assertEquals('New page, new name', $page->Title);
        $hasManyRelation = $page->HasManyRelation()->sort('ID')->toArray();
        $this->assertEquals(2, count($hasManyRelation));
        $this->assertEquals('Test A Title', $hasManyRelation[0]->Title);
        $this->assertEquals('<p>Test A Content</p>', $hasManyRelation[0]->Content);
        $this->assertEquals(0, $hasManyRelation[0]->ImageID);
        $this->assertEquals('Test B Title', $hasManyRelation[1]->Title);
        $this->assertEquals('<p>Test B Content</p>', $hasManyRelation[1]->Content);
        $this->assertNotEquals(0, $hasManyRelation[1]->ImageID);

        // Edit existing MultiRecordField records on page
        $this->get($page->CMSEditLink());
        $response = $this->submitForm('Form_EditForm', 'action_publish', array(
            'ID' => $page->ID,
            'Title' => 'New page, newer name',
        ), array(
            'HasManyRelation__MultiRecordField__MultiRecordField_HasManyTest__'.$hasManyRelation[0]->ID.'__Title' => 'Test A New Title',
            'HasManyRelation__MultiRecordField__MultiRecordField_HasManyTest__'.$hasManyRelation[0]->ID.'__Content' => '<p>Test A New Content</p>',
            'HasManyRelation__MultiRecordField__MultiRecordField_HasManyTest__'.$hasManyRelation[1]->ID.'__Title' => 'Test B New Title',
            'HasManyRelation__MultiRecordField__MultiRecordField_HasManyTest__'.$hasManyRelation[1]->ID.'__Content' => '<p>Test B New Content</p>',
        ));
        $this->assertEquals(200, $response->getStatusCode());
        $page = Page::get()->byID($page->ID);
        $this->assertEquals('New page, newer name', $page->Title);
        $hasManyRelation = $page->HasManyRelation()->sort('ID')->toArray();
        $this->assertEquals(2, count($hasManyRelation));
        $this->assertEquals('Test A New Title', $hasManyRelation[0]->Title);
        $this->assertEquals('<p>Test A New Content</p>', $hasManyRelation[0]->Content);
        $this->assertEquals('Test B New Title', $hasManyRelation[1]->Title);
        $this->assertEquals('<p>Test B New Content</p>', $hasManyRelation[1]->Content);

        // Add new and edit existing MultiRecordField records on page
        $this->get($page->CMSEditLink());
        $response = $this->submitForm('Form_EditForm', 'action_publish', array(
            'ID' => $page->ID,
            'Title' => 'New page, newest name',
        ), array(
            'HasManyRelation__MultiRecordField__MultiRecordField_HasManyTest__'.$hasManyRelation[0]->ID.'__Title' => 'Test A Newer Title',
            'HasManyRelation__MultiRecordField__MultiRecordField_HasManyTest__'.$hasManyRelation[0]->ID.'__Content' => '<p>Test A Newer Content</p>',
            'HasManyRelation__MultiRecordField__MultiRecordField_HasManyTest__'.$hasManyRelation[1]->ID.'__Title' => 'Test B Newer Title',
            'HasManyRelation__MultiRecordField__MultiRecordField_HasManyTest__'.$hasManyRelation[1]->ID.'__Content' => '<p>Test B Newer Content</p>',
            'HasManyRelation__MultiRecordField__MultiRecordField_HasManyTest__new_1__Title' => 'Test C Title',
            'HasManyRelation__MultiRecordField__MultiRecordField_HasManyTest__new_1__Content' => '<p>Test C Content</p>',
        ));
        $this->assertEquals(200, $response->getStatusCode());
        $page = Page::get()->byID($page->ID);
        $this->assertEquals('New page, newest name', $page->Title);
        $hasManyRelation = $page->HasManyRelation()->sort('ID')->toArray();
        $this->assertEquals(3, count($hasManyRelation));
        $this->assertEquals('Test A Newer Title', $hasManyRelation[0]->Title);
        $this->assertEquals('<p>Test A Newer Content</p>', $hasManyRelation[0]->Content);
        $this->assertEquals('Test B Newer Title', $hasManyRelation[1]->Title);
        $this->assertEquals('<p>Test B Newer Content</p>', $hasManyRelation[1]->Content);
        $this->assertEquals('Test C Title', $hasManyRelation[2]->Title);
        $this->assertEquals('<p>Test C Content</p>', $hasManyRelation[2]->Content);
    }

    public function testSaveAndEditCheckboxes()
    {
        $this->logInAs('admin');

        $page = MultiRecordField_PageTest::create();
        $page->Title = 'New page';
        $page->write();

        $this->get($page->CMSEditLink());

        // Add relations
        $response = $this->submitForm('Form_EditForm', 'action_publish', array(
            'ID' => $page->ID,
            'Title' => 'New page, new name',
        ), array(
            'HasManyRelation__MultiRecordField__MultiRecordField_HasManyTest__new_1__Title' => 'Test A Title',
            'HasManyRelation__MultiRecordField__MultiRecordField_HasManyTest__new_1__Content' => '<p>Test A Content</p>',
            'HasManyRelation__MultiRecordField__MultiRecordField_HasManyTest__new_1__ACheckbox' => true,
            'HasManyRelation__MultiRecordField__MultiRecordField_HasManyTest__new_2__Title' => 'Test B Title',
            'HasManyRelation__MultiRecordField__MultiRecordField_HasManyTest__new_2__ACheckbox' => null,
        ));
        $this->assertEquals(200, $response->getStatusCode());
        $page = Page::get()->byID($page->ID);
        $this->assertEquals('New page, new name', $page->Title);
        $hasManyRelation = $page->HasManyRelation()->sort('ID')->toArray();
        $this->assertEquals(2, count($hasManyRelation));
        $this->assertEquals('Test A Title', $hasManyRelation[0]->Title);
        $this->assertEquals(true, $hasManyRelation[0]->ACheckbox);
        $this->assertEquals('Test B Title', $hasManyRelation[1]->Title);
        $this->assertEquals(false, $hasManyRelation[1]->ACheckbox);

        // Edit existing MultiRecordField records on page
        $this->get($page->CMSEditLink());
        $response = $this->submitForm('Form_EditForm', 'action_publish', array(
            'ID' => $page->ID,
            'Title' => 'New page, newer name',
        ), array(
            'HasManyRelation__MultiRecordField__MultiRecordField_HasManyTest__'.$hasManyRelation[0]->ID.'__Title' => 'Test A New Title',
            'HasManyRelation__MultiRecordField__MultiRecordField_HasManyTest__'.$hasManyRelation[0]->ID.'__Content' => '<p>Test A New Content</p>',
            'HasManyRelation__MultiRecordField__MultiRecordField_HasManyTest__'.$hasManyRelation[0]->ID.'__ACheckbox' => null,
            'HasManyRelation__MultiRecordField__MultiRecordField_HasManyTest__'.$hasManyRelation[1]->ID.'__Title' => 'Test B New Title',
            'HasManyRelation__MultiRecordField__MultiRecordField_HasManyTest__'.$hasManyRelation[1]->ID.'__Content' => '<p>Test B New Content</p>',
            'HasManyRelation__MultiRecordField__MultiRecordField_HasManyTest__'.$hasManyRelation[1]->ID.'__ACheckbox' => true,
        ));
        $this->assertEquals(200, $response->getStatusCode());
        $page = Page::get()->byID($page->ID);
        $this->assertEquals('New page, newer name', $page->Title);
        $hasManyRelation = $page->HasManyRelation()->sort('ID')->toArray();
        $this->assertEquals(2, count($hasManyRelation));
        $this->assertEquals('Test A New Title', $hasManyRelation[0]->Title);
        $this->assertEquals(false, $hasManyRelation[0]->ACheckbox);
        $this->assertEquals('Test B New Title', $hasManyRelation[1]->Title);
        $this->assertEquals(true, $hasManyRelation[1]->ACheckbox);
    }
}