jQuery(document).ready(function(){
    var userRelation=customAdminData.userdistrelation;
    
    var userfirstName = customAdminData.firstName;
    var userrole = customAdminData.userRole;
  
    jQuery('.acf-field-relationship').mouseenter();

    function userSelect(searchText){
        setTimeout(function(){
            jQuery('.choices-list li span').filter(function() {
                // Log the current element's data-id and the searchText for debugging
                console.log('data-id:', jQuery(this).attr('data-id'), 'searchText:', searchText);
                
                // Check if the current element's 'data-id' attribute matches 'searchText'
                if(jQuery(this).attr('data-id') == searchText){
                    jQuery(this).click(); // Click the matched element
                }
            });
        }, 5000);
    }
    
    // Assuming userRelation is defined and passed correctly
    userSelect(userRelation);
    

    setTimeout(function(){
        
    // $('.acf-field-66cdbd57170bf .hasDatepicker').on('change', function() {
    //     alert("hello");
    //     // Get the selected start date
    //     var startDate = $(this).val();
        
    //     // Get the end date field
    //     var endDateField = $('.acf-field-66cdbd7b170c0 .hasDatepicker');
        
    //     // Get the current value of the end date
    //     var endDate = endDateField.val();
        
    //     // If end date is empty or less than start date, update it
    //     if (endDate === '' || endDate < startDate) {
    //         endDateField.val(startDate);
    //     }

    //     // Set the minimum allowed date for the end date
    //     endDateField.attr('min', startDate);
    // });
}, 5000);






})

