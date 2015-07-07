<?php
include 'common_functions.php';
include 'error_handling.php';

session_start();

// HTTPS redirect
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    redirect_to("https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
}

cookie_check();

if (isset($_SESSION['logged_time'])) {
    if (session_expired()) {
        // redirect to index.php
        redirect_to("index.php");
    }

    check_user_validity();

    $conn = dbconnection();
    if (!mysqli_connect_error()) {
        if (isset($_POST['status'])) {
            switch ($_POST['status']) {
                case "addbooking": {
                    // lock bookings table WRITE

                    // check validity participants -> > 0 V <= 100

                    // check start and end validity -> start < end

                    // check if participants <= available_participants for each half hour spot

                    // if true insert booking

                    // else report error

                    break;
                }

                case "deletebooking": {
                    if (isset($_POST['id']) && ($id = intval($_POST['id'])) > 0) {
                        // lock booking table WRITE
                        mysqli_query($conn, "LOCK TABLES bookings WRITE");
                        // check if booking.username = SESSION.username
                        $where = "id='" . $id . "'";
                        $query = sql_query_select("username", "bookings", $where, null);
                        if ($query != null) {
                            if ($res = mysqli_query($conn, $query) && mysqli_num_rows($res) == 1) {
                                // remove booking from the list
                                mysqli_free_result($res);
                                $query = sql_query_delete("bookings", "id='".$id."'");
                                if ($query != null) {
                                    try {
                                        if (!mysqli_query($conn, $query))
                                            throw new Exception();
                                        $error = "SUCCESS DELETE BOOKING";
                                    } catch (Exception $e) {
                                        $error = "ERROR DELETE BOOKING";
                                    }
                                }
                            }
                        }
                        // unlock booking table
                        mysqli_query($conn, "UNLOCK TABLES");
                    }
                    break;
                }
            }
        }
    } else {
        // database error
        error_page_redirect("Database error connection - userpage.php line 195");
    }
} else {
    // user is not logged in -> redirect to index.php
    session_destroy();
    redirect_to("index.php");
}
?>

<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <?php
    insert_head();
    ?>
    <title>User Page</title>
</head>
<body>
<?php
include "header.php";

include 'error_message.php'
?>
<!-- Notify if Javascript is enabled or not -->
<noscript>
    <?php
    $error = "ERROR JAVASCRIPT DISABLED";
    include 'error_message.php';
    ?>
</noscript>
<div>
    <?php
    include 'navigation_bar.php';
    ?>
    <div class="right-half">
        <h3>Username</h3>

        <p><?= $_SESSION['username'] ?></p>
        <br/>

        <h3>Name</h3>

        <p><?= $_SESSION['name'] ?></p>
        <br/>

        <!-- Form for inserting a booking -->
        <div>
            <form name="addBooking" method="get" action="userpage.php">
                <fieldset>
                    <legend><b>Booking Form</b></legend>
                    <input type="hidden" name="status" value="addbooking"/>

                    <div>
                        <label>Insert Number of Participants</label><br/>
                        <input type="number" name="participants" min="1" required="required">
                    </div>
                    <br/>

                    <div>
                        <label>Insert Start Time</label><br/>
                        <input type="time" name="start_time" pattern="([01]?[0-9]|2[0-3]):([0-5][0-9])"
                               required="required" placeholder="hh:mm" title="[0-23]:[0-59]">
                    </div>
                    <br/>

                    <div>
                        <label>Insert End Time</label><br/>
                        <input type="time" name="end_time" pattern="([01]?[0-9]|2[0-3]):([0-5][0-9])"
                               required="required" placeholder="hh:mm" title="[0-23]:[0-59]">
                    </div>
                    <br/>

                    <button type="submit">Confirm</button>
                </fieldset>
            </form>
        </div>
        <br/>
        <!-- List of performed bookings by the user -->
        <div>
            <?php
            mysqli_query($conn, "LOCK TABLES users READ");
            $where = "username='" . $_SESSION['username'] . "'";
            $query = sql_query_select("*", "bookings", $where, "username");
            if ($query != null) {
                if ($res = mysqli_query($conn, $query)) {
                    ?>
                    <h3>User's Bookings</h3>
                    <table>
                        <thead>
                        <tr>
                            <th>#Participants</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Delete</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        while ($row = mysqli_fetch_array($res, MYSQL_ASSOC)) {
                            ?>
                            <tr>
                                <td><?= $row['participants'] ?></td>
                                <td><?= sql_string_time($row['start_time']) ?></td>
                                <td><?= sql_string_time($row['end_time']) ?></td>
                                <td>
                                    <form action="userpage.php" method="post">
                                        <input type="hidden" name="status" value="deletebooking">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <button type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php
                        } ?>
                        </tbody>
                    </table>
                    <?php
                    mysqli_free_result($res);
                } else {
                    // TODO no bookings found
                }
            }
            ?>
        </div>
        <br/>
        <!-- List of all bookings by other users -->
        <div>
            <?php
            $where = "username<>'" . $_SESSION['username'] . "'";
            $query = sql_query_select("*", "bookings", $where, "username");
            if ($query != null) {
                if ($res = mysqli_query($conn, $query)) {
                    ?>
                    <h3>Other Users' Bookings</h3>
                    <table>
                        <thead>
                        <tr>
                            <th>User</th>
                            <th>#Participants</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        while ($row = mysqli_fetch_array($res, MYSQL_ASSOC)) {
                            ?>
                            <tr>
                                <td><?= $row['username'] ?></td>
                                <td><?= $row['participants'] ?></td>
                                <td><?= sql_string_time($row['start_time']) ?></td>
                                <td><?= sql_string_time($row['end_time']) ?></td>
                            </tr>
                        <?php
                        } ?>
                        </tbody>
                    </table>
                    <?php
                    mysqli_free_result($res);
                    mysqli_query($conn, "UNLOCK TABLES");
                } else {
                    // TODO no bookings found
                }
            }
            ?>
        </div>
    </div>
</div>
<?php
// close db connection
mysqli_close($conn);
?>
</body>
<!-- load javascript files -->
<script type="text/javascript" src="javascript/userpage.js"></script>
<script type="text/javascript" src="javascript/common.js"></script>
</html>