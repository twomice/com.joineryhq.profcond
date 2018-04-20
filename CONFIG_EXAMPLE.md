# CiviCRM Profile Conditionals
## A more involved example configuration:

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
          '#priceset div.discounted_ticket_x2-section' => array(
            'display' => 'hide',
          ),
          'div#editrow-participant_role' => array(
            'display' => 'hide',
          ),
        ),
        'profiles' => array (
          '1' => array(
            'display' => 'hide', 
          ),
          // 108	Bay Parade Swimmer
          '108' => array(
            'display' => 'hide',
          ),
          // 110	Bay Parade General Questions
          '110' => array(
            'display' => 'hide',
          ),
          // 111	Bay Parade Boater Volunteer
          '111' => array(
            'display' => 'hide',
          ),
          // 112	Bay Parade Paddler Volunteer
          '112' => array(
            'display' => 'hide',
          ),
          // 113	Bay Parade On Land Volunteer
          '113' => array(
            'display' => 'hide',
          ),
          // 114	Bay Parade Volunteer
          '114' => array(
            'display' => 'hide',
          ),
          // 116	Bay Parade on a boat Volunteer
          '116' => array(
            'display' => 'hide',
          ),
          // 120 Bay Parade Fundraising
          '120' => array(
            'display' => 'hide',
          ),
          // 121 Bay Parade Paddling
          '121' => array(
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
              'div.price-set-row.ticket_selection-row3' => array(
                'display' => 'hide',
              ),
              'div.price-set-row.ticket_selection-row4' => array(
                'display' => 'hide',
              ),
              'div#editrow-participant_role select' => array(
                'value' => '17',
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
      '2_kayak' => array(
        'conditions' => array(
          'all_of' => array(
            array(
              'id' => 'custom_261',
              'op' => 'value_is', 
              'value' => '2', //"kayak"
            ),
          )
        ),
        'states' => array(
          'pass' => array (
            'profiles' => array (
              '109' => array(
                'display' => 'show',
              ),
            ),
            'selectors' => array(
              '#priceset' => array(
                'display' => 'show',
              ),
              'div.price-set-row.ticket_selection-row1' => array(
                'display' => 'hide',
              ),
              'div.price-set-row.ticket_selection-row2' => array(
                'display' => 'show',
              ),
              'div.price-set-row.ticket_selection-row2 input[type="radio"]' => array(
                'is_price_change' => TRUE,
                'properties' => array(
                  'checked' => TRUE,
                ),
              ),
              'div.price-set-row.ticket_selection-row3' => array(
                'display' => 'hide',
              ),
              'div.price-set-row.ticket_selection-row4' => array(
                'display' => 'hide',
              ),
              'div#editrow-participant_role select' => array(
                'value' => '14',
              ),
            ),
          ),
          'fail' => array(
            'profiles' => array (
              '109' => array(
                'display' => 'hide',
              ),
            ),
            'selectors' => array(
              'div.price-set-row.ticket_selection-row2 input[type="radio"]' => array(
                'is_price_change' => TRUE,
                'properties' => array(
                  'checked' => FALSE,
                ),
              ),
            ),
          ),
        ),
      ), 
      '3_sup' => array(
        'conditions' => array(
          'all_of' => array(
            array(
              'id' => 'custom_261',
              'op' => 'value_is', 
              'value' => '3', //"SUP"
            ),
          )
        ),
        'states' => array(
          'pass' => array (
            'profiles' => array (
              '117' => array(
                'display' => 'show',
              ),
            ),
            'selectors' => array(
              '#priceset' => array(
                'display' => 'show',
              ),
              'div.price-set-row.ticket_selection-row1' => array(
                'display' => 'hide',
              ),
              'div.price-set-row.ticket_selection-row2' => array(
                'display' => 'hide',
              ),
              'div.price-set-row.ticket_selection-row3' => array(
                'display' => 'show',
              ),
              'div.price-set-row.ticket_selection-row3 input[type="radio"]' => array(
                'is_price_change' => TRUE,
                'properties' => array(
                  'checked' => TRUE,
                ),
              ),
              'div.price-set-row.ticket_selection-row4' => array(
                'display' => 'hide',
              ),
              'div#editrow-participant_role select' => array(
                'value' => '15',
              ),
            ),
          ),
          'fail' => array(
            'profiles' => array (
              '117' => array(
                'display' => 'hide',
              ),
            ),
            'selectors' => array(
              'div.price-set-row.ticket_selection-row3 input[type="radio"]' => array(
                'is_price_change' => TRUE,
                'properties' => array(
                  'checked' => FALSE,
                ),
              ),
            ),
          ),
        ),
      ), 
      '4_volunteer' => array(
        'conditions' => array(
          'all_of' => array(
            array(
              'id' => 'custom_261',
              'op' => 'value_is', 
              'value' => '4', //"volunteer"
            ),
          )
        ),
        'states' => array(
          'pass' => array (
            'profiles' => array (
              '114' => array(
                'display' => 'show',
              ),
            ),
            'selectors' => array(
              '#priceset' => array(
                'display' => 'show',
              ),
              'div.price-set-row.ticket_selection-row1' => array(
                'display' => 'hide',
              ),
              'div.price-set-row.ticket_selection-row2' => array(
                'display' => 'hide',
              ),
              'div.price-set-row.ticket_selection-row3' => array(
                'display' => 'hide',
              ),
              'div.price-set-row.ticket_selection-row4' => array(
                'display' => 'show',
              ),
              'div.price-set-row.ticket_selection-row4 input[type="radio"]' => array(
                'is_price_change' => TRUE,
                'properties' => array(
                  'checked' => TRUE,
                ),
              ),
              'div#editrow-participant_role select' => array(
                'value' => '16',
              ),
            ),
          ),
          'fail' => array(
            'profiles' => array (
              '114' => array(
                'display' => 'hide',
              ),
            ),
            'selectors' => array(
              'div.price-set-row.ticket_selection-row4 input[type="radio"]' => array(
                'is_price_change' => TRUE,
                'properties' => array(
                  'checked' => FALSE,
                ),
              ),
            ),
          ),
        ),
      ), 
      '5_any' => array(
        'conditions' => array(
          'any_of' => array(
            array(
              'id' => 'custom_261',
              'op' => 'value_is', 
              'value' => '1', //"swim"
            ),
            array(
              'id' => 'custom_261',
              'op' => 'value_is', 
              'value' => '2', //"kayak"
            ),
            array(
              'id' => 'custom_261',
              'op' => 'value_is', 
              'value' => '3', //"sup"
            ),
            array(
              'id' => 'custom_261',
              'op' => 'value_is', 
              'value' => '4', //"volunteer"
            ),
          )
        ),
        'states' => array(
          'pass' => array (
            'profiles' => array (
              '110' => array(
                'display' => 'show',
              ),
            ),
            'selectors' => array(
              '#priceset' => array(
                'display' => 'show',
              ),
            ),
          ),
          'fail' => array(
            'profiles' => array (
              '110' => array(
                'display' => 'hide',
              ),
            ),
            'selectors' => array(
              '#priceset' => array(
                'display' => 'hide',
              ),
            ),
          ),
        ),
      ), 
      '6_kayak_or_sup' => array(
        'conditions' => array(
          'any_of' => array(
            array(
              'id' => 'custom_261',
              'op' => 'value_is', 
              'value' => '2', //"kayak"
            ),
            array(
              'id' => 'custom_261',
              'op' => 'value_is', 
              'value' => '3', //"sup"
            ),
          )
        ),
        'states' => array(
          'pass' => array (
            'profiles' => array (
              '121' => array(
                'display' => 'show',
              ),
            ),
          ),
          'fail' => array(
            'profiles' => array (
              '121' => array(
                'display' => 'hide',
              ),
            ),
          ),
        ),
      ), 
      '7_solo_swimmer' => array(
        'conditions' => array(
          'all_of' => array(
            array(
              'id' => 'custom_205',
              'op' => 'value_is', 
              'value' => '1', //"solo swimmer"
            ),
          )
        ),
        'states' => array(
          'pass' => array (
            'selectors' => array(
              'div.editrow_custom_208-section' => array(
                'display' => 'hide',
              ),
              'div.editrow_custom_260-section' => array(
                'display' => 'hide',
              ),
            ),
          ),
          'fail' => array(
            'selectors' => array(
              'div.editrow_custom_208-section' => array(
                'display' => 'show',
              ),
              'div.editrow_custom_260-section' => array(
                'display' => 'show',
              ),
            ),
          ),
        ),
      ), 
      '8_fundraising' => array(
        'conditions' => array(
          'all_of' => array(
            array(
              'id' => 'discountcode',
              'op' => 'value_is', 
              'value' => '', // (empty)
            ),
            array(
              'id' => 'custom_261',
              'op' => 'value_is_one_of', 
              'value' => [
                '1', //"swim"
                '2', //"kayak"
                '3', //"sup"
              ]
            ),
          )
        ),
        'states' => array(
          'pass' => array (
            'profiles' => array(
              '120' => array(
                'display' => 'show',
              ),
            ),
          ),
          'fail' => array(
            'profiles' => array(
              '120' => array(
                'display' => 'hide',
              ),
            ),
          ),
        ),
      ), 
      '8-5_extra_gift' => array(
        'conditions' => array(
          'all_of' => array(
            array(
              'id' => 'discountcode',
              'op' => 'value_is', 
              'value' => '', // (empty)
            ),
            array(
              'id' => 'custom_261',
              'op' => 'value_is_one_of', 
              'value' => [
                '1', //"swim"
                '2', //"kayak"
                '3', //"sup"
                '4', //"sup"
              ]
            ),
          )
        ),
        'states' => array(
          'pass' => array (
            'selectors' => array(
              'div#priceset div.fundraising-section' => array(
                'display' => 'show',
              ),
            ),
          ),
          'fail' => array(
            'selectors' => array(
              'div#priceset div.fundraising-section' => array(
                'display' => 'hide',
              ),
              'div#priceset div.fundraising-section input' => array(
                'is_price_change' => TRUE,
                'value' => '',
              ),
            ),
          ),
        ),
      ), 
      '9_kayak_discount' => array(
        'conditions' => array(
          'all_of' => array(
            array(
              'id' => 'custom_214',
              'op' => 'value_is', 
              'value' => '2', // "bring my own kayak"
            ),
          )
        ),
        'states' => array(
          'pass' => array (
            'selectors' => array(
              'div.price-set-row.discounted_ticket_x2-row1 input[type="radio"]' => array(
                'is_price_change' => TRUE,
                'properties' => array(
                  'checked' => TRUE,
                ),
              ),
            ),
          ),
          'fail' => array(
            'selectors' => array(   
              'div.price-set-row.discounted_ticket_x2-row1 input[type="radio"]' => array(
                'is_price_change' => TRUE,
                'properties' => array(
                  'checked' => FALSE,
                ),
              ),
            ),
          ),
        ),
      ), 
      '10_sup_discount' => array(
        'conditions' => array(
          'all_of' => array(
            array(
              'id' => 'custom_214',
              'op' => 'value_is', 
              'value' => '4', // "bring my own sup"
            ),
          )
        ),
        'states' => array(
          'pass' => array (
            'selectors' => array(
              'div.price-set-row.discounted_ticket_x2-row2 input[type="radio"]' => array(
                'is_price_change' => TRUE,
                'properties' => array(
                  'checked' => TRUE,
                ),
              ),
            ),
          ),
          'fail' => array(
            'selectors' => array(   
              'div.price-set-row.discounted_ticket_x2-row2 input[type="radio"]' => array(
                'is_price_change' => TRUE,
                'properties' => array(
                  'checked' => FALSE,
                ),
              ),
            ),
          ),
        ),
      ), 
      '11_volunteer_boater' => array(
        'conditions' => array(
          'all_of' => array(
            array(
              'id' => 'custom_227',
              'op' => 'value_is', 
              'value' => '1', // "bringing a boat"
            ),
          )
        ),
        'states' => array(
          'pass' => array (
            'profiles' => array(
              '111' => array(
                'display' => 'show',
              ),
            ),
          ),
          'fail' => array(
            'profiles' => array(
              '111' => array(
                'display' => 'hide',
              ),
            ),
          ),
        ),
      ), 
      '12_volunteer_on_boat' => array(
        'conditions' => array(
          'all_of' => array(
            array(
              'id' => 'custom_227',
              'op' => 'value_is', 
              'value' => '2', // "I'm on a boat"
            ),
          )
        ),
        'states' => array(
          'pass' => array (
            'profiles' => array(
              '116' => array(
                'display' => 'show',
              ),
            ),
          ),
          'fail' => array(
            'profiles' => array(
              '116' => array(
                'display' => 'hide',
              ),
            ),
          ),
        ),
      ), 
      '13_volunteer_on_boat' => array(
        'conditions' => array(
          'all_of' => array(
            array(
              'id' => 'custom_227',
              'op' => 'value_is', 
              'value' => '3', // "kayak or sup"
            ),
          )
        ),
        'states' => array(
          'pass' => array (
            'profiles' => array(
              '112' => array(
                'display' => 'show',
              ),
            ),
          ),
          'fail' => array(
            'profiles' => array(
              '112' => array(
                'display' => 'hide',
              ),
            ),
          ),
        ),
      ), 
      '14_volunteer_on_boat' => array(
        'conditions' => array(
          'all_of' => array(
            array(
              'id' => 'custom_227',
              'op' => 'value_is', 
              'value' => '4', // "on land"
            ),
          )
        ),
        'states' => array(
          'pass' => array (
            'profiles' => array(
              '113' => array(
                'display' => 'show',
              ),
            ),
          ),
          'fail' => array(
            'profiles' => array(
              '113' => array(
                'display' => 'hide',
              ),
            ),
          ),
        ),
      ), 
      '15_volunteer_course_paddle' => array(
        'conditions' => array(
          'all_of' => array(
            array(
              'id' => 'custom_229',
              'op' => 'value_is', 
              'value' => '2', // "paddle"
            ),
          )
        ),
        'states' => array(
          'pass' => array (
            'selectors' => array(
              'div.editrow_custom_232-section' => array(
                'display' => 'hide',
              ),
              'div.editrow_custom_262-section' => array(
                'display' => 'hide',
              ),
            ),
          ),
          'fail' => array(
            'selectors' => array(
              'div.editrow_custom_232-section' => array(
                'display' => 'show',
              ),
              'div.editrow_custom_262-section' => array(
                'display' => 'show',
              ),
            ),
          ),
        ),
      ), 
      '16_volunteer_course_swim' => array(
        'conditions' => array(
          'all_of' => array(
            array(
              'id' => 'custom_229',
              'op' => 'value_is', 
              'value' => '1', // "swim"
            ),
          )
        ),
        'states' => array(
          'pass' => array (
            'selectors' => array(
              'div.editrow_custom_236-section' => array(
                'display' => 'hide',
              ),
              'div.editrow_custom_248-section' => array(
                'display' => 'hide',
              ),
              'div.editrow_custom_245-section' => array(
                'display' => 'hide',
              ),
            ),
          ),
          'fail' => array(
            'selectors' => array(
              'div.editrow_custom_236-section' => array(
                'display' => 'show',
              ),
              'div.editrow_custom_248-section' => array(
                'display' => 'show',
              ),
              'div.editrow_custom_245-section' => array(
                'display' => 'show',
              ),
            ),
          ),
        ),
      ), 
    ),
  ),
  'contribution' => array(
    // TODO: contribution pages not yet supported.
  )
);
```