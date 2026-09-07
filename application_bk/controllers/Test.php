<?php

$Path = '\\\\172.16.43.154\e\laragon\www\fad_count_local\masterfile\\';

// Check if directory exists
if (!is_dir($Path)) {

    $this->session->set_flashdata(
        'msg',
        "CSV folder not found: $Path"
    );

    redirect('menu');
    return;
}

// Check if directory is readable
if (!is_readable($Path)) {

    $this->session->set_flashdata(
        'msg',
        "CSV folder is not readable: $Path"
    );

    redirect('menu');
    return;
}

// Get CSV files
$files = glob($Path . '*.csv');

// Count CSV files
$csvFileCount = count($files);

// Check if no CSV files were found
if ($csvFileCount == 0) {

    $this->session->set_flashdata(
        'msg',
        "No CSV files found in masterfile folder."
    );

    redirect('menu');
    return;
}

// CSV files found
$this->session->set_flashdata(
    'msg',
    "CSV files found: " . $csvFileCount
);

// Redirect to menu
redirect('menu');
return;
