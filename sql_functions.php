<?php
// establish a connection to the db
function dbconnection()
{
    $conn = Common::get_db_connection();
    return $conn;
}

// prepare a query on a single table
// select must be a single string
// where must be a single string in the format 'KEY_1='VALUE_1' AND KEY_2='VALUE_2' ...'
// order must be a single string in the format 'KEY_1, KEY_2, ... ASC/DESC'
function sql_query_select($select, $from, $where, $order)
{
    if ($select != null && $from != null) {
        $query = "SELECT ";

        // insert select elements
        $query .= $select;

        // insert from
        $query .= " FROM " . $from;

        // insert where clause
        if ($where != null) {
            $query .= " WHERE ";
            $query .= $where;
        }

        if ($order != null) {
            $query .= " ORDER BY ";
            $query .= $order;
        }

        return $query;
    } else {
        return null;
    }
}

// function to prepare a query to insert a value into a table
// insert must be in the form TABLE(KEY_1, .., KEY_N)
// values must be in the form ('VALUE_1', .., 'VALUE_2')
function sql_query_insert($insert, $values)
{
    if ($insert != null && $values != null) {
        $query = "INSERT INTO ";
        $query .= $insert;
        $query .= " VALUES ";
        $query .= $values;
        return $query;
    } else {
        return null;
    }
}

// function to prepare a query to update a row into a table
// values must be a single string in the format 'KEY_1='VALUE_1', KEY_2='VALUE_2' ...'
// where must be a single string in the format 'KEY_1='VALUE_1' AND KEY_2='VALUE_2' ...'
function sql_query_update($from, $values, $where)
{
    if ($from != null && $values != null) {
        $query = "UPDATE ";
        $query .= $from;
        $query .= " SET ";
        $query .= $values;
        if ($where != null) {
            $query .= " WHERE ";
            $query .= $where;
        }
        return $query;
    } else {
        return null;
    }
}

// function to prepare a query to delete a row into a table
// where must be a single string in the format 'KEY_1='VALUE_1' AND KEY_2='VALUE_2' ...'
function sql_query_delete($from, $where)
{
    if ($from != null && $where != null) {
        $query = "DELETE FROM ";
        $query .= $from;
        $query .= " WHERE ";
        $query .= $where;
        return $query;
    } else {
        return false;
    }
}

// to call for avoiding sql injection
function sql_clean_up($variable)
{
    $variable = strip_tags($variable);
    $variable = htmlentities($variable);
    $variable = stripslashes($variable);
    // doesn't work on university site
    //$variable = mysql_real_escape_string($variable);

    return $variable;
}

// convert an sql time into a HH:mm string
function sql_string_time($time)
{
    $tmp = strtotime($time);
    return date("H:i", $tmp);
}

?>