# CiviCRM Profile Conditionals

This civicrm extension adds support for dynamic in-page behaviors based on changes
to form fields in CiviCRM forms, including showing/hiding form elements and
setting field values.

Current support includes the following forms:
  * event registration (/civicrm/event/register)

Patch welcome for other forms.

## Install

git clone https://github.com/twomice/com.joineryhq.profcond in your local extension repository and it should work.

## Configuration

This extension has no browser-basd configuration form within CiviCRM. Configuration
is by PHP arrays in code within the civicrm.settings.php file.

You need to add in your civicrm.settings.php a new config variable
```php
global $civicrm_setting;
$civicrm_setting['com.joineryhq.profcond']['com.joineryhq.profcond'] = array(
  'event' => array(
    81 => array (
      'onload' => array(
        'selectors' => array(
          '#priceset' => array(
            'display' => 'hide',
          ),
        ),
        'profiles' => array (
          '1' => array(
            'display' => 'hide',
          ),
        ),
      ),
      '1_swim' => array(
        'conditions' => array(
          'all_of' => array(
            array(
              'id' => 'custom_261',
              'op' => 'value_is',
              'value' => '1', //"swim"
            ),
          )
        ),
        'states' => array(
          'pass' => array (
            'profiles' => array (
              '108' => array(
                'display' => 'show',
              ),
            ),
            'selectors' => array(
              '#priceset' => array(
                'display' => 'show',
              ),
              'div.price-set-row.ticket_selection-row1' => array(
                'display' => 'show',
              ),
              'div.price-set-row.ticket_selection-row1 input[type="radio"]' => array(
                'is_price_change' => TRUE,
                'properties' => array(
                  'checked' => TRUE,
                ),
              ),
              'div.price-set-row.ticket_selection-row2' => array(
                'display' => 'hide',
              ),
            ),
          ),
          'fail' => array(
            'profiles' => array (
              '1' => array(
                'display' => 'hide',
              ),
              '108' => array(
                'display' => 'hide',
              ),
            ),
            'selectors' => array(
              'div.price-set-row.ticket_selection-row1 input[type="radio"]' => array(
                'is_price_change' => TRUE,
                'properties' => array(
                  'checked' => FALSE,
                ),
              ),
            ),
          ),
        ),
      ),
    ),
  ),
  'contribution' => array(
    // Contribution pages are not yet supported.
  ),
);

```

This nested array format is fragile but explicit, expressing any number of rules,
and for each rule, any number of conditions (either AND or OR), and any number
of field states to manifest when the condition test as either true or false.

```php
$civicrm_setting['com.joineryhq.profcond']['com.joineryhq.profcond'] = array(
  '[page-type]' => array(
    [page-id] => array (
      '[rule-name]' => array(
        'conditions' => array(
          '[condition-type]' => array(
            array(
              'id' => '[field-id]',
              'op' => '[operator]',
              'value' => '[field-value]',
            ),
          )
        ),
        'states' => array(
          '[condition-success]' => array (
            '[state-type]' => array (
              '[state-selector]' => array(
                '[state-property]' => '[state-property-value]',
                [option] => [option_value],
              ),
            ),
          ),
        ),
      ),
    ),
  ),
);

```

### [page-type]
Must be 'event' to indicate that this section applies to event registration pages.
In future, other values may be supported, such as 'contribution' for contribution
pages.

### [page-id]
Must be an event ID. Rules in this section will only be applied to the events with
this ID.

### [rule-name]
Must be a unique string within this page-type/page-id section.
Must also be suitable for use as a CSS class.

### [condition-type]
A string indicating how the conditions should be joined. One of:
* any_of: If any condition is met, the conditions are considered passing.
* all_of: If all conditions are met, the conditions are considered passing.

Combining `any_of` with `all_of` has not been tested and may produce unpredictable results.
It's recommended to use one or the other. Nesting of conditions is not supported.
Thus, under 'conditions', you should have one array element, keyed to either `any_of`
or `all_of`, containing any number of conditions.

### [field-id]
The HTML "id" attribute of the field to be tested in this condition.

### [operator]
The type of comparison to be performed for this field. One of:

* value_is: The field must have the given value for the condition to pass.
* value_is_one_of: The field must have any of the given values for the condition to pass.
* is_checked: The field must be checked to pass (appropriate for checkbox and radio elements).

### [field-value]
The value to be compared in this condition.

### [condition-success]
One of:

* 'pass': If the conditions are considered passing, these states will be applied.
* 'fail': If the conditions are considered failing, these states will be applied.

### [state-type]
One of:

* 'profile': This state will be applied to a CiviCRM profile on the page.
* 'selector': This state will be applied to an element on the page described by a jQuery selector.

### [state-selector]
Depending on the value of [state-type], one of the following:

* If [state-type] is 'profile', the CiviCRM system ID of the profile.
* If [state-type] is 'selector', a jQuery selector describing one or more elements on the page.

### [state-property]
The attribute of the given element to be changed. One of:

* 'display'
* 'properties'
* 'attributes'
* 'value'
* 'before'
* 'after'
* 'is_price_change'

See [state-property-value] for more information.

### [state-property-value]
Depending on the value of [state-property], the appropriate value for that property.
For these values of [state-property], the possible [state-property] values are:

* 'display': One of:
  * 'hide': The element will be hidden with jQuery().hide().
  * 'show': The element will be shown with jQuery().show().
* 'properties': An associative array of properties, to be applied to the element with jQuery().prop().
* 'attributes': An associative array of attributes, to be applied to the element with jQuery().attr().
* 'value': The value to which the element (which should be a form field) will be set, using jQuery().val().
* 'before': The element will be relocated before the element indicated by this jQuery selector, with jQuery().insertBefore().
* 'after': The element will be relocated after the element indicated by this jQuery selector, with jQuery().insertAfter().
* 'is_price_change': TRUE if this field is a price field and changing it should update the total amount. Defaults to FALSE.

## More examples
A more involved example is contained in [CONFIG_EXAMPLE.md](CONFIG_EXAMPLE.md).

