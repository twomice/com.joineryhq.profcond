(function ($, ts) {


  /**
   * Override CiviCRM's calculateTotalFee(); we want to calculate for all
   * price fields, which we may have moved outside of #priceset.
   * Calculate the total fee for the visible priceset.
   */
  calculateTotalFee = function calculateTotalFee() {
    var totalFee = 0;
    cj(".profcond-price-element[price]").each(function () {
      totalFee = totalFee + cj(this).data('line_raw_total');
    });
    return totalFee;
  };



  /**
   * Compile a list of all ':hidden' fields and store that list in the 'profcond_hidden_fields'
   * hidden field, so it will be passed to the form handler.
   *
   * @param Event e
   */
  var profcondStoreHidden = function profcondStoreHidden(e) {
    var hiddenFields = [];
    CRM.$('input:hidden, select:hidden, textarea:hidden').each(function (idx, el) {
      // If this is a select2 base control, it will always be hidden. We only care
      // if the select2 itself is hidden.
      if ((el.type == 'select-one' || el.type == 'select-multiple') && CRM.$(el).hasClass('crm-select2')) {
        var select2id = 's2id_' + el.id;
        if (CRM.$('#' + select2id).is(':hidden')) {
          hiddenFields.push(el.name);
        }
      // If this is a datepicker base control, it will always be hidden. We only care
      // if the datepicker itself is hidden.
      } else if (
        el.type == 'text' &&
        ($(el).hasClass('crm-hidden-date') || el.hasAttribute('data-crm-datepicker'))
      ) {
        var datepickerid = $(el).siblings('input.hasDatepicker').attr('id');
        if (CRM.$('#' + datepickerid).is(':hidden')) {
          hiddenFields.push(el.name);
        }
      } else if (el.name.length) {
        hiddenFields.push(el.name);
      }
    });
    CRM.$('#profcond_hidden_fields').val(JSON.stringify(hiddenFields));
  };


  var profcondApplyStates = function profcondApplyStates(states) {
    if (typeof states == 'undefined') {
      // Maybe there is no defined 'pass', 'fail' or 'onload' state,
      // in which case do nothing and return.
      return;
    }
    var state;
    if (typeof states.selectors != 'undefined') {
      for (var selector in states.selectors) {
        state = states.selectors[selector];
        profcondApplyState(selector, 'selector', state);
      }
    }
    if (typeof states.profiles != 'undefined') {
      for (var profile in states.profiles) {
        state = states.profiles[profile];
        profcondApplyState(profile, 'profile', state);
      }
    }
  };

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
    if (state.css) {
      el.css(state.css);
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

    if (state.appendTo) {
      el.appendTo(state.appendTo);
    }

    if (state.prependTo) {
      el.prependTo(state.prependTo);
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
  };

  /**
   *
   * @param String conditionType Either 'all_of' or 'any_of'
   * @param String conditions Object specifying fields and values to test.
   * @returns Boolean true if conditions pass; otherwise false.
   */
  var profcondTestCondition = function profcondTestCondition(conditionType, conditions) {
    var passes = 0;
    var val;
    for (var i in conditions) {
      var conditionPass = false;
      var condition = conditions[i];
      var el = profcondGetConditionElement(condition);
      if (condition.op == 'value_is') {
        if (el.val() == condition.value) {
          conditionPass = true;
        }
      } else if (condition.op == 'value_is_one_of') {
        if (condition.value.indexOf(el.val()) > -1) {
          conditionPass = true;
        }
      } else if (condition.op == 'is_checked') {
        if (el.is(":checked")) {
          conditionPass = true;
        }
      }

      if (conditionPass) {
        if (conditionType == 'any_of') {
          return true;
        }
        passes++;
      } else {
        if (conditionType == 'all_of') {
          return false;
        }
      }
    }

    if (passes == conditions.length) {
      return true;
    }
  };

  var profcondGetEl = function profcondGetEl(id, type) {
    var el;
    switch (type) {
      case 'selector':
        el = CRM.$(id);
        break;
      case 'profile':
        el = CRM.$('fieldset.crm-profile-id-' + id);
        break;
    }
    return el;
  };

  var profcondInitializeRules = function profcondInitializeRules() {
    for (var ruleName in CRM.vars.profcond.eventConfig) {
      var rule = CRM.vars.profcond.eventConfig[ruleName];
      var ruleClass = 'profcond-has-rule_' + ruleName;
      for (var conditionType in rule.conditions) {
        var conditions = rule.conditions[conditionType];
        for (var i in conditions) {
          profcondApplyRule(conditionType, conditions, rule.states);
          if (ruleName != 'onload') {
            var el = profcondGetConditionElement(conditions[i]);
            if (el.is('input[type="radio"]')) {
              // If this is a radio button, we need to listen on all like-named
              // radios, so define el that way.
              el = CRM.$('input[type="radio"][name="' + el.attr('name') + '"]');
            }
            if (!el.hasClass(ruleClass)) {
              el.change({'conditionType': conditionType, 'conditions': conditions, 'states': rule.states}, profcondHandleChange);
              el.addClass(ruleClass);
            }
          }
        }
      }
    }
  };

  var profcondGetConditionElement = function profcondGetConditionElement(condition) {
    var el;
    if (typeof condition.id != 'undefined') {
      el = CRM.$('#' + condition.id);
    } else if (typeof condition.selector != 'undefined') {
      el = CRM.$(condition.selector);
    }
    return el;
  };

  var profcondHandleChange = function profcondHandleChange(e) {
    profcondApplyRule(e.data.conditionType, e.data.conditions, e.data.states);
  };

  var profcondApplyRule = function profcondApplyRule(conditionType, conditions, states) {
    var pass = profcondTestCondition(conditionType, conditions);
    if (pass) {
      profcondApplyStates(states.pass);
    } else {
      profcondApplyStates(states.fail);
    }
  };

  // First thing, add .profcond-price-element class to all price fields, so we can total them
  // with our custom calculateTotalFee() function.
  cj("#priceset [price]").addClass('profcond-price-element');

  // Apply default state on page load.
  if (CRM.vars.profcond.eventConfig.onload) {
    profcondApplyStates(CRM.vars.profcond.eventConfig.onload);
  }

  profcondInitializeRules();

  // Add submit handler to form, to pass compiled list of hidden fields with submission.
  CRM.$('form#' + CRM.vars.profcond.formId).submit(profcondStoreHidden);

})(CRM.$, CRM.ts('com.joineryhq.profcond'));


