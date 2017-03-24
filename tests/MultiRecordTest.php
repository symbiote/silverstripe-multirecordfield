<?php

class MultiRecordTest extends FunctionalTest
{
    protected $usesDatabase = true;
    
    protected static $disable_themes = true;

    protected static $fixture_file = 'MultiRecordTest.yml';

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

        // Add new MultiRecordField records on page
        $this->get($page->CMSEditLink());
        $response = $this->submitForm('Form_EditForm', 'action_publish', array(
            'ID' => $page->ID,
            'Title' => 'New page, new name',
        ), array(
            'HasManyRelation__MultiRecordField__MultiRecordField_HasManyTest__new_1__Title' => 'Test A Title',
            'HasManyRelation__MultiRecordField__MultiRecordField_HasManyTest__new_1__Content' => '<p>Test A Content</p>',
            'HasManyRelation__MultiRecordField__MultiRecordField_HasManyTest__new_2__Title' => 'Test B Title',
            'HasManyRelation__MultiRecordField__MultiRecordField_HasManyTest__new_2__Content' => '<p>Test B Content</p>',
        ));
        $this->assertEquals(200, $response->getStatusCode());
        $page = Page::get()->byID($page->ID);
        $this->assertEquals('New page, new name', $page->Title);
        $hasManyRelation = $page->HasManyRelation()->sort('ID')->toArray();
        $this->assertEquals(2, count($hasManyRelation));
        $this->assertEquals('Test A Title', $hasManyRelation[0]->Title);
        $this->assertEquals('<p>Test A Content</p>', $hasManyRelation[0]->Content);
        $this->assertEquals('Test B Title', $hasManyRelation[1]->Title);
        $this->assertEquals('<p>Test B Content</p>', $hasManyRelation[1]->Content);

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

    /**
     * Identical to parent::submitForm() but allows $ajaxData as $data needs to explictly exist in the form or
     * it's silently lost.
     */
    public function submitForm($formID, $button = null, $data = array(), $ajaxData = array()) 
    {
        $this->cssParser = null;
        $response = $this->submitForm_mainSession($this->mainSession, $formID, $button, $data, $ajaxData);
        if($this->autoFollowRedirection && is_object($response) && $response->getHeader('Location')) {
            $response = $this->mainSession->followRedirection();
        }
        return $response;
    }

    /**
     * Identical to TestSession::submitForm() but allows $ajaxData as $data needs to explictly exist in the form or
     * it's silently lost.
     */
    protected function submitForm_mainSession($session, $formID, $button = null, $data = array(), $ajaxData = array()) 
    {
        $page = $session->lastPage();
        if($page) {
            $form = $page->getFormById($formID);
            if (!$form) {
                user_error("TestSession::submitForm failed to find the form {$formID}");
            }

            foreach($data as $k => $v) {
                $form->setField(new SimpleByName($k), $v);
            }

            if($button) {
                $submission = $form->submitButton(new SimpleByName($button));
                if(!$submission) throw new Exception("Can't find button '$button' to submit as part of test.");
            } else {
                $submission = $form->submit();
            }

            $url = Director::makeRelative($form->getAction()->asString());

            $postVars = array();
            parse_str($submission->_encode(), $postVars);
            // NOTE(Jake): Add in additional AJAX data
            $postVars = array_merge($postVars, $ajaxData);
            return $session->post($url, $postVars);

        } else {
            user_error("TestSession::submitForm called when there is no form loaded."
                . " Visit the page with the form first", E_USER_WARNING);
        }
    }

    /*protected function getAJAXFields($class = '', $index = 1)
    {
        // todo(Jake): jump $this->get() back to previous URL
        $storeResponse = $this->mainSession->lastResponse();
        Debug::dump($storeResponse); exit;

        // todo(Jake): Retrieve from HTML response
        $formAction = 'admin/pages/edit/EditForm';
        if (!$class) {
            // todo(Jake): Build from data-action, etc
            $class = 'MultiRecordField_HasManyTest';
        }
        $fieldAction = '/field/HasManyRelation/addinlinerecord/'.$class;
        $this->get(Controller::join_links($formAction, $fieldAction));

        // Get fields
        $result = array();
        foreach (array('input', 'textarea', 'select') as $fieldTagType) {
            foreach ($this->cssParser()->getBySelector($fieldTagType) as $simpleXMLElement) {
                $attributes = array();
                foreach ($simpleXMLElement->attributes() as $k => $v) {
                    $attributes[$k] = $v->__toString();
                }
                $name = $attributes['name'];
                $name = str_replace(array(
                    // Handle various nested depths
                    'o-multirecordediting-1-id',
                    'o-multirecordediting-2-id',
                    'o-multirecordediting-3-id',
                    'o-multirecordediting-4-id'
                ), 'new_'.$depth, $name);
                $attributes['name'] = $name;
                $result[$name] = $attributes;
            }
        }

        return $result;
    }*/

    // eg. request URL: http://127.0.0.1/Silverstripe/3.5.3/admin/pages/edit/EditForm/field/HasManyRelation/addinlinerecord/MultiRecordField_HasManyTest
    // form action: admin/pages/edit/EditForm
    // field action:  /field/HasManyRelation/addinlinerecord/MultiRecordField_HasManyTest
    /*
        // todo(Jake): Retrieve from HTML response
        $formAction = 'admin/pages/edit/EditForm';
        // todo(Jake): Build from data-action, etc
        $fieldAction = '/field/HasManyRelation/addinlinerecord/MultiRecordField_HasManyTest';
        // Check if correct number of fields are returned
        $this->get(Controller::join_links($formAction, $fieldAction));
        $fields = $this->getAJAXFormFields();
    */
    /*protected function getAJAXFormFields() 
    {
        $result = array();
        foreach (array('input', 'textarea', 'select') as $fieldTagType) {
            foreach ($this->cssParser()->getBySelector($fieldTagType) as $simpleXMLElement) {
                $attributes = array();
                foreach ($simpleXMLElement->attributes() as $k => $v) {
                    $attributes[$k] = $v->__toString();
                }
                $attributes['name'] = $this->replaceTempName($attributes['name']);
                $result[$attributes['name']] = $attributes;
            }
        }
        return $result;
    }
    protected function replaceTempName($name, $index = 1) 
    {
        return str_replace(array(
            // Handle various nested depths
            'o-multirecordediting-1-id',
            'o-multirecordediting-2-id',
            'o-multirecordediting-3-id',
            'o-multirecordediting-4-id'
        ), 'new_'.$index, $name);
    }*/
}