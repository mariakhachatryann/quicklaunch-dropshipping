$(document).ready(function () {
    $('#lead-subject_id').on('change', function () {
        if ($(this).val() == 1) {
            $('label[for=lead-additional_data], input#lead-additional_data').show();
        } else {
            $('label[for=lead-additional_data], input#lead-additional_data').hide();
        }
    })

    $('#lead-subject_id').trigger('change');

})