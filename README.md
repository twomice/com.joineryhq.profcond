
# CiviCRM Profile Conditionals

This CiviCRM extension adds support for dynamic in-page behaviors based on changes
to form fields in CiviCRM forms, including showing/hiding form elements and
setting field values.

Current support includes the following forms:
  * event registration (/civicrm/event/register), including "additional participant" form
  * contribution page (/civicrm/contribute/transact)

Patch welcome for other forms.

## Install

git clone https://github.com/twomice/com.joineryhq.profcond in your local extension repository and it should work.

## Configuration

This extension has no browser-based configuration form within CiviCRM. Configuration
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
  ),
);

```

This nested array format is fragile but explicit, expressing any number of rules,
and for each rule, any number of conditions (either AND or OR), and any number
of field states to manifest when the condition test as either true or false.

```php
$civicrm_setting['com.joineryhq.profcond']['com.joineryhq.profcond'] = array(
  '[entity-type]' => array(
    [entity-id] => array (
      '[rule-name]' => array(
        'limit' => array(
          'formId' => array(
            'pattern' => [limit-regex-pattern],
            'flags' => [limit-regex-flags],
        ),
        'conditions' => array(
          '[condition-type]' => array(
            array(
              '[subject-identifier-type]' => '[subject-identifier]',
              'op' => '[operator]',
              'value' => '[test-value]',
              'negate' => [negate-value],
            ),
          )
        ),
        'states' => array(
          '[condition-success]' => array (
            '[state-type]' => array (
              '[state-selector]' => array(
                '[state-property]' => '[state-property-value]',
              ),
            ),
          ),
        ),
      ),
    ),
  ),
);

```

### [entity-type]
Must be 'event', 'contribution', or 'priceset' to indicate that this section applies 
to event registration pages, contribution pages, or one of those using a specific price set.
In future, other values may be supported.

### [entity-id]
Must be one these:
* An event ID, contribution page ID, or price set ID. Rules in this section will only be applied to this entity-type and ID.
* The string `all`, to apply the rule to all entities of the specified entity-type.  If you specify both `all`
and a specific ID, the ID settings will override the `all` settings.

### [rule-name]
* Must be a unique string within this entity-type/entity-id section.
* Must also be suitable for use as a CSS class.
* The special name 'onload' defines a rule which fires upon page load;  
  its key is 'onload' and its value is an array in the form of [condition-success];  
  upon page load, the state described in this rule is applied unconditionally.

### "limit"
Optional array defining a regular expression that will limit the forms on which
this rule is applied. Because [entity-type] and [entity-id] already facilitate limiting
rules to specific events and contribution pages, this "limit" array is only expected
to be useful in the case of multi-participant event registrations, in which the
primary participant form has the formId "Register", and additional participant
forms have the formId "Participant_1", "Participant_2", etc. The "limit" definition
provides a way to specify that certain rules should only apply on the "Register" formId,
or one or more of the additional participant forms.

### [limit-regex-pattern]
String. A pattern to be used in `new RegExp('[limit-regex-pattern]', '[limit-regex-flags]').
If the regular expression does not matche the formId value (see "limit"), this rule
will not be used at all on the page.

### [limit-regex-flags]
String. A pattern to be used in `new RegExp('[limit-regex-pattern]', '[limit-regex-flags]').
Optional, and probably not needed in any case; but see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Regular_Expressions#Advanced_searching_with_flags
for more on regular expression flags.

### [condition-type]
A string indicating how the conditions should be joined. One of:
* any_of: If any condition is met, the conditions are considered passing.
* all_of: If all conditions are met, the conditions are considered passing.

Combining `any_of` with `all_of` has not been tested and may produce unpredictable results.
It's recommended to use one or the other. Nesting of conditions is not supported.
Thus, under 'conditions', you should have one array element, keyed to either `any_of`
or `all_of`, containing any number of conditions.

### [subject-identifier-type]
One of:
* id: if the item being evaluated is a DOM element on the page.
* selector: if the item being evaluated is a DOM element on the page.
* variable: if the item being evaluated is a JavaScript variable. (See "Variables" for
  more information about the JavaScript variables defined by Profile Conditionals.)

### [subject-identifier]
One of these, depending on the value of [subject-identifier-type]:
* If [subject-identifier-type] is 'id': The HTML "id" attribute of the field to be tested in this condition.
* If [subject-identifier-type] is 'selector': A jQuery selector describing the field to be tested in this condition.
* If [subject-identifier-type] is 'variable': One of these:
  * String: name of a global variable (property of the window object)
  * Array: ordered array of nested variable names within a global object variable (property of the window object)  
    Example:
    * `['CRM','vars','myextension','foobar',0]` to test the value of window.CRM.vars.myextension.foobar[0]

### [operator]
The type of comparison to be performed for this field. One of:

* value_is: The subject of [subject-identifier] must have the given [test-value] value for the condition to pass.
* value_is_one_of: The subject of [subject-identifier] must have any of the given [test-value] values for the condition to pass.
* value_gt: The subject of [subject-identifier] must have a value which is greater than the given [test-value] values for the condition to pass (uses JavaScript `>` operator, expects to compare numeric values).
* value_gte: The subject of [subject-identifier] must have a value which is greater than or equal to the given [test-value] values for the condition to pass (uses JavaScript `>=` operator, expects to compare numeric values).
* value_lt: The subject of [subject-identifier] must have a value which is less than the given [test-value] values for the condition to pass (uses JavaScript `<` operator, expects to compare numeric values).
* value_lte: The subject of [subject-identifier] must have a value which is less than or equal to the given [test-value] values for the condition to pass (uses JavaScript `<=` operator, expects to compare numeric values).
* is_set: The subject of [subject-identifier] must have a value for the condition to pass.
* is_checked: The subject of [subject-identifier] must be checked to pass (appropriate for checkbox and radio elements);  
  only appropriate where [subject-identifier-type] is 'id' or 'selector'.

### [test-value]
The value to be compared in this condition.

### [negate-value]
Boolean TRUE or FALSE. If TRUE, the condition evaluation (a boolean) will be negated. Default is FALSE (no negation).
For example, the following condition will pass if the value of `#custom_261` is NOT '1':
```
  array(
    'id' => 'custom_261',
    'op' => 'value_is',
    'value' => '1',
    'negate' => TRUE,
  ),
```

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
* 'css'
* 'html'
* 'attributes'
* 'value'
* 'before'
* 'after'
* 'is_price_change'
* 'triggerEvents' (experimental)
* 'copyValue' (experimental)

See [state-property-value] for more information.

### [state-property-value]
Depending on the value of [state-property], the appropriate value for that property.
For these values of [state-property], the possible [state-property] values are:

* 'display': One of:
  * 'hide': The element will be hidden with jQuery().hide().
  * 'show': The element will be shown with jQuery().show().
* 'properties': An associative array of properties, to be applied to the element with jQuery().prop().
* 'css': An associative array of properties, to be applied to the element with jQuery().css().
* 'html': An associative array of properties, to be applied to the element with jQuery().html().
* 'attributes': An associative array of attributes, to be applied to the element with jQuery().attr().
* 'value': The value to which the element (which should be a form field) will be set, using jQuery().val().
* 'before': The element will be relocated before the element indicated by this jQuery selector, with jQuery().insertBefore().
* 'after': The element will be relocated after the element indicated by this jQuery selector, with jQuery().insertAfter().
* 'is_price_change': TRUE if this field is a price field and changing it should update the total amount. Defaults to FALSE.
* 'triggerEvents' (experimental): An array of event types, to be triggered on the element with jQuery().trigger().
* 'copyValue' (experimental): The element is assumed to be a field, the value of which will be set to the value of the element indicated by this jQuery selector, which is also assume to be a field.

## Variables
ProfileConditionals defines these JavaScript variables in CRM.vars.profcond:

* pageConfig: the full array of rules as configured for the current [entity-type] and [entity-id].
* formId: the `id` attribute of the CiviCRM form being displayed.
* submittedParticipantValues: defined on "additional participantn" forms in a multi-participant event registration;
  an array of the values submitted values (omitting credit card details) from the
  participants submitted on earlier forms. `submittedParticipantValues[0]` contains
  values submitted for the primary participant, `submittedParticipantValues[1]` for
  the first additional participant, etc. This variable, when referenced by a condition
  in which [subject-identifier] = 'variable', optionally in conjunction with a rule "limit",
  allows definition of rules which affect behavior on an additional participant
  form based on selections in earlier participant forms.

## More examples
A more involved example is contained in [CONFIG_EXAMPLE.md](CONFIG_EXAMPLE.md).

## Developer hooks
This extension provides `hook_civicrm_profcond_alterConfig()`, which can be implemented like so:
```
/**
 * Implements hook_civicrm_profcond_alterConfig().
 * @link https://twomice.github.io/com.joineryhq.profcond/#developer-hooks
 */
function myextension_civicrm_profcond_alterConfig(&$config, $pageType, $entityId) {
  // $config contains the full value of $civicrm_setting['com.joineryhq.profcond']['com.joineryhq.profcond'];
  //    change as needed.
  // $pageType will be either 'contribution' or 'event'.
  // $entityId will be the current event ID or contribution page ID.

  // If we're on an event registration page, and my custom decider says to do so,
  // create a rule for showing/hiding profile id=1.
  if ($pageType == 'event') {
    if (_myextensionWantsToAlterProfCondForEvent($entityId)) {
      $config['event'][$entityId]['myextension_1'] = [
        'conditions' => [
          'all_of' => [
            [
              'id' => 'myExtensionCustomField',
              'op' => 'value_is_one_of',
              'value' => [1, 2, 3, 4],
            ],
          ],
        ],
        'states' => [
          'pass' => [
            'profiles' => [
              1 => [
                'display' => 'show',
              ],
            ],
          ],
          'fail' => [
            'profiles' => [
              1 => [
                'display' => 'hide',
              ],
            ],
          ],
        ],
      ];
    }
  }
}
```

## FAQs
1. **What about wildcards? I want to apply the same rules to several different events.**  
    Use the string `all` as the entity-id; see notes on `[entity-id]`, above.

    Alternately, you could do something like this for a more specific set of events (or the equivalent, for contribution pages):
    ```
    $eventsConfig = array(
      // whatever
    );

    $civicrm_setting['com.joineryhq.profcond']['com.joineryhq.profcond'] = array(
      'event' => array(
        '81' => $eventsConfig,
        '82' => $eventsConfig,
        '83' => $eventsConfig,
      ),
    );
    ```

2. **I want to conditionally display *and require* a field. How can I do that?**  
   Configure the two fields as required within the profile. Use this extension to show/hide them according to your own rules. If this extension hides a required field, it will also ensure that the field is not required when hidden.


## Support
![screenshot](/images/joinery-logo.png)

Joinery provides services for CiviCRM including custom extension development, training, data migrations, and more. We aim to keep this extension in good working order, and will do our best to respond appropriately to issues reported on its [github issue queue](https://github.com/twomice/com.joineryhq.profcond/issues). In addition, if you require urgent or highly customized improvements to this extension, we may suggest conducting a fee-based project under our standard commercial terms.  In any case, the place to start is the [github issue queue](https://github.com/twomice/com.joineryhq.profcond/issues) -- let us hear what you need and we'll be glad to help however we can.

And, if you need help with any other aspect of CiviCRM -- from hosting to custom development to strategic consultation and more -- please contact us directly via https://joineryhq.com
