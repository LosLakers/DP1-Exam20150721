<?php
// used to check the validity of the user
// confront a cookie set in the client with a data in the $_SESSION
// used to avoid exchanging session cookie and guess another user
function check_user_validity() {
    if (isset($_COOKIE['user_cookie']) && isset($_SESSION['user_cookie'])) {
        if (strcmp($_COOKIE['user_cookie'], $_SESSION['user_cookie']) != 0) {
            session_destroy();
            die();
        }
    }
}

// function to check if cookies are enabled or not
function cookie_check() {
    if (!isset($_SESSION['cookie_en']) && !isset($_COOKIE['cookie_enabled'])) {
        $_SESSION['cookie_en'] = false;
        redirect_to("error_page.php");
    }
}

// to check if the session is expired or not
function session_expired()
{
    $session_duration = 2 * 60;
    if (isset($_SESSION['logged_time'])) {
        $current_time = time();
        if (($current_time - $_SESSION['logged_time']) > $session_duration) {
            session_destroy();
            session_start();
            return true;
        } else {
            // update session time
            $_SESSION['logged_time'] = time();
            return false;
        }
    } else {
        return false;
    }
}

// login management
function login($conn, $username, $password)
{
    $result = false;

    if ($username != '' && $password != '') {
        // protection against SQL injection
        $username = sql_clean_up($username);
        $password = sql_clean_up($password);

        mysqli_query($conn, "LOCK TABLES users READ");

        $where = "username='" . $username . "' AND password='" . $password . "'";
        $query = sql_query_select('*', 'users', $where, null);
        if ($query != null) {
            $res = mysqli_query($conn, $query);
            if ($res != false && mysqli_num_rows($res) == 1) {
                $_SESSION['username'] = $username;
                $row = mysqli_fetch_array($res, MYSQLI_ASSOC);
                $_SESSION['name'] = $row['name'];
                // save session time of login
                $_SESSION['logged_time'] = time();
                $result = true;
            }
            mysqli_free_result($res);
        }

        mysqli_query($conn, "UNLOCK TABLES");
    }

    return $result;
}

// logout management
function logout()
{
    session_destroy();
    $redirect_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    redirect_to($redirect_url);
}

?>