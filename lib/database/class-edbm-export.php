<?php
class EDBM_Export {

    public static function process($offset = 0, $filename='backup') {

    global $wpdb;

    $tables = $wpdb->get_col("SHOW TABLES");
    // $limit  = 500; // rows per batch
    $max_insert_rows = 200; // rows per grouped INSERT

    $filename = $_SERVER['SERVER_NAME'] . '_backup_' . date("Ymd");
    $file = EDBM_PATH . $filename . '.sql';
    $handle = fopen($file, $offset === 0 ? 'w' : 'a');

    foreach ($tables as $table) {

        if (strpos($table, $wpdb->prefix) !== 0) continue;

        // Get column definitions
        $columns_info = $wpdb->get_results("SHOW COLUMNS FROM `$table`", ARRAY_A);

        $column_types = [];
        $column_names = [];

        foreach ($columns_info as $col) {
            $column_types[$col['Field']] = strtolower($col['Type']);
            $column_names[] = "`{$col['Field']}`";
        }

        if ($offset === 0) {
            $create = $wpdb->get_row("SHOW CREATE TABLE `$table`", ARRAY_N);
            fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n");
            fwrite($handle, $create[1] . ";\n\n");
        }

        $rows = $wpdb->get_results(
            "SELECT * FROM `$table`",// LIMIT $offset,$limit",
            ARRAY_A
        );

        if (empty($rows)) continue;

        $grouped_values = [];
        $row_counter = 0;

        foreach ($rows as $row) {

            $values = [];

            foreach ($row as $column => $value) {

                if (is_null($value)) {
                    $values[] = "NULL";
                    continue;
                }

                $type = $column_types[$column];

                // Numeric types
                if (preg_match('/int|float|double|decimal|bit|bool/', $type)) {
                    $values[] = $value;
                }

                // Binary / blob types
                elseif (preg_match('/blob|binary|varbinary/', $type)) {
                    $values[] = "0x" . bin2hex($value);
                }

                // Everything else = string
                else {
                    // print_r($wpdb->_real_escape($value));
                    // $values[] = "'" . $value . "'";
                    // $values[] = "'" . $wpdb->_escape($value) . "'";
                    $values[] = "'" . str_replace('QqQ_s_qQqQ','%',$wpdb->_real_escape(str_replace('%','QqQ_s_qQqQ',$value))). "'";
                    // $values[] = "'" . $wpdb->_real_escape($value) . "'";
                }
            }

            $grouped_values[] = "(" . implode(',', $values) . ")";
            $row_counter++;

            // Write batch insert
            if ($row_counter >= $max_insert_rows) {

                fwrite(
                    $handle,
                    "INSERT INTO `$table` (" . implode(',', $column_names) . ") VALUES\n"
                    . implode(",\n", $grouped_values)
                    . ";\n"
                );

                $grouped_values = [];
                $row_counter = 0;
            }
        }

        // Write remaining rows
        if (!empty($grouped_values)) {
            fwrite(
                $handle,
                "INSERT INTO `$table` (" . implode(',', $column_names) . ") VALUES\n"
                . implode(",\n", $grouped_values)
                . ";\n"
            );
        }
    }

    fclose($handle);

    return false;//count($rows) === $limit;
}


    public static function zip($filename='backup') {

        $zip = new ZipArchive();


        $zipFile = EDBM_PATH . $filename . '.zip';

        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            $zip->addFile(EDBM_PATH . $filename.'.sql', $filename.'.sql');
            $zip->close();
        }

        return EDBM_URL . $filename . '.zip';
    }
}
