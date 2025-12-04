jQuery(document).ready(function($) {
    // Hide the field by default
    var fieldRow = $('.acf-field-66d19d4f66958');
    var headingField= $('#acf-form-data').next('h2');
    var fieldrollRow = $('.acf-field-67ab554695490');
    var headingrollField= $('.form-table').prev('h2');
    fieldRow.hide();
    headingField.hide();
    fieldrollRow.hide();
    headingrollField.hide();
    // Show/hide based on role selection
    $('#role').change(function() {
        var selectedRole = $(this).val();
        if (selectedRole === 'districteventuser') {
            fieldRow.show();
            headingField.hide();
        } else {
            fieldRow.hide();
            headingField.hide();
        }
        if (selectedRole === 'district_rollcall') {
            fieldrollRow.show();
            headingrollField.hide();
            headingField.hide();
        } else {
            fieldrollRow.hide();
            headingrollField.hide();
        }
    });

    // Trigger the change event on page load in case the role is already selected
    $('#role').trigger('change');
});
