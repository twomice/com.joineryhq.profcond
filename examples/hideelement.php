<?php
/* Name of Event or Contribution Page */
/**
 * Example code for defining a rule that will, triggered by the "checked" state of 
 * a given field option "Triggering Checkable Option" (which may be a radio
 * button or a checkbox), HIDE other fields which may also be radio buttons or checkboxes.
 * 
 * To re-use this code on any contribution page or event registration form, edit
 * the values defined under $HideElement, per
 * the comment on each line.
 * 
 */
// jQuery selectors (CSS selectors) for 
$HideElement = array(
  /**
   *  Special value indicating the context of this rule:
   *    'event' if this is for an event registration form
   *    'contribution' if this is for a contribution page.
   */
  'entityType' => 'event',
  
  /**
   * The civicrm ID of the context page:
   *    If this is for an event registration form, use the event ID.
   *    If this is for a contribution page, use the contribution page ID.
   */
  'entityId' => '455',
  
  /**
   * CSS selectors that are used by jQuery to identify the relevant form elements.
   */
  'selectors' => array(
    /**
     * Selector identifying the triggering field (checkbox or radio button), the state of
     * which will cause the price fields to be HIDDEN.
     */
    'triggering_checkable_option' =>'
      div.price-set-row.packages-row1 input[type="radio"]
    ',
    
    /**
     * Selector identifying a "price row" div element in the form, which element
     * should be HIDDEN if the triggering field is selected. 
     */
    'wrapper_to_hide_if_triggering_is_checked' => '
      div.price-set-row.a_la_carte-row1
     '
 ),
);
/*********************************
 * NOTHING IN THE FOLLOWING ARRAY NEEDS ANY MODIFICATION.
 ***********************************/
$HideElementRuleCounter++;
$inputs_to_hide_if_triggering_is_checked = str_replace(',', ' input,', str_replace("\n", ' ', $HideElement['selectors']['wrapper_to_hide_if_triggering_is_checked'])) . ' input';
$civicrm_setting['com.joineryhq.profcond']['com.joineryhq.profcond'][$HideElement['entityType']][$HideElement['entityId']]['JoineryHideElement_' . $HideElementRuleCounter] = array(
  // We use $HideElementRuleCounter to ensure that every rule has a 
  // unique key name, which is important if we're re-using this boilerplate block
  // multiple times on the same entity page.
  'conditions' => array(
    'all_of' => array(
      array(
        'selector' => $HideElement['selectors']['triggering_checkable_option'],
        'op' => 'is_checked',
      ),
    ),
  ),
  'states' => array(
    'pass' => array(
      'selectors' => array(
        $HideElement['selectors']['wrapper_to_hide_if_triggering_is_checked'] => array(
          'display' => 'hide',
        ),
      ),
    ),
    'fail' => array(
      'selectors' => array(
        $HideElement['selectors']['wrapper_to_hide_if_triggering_is_checked'] => array(
          'display' => 'show',
        ),
        $inputs_to_hide_if_triggering_is_checked => array(
          'is_price_change' => TRUE,
          'properties' => array(
            'checked' => FALSE,
          ),
        ),
      ),
    ),
  ),
);
