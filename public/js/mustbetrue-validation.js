// Registers the jQuery Unobtrusive Validation adapter for [MustBeTrue]
// (Validation/MustBeTrueAttribute.cs) - a checkbox that must be checked
// (agreement/certification consent). jQuery Validate's built-in "range"
// method doesn't work against a checkbox's true/false value, which is what
// silently blocked the Join application's Submit button even when the boxes
// were checked; this adapter reads the element's own .checked state instead.
//
// Loaded once, site-wide, from _Layout.cshtml after the validation scripts
// section - guarded so it's a harmless no-op on any page that doesn't load
// jQuery Validate at all.
(function () {
    'use strict';
    if (!window.jQuery || !jQuery.validator || !jQuery.validator.unobtrusive) return;

    jQuery.validator.addMethod('mustbetrue', function (value, element) {
        return element.checked;
    });

    jQuery.validator.unobtrusive.adapters.addBool('mustbetrue');
})();
