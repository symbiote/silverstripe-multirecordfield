<?php

class MultiRecordQuickAddNewExtension extends Extension {
    public function updateAttributes(&$attributes) {
        if (!$this->owner->multiRecordAction) {
            return;
        }
        if (!isset($attributes['data-quickaddnew-action'])) {
            // Ignore if not using QuickAddNew
            return;
        }
        $action = $attributes['data-quickaddnew-action'];

        // Rewrite QuickAddNew button action URL to feed through MultiRecordField
        $newAction = str_replace('/field/'.$this->owner->getName(), '/field/'.$this->owner->multiRecordAction, $action);
        $attributes['data-quickaddnew-action'] = $newAction;
    }

    public function updateQuickAddNewForm($form) {
        $formField = $form->getController();
        if (!$formField instanceof FormField) {
            return;
        }
        // Rewrite form action to feed through MultiRecordField
        if ($formField->multiRecordAction) {
            $formAction = Controller::join_links($formField->getForm()->FormAction(), 'field/'.$formField->multiRecordAction, $form->getName());
            $form->setFormAction($formAction);
        }
    }
}