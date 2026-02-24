<?php
class EDBM_Logger {

    public static function log($message) {

        $file = EDBM_PATH . 'restore.log';
        $date = date('Y-m-d H:i:s');
        file_put_contents($file, "[$date] $message\n", FILE_APPEND);
    }
}
