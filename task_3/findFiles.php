<?php
$filesArr = array();
$filesDir = dir("/datafiles");
$fileNamePattern = "/[a-zA-z0-9]+\.ixt$/";

/**
 * Reading directory and check that file matches pattern and is not a directory
 */
if ($files = scandir($filesDir, SCANDIR_SORT_ASCENDING)) {
    foreach($files as $file) {
        if (
            preg_match($fileNamePattern, $file)
            && !is_dir($filesDir . '/' . $file)
        )
        {
            /**
             * Print file name
             */
            echo $file."\n";
        }
    }
}
else {
    echo "Files not found";
}
