<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MasterfileMonitor extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->database();
        $this->load->model('MasterfileModel');

        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }
    }

    public function index()
    {
        $search = $this->input->get('search') ? trim($this->input->get('search')) : '';
        $locname = $this->input->get('locname') ? trim($this->input->get('locname')) : '';
        $dept = $this->input->get('dept') ? trim($this->input->get('dept')) : '';
        $ast_type = $this->input->get('ast_type') ? trim($this->input->get('ast_type')) : '';
        $cat_type = $this->input->get('cat_type') ? trim($this->input->get('cat_type')) : '';
        $sort_by = $this->input->get('sort_by') ? trim($this->input->get('sort_by')) : 'barcode';
        $sort_order = $this->input->get('sort_order') ? trim($this->input->get('sort_order')) : 'asc';

        $currentBarpost = '';
        $currentCasBarpost = '';

        if (!empty($dept)) {
            $barpostData = $this->MasterfileModel->getBarpostByDepartment($dept);
            if ($barpostData) {
                $currentBarpost = isset($barpostData['barpost']) ? $barpostData['barpost'] : '';
                $currentCasBarpost = isset($barpostData['cas_barpost']) ? $barpostData['cas_barpost'] : '';
            }
        }

        $page = $this->input->get('page') ? (int) $this->input->get('page') : 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $masterfileData = $this->MasterfileModel->getMasterfileWithFilters(
            $offset,
            $limit,
            $search,
            $locname,
            $dept,
            $ast_type,
            $cat_type,
            $sort_by,
            $sort_order,
            $currentBarpost,
            $currentCasBarpost
        );
        $totalCount = $this->MasterfileModel->getMasterfileCountWithFilters(
            $search,
            $locname,
            $dept,
            $ast_type,
            $cat_type,
            $currentBarpost,
            $currentCasBarpost
        );

        $locations = $this->MasterfileModel->getUniqueLocations();
        if (!empty($locname)) {
            $departments = $this->MasterfileModel->getDepartmentsByLocation($locname);
        } else {
            $departments = $this->MasterfileModel->getUniqueDepartments();
        }

        $data = [
            'masterfile' => $masterfileData,
            'total_count' => $totalCount,
            'current_page' => $page,
            'per_page' => $limit,
            'total_pages' => ceil($totalCount / $limit),
            'search' => $search,
            'locname' => $locname,
            'dept' => $dept,
            'ast_type' => $ast_type,
            'cat_type' => $cat_type,
            'asset_types' => $this->MasterfileModel->getUniqueAssetTypes(),
            'category_types' => $this->MasterfileModel->getUniqueCategoryTypes(),
            'sort_by' => $sort_by,
            'sort_order' => $sort_order,
            'currentBarpost' => $currentBarpost,
            'currentCasBarpost' => $currentCasBarpost,
            'locations' => $locations,
            'departments' => $departments,
        ];

        $this->load->view('csv_masterfile_list', $data);
    }

    public function getDepartmentsByLocation()
    {
        $location = $this->input->get('location');
        $this->output->set_content_type('application/json');

        if (empty($location)) {
            $departments = $this->MasterfileModel->getUniqueDepartments();
        } else {
            $departments = $this->MasterfileModel->getDepartmentsByLocation($location);
        }

        $deptArray = [];
        if (!empty($departments)) {
            foreach ($departments as $dept) {
                if (isset($dept['dept'])) {
                    $deptArray[] = $dept['dept'];
                }
            }
        }

        echo json_encode($deptArray);
    }

    public function getBarpostByDepartment()
    {
        $dept = $this->input->get('dept');
        $this->output->set_content_type('application/json');

        if (empty($dept)) {
            echo json_encode(['barpost' => '', 'cas_barpost' => '']);
            return;
        }

        $barpostData = $this->MasterfileModel->getBarpostByDepartment($dept);

        if ($barpostData) {
            echo json_encode([
                'barpost' => isset($barpostData['barpost']) ? $barpostData['barpost'] : '',
                'cas_barpost' => isset($barpostData['cas_barpost']) ? $barpostData['cas_barpost'] : '',
            ]);
        } else {
            echo json_encode(['barpost' => '', 'cas_barpost' => '']);
        }
    }
}
