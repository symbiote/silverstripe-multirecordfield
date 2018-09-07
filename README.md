# Multi Record Field

A drop-in replacement for GridField.
Allows creating and editing multiple records in a backend or frontend form.

## Supports

* SilverStripe 3.2 and up
* PHP 5.4+
* Display Logic
* Dropzone Module
* Quick Add New Module

## Example Use
 

```php
class Page extends SiteTree {
    private static $has_many = array(
        'Cells'      => 'BasicContent',
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $editor = MultiRecordField::create('ContentCellEditor', 'Content Cells', $this->Cells());
        $fields->addFieldToTab('Root.ContentCells', $editor);

        return $fields;
    }
}
```

**MultiRecordField Nesting**

The `MultiRecordField` supports nesting of other 
`MultiRecordField`s. When the field detects a `MultiRecordField` 
in the set of fields to edit, that field is added as another nested toggle 
field inside the parent set of fields for editing. 

**Transform existing GridField into MultiRecordField **

You may want to retain a few configurations made to the GridField that MultiRecordField supports.
In that case, you'll want to utilize the `MultiRecordTransformation` class.

This will ensure the properties on GridFieldExtensions `GridFieldAddNewMultiClass` will carry across.

```php
<?php

foreach ($fields->dataFields() as $field) {
    if ($field instanceof GridField) {
        $fields->replaceField($field->getName(), $field->transform(new MultiRecordTransformation));
    }
}
```

**Custom fields**

The `MultiRecordField` uses the output of `getCMSFields` when building
the fieldlist used for editing. To provide an alternate set of fields, define
a `getMultiRecordFields` method that returns a `FieldList` object.

Additionally, the `MultiRecordField` calls the `updateMultiEditFields` 
extension hook on the _record_ being edited to allow extensions a chance to
change the fields. 

## Screenshots

![Alt text](/screenshots/1.png "Image of using Elemental with MultiRecordField")

# To-Do

Unit Tests
Backend:
    - Ensure all form data is restored correctly when a Form $Validator returns false
    - Ensure Display Logic works cleanly with this module.
    - Ensure permission checking works as expected.
Frontend:
    - Test sorting, ensure correct hidden fields are updated
    - Test add button and AJAX response.
    - Test error messages / display

## Maintainers

* Jake Bentvelzen <jake@symbiote.com.au>
 
## Bugtracker
