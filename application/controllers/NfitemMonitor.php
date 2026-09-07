<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CsvMonitor extends CI_Controller {

    private $dir;

    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Manila');
        $this->dir = FCPATH . 'nfitems/';
        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }

        if (!$this->session->userdata('is_admin')) {
            show_error('Access denied.', 403);
        }
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
    
}
