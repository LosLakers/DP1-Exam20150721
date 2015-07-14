<?php
include 'commons.php';
include 'error_handling.php';

session_start();

// HTTPS redirect
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    redirect_to("https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
}

// check if cookies are enabled
cookie_check();
if (isset($_SESSION['cookieEn']) && $_SESSION['cookieEn'] == false) {
    setcookie("cookie_enabled", "enabled", null, "/");
    $_SESSION['cookieEn'] = true;
}

// if user is logged in, then check if the session is expired or not
session_expired();

// double check user validity - security reason
check_user_validity();

$conn = dbconnection();
if (!mysqli_connect_error()) {
    if (isset($_POST['status'])) {
        switch ($_POST['status']) {
            case 'logout': { // manage logout
                logout();
                break;
            }
            case 'login': { // manage login
                $username = isset($_POST['username']) ? $_POST['username'] : '';
                $password = isset($_POST['password']) ? $_POST['password'] : '';
                if (!login($conn, $username, $password)) {
                    $error = "ERROR LOGIN";
                }
                if (isset($_SESSION['logged_time'])) {
                    // set a cookie with a md5 random value saved in the $_SESSION for double check
                    $id_rand = rand();
                    $id_rand = md5($id_rand);
                    setcookie("user_cookie", $id_rand, 0, "/", null, false, true);
                    $_SESSION['user_cookie'] = $id_rand;
                }
                break;
            }
            default:
                break;
        }
    }
} else {
    // redirect to error page because of db error
    error_page_redirect("Database connection error - index.php line 41");
}
?>

<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <?php
    insert_head();
    ?>
    <title>Index</title>
</head>
<body>
<?php
include "header.php";

include 'error_message.php';
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
        <?php
        mysqli_query($conn, "LOCK TABLES bookings READ");
        $query = sql_query_select("*", "bookings", NULL, "participants DESC");
        if ($query != null) {
            ?>
            <h3>Overview Bookings</h3>
            <?php
            if (($res = mysqli_query($conn, $query)) !== false && mysqli_num_rows($res) > 0) {
                ?>
                <table>
                    <thead>
                    <tr>
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
            } else {
                ?>
                <p>No bookings are present at this time</p>
                <?php
            }
        }
        mysqli_query($conn, "UNLOCK TABLES");
        ?>
    </div>
</div>
</body>
<?php
// close db connection
mysqli_close($conn);
?>
<!-- load javascript files -->
<script type="text/javascript" src="javascript/common.js"></script>
</html>