<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UserImport extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }

        if (!$this->session->userdata('is_admin')) {
            show_error('Access denied.', 403);
        }
        $this->load->library('session'); // flash messages
    }

    function fix_name($name) {
        $name = trim($name);
        $enc = mb_detect_encoding($name, ['UTF-8','ISO-8859-1','Windows-1252'], true);
        
        if($enc && $enc != 'UTF-8') {
            return mb_convert_encoding($name, 'UTF-8', $enc);
        }
        return $name; // already UTF-8
    }
    

    // Direct import from folder (no view needed)
    public function importCsv()
    {
        $csvPath = FCPATH . 'users/'; // folder where CSVs are stored

        if(!is_dir($csvPath)) {
            $this->session->set_flashdata('msg', "CSV folder not found: $csvPath");
            redirect('menu');
        }

        // Scan folder for CSV files
        $files = glob($csvPath . '*.csv');
        if(empty($files)) {
            $this->session->set_flashdata('msg', "No CSV files found in users folder.");
            redirect('menu');
        }

        $totalInserted = 0;
        $totalSkipped  = 0;

        foreach($files as $file) {
            $fileName  = basename($file);
            $fileMTime = filemtime($file);

            // Check CSV import log
            $existing = $this->db
                ->where('LOWER(filename)=', strtolower($fileName))
                ->get('csv_import_log')
                ->row();


            if($existing && strtotime($existing->last_modified) >= $fileMTime) {
                // Already imported and not modified
                continue;
            }

            // Open CSV
            $handle = fopen($file, "r");
            if(!$handle) continue;

            // Skip header
            $header = fgetcsv($handle, 1000, ",");

            $skipped  = 0;
            $inserted = 0;

            while(($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if(count($row) < 5) continue;

                $username   = mb_convert_encoding(trim($row[0]), 'UTF-8', 'Windows-1252');
                // $fullname   = mb_convert_encoding(trim($row[1]), 'UTF-8', 'Windows-1252');
                $fullname   = $this->fix_name($row[1]);
                $password   = trim($row[2]);
                $photo      = isset($row[5]) ? trim($row[5]) : '';
                // $location   = mb_convert_encoding(trim($row[3]), 'UTF-8', 'Windows-1252');
                // $department = mb_convert_encoding(trim($row[4]), 'UTF-8', 'Windows-1252');

                if(empty($username) || empty($fullname)) {
                    $skipped++;
                    continue;
                }

                // Skip duplicates in users table
                $exists = $this->db->where('username', $username)
                                   ->or_where('fullname', $fullname)
                                   ->get('users')
                                   ->row();

                if($exists) {
                    $skipped++;
                    continue;
                }

                $data = [
                    'username'   => $username,
                    'fullname'   => $fullname,
                    'password'   => md5($password),
                    'photo'      => $photo
                    // 'location'   => $location,
                    // 'department' => $department,
                ];

                $this->db->insert('users', $data);
                $inserted++;
            }

            fclose($handle);

            // Log CSV import safely
            $logData = [
                'filename'      => $fileName,
                'last_modified' => date('Y-m-d H:i:s', $fileMTime)
            ];

            if ($existing) {
                // Update last_modified
                $this->db->where('id', $existing->id)
                         ->update('csv_import_log', $logData);
            } else {
                // Insert new row
                $this->db->insert('csv_import_log', $logData);
            }            

            $totalInserted += $inserted;
            $totalSkipped  += $skipped;
        }

        $this->session->set_flashdata('msg', "$totalInserted user(s) imported, $totalSkipped skipped (duplicates/empty).");
        redirect('menu');
    }
}
