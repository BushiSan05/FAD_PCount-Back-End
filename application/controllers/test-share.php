<?php

$path = '\\\\172.16.43.154\\e\\laragon\\www\\fad_count_local\\masterfile';

var_dump([
    'directory_exists' => is_dir($path),
    'readable' => is_readable($path),
    'csv_files' => glob($path . '*.csv'),
]);
