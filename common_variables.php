<?php
class Common {
    // max number of contemporary participants
    private static $MAX_PARTICIPANTS = 100;

    public static function get_max_participants() {
        return self::$MAX_PARTICIPANTS;
    }

    // database variables
    private static $HOST = "localhost";
    private static $USER = "root";
    private static $PASSWORD = "";
    private static $DATABASE = "dp1_exam";

    public static function get_db_connection() {
        return new mysqli(self::$HOST, self::$USER, self::$PASSWORD, self::$DATABASE);
    }
}

?>