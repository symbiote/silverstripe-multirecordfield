<?php

namespace Symbiote\MultiRecordField\Field\thirdparty;


use Sheadawson\Linkable\Forms\EmbeddedObjectField;



if (class_exists(EmbeddedObjectField::class)) {
    /**
     * Support for EmbeddedObjectField from Shea Dawson's Linkable module.
     */
    class MultiRecordEmbeddedObjectField extends EmbeddedObjectField
    {
        /**
         * Override with a new action.
         *
         * @var string
         */
        public $multiRecordAction = '';

        public function Link($action = null) {
            if ($this->multiRecordAction) {
                return $this->form->FormAction().'/field/'.$this->multiRecordAction.'/'.$action;
            }
            return parent::Link($action);
        }
    }

}
