CRM.$(function ($, ts) {

  // Define special handler for select2 elements.
  var ps2 = new profcondSelect2($);
  // Store a list of required fields.
  var requiredFields = [];
  // Note whether Stripe is in use; this effects handling of hidden required fields.
  var isStripePaymentProcessor = (CRM.vars.stripe !== undefined);

  /**
   * Override CiviCRM's calculateTotalFee(); we want to calculate for all
   * price fields, which we may have moved outside of #priceset.
   * Calculate the total fee for the visible priceset.
   *
   * Because we're overriding a global-scope function, do not scope it
   * with `var` here.
   */
  calculateTotalFee = function calculateTotalFee() {
    var totalFee = 0;
    $(".profcond-price-element[price]").each(function () {
      totalFee = totalFee + $(this).data('line_raw_total');
    });
    return totalFee;
  };

  /**
   * If console logging is enabled, print a message to the console.
   */
  var profcondLogDebug = function profcondLogDebug() {
    if (CRM.vars.profcond.isDebug) {
      console.log.apply(console, arguments);
    }
  };

  var profcondElementIsSelect2 = function profcondElementIsSelect2(el) {
    // Most select2 controls (Price-field selects, core select fields (e.g. Address State), "Select"-type custom fields)
    // are represented by a hidden <select> control.
    // On the other hand, Entity Reference and Contact Reference custom fields are represented differently:
    // by a hidden <input type="text"> field.
    // Conveniently, both types have an `id` attribute AND a corresponding <div> element with an `id` like "s2id_[hidden-field-id]".
    // E.g.
    //  price field: hidden <select id="price_17">; corresponding select2 div: <div id="s2id_price_17">
    //  contact reference: hidden <input type="text" id="custom_35">; corresponding select2 div: <div id="s2id_custom_35">

    return $(el).closest('div.content').find('div#s2id_' + el.id).length;
  }

  /**
   * Compile a list of all ':hidden' fields and store that list in the 'profcond_hidden_fields'
   * hidden field, so it will be passed to the form handler.
   *
   * @param Event e
   */
  var profcondStoreHidden = function profcondStoreHidden(e) {
    var hiddenFields = [];
    // Find all hidden input, select, and textarea fields in the form.
    /*jshint multistr: true */
    $('\
      form#' + CRM.vars.profcond.formId +' input:hidden, \n\
      form#' + CRM.vars.profcond.formId +' select:hidden, \n\
      form#' + CRM.vars.profcond.formId +' textarea:hidden \n\
    ')
    // Omit html <input type="hidden"> elements, which can in no meaningful way
    // be considered "required" for form validation.
    .not('[type="hidden"]')
    .each(function (idx, el) {
    if (
        el.type == 'text' &&
        ($(el).hasClass('crm-hidden-date') || el.hasAttribute('data-crm-datepicker'))
      ) {
        var datepickerid = $(el).siblings('input.hasDatepicker').attr('id');
        if ($('#' + datepickerid).is(':hidden')) {
          hiddenFields.push(el.name);
        }
      // If this is a radio, we only care if all radios with this name are hidden;
      // if any radio in this named radio group is still visible, we don't count
      // this named field as hidden. (Why? Because the only purpose of noting which
      // fields are hidden is to prevent them from being required. But if any one
      // radio option in a required radio field is visible, then the field should
      // be required.)
      } else if (el.type == 'radio') {
        if (!$('input[type="radio"][name="' + el.name + '"]').not(':hidden').length) {
          hiddenFields.push(el.name);
        }
      // If this is a checkbox, we only care if all checkboxes with this name are hidden;
      // if any checkbox in this named checkbox group is still visible, we don't count
      // this named field as hidden. (Why? Because the only purpose of noting which
      // fields are hidden is to prevent them from being required. But if any one
      // checkbox option in a required checkbox field is visible, then the field should
      // be required.)
      } else if (el.type == 'checkbox') {
        // Checkboxes have names like 'basename[value]' in typical PHP submit-checkbox-as-array fashion.
        // So we can use a regex to identify basename, and then look for like-named visible checkboxes.
        var fieldBaseName = el.name.replace(/^([^\[]+)\[.+$/, '$1');
        if (!$('input[type="checkbox"][name^="' + fieldBaseName + '["]').not(':hidden').length) {
          hiddenFields.push(fieldBaseName);
        }
      }
      // If this is a select2 base control, it will always be hidden. We only care
      // if the select2 itself is hidden.
      else if (profcondElementIsSelect2(el)) {
        var select2id = 's2id_' + el.id;
        if ($('#' + select2id).is(':hidden')) {
          hiddenFields.push(el.name);
        }
      }
      // If this is a datepicker base control, it will always be hidden. We only care
      // if the datepicker itself is hidden.
      else if (el.name.length) {
        // Finally, treat the field as a 'normal' text or other input, and use the 'name' attribute.
        hiddenFields.push(el.name);
      }
    });
    $('#profcond_hidden_fields').val(JSON.stringify(hiddenFields));
  };


  var profcondApplyStates = function profcondApplyStates(states) {
    if (typeof states == 'undefined') {
      // Maybe there is no defined 'pass', 'fail' or 'onload' state,
      // in which case do nothing and return.
      return;
    }
    var state = {};
    if (typeof states.selectors != 'undefined') {
      for (var selector in states.selectors) {
        state = states.selectors[selector];
        profcondLogDebug('applying state to selector', selector, state);
        profcondApplyState(selector, 'selector', state);
      }
    }
    if (typeof states.profiles != 'undefined') {
      for (var profile in states.profiles) {
        state = states.profiles[profile];
        profcondLogDebug('applying state to profile', profile, state);
        profcondApplyState(profile, 'profile', state);
      }
    }
  };

  var profcondApplyState = function profcondApplyState(id, type, state) {
    var el = profcondGetEl(id, type);
    if (!el.length) {
      profcondLogDebug('WARNING: applying state to non-existent element', id, type);
    }
    if (state.display) {
      if (ps2.elementIsSelect2Option(el)) {
        ps2.setDisplayState(el, state.display);
      }
      else {
        var jQueryShowHideOptions = {
          'duration': 0
        };
        if (isStripePaymentProcessor) {
          // If Stripe is in use, we must track hidden required fields now,
          // because Stripe will prevent us from using the Submit event to do so.
          // (If Stripe is not in use, profcondStoreHidden is only called once
          // at form Submit, which is more efficient; for this event handler asignment,
          // search this code for isStripePaymentProcessor.)
          jQueryShowHideOptions.always = function() {
            // Experience shows that If we don't wait some, profcondStoreHidden will
            // not correctly detect is:hidden status of select2 fields; this in spite
            // of the expectations of 'always', which is that the function will
            // fire after the show/hide promise returns.
            // Therefore we wait 10 ms.
            setTimeout(profcondStoreHidden, 10);
          };
        }

        switch (state.display) {
          case 'show':
            el.show(jQueryShowHideOptions);
            el.find('input,textarea').each(function() {
              if (requiredFields.includes(this.id)) {
                this.classList.add('required');
              }
            });
            break;
          case 'hide':
            el.hide(jQueryShowHideOptions);
            el.find('.required').removeClass('required');
            break;
        }
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
    if (state.html) {
      el.html(state.html);
    }

    if (typeof state.value != 'undefined') {
      el.val(state.value).change();
    }

    if (state.copyValue) {
      el.val($(state.copyValue).val()).change();
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

    if (state.triggerEvents && Array.isArray(state.triggerEvents)) {
      for(var i in state.triggerEvents) {
        el.trigger(state.triggerEvents[i]);
      }
    }

    if (state.is_price_change) {
      switch (el.attr('type')) {
        case 'checkbox':
        case 'radio':
          // Checkboxes and radios -- at least -- have a strange behavior for
          // .triggerHandler() in that (somehow) if you call it for a selector
          // that matches one element, it will call the correct click handler
          // for the checkbox, but not if the selector matches multiple elements.
          // So instead of calling it on `el`, we use .each() to call it
          // separately on each matching element indivdually.
          el.each(function(idx, elem) {
            $(elem).triggerHandler('click');
            $(elem).triggerHandler('change');
          });
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
      if (!conditions.hasOwnProperty(i)) {
        continue;
      }
      var conditionPass = false;
      var condition = conditions[i];
      if (condition.op == 'is_checked') {
        var el = profcondGetConditionElement(condition);
        if (el.is(":checked")) {
          conditionPass = true;
        }
      }
      else {
        val = profcondGetConditionValue(condition);
        if (condition.op == 'value_is') {
          if (Array.isArray(val)) {
            val.forEach((selectOption) => {
              if (selectOption == condition.value) {
                conditionPass = true;
              }
            });
          }
          else {
            if (val == condition.value) {
              conditionPass = true;
            }
          }
        }
        else if (condition.op == 'value_lt') {
          if (val && val < condition.value) {
            conditionPass = true;
          }
        }
        else if (condition.op == 'value_lte') {
          if (val && val <= condition.value) {
            conditionPass = true;
          }
        }
        else if (condition.op == 'value_gt') {
          if (val && val > condition.value) {
            conditionPass = true;
          }
        }
        else if (condition.op == 'value_gte') {
          if (val && val >= condition.value) {
            conditionPass = true;
          }
        }
        else if (condition.op == 'value_is_one_of') {
          if (condition.value.indexOf(val) > -1) {
            conditionPass = true;
          }
        }
        else if (condition.op == 'is_set') {
          if (val) {
            conditionPass = true;
          }
        }
      }

      if (condition.negate) {
        // If so instructed, negate the pass/fail result of this condition.
        conditionPass = conditionPass ? false : true;
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
        el = $(id);
        break;
      case 'profile':
        el = $('fieldset.crm-profile-id-' + id);
        break;
    }
    return el;
  };

  var profcondInitializeRules = function profcondInitializeRules() {
    for (var ruleName in CRM.vars.profcond.pageConfig) {
      var rule = CRM.vars.profcond.pageConfig[ruleName];
      if (typeof rule.limit == 'object' && typeof rule.limit.formId == 'object') {
        if (typeof rule.limit.formId.pattern == 'string') {
          regex = new RegExp(rule.limit.formId.pattern, rule.limit.formId.flags);
          if (! regex.test(CRM.vars.profcond.formId)) {
            profcondLogDebug('ProfileConditionals: Rule limit does not match formId', CRM.vars.profcond.formId, 'skipping rule', ruleName);
            continue;
          }
        }
      }
      profcondLogDebug('onload, applying rule: ', ruleName);
      var ruleClass = 'profcond-has-rule_' + ruleName;
      for (var conditionType in rule.conditions) {
        var conditions = rule.conditions[conditionType];
        $.each(conditions, function(i, condition) {
          // FIXME: calling profcondApplyRule() in this each loop seem to have the 
          // effect of applying each rule multiple times, which is problematic 
          // when the state is something like so:
          // ...
          // '<p>insert this text</p>' => ['before', '#theform']
          // ...
          // because the <p> gets inserted multiple times, which is surely
          // not the intent.
          // It's probably fine to move this directly above the $.each() loop,
          // but that needs testing.
          profcondApplyRule(conditionType, conditions, rule.states);
          if (ruleName != 'onload') {
            var el = profcondGetConditionElement(condition);
            if (!el) {
              return;
            }
            if (el.is('input[type="radio"]')) {
              // If this is a radio button, we need to listen on all like-named
              // radios, so define el that way.
              el = $('input[type="radio"][name="' + el.attr('name') + '"]');
            }
            if (!el.hasClass(ruleClass)) {
              el.on('change input', {'ruleClass': ruleClass, 'conditionType': conditionType, 'conditions': conditions, 'states': rule.states}, profcondHandleChange);
              el.addClass(ruleClass);
            }
          }
        });
      }
    }
  };

  var profcondGetConditionElement = function profcondGetConditionElement(condition) {
    var el = false;
    if (typeof condition.id != 'undefined') {
      el = $('#' + condition.id);
    } else if (typeof condition.selector != 'undefined') {
      el = $(condition.selector);
    }
    return el;
  };

  var profcondGetConditionValue = function profcondGetConditionValue(condition) {
    if (typeof condition.variable != 'undefined') {
      if (Array.isArray(condition.variable)) {
        var conditionVar = window;
        var conditionVarStringName = 'window';
        for (var i in condition.variable) {
          var property = condition.variable[i];
          conditionVarStringName += '.' + property;
          if (!conditionVar.hasOwnProperty(property)) {
            profcondLogDebug('ProfileConditionals: Config references unknown javascript variable', conditionVarStringName);
            return;
          }
          conditionVar = conditionVar[property];
        }
        return conditionVar;
      }
      else {
        return window[condition.variable];
      }
    }
    else {
      el = profcondGetConditionElement(condition);
      return el.val();
    }
  };

  var profcondHandleChange = function profcondHandleChange(e) {
    profcondLogDebug('Event has triggered rule: ', e.data.ruleClass);
    profcondApplyRule(e.data.conditionType, e.data.conditions, e.data.states);
  };

  var profcondApplyRule = function profcondApplyRule(conditionType, conditions, states) {
    var pass = profcondTestCondition(conditionType, conditions);
    profcondLogDebug('rule passed', pass);
    if (pass) {
      profcondApplyStates(states.pass);
    } else {
      profcondApplyStates(states.fail);
    }
  };

  // First thing, add .profcond-price-element class to all price fields, so we can total them
  // with our custom calculateTotalFee() function.
  $("#priceset [price]").addClass('profcond-price-element');

    // Store a list of required fields to better handle jQuery Validate.
    $('.required').each(function() {
      requiredFields.push(this.id);
    });

  // Apply default state on page load.
  if (CRM.vars.profcond.pageConfig.onload) {
    profcondLogDebug('Onload rule: ');
    profcondApplyStates(CRM.vars.profcond.pageConfig.onload);
  }

  profcondInitializeRules();

  // If Stripe is not in use, we can track hidden required fields once upon form
  // submit, which is moste efficient. (If Stripe is in use, we cannot, because Stripe will prevent us from
  // using the Submit event to do so; in that case, profcondStoreHidden is called
  // each time any element is shown or hidden; for that usage, search this code
  // for isStripePaymentProcessor.)
  if (!isStripePaymentProcessor) {
    // Add submit handler to form, to pass compiled list of hidden fields with submission.
    $('form#' + CRM.vars.profcond.formId).submit(profcondStoreHidden);
  }

});
