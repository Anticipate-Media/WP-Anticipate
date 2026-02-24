<?php
class EDBM_Restore {

    public static function enable_maintenance() {
        file_put_contents(ABSPATH . '.maintenance', '<?php $upgrading = time(); ?>');
    }

    public static function disable_maintenance() {
        @unlink(ABSPATH . '.maintenance');
    }

    public static function process($file, $search = '', $replace = '') {

        global $wpdb;

        self::enable_maintenance();

        $wpdb->query('START TRANSACTION');

        $handle = fopen($file, 'r');
        $query = '';

        while (!feof($handle)) {

            $line = fgets($handle);

            if (substr(trim($line), -1) != ';') {
                $query .= $line;
                continue;
            }

            $query .= $line;

            if ($search && $replace) {
                $query = str_replace($search, $replace, $query);
            }

            $wpdb->query($query);
            $query = '';
        }

        fclose($handle);

        $wpdb->query('COMMIT');

        self::disable_maintenance();

        EDBM_Logger::log('Restore completed');

        return true;
    }
}
