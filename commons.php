<?php
include 'common_variables.php';
include 'authentication_management.php';
include 'sql_functions.php';

// insert jquery and css
function insert_head()
{
    echo "<script type='text/javascript' src='javascript/jquery-2.1.4.min.js'></script>";
    echo "<link href='css/css_file.css' rel='stylesheet' type='text/css'>";
}

// function to redirect to another page
function redirect_to($page)
{
    header("Location: $page");
    die();
}

// error page redirect
function error_page_redirect($message)
{
    $_SESSION['error_message'] = $message;
    $redirect = "error_page.php";
    header("Location: $redirect");
    die();
}

?>