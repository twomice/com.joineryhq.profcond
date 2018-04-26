(function($, ts) {
  
  /**
   * Compile a list of all ':hidden' fields and store that list in the 'profcond_hidden_fields'
   * hidden field, so it will be passed to the form handler.
   * 
   * @param Event e
   */
  var profcondStoreHidden = function profcondStoreHidden(e) {
    var hiddenFields = [];
    CRM.$('input:hidden, select:hidden, textarea:hidden').each(function(idx, el) {
      // If this is a select2 base control, it will always be hidden. We only care
      // if the select2 itself is hidden.
      if ((el.type == 'select-one' || el.type == 'select-multiple') && CRM.$(el).hasClass('crm-select2')) {
        var select2id = 's2id_' + el.id;
        if (CRM.$('#' + select2id).is(':hidden')) {
          hiddenFields.push(el.name);
        }
      }
      else if (el.name.length) {
        hiddenFields.push(el.name);
      }
    });
    CRM.$('#profcond_hidden_fields').val(JSON.stringify(hiddenFields));
  }
  
  
  var profcondApplyStates = function profcondApplyStates(states) {
    if (typeof states.selectors != 'undefined') {
      for (var selector in states.selectors) {
        var state = states.selectors[selector];
        profcondApplyState(selector, 'selector', state);
      }
    }
    if (typeof states.profiles != 'undefined') {
      for (var profile in states.profiles) {
        var state = states.profiles[profile];
        profcondApplyState(profile, 'profile', state);
      }
    }
  }
  
  var profcondApplyState = function profcondApplyState(id, type, state) {
    var el = profcondGetEl(id, type);
    if (state.display) {
      switch (state.display) {
        case 'show':
          el.show();
          break;
        case 'hide':
          el.hide();
          break;
      }
    }
    if (state.properties) {
      el.prop(state.properties);
    }
    if (state.attributes) {
      el.attr(state.attributes);
    }
    
    if (typeof state.value != 'undefined') {
      el.val(state.value).change();
    }
    
    if (state.before) {
      el.insertBefore(state.before);
    }

    if (state.after) {
      el.insertAfter(state.after);
    }

    if (state.is_price_change) {
      switch (el.attr('type')) {
        case 'checkbox':
        case 'radio':
          el.triggerHandler('click');
          break;
        case 'text':
          el.triggerHandler('keyup');
          break;
        case 'select-one':
          el.triggerHandler('change');
          break;
      }
    }
  }
  
  /**
   * 
   * @param String conditionType Either 'all_of' or 'any_of'
   * @param String conditions Object specifying fields and values to test.
   * @returns Boolean true if conditions pass; otherwise false.
   */
  var profcondTestCondition = function profcondTestCondition(conditionType, conditions) {
    var passes = 0;
    for (var i in conditions) {
      var conditionPass = false;
      var condition = conditions[i];
      if (condition.op == 'value_is') {
        var val = CRM.$('#' + condition.id).val();
        if (val == condition.value) {
          conditionPass = true;
        }
      }
      else if (condition.op == 'value_is_one_of') {
        var val = CRM.$('#' + condition.id).val();
        if (condition.value.indexOf(val) > -1) {
          conditionPass = true;
        }        
      }
      
      if (conditionPass) {
        if (conditionType == 'any_of') {
          return true;
        }
        passes++;
      }
      else {
        if (conditionType == 'all_of') {
          return false;
        }
      }
    }
    
    if (passes == conditions.length) {
      return true;
    }
  }
  
  var profcondGetEl = function profcondGetEl(id, type) {
    var el;
    switch (type) {
      case 'selector':
        el = CRM.$(id);
        break;
      case 'profile':
        el = CRM.$('fieldset.crm-profile-id-' + id)
        break;
    }
    return el;
  }
  
  var profcondInitializeRules = function profcondInitializeRules() {
    for (var ruleName in CRM.vars.profcond.eventConfig) {
      
      
      var rule = CRM.vars.profcond.eventConfig[ruleName];
      var ruleClass = 'profcond-has-rule_' + ruleName;
      for (var conditionType in rule.conditions) {
        var conditions = rule.conditions[conditionType];
        for (var i in conditions) {
          profcondApplyRule(conditionType, conditions, rule.states);          
          if (ruleName != 'onload') {
            if (!CRM.$('#' + conditions[i].id).hasClass(ruleClass)) {
              CRM.$('#' + conditions[i].id).change({'conditionType': conditionType, 'conditions': conditions, 'states': rule.states}, profcondHandleChange);
              CRM.$('#' + conditions[i].id).addClass(ruleClass);
            }
          }
        }
      }
    }
    
  }
  
  var profcondHandleChange = function profcondHandleChange(e) {
    profcondApplyRule(e.data.conditionType, e.data.conditions, e.data.states);
  }
  
  var profcondApplyRule = function profcondApplyRule (conditionType, conditions, states){
    if (profcondTestCondition(conditionType, conditions)) {
      profcondApplyStates(states.pass);
    }
    else {
      profcondApplyStates(states.fail);
    }
  }
  
  // Apply default state on page load.
  if (CRM.vars.profcond.eventConfig.onload) {
    profcondApplyStates(CRM.vars.profcond.eventConfig.onload);
  }
  
  profcondInitializeRules();
  
  // Add submit handler to form, to pass compiled list of hidden fields with submission.
  CRM.$('form#' + CRM.vars.profcond.formId).submit(profcondStoreHidden);
  
})(CRM.$, CRM.ts('com.joineryhq.profcond'));


