<?php
// insert a booking record in the database
function insert_record($conn, $username, $participants, $start_time, $end_time)
{
    $error = "ERROR CREATE BOOKING";

    $insert = "bookings(username, participants, start_time, end_time)";
    $values = "('" . $username . "', '"
        . $participants . "', '"
        . $start_time . "', '"
        . $end_time . "')";
    $query = sql_query_insert($insert, $values);
    if ($query != null) {
        if (mysqli_query($conn, $query))
            $error = "SUCCESS CREATE BOOKING";
    }
    return $error;
}

// compare start time and end time passed as strings
function compare_time($start_time, $end_time)
{
    $start_time = strtotime($start_time);
    $end_time = strtotime($end_time);
    if ($end_time === -1 || $start_time === -1 || $end_time <= $start_time) {
        return false;
    } else {
        return true;
    }
}

// scans the bookings array to check the availability or not of the hall
function check_availability($bookings, $participants)
{
    $insert = true;
    foreach ($bookings as $value) {
        if (($value + $participants) > Common::get_max_participants()) {
            $insert = false;
            break;
        }
    }

    return $insert;
}

?>