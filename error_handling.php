<?php

$error_messages = array(
    // error messages
    'ERROR USERNAME SELECT'     => 'Username already in use',
    'ERROR USER INSERT'         => 'Error in inserting the user, try again later',
    'ERROR LOGIN'               => 'Username and/or password are wrong, try again',
    'ERROR CREATE BOOKING'      => 'Error in performing a booking',
    'ERROR DELETE BOOKING'      => 'An error occurred in deleting a booking',
    'ERROR NUMBER PARTICIPANTS' => 'Number of participants is not correct',
    'ERROR TIME COMPARE'        => 'Error in comparing start time and end time',
    'ERROR JAVASCRIPT DISABLED' => 'This page needs Javascript to work properly',

    // success messages
    'SUCCESS USER INSERT'       => 'User successfully inserted',
    'SUCCESS CREATE BOOKING'    => 'Booking successfully created',
    'SUCCESS DELETE BOOKING'    => 'Booking successfully deleted'
);

$type_messages = array(
    'ERROR'     => 'has-error',
    'SUCCESS'   => 'has-success'
);

function get_message_type($error)
{
    global $type_messages;
    if (strpos($error, 'SUCCESS') !== false) {
        return $type_messages['SUCCESS'];
    } else if (strpos($error, 'ERROR') !== false) {
        return $type_messages['ERROR'];
    } else {
        return null;
    }
}

function get_message($error)
{
    global $error_messages;
    if (isset($error_messages[$error])) {
        return $error_messages[$error];
    } else {
        return null;
    }
}