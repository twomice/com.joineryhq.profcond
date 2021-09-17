<?php 

/* *************************************
 * BEGIN: Boilerplate code for "toggleSiblingsPerParent" example rule definition.
 ************************************* */
/**
 * Example code for defining a rule that will, triggered by the "checked" state of 
 * a given price field option "Triggering Price Option" (which may be a radio
 * button or a checkbox), show display one or the other of two other price options
 * (which may also be radio buttons or checkboes).
 * 
 * This example is configured:
 * 1. To work on the event registration form for an event with ID "446". 
 * 2. To monitor the "selected" state of the checkbox or radio button in a 
 *    price field option having HTML class "super_early_bird-row1".
 * 3. If that triggering field is SELECTED, to HIDE the price field option
 *    having HTML class "add_ons-row1" and to DISPLAY the price field option
 *    having HTML class "add_ons-row2" (and to do the inverse if the triggering
 *    field is NOT SELECTED).
 * 
 * To re-use this code on any contribution page or event registration form, edit
 * the values defined under $contionalBoilerplateToggleSiblingsPerParent, per
 * the comment on each line.
 * 
 */
// jQuery selectors (CSS selectors) for 
$contionalBoilerplateToggleSiblingsPerParent = array(
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
  'entityId' => '446',
  
  /**
   * CSS selectors that are used by jQuery to identify the relevant form elements.
   */
  'selectors' => array(
    /**
     * Selector identifying the triggering field (checkbox or radio button), the state of
     * which will cause one or the other of two price options to be displayed.
     * Because this is a CSS/jQuery selector, multiple fields can be defined.
     */
    'triggering_checkable_option' => 'div.price-set-row.super_early_bird-row1 input[type="checkbox"]',
    
    /**
     * Selector identifying a "price row" div element in the form, which element
     * should be HIDDEN if the triggering field is selected. 
     * This selector is based on HTML markup that's created by CiviCRM. Typically
     * it will be a <div> element with class "price-set-row" (note that there
     * will usually be many <div> elements with this class, and you will need
     * additional classes to specify only this one row.
     */
    'wrapper_to_hide_if_triggering_is_checked' => 'div.price-set-row.add_ons-row1',
    
    /**
     * Selector identifying a "price row" div element in the form, which element
     * should be SHOWN if the triggering field is selected. 
     * This selector is based on HTML markup that's created by CiviCRM. Typically
     * it will be a <div> element with class "price-set-row" (note that there
     * will usually be many <div> elements with this class, and you will need
     * additional classes to specify only this one row.
     */
    'wrapper_to_show_if_triggering_is_checked' => 'div.price-set-row.add_ons-row2',
 ),
);
/*********************************
 * NOTHING IN THE REMAINER OF THIS EXAMPLE NEEDS ANY MODIFICATION.
 ***********************************/
$toggleSiblinsPerParentRuleCounter++;
$civicrm_setting['com.joineryhq.profcond']['com.joineryhq.profcond'][$contionalBoilerplateToggleSiblingsPerParent['entityType']][$contionalBoilerplateToggleSiblingsPerParent['entityId']]['toggleSiblinsPerParent_' . $toggleSiblinsPerParentRuleCounter] = array(
  // We use $toggleSiblinsPerParentRuleCounter to ensure that every rule has a 
  // unique key name, which is important if we're re-using this boilerplate block
  // multiple times on the same entity page.
  'conditions' => array(
    'all_of' => array(
      array(
        'selector' => $contionalBoilerplateToggleSiblingsPerParent['selectors']['triggering_checkable_option'],
        'op' => 'is_checked',
      ),
    ),
  ),
  'states' => array(
    'pass' => array(
      'selectors' => array(
        $contionalBoilerplateToggleSiblingsPerParent['selectors']['wrapper_to_show_if_triggering_is_checked'] => array(
          'display' => 'show',
        ),
        $contionalBoilerplateToggleSiblingsPerParent['selectors']['wrapper_to_hide_if_triggering_is_checked'] => array(
          'display' => 'hide',
        ),
        "{$contionalBoilerplateToggleSiblingsPerParent['selectors']['wrapper_to_hide_if_triggering_is_checked']} input" => array(
          'is_price_change' => TRUE,
          'properties' => array(
            'checked' => FALSE,
          ),
        ),
      ),
    ),
    'fail' => array(
      'selectors' => array(
        $contionalBoilerplateToggleSiblingsPerParent['selectors']['wrapper_to_hide_if_triggering_is_checked'] => array(
          'display' => 'show',
        ),
        $contionalBoilerplateToggleSiblingsPerParent['selectors']['wrapper_to_show_if_triggering_is_checked'] => array(
          'display' => 'hide',
        ),
        "{$contionalBoilerplateToggleSiblingsPerParent['selectors']['wrapper_to_show_if_triggering_is_checked']} input" => array(
          'is_price_change' => TRUE,
          'properties' => array(
            'checked' => FALSE,
          ),
        ),
      ),
    ),
  ),
);
/* *************************************
 * END: Boilerplate code for "toggleSiblingsPerParent" example rule definition.
 ************************************* */