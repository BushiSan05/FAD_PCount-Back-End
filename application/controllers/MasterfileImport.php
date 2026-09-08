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

        $this->load->library('session');
    }

    private function safe_utf8($string)
    {
        if (empty($string)) {
            return '';
        }

        $enc = mb_detect_encoding($string, 'UTF-8, ISO-8859-1, Windows-1252', true);
        if ($enc) {
            return mb_convert_encoding($string, 'UTF-8', $enc);
        }

        return $string;
    }

    public function importCsv()
    {
        $csvPath = FCPATH . 'masterfile/';
        $batchSize = 1000;

        if (!is_dir($csvPath)) {
            $this->session->set_flashdata('msg', "CSV folder not found: $csvPath");
            redirect('menu');
            return;
        }

        $files = glob($csvPath . '*.csv');
        if (empty($files)) {
            $this->session->set_flashdata('msg', 'No CSV files found in masterfile folder.');
            redirect('menu');
            return;
        }

        sort($files);

        $totalInserted = 0;
        $totalSkipped = 0;
        $totalFiles = count($files);

        $this->db->trans_start();
        $this->db->truncate('masterfile');
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('msg', 'Failed to clear masterfile table.');
            redirect('menu');
            return;
        }

        foreach ($files as $file) {
            $fileName = basename($file);
            $handle = fopen($file, 'r');
            if ($handle === FALSE) {
                continue;
            }

            fgetcsv($handle, 0, ',');

            $insertData = [];
            $fileSkipped = 0;

            while (($row = fgetcsv($handle, 0, ',')) !== FALSE) {
                if (count($row) < 8) {
                    $fileSkipped++;
                    continue;
                }

                $barcode = $this->safe_utf8(trim($row[0]));
                $desc = $this->safe_utf8(trim($row[1]));
                $acost = floatval(trim($row[2]));
                $adate = $this->safe_utf8(trim($row[3]));
                $ast_type = $this->safe_utf8(trim($row[4]));
                $cat_type = $this->safe_utf8(trim($row[5]));
                $est_life = floatval(trim($row[6]));
                $locname = $this->safe_utf8(trim($row[7]));
                $dept = $this->safe_utf8(trim($row[8]));
                $status = $this->safe_utf8(trim($row[9]));

                if (empty($barcode) || empty($desc)) {
                    $fileSkipped++;
                    continue;
                }

                $insertData[] = [
                    'barcode' => $barcode,
                    'desc' => $desc,
                    'acost' => $acost,
                    'adate' => $adate,
                    'ast_type' => $ast_type,
                    'cat_type' => $cat_type,
                    'est_life' => $est_life,
                    'locname' => $locname,
                    'dept' => $dept,
                    'status' => $status,
                    'barpost' => $fileName,
                ];

                if (count($insertData) >= $batchSize) {
                    $this->db->trans_start();
                    $this->db->insert_batch('masterfile', $insertData);
                    $this->db->trans_complete();

                    if ($this->db->trans_status() === FALSE) {
                        fclose($handle);
                        $this->session->set_flashdata('msg', "Import failed while processing $fileName.");
                        redirect('menu');
                        return;
                    }

                    $totalInserted += count($insertData);
                    $insertData = [];
                }
            }

            if (!empty($insertData)) {
                $this->db->trans_start();
                $this->db->insert_batch('masterfile', $insertData);
                $this->db->trans_complete();

                if ($this->db->trans_status() === FALSE) {
                    fclose($handle);
                    $this->session->set_flashdata('msg', "Import failed while processing $fileName.");
                    redirect('menu');
                    return;
                }

                $totalInserted += count($insertData);
            }

            fclose($handle);
            $totalSkipped += $fileSkipped;
        }

        $this->session->set_flashdata(
            'msg',
            "CSV import completed. $totalInserted asset(s) imported, $totalSkipped row(s) skipped from $totalFiles CSV file(s)."
        );
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
        $totalSkipped = 0;

        foreach ($files as $file) {
            $handle = fopen($file, 'r');
            if (!$handle) {
                continue;
            }

            fgetcsv($handle);

            while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
                if (count($row) < 7) {
                    continue;
                }

                $barcode = $this->safe_utf8(trim($row[0]));
                $desc = $this->safe_utf8(trim($row[1]));

                if (empty($barcode) || empty($desc)) {
                    $totalSkipped++;
                    continue;
                }

                $exists = $this->db->where('barcode', $barcode)->get('masterfile')->row();
                if ($exists) {
                    $totalSkipped++;
                    continue;
                }

                $this->db->insert('masterfile', [
                    'barcode' => $barcode,
                    'desc' => $desc,
                    'acost' => floatval(trim($row[2])),
                    'adate' => $this->safe_utf8(trim($row[3])),
                    'ast_type' => $this->safe_utf8(trim($row[4])),
                    'cat_type' => $this->safe_utf8(trim($row[5])),
                    'est_life' => floatval(trim($row[6])),
                    'locname' => $this->safe_utf8(trim($row[7])),
                    'dept' => $this->safe_utf8(trim($row[8])),
                    'status' => $this->safe_utf8(trim($row[9])),
                ]);
                $totalInserted++;
            }

            fclose($handle);
        }

        echo json_encode(['inserted' => $totalInserted, 'skipped' => $totalSkipped]);
    }
}
