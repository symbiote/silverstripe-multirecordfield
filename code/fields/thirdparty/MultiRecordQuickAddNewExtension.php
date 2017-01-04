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
}