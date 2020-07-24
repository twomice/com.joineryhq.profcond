var profcondSelect2 = function profcondSelect2($) {

  var profcondSelect2 = this;
  
  // Object to store ongoing on-page display state for all select2 options.
  // Structure: optionState[select-profcondId][option-profcondId] = INT
  //   - select-profcondId: unique internal ID assigned to the select element
  //   - option-profcondId: unique internal ID assigned to the option element
  //   - INT: 0 if the option should be hidden; otherwise 1.
  // See constructor(), where this object is populated with initial values.
  this.optionState = {};

  /**
   * Test whether an element is an option within a select2 element.
   * @param HTML Element el
   * @returns boolean
   */
  this.elementIsSelect2Option = function elementIsSelect2Option(el) {
    return ($(el).is('select.crm-select2 option'));
  };

  /**
   * Adjust the on-page internal display state ('show' or 'hide') for a given
   * select2 option. This does not immediately impact the visual display on
   * the page; instead, it's used by the select2 "open" event handler 
   * (this.applyDisplayState()), which will show/hide the select2 options
   * at that time.
   * 
   * @param HTML Element el
   * @param String display Either 'show' or 'hide'
   * @returns void
   */
  this.setDisplayState = function setDisplayState(el, display) {
    var sid = $(el).closest('select.crm-select2').prop('profcondId');
    var oid = $(el).prop('profcondId');

    switch (display) {
      case 'show':
        this.optionState[sid][oid] = 1;
        break;
      case 'hide':
        this.optionState[sid][oid] = 0;
        break;
    }
  };

  /**
   * Event handler for select2 "open" event. Use this.optionState to show/hide
   * options in the given select2 element.
   * 
   * @param Event e
   * @returns void
   */
  this.applyDisplayState = function applyDisplayState(e) {
    var sid = $(this).prop('profcondId');
    for (var o in profcondSelect2.optionState[sid]) {
      if (profcondSelect2.optionState[sid][o] == 0) {
        var opSelector = 'li.select2-result.profcondId_' + o;
        $(opSelector).hide();
      }
    }
  };

  // Initialize all crm-select2 <select> controls by assigning
  // unique values for profcondId to the select and to each of its options;
  // also store the original values for ongoing state maintenance
  $('select.crm-select2').each(function(sid, sel){
    // Create an object to store options display state for this select2 element.
    
    profcondSelect2.optionState[sid] = {};
    // Assign a unique internal ID to the select element. We need this for
    // easy reference to options in profcondSelect2.optionState later.
    $(sel).prop('profcondId', sid);
    // Also assign a unique internal ID for each option in the select
    $(sel).find('option').each(function(oid, op){
      var okey = sid + '_' + oid;
      // Assign the ID as a property of the element. We need this for easy
      // reference to the element in profcondSelect2.optionState later.
      $(op).prop('profcondId', okey);
      // Add to the option element an HTML class containing this ID. This class
      // is expected to be unique on the entire page, and is copied to the
      // select2 selectable option; thus we can reference it later in a jQuery
      // selector to show/hide that select2 option.
      $(op).addClass('profcondId_' + okey);
      // By design, all existing options are displayed, so at this point we'll
      // force this display state to '1' (meaning 'show').
      profcondSelect2.optionState[sid][okey] = 1;
    });
  });

  // Add an event handler for the select2 "open" event on all select2 elements.
  $('select.crm-select2').on('select2-open', {'ps2': this}, this.applyDisplayState);
};