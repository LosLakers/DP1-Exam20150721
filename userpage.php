<?php
include 'commons.php';
include 'booking_management.php';
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
                    if (isset($_POST['participants']) && isset($_POST['start_time']) && isset($_POST['end_time'])) {

                        // check validity participants -> > 0 V <= max participants
                        $participants = intval($_POST['participants']);
                        if ($participants < 0 || $participants > Common::get_max_participants()) {
                            $error = "ERROR NUMBER PARTICIPANTS";
                            break;
                        }

                        // check start and end validity -> start < end
                        if (!compare_time($_POST['start_time'], $_POST['end_time'])) {
                            $error = "ERROR TIME COMPARE";
                            break;
                        }
                        $start_time = $_POST['start_time'] . ":00";
                        $end_time = $_POST['end_time'] . ":00";

                        // check if participants <= available_participants for each half hour spot
                        mysqli_query($conn, "LOCK TABLES bookings WRITE");
                        $where = "(start_time<='" . $start_time . "' AND end_time>'" . $start_time . "') OR "
                            . "(start_time<'" . $end_time . "' AND end_time>='" . $end_time . "') OR "
                            . "(start_time>='" . $start_time . "' AND end_time<='" . $end_time . "')";
                        $order = "start_time ASC";
                        $query = sql_query_select("*", "bookings", $where, $order);
                        if ($query != null) {
                            if (($res = mysqli_query($conn, $query)) !== false) {
                                // no overlapped bookings -> insert record without any problem
                                if (mysqli_num_rows($res) == 0) {
                                    // insert the new record
                                    $error = insert_record($conn, $_SESSION['username'], $participants, $start_time, $end_time);
                                } else {
                                    // control other bookings
                                    $start_time = explode(":", $_POST['start_time']);
                                    $minutes = intval($start_time[1]);
                                    $end_time = explode(":", $_POST['end_time']);
                                    $end_hours = intval($end_time[0]);
                                    $end_minutes = intval($end_time[1]);

                                    // create intervals of 30 minutes from start time to end time
                                    $bookings = array();
                                    for ($i = intval($start_time[0]); $i <= $end_hours; $i++) {
                                        if ($i == $end_hours) {
                                            if ($end_minutes > 0) {
                                                $time = $i . ":" . "00";
                                                $bookings[$time] = 0;
                                            }
                                            if ($end_minutes >= 30 && $end_minutes < 60) {
                                                $time = $i . ":" . "30";
                                                $bookings[$time] = 0;
                                            }
                                        } else {
                                            // minutes less than 30
                                            if ($minutes < 30) {
                                                $time = $i . ":" . "00";
                                                $bookings[$time] = 0;
                                                $minutes = 30;
                                            }
                                            if ($minutes >= 30 && $minutes < 60) {
                                                $time = $i . ":" . "30";
                                                $bookings[$time] = 0;
                                                $minutes = 0;
                                            }
                                        }
                                    }

                                    // for each interval, add participants already present in the hall
                                    while (($row = mysqli_fetch_array($res, MYSQL_ASSOC)) != null) {
                                        $start_time = sql_string_time($row['start_time']);
                                        $start_time_arr = explode(":", $start_time);
                                        $end_time = sql_string_time($row['end_time']);
                                        $end_time_arr = explode(":", $end_time);

                                        if (intval($start_time_arr[1]) < 30) {
                                            $start_time_arr[1] = "00";
                                        } else {
                                            $start_time_arr[1] = "30";
                                        }
                                        $start_time = $start_time_arr[0] . ":" . $start_time_arr[1];
                                        // if true, it means that the event starts before the one to add
                                        $from_start = !array_key_exists($start_time, $bookings) ? true : false;

                                        if (intval($end_time_arr[1]) == 0) {
                                            $end_time_arr[0] = "" . (intval($end_time_arr[0]) - 1);
                                            $end_time_arr[1] = "30";
                                        } else if (intval($end_time_arr[1]) < 30) {
                                            $end_time_arr[1] = "00";
                                        } else {
                                            $end_time_arr[1] = "30";
                                        }
                                        $end_time = $end_time_arr[0] . ":" . $end_time_arr[1];
                                        // if true, it means that the event ends after the one to add
                                        $to_end = !array_key_exists($end_time, $bookings) ? true : false;

                                        $participants = intval($row['participants']);
                                        if ($from_start) {
                                            if ($to_end) {
                                                foreach ($bookings as $key => $value) $bookings[$key] += $participants;
                                            } else {
                                                foreach ($bookings as $key => $value) {
                                                    $bookings[$key] += $participants;
                                                    if (strcmp($key, $end_time) == 0) break;
                                                }
                                            }
                                        } else {
                                            if ($to_end) {
                                                $start = false;
                                                reset($bookings);
                                                // if it past the end of the array, each returns false
                                                while (list($key, $value) = each($bookings)) {
                                                    if (strcmp($key, $start_time) == 0) $start = true;

                                                    if ($start) $bookings[$key] += $participants;
                                                }
                                            } else {
                                                $start = false;
                                                $end = false;
                                                reset($bookings);
                                                // if it past the end of the array, each returns false
                                                while (list($key, $value) = each($bookings)) {
                                                    if (strcmp($key, $start_time) == 0) $start = true;

                                                    if ($start) $bookings[$key] += $participants;

                                                    if (strcmp($key, $end_time) == 0) $end = true;

                                                    if ($end) break;
                                                }
                                            }
                                        }
                                    }

                                    // check if is possible to add the booking
                                    $participants = intval($_POST['participants']);
                                    $insert = check_availability($bookings, $participants);

                                    $start_time = $_POST['start_time'] . ":00";
                                    $end_time = $_POST['end_time'] . ":00";
                                    if ($insert) $error = insert_record($conn, $_SESSION['username'], $participants, $start_time, $end_time);
                                    else $error = "ERROR NUMBER PARTICIPANTS";
                                }
                            } else {
                                $error = "ERROR CREATE BOOKING";
                            }
                            mysqli_free_result($res);
                        }
                        mysqli_query($conn, "UNLOCK TABLES");
                    }
                    break;
                }

                case "deletebooking": {
                    if (isset($_POST['id']) && ($id = intval($_POST['id'])) > 0) {
                        // lock booking table WRITE
                        mysqli_query($conn, "LOCK TABLES bookings WRITE");
                        // check if booking.username = SESSION.username
                        $where = "id='" . $id . "' AND username='" . $_SESSION['username'] . "'";
                        $query = sql_query_select("*", "bookings", $where, null);
                        if ($query != null) {
                            if (($res = mysqli_query($conn, $query)) !== false && mysqli_num_rows($res) == 1) {
                                // remove booking from the list
                                mysqli_free_result($res);
                                $query = sql_query_delete("bookings", "id='" . $id . "'");
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

                default:
                    break;
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
            <form name="addBooking" method="post" action="userpage.php">
                <fieldset>
                    <legend><b>Booking Form - Insert a new booking</b></legend>
                    <input type="hidden" name="status" value="addbooking"/>

                    <div>
                        <label>Insert Number of Participants</label><br/>
                        <input type="number" name="participants" min="1" max="<?= Common::get_max_participants() ?>"
                               required="required">
                    </div>
                    <br/>

                    <div>
                        <label>Insert Start Time</label><br/>
                        <input id="start_time" type="time" name="start_time" pattern="([01]?[0-9]|2[0-3]):([0-5][0-9])"
                               required="required" placeholder="hh:mm" title="[0-23]:[0-59]">
                    </div>
                    <br/>

                    <div>
                        <label>Insert End Time</label><br/>
                        <input id="end_time" type="time" name="end_time" pattern="([01]?[0-9]|2[0-3]):([0-5][0-9])"
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
            mysqli_query($conn, "LOCK TABLES bookings READ");
            $where = "username='" . $_SESSION['username'] . "'";
            $query = sql_query_select("*", "bookings", $where, "username");
            if ($query != null) {
                ?>
                <h3>User's Bookings</h3>
                <?php
                if (($res = mysqli_query($conn, $query)) !== false) {
                    if (mysqli_num_rows($res) > 0) {
                        ?>
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
                    } else {
                        echo "You didn't perform any booking";
                    }
                    mysqli_free_result($res);
                } else {
                    echo "Error loading your bookings";
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
                ?>
                <h3>Other Users' Bookings</h3>
                <?php
                if (($res = mysqli_query($conn, $query)) !== false) {
                    ?>
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
                    echo "Error loading other bookings";
                }
            }
            ?>
        </div>
        <br/>
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