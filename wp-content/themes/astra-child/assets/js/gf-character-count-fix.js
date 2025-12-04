jQuery(document).ready(function ($) {
    // Override character count logic for textareas
    jQuery(document).on('input', '.ginput_container_textarea textarea', function () {
        var $textarea = $(this);
        var maxLength = parseInt($textarea.attr('maxlength')) || 0;

        if (maxLength > 0) {
            // Get the current value and normalize newline characters to \r\n
            var currentValue = $textarea.val().replace(/\n/g, '\r\n');
            var currentLength = currentValue.length;

            // Truncate the value if it exceeds the maximum length
            if (currentLength > maxLength) {
                $textarea.val(currentValue.substring(0, maxLength).replace(/\r\n/g, '\n')); // Convert back for display
                currentLength = maxLength; // Adjust the displayed count
            }

            var $charCount = jQuery('.customTextCount');
            if ($charCount.length) {
                $charCount.text(`${currentLength} of ${maxLength} max characters`);
            }
        }
    });
});
