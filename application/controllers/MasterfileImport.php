<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MasterfileImport extends CI_Controller
{

    public function __construct()
    {
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

    private function safe_utf8($string)
    {
        if (empty($string)) return '';
        $enc = mb_detect_encoding($string, 'UTF-8, ISO-8859-1, Windows-1252', true);
        if ($enc) {
            return mb_convert_encoding($string, 'UTF-8', $enc);
        } else {
            return $string;
        }
    }

    // // Optimized import
    // public function importCsv()
    // {
    //     $csvPath = FCPATH . 'masterfile/';

    //     if (!is_dir($csvPath)) {
    //         $this->session->set_flashdata('msg', "CSV folder not found: $csvPath");
    //         redirect('menu');
    //     }

    //     $files = glob($csvPath . '*.csv');
    //     if (empty($files)) {
    //         $this->session->set_flashdata('msg', "No CSV files found in masterfile folder.");
    //         redirect('menu');
    //     }

    //     // // Load all existing barcodes once
    //     // $existingBarcodes = $this->db->select('barcode')->get('masterfile')->result_array();
    //     // $existingBarcodes = array_column($existingBarcodes, 'barcode');
    //     // $existingBarcodes = array_flip($existingBarcodes); // faster isset check
    //     // $existingBarcodes[$barcode] = true;

    //     $totalInserted = 0;
    //     $totalSkipped  = 0;

    //     foreach ($files as $file) {
    //         $fileName  = basename($file);
    //         $fileMTime = filemtime($file);

    //         // Check CSV import log
    //         $existingLog = $this->db
    //             ->where('LOWER(filename)=', strtolower($fileName))
    //             ->get('csv_import_log')
    //             ->row();

    //         if ($existingLog && strtotime($existingLog->last_modified) >= $fileMTime) {
    //             // Already imported and not modified
    //             continue;
    //         }

    //         $handle = fopen($file, "r");
    //         if (!$handle) continue;

    //         // Skip header
    //         $header = fgetcsv($handle, 1000, ",");

    //         $insertData = [];
    //         $skipped    = 0;

    //         while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
    //             if (count($row) < 8) continue;

    //             $barcode  = $this->safe_utf8(trim($row[0]));
    //             $desc     = $this->safe_utf8(trim($row[1]));
    //             $acost    = floatval(trim($row[2]));
    //             $adate    = $this->safe_utf8(trim($row[3]));
    //             $cat_type = $this->safe_utf8(trim($row[4]));
    //             $locname  = $this->safe_utf8(trim($row[5]));
    //             $dept     = $this->safe_utf8(trim($row[6]));
    //             $status   = $this->safe_utf8(trim($row[7]));

    //             // if(empty($barcode) || empty($desc) || isset($existingBarcodes[$barcode])) {
    //             //     $skipped++;
    //             //     continue;
    //             // }

    //             if (empty($barcode) || empty($desc)) {
    //                 $skipped++;
    //                 continue;
    //             }

    //             $insertData[] = [
    //                 'barcode'  => $barcode,
    //                 'desc'     => $desc,
    //                 'acost'    => $acost,
    //                 'adate'    => $adate,
    //                 'cat_type' => $cat_type,
    //                 'locname'  => $locname,
    //                 'dept'     => $dept,
    //                 'status'   => $status,
    //                 'barpost' => $fileName,
    //             ];

    //             // Add to existing barcodes to avoid duplicates within same CSV
    //             // $existingBarcodes[$barcode] = true;
    //         }

    //         fclose($handle);

    //         // Batch insert if we have rows
    //         if (!empty($insertData)) {
    //             $this->db->insert_batch('masterfile', $insertData);
    //             $totalInserted += count($insertData);
    //         }
    //         $totalSkipped  += $skipped;

    //         // Log CSV import
    //         $logData = [
    //             'filename'      => $fileName,
    //             'last_modified' => date('Y-m-d H:i:s', $fileMTime)
    //         ];

    //         if ($existingLog) {
    //             $this->db->where('id', $existingLog->id)
    //                 ->update('csv_import_log', $logData);
    //         } else {
    //             $this->db->insert('csv_import_log', $logData);
    //         }
    //     }

    //     $this->session->set_flashdata('msg', "$totalInserted asset(s) imported, $totalSkipped skipped (duplicates/empty).");
    //     redirect('menu');
    // }

    public function importCsv()
    {
        $csvPath = FCPATH . 'masterfile/';

        // Number of rows to insert at a time
        $batchSize = 1000;

        // Check if folder exists
        if (!is_dir($csvPath)) {
            $this->session->set_flashdata(
                'msg',
                "CSV folder not found: $csvPath"
            );
            redirect('menu');
            return;
        }

        // Get all CSV files
        $files = glob($csvPath . '*.csv');

        if (empty($files)) {
            $this->session->set_flashdata(
                'msg',
                "No CSV files found in masterfile folder."
            );
            redirect('menu');
            return;
        }

        // Optional: sort files alphabetically
        sort($files);

        $totalInserted = 0;
        $totalSkipped  = 0;
        $totalFiles    = count($files);

        /*
     * ==========================================================
     * CLEAR MASTERFILE TABLE
     * ==========================================================
     */

        $this->db->trans_start();

        $this->db->truncate('masterfile');

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata(
                'msg',
                'Failed to clear masterfile table.'
            );
            redirect('menu');
            return;
        }

        /*
     * ==========================================================
     * IMPORT EACH CSV FILE
     * ==========================================================
     */

        foreach ($files as $file) {

            $fileName = basename($file);

            $handle = fopen($file, 'r');

            if ($handle === FALSE) {
                continue;
            }

            /*
         * Skip CSV header
         */
            fgetcsv($handle, 0, ",");

            $insertData = [];
            $fileInserted = 0;
            $fileSkipped  = 0;

            /*
         * ======================================================
         * READ CSV ONE ROW AT A TIME
         * ======================================================
         */

            while (($row = fgetcsv($handle, 0, ",")) !== FALSE) {

                /*
             * Make sure the CSV contains at least 8 columns
             */
                if (count($row) < 8) {
                    $fileSkipped++;
                    continue;
                }

                /*
             * Get CSV values
             */
                $barcode  = $this->safe_utf8(trim($row[0]));
                $desc     = $this->safe_utf8(trim($row[1]));
                $acost    = floatval(trim($row[2]));
                $adate    = $this->safe_utf8(trim($row[3]));
                $cat_type = $this->safe_utf8(trim($row[4]));
                $locname  = $this->safe_utf8(trim($row[5]));
                $dept     = $this->safe_utf8(trim($row[6]));
                $status   = $this->safe_utf8(trim($row[7]));

                /*
             * Skip empty barcode or description
             */
                if (empty($barcode) || empty($desc)) {
                    $fileSkipped++;
                    continue;
                }

                /*
             * Add row to current batch
             */
                $insertData[] = [
                    'barcode'  => $barcode,
                    'desc'     => $desc,
                    'acost'    => $acost,
                    'adate'    => $adate,
                    'cat_type' => $cat_type,
                    'locname'  => $locname,
                    'dept'     => $dept,
                    'status'   => $status,
                    'barpost'  => $fileName
                ];

                /*
             * ==================================================
             * INSERT WHEN BATCH SIZE IS REACHED
             * ==================================================
             */

                if (count($insertData) >= $batchSize) {

                    $this->db->trans_start();

                    $this->db->insert_batch(
                        'masterfile',
                        $insertData
                    );

                    $this->db->trans_complete();

                    if ($this->db->trans_status() === FALSE) {

                        fclose($handle);

                        $this->session->set_flashdata(
                            'msg',
                            "Import failed while processing $fileName."
                        );

                        redirect('menu');
                        return;
                    }

                    $insertedCount = count($insertData);

                    $totalInserted += $insertedCount;
                    $fileInserted  += $insertedCount;

                    /*
                 * Clear the batch from memory
                 */
                    $insertData = [];
                }
            }

            /*
         * ======================================================
         * INSERT REMAINING RECORDS
         * ======================================================
         */

            if (!empty($insertData)) {

                $this->db->trans_start();

                $this->db->insert_batch(
                    'masterfile',
                    $insertData
                );

                $this->db->trans_complete();

                if ($this->db->trans_status() === FALSE) {

                    fclose($handle);

                    $this->session->set_flashdata(
                        'msg',
                        "Import failed while processing $fileName."
                    );

                    redirect('menu');
                    return;
                }

                $insertedCount = count($insertData);

                $totalInserted += $insertedCount;
                $fileInserted  += $insertedCount;

                $insertData = [];
            }

            fclose($handle);

            $totalSkipped += $fileSkipped;
        }

        /*
     * ==========================================================
     * FINAL MESSAGE
     * ==========================================================
     */

        $message =
            "CSV import completed. " .
            "$totalInserted asset(s) imported, " .
            "$totalSkipped row(s) skipped from " .
            "$totalFiles CSV file(s).";

        $this->session->set_flashdata('msg', $message);

        redirect('menu');
    }


    public function importCsvJson()
    {
        $this->output->set_content_type('application/json');

        $csvPath = FCPATH . 'masterfile/';
        $files = glob($csvPath . '*.csv');

        if (empty($files)) {
            echo json_encode(['error' => 'No CSV files found.']);
            return;
        }

        $totalInserted = 0;
        $totalSkipped  = 0;

        foreach ($files as $file) {
            $handle = fopen($file, "r");
            if (!$handle) continue;
            fgetcsv($handle); // skip header

            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if (count($row) < 7) continue;

                $barcode  = $this->safe_utf8(trim($row[0]));
                $desc     = $this->safe_utf8(trim($row[1]));

                if (empty($barcode) || empty($desc)) {
                    $totalSkipped++;
                    continue;
                }

                $exists = $this->db->where('barcode', $barcode)->get('masterfile')->row();
                if ($exists) {
                    $totalSkipped++;
                    continue;
                }

                $data = [
                    'barcode'  => $barcode,
                    'desc'     => $desc,
                    'acost'    => floatval(trim($row[2])),
                    'adate'    => $this->safe_utf8(trim($row[3])),
                    'cat_type' => $this->safe_utf8(trim($row[4])),
                    'locname'  => $this->safe_utf8(trim($row[5])),
                    'dept'     => $this->safe_utf8(trim($row[6])),
                    'status'   => $this->safe_utf8(trim($row[7])),
                ];

                $this->db->insert('masterfile', $data);
                $totalInserted++;
            }
            fclose($handle);
        }

        echo json_encode(['inserted' => $totalInserted, 'skipped' => $totalSkipped]);
    }
}
