<?php
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

?>