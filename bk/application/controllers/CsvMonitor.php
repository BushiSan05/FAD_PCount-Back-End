<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CsvMonitor extends CI_Controller {

    private $dir;

    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Manila');
        $this->dir = FCPATH . 'pcountdata/';
        $this->nfdir = FCPATH . 'nfitems/';
        $this->load->library('session');
    }

    public function menu()
    {
        $data['msg'] = $this->session->flashdata('msg');
        $this->load->view('csv_menu', $data);
    }


    // 📄 Main monitoring page
    public function index()
    {
        $files = [];

        if (is_dir($this->dir)) {
            foreach (scandir($this->dir) as $file) {
                if ($file === '.' || $file === '..') continue;

                $nameParts = explode('_', $file);
                $uploader = isset($nameParts[2]) ? $nameParts[2] : 'Unknown';
                $path = $this->dir . $file;

                $files[] = [
                    'name' => $file,
                    'size' => filesize($path),
                    'time' => date('Y-m-d h:i:s', filemtime($path)),
                    'uploader' => $uploader
                ];                
            }
        }

        // Sort by 'time' descending (newest first)
        usort($files, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        $data['files'] = $files;
        $this->load->view('csv_monitor', $data);
    }

    public function nfitem()
    {
        $files = [];

        if (is_dir($this->nfdir)) {
            foreach (scandir($this->nfdir) as $file) {
                if ($file === '.' || $file === '..') continue;

                $nameParts = explode('_', $file);
                $uploader = isset($nameParts[2]) ? $nameParts[2] : 'Unknown';
                $path = $this->nfdir . $file;

                $files[] = [
                    'name' => $file,
                    'size' => filesize($path),
                    'time' => date('Y-m-d h:i:s', filemtime($path)),
                    'uploader' => $uploader
                ];                
            }
        }

        // Sort by 'time' descending (newest first)
        usort($files, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        $data['files'] = $files;
        $this->load->view('csv_nfitems', $data);
    }

    // 📥 Download CSV
    public function download($filename)
    {
        $filePath = $this->dir . $filename;

        if (!file_exists($filePath)) {
            show_404();
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        readfile($filePath);
        exit;
    }

    public function viewNfCsv($filename)
    {
        $filename = rawurldecode($filename);
        $path = FCPATH . 'nfitems/' . $filename;
    
        if (!file_exists($path)) {
            show_404();
        }
    
        $fp = fopen($path, 'r');
    
        $header = fgetcsv($fp);

        // Remove UTF-8 BOM if present
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        
        $rows = [];
        
        while (($row = fgetcsv($fp)) !== false) {
            $row = array_pad($row, count($header), '');
            $rows[] = array_combine($header, $row);
        }
    
        fclose($fp);
    
        $data = [
            'filename' => $filename,
            'rows' => $rows
        ];
    
        $this->load->view('csv_nfitems_viewer', $data);
    }

    public function upload()
    {
        $dir = FCPATH . 'masterfile/';
        $files = [];
    
        if (is_dir($dir)) {
            foreach (scandir($dir) as $file) {
                if ($file === '.' || $file === '..') continue;
                if (pathinfo($file, PATHINFO_EXTENSION) !== 'csv') continue;
    
                $path = $dir . $file;
    
                $files[] = [
                    'name' => $file,
                    'size' => round(filesize($path) / 1024, 2),
                    'time' => date('Y-m-d H:i:s', filemtime($path))
                ];
            }
        }
    
        // ✅ PHP-compatible sorting
        usort($files, function ($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });
    
        $this->load->view('csv_upload', ['files' => $files]);
    }    
    

    public function doUpload()
    {
        $uploadDir = FCPATH . 'masterfile/';
    
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $newName = $this->input->post('new_name');
        $overwrite = $this->input->post('overwrite') === 'true'; // true or false
    
        if (!$newName) {
            http_response_code(400);
            echo 'Filename is required';
            return;
        }
    
        $newName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $newName);
        $newFile = $uploadDir . $newName . '.csv';

        if (file_exists($newFile) && !$overwrite) {
            http_response_code(409); // Conflict
            echo 'File "' . $newName . '.csv" already exists';
            return;
        }

        $config['upload_path']   = $uploadDir;
        $config['allowed_types'] = 'csv';
        $config['file_name']     = $newName . '.csv';
        $config['overwrite']     = $overwrite;
    
        $this->load->library('upload', $config);
    
        if (!$this->upload->do_upload('csv_file')) {
            http_response_code(400);
            echo $this->upload->display_errors('', '');
            return;
        }
    
        echo 'OK';
    }

    
}
