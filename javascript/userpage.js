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
        // if pattern doesn't work, this one prevent the form to submit
        if (!validTime('#start_time') || !validTime('#end_time')) {
            event.preventDefault();
        }

        // check that start time is less than end_time
        if (!compare('#start_time', '#end_time')) {
            insertError('#end_time');
            event.preventDefault();
        }
    });
});

function validTime(input) {
    var value = $(input).val();
    var regexp = /([01]?[0-9]|2[0-3]):([0-5][0-9])/g;
    if (regexp.test(value)) {
        return true;
    } else {
        return false;
    }
}

function compare(start_input, end_input) {
    var start = $(start_input).val();
    var end = $(end_input).val();

    var start_array = start.split(":");
    var end_array = end.split(":");

    // start hours less than end hours
    if (parseInt(start_array[0]) < parseInt(end_array[0])) {
        return true;
    } else if (parseInt(start_array[0]) === parseInt(end_array[0])) {
        // start minutes less than end minutes
        if (parseInt(start_array[1]) < parseInt(end_array[1])) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function insertError(input) {
    var div = $(input).parent();
    div.addClass('has-error');
    var text = "<div id='error_message'><b>End Time must be greater than Start Time</b></div>";
    div.append(text);
}