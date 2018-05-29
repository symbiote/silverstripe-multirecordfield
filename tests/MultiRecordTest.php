<?php

class MultiRecordTest extends FunctionalTest
{
    const IMAGE_BASE64_DATA = '/9j/4AAQSkZJRgABAQEAYABgAAD/4QAiRXhpZgAATU0AKgAAAAgAAQESAAMAAAABAAEAAAAAAAD/2wBDAAIBAQIBAQICAgICAgICAwUDAwMDAwYEBAMFBwYHBwcGBwcICQsJCAgKCAcHCg0KCgsMDAwMBwkODw0MDgsMDAz/2wBDAQICAgMDAwYDAwYMCAcIDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAz/wAARCAAgACADASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD9rJNaVvut81V5tYdfm3Vz9rqi3Dbvu1aebzF6/ernNDUh8TbmKs3zbqvW+qedt+Zq5CZvJk3I3y1q6TdM8a7aAOR0lnhVvm+VaL7X2s22tub+7USWs2nybnb5aZqWnvqEDMi1oZlu31xbhVUt81aumaksP3fm3Vx8No9mzb/4fu1LZ6z9lnVfMbb6UBzdz//Z';

    protected $usesDatabase = true;

    protected static $disable_themes = true;

    protected static $fixture_file = 'MultiRecordSaveAndEditTest.yml';

    protected $extraDataObjects = array(
        'MultiRecordField_PageTest',
        'MultiRecordField_HasManyTest'
    );

    public function testMultiRecordFieldPresent()
    {
        $this->logInAs('admin');

        $page = MultiRecordField_PageTest::create();
        $page->Title = 'New page';
        $page->write();

        $this->get($page->CMSEditLink());
        $this->assertPartialMatchBySelector('.js-multirecordfield-add-inline', 'Add Multi Record Field Has Many Test');
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
     * Copy-paste from UploadFieldTest
     */
    protected function getUploadFile($tmpFileName = 'UploadFieldTest-testUpload.txt') {
        $tmpFilePath = TEMP_FOLDER . '/' . $tmpFileName;
        $tmpFileContent = base64_decode(self::IMAGE_BASE64_DATA);
        //for($i=0; $i<10000; $i++) $tmpFileContent .= '0';
        file_put_contents($tmpFilePath, $tmpFileContent);

        // emulates the $_FILES array
        return array(
            'name' => array('Uploads' => array($tmpFileName)),
            'type' => array('Uploads' => array('text/plaintext')),
            'size' => array('Uploads' => array(filesize($tmpFilePath))),
            'tmp_name' => array('Uploads' => array($tmpFilePath)),
            'error' => array('Uploads' => array(UPLOAD_ERR_OK)),
        );
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