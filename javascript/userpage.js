$(document).ready(function () {
    // when a checkbox is selected, is added to the form for deleting a reservation
    // when is deselected, is removed from the form
    $('input[type=checkbox]').click(function () {
        var checkboxval = $(this).val();

        if ($(this).is(':checked')) {
            // action if the checkbox is selected
            var input = '<input type=hidden id=' + checkboxval + ' name=reservation[] value=' + checkboxval + '>';
            $('#deleteReservations').append(input);

        } else {
            // action if the checkbox is deselected
            var input = '#' + checkboxval;
            $(input).remove();
        }
    });

    $('form[name="addBooking"]').submit(function (event) {
        if (!valid_time('#start_time') || !valid_time('#end_time')) {
            event.preventDefault();
        }
    });
});

function valid_time(input) {
    var value = $(input).val();
    var regexp = /([01]?[0-9]|2[0-3]):([0-5][0-9])/g;
    if (regexp.test(value)) {
        return true;
    } else {
        return false;
    }
}