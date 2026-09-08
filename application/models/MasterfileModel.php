<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MasterfileModel extends CI_Model
{
    private function applyBarpostFilter($currentBarpost = '', $currentCasBarpost = '')
    {
        if (empty($currentBarpost) && empty($currentCasBarpost)) {
            return;
        }

        $barpostValues = [];
        if (!empty($currentBarpost)) {
            $barpostValues[] = $currentBarpost;
        }
        if (!empty($currentCasBarpost) && $currentCasBarpost !== $currentBarpost) {
            $barpostValues[] = $currentCasBarpost;
        }
        if (empty($barpostValues)) {
            return;
        }

        $escapedBarposts = [];
        foreach ($barpostValues as $barpost) {
            $escapedBarposts[] = $this->db->escape($barpost);
        }
        $barpostList = implode(',', $escapedBarposts);

        $currentBarcodeExists = "
            NOT EXISTS (
                SELECT 1
                FROM masterfile AS mf_current
                WHERE mf_current.barcode = masterfile.barcode
                AND mf_current.barpost IN ($barpostList)
            )
        ";

        $this->db->group_start();
        $this->db->where_in('masterfile.barpost', $barpostValues);
        $this->db->or_group_start();
        $this->db->where('masterfile.status !=', 'FA_TRAN');
        $this->db->where($currentBarcodeExists, null, false);
        $this->db->group_end();
        $this->db->group_end();
    }

    private function applyMasterfileFilters(
        $search = '',
        $locname = '',
        $dept = '',
        $ast_type = '',
        $cat_type = '',
        $currentBarpost = '',
        $currentCasBarpost = ''
    ) {
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('barcode', $search);
            $this->db->or_like('desc', $search);
            $this->db->group_end();
        }
        if (!empty($locname)) {
            $this->db->where('locname', $locname);
        }
        if (!empty($dept)) {
            $this->db->where('dept', $dept);
        }
        if (!empty($ast_type)) {
            $this->db->where('ast_type', $ast_type);
        }
        if (!empty($cat_type)) {
            $this->db->where('cat_type', $cat_type);
        }

        $this->applyBarpostFilter($currentBarpost, $currentCasBarpost);
    }

    private function getDistinctColumn($column)
    {
        $this->db->distinct();
        $this->db->select($column);
        $this->db->from('masterfile');
        $this->db->where($column . ' !=', '');
        $this->db->order_by($column, 'asc');
        return $this->db->get()->result_array();
    }

    public function getMasterfileWithFilters(
        $offset,
        $limit,
        $search = '',
        $locname = '',
        $dept = '',
        $ast_type = '',
        $cat_type = '',
        $sort_by = 'barcode',
        $sort_order = 'asc',
        $currentBarpost = '',
        $currentCasBarpost = ''
    ) {
        $this->db->select(
            'barcode, desc, acost, adate, ast_type, cat_type, est_life, locname, dept, status, barpost'
        );
        $this->db->from('masterfile');
        $this->applyMasterfileFilters(
            $search,
            $locname,
            $dept,
            $ast_type,
            $cat_type,
            $currentBarpost,
            $currentCasBarpost
        );

        $valid_sort_fields = [
            'barcode', 'desc', 'acost', 'adate', 'ast_type', 'cat_type',
            'est_life', 'locname', 'dept', 'status', 'barpost',
        ];

        $sort_order = strtolower($sort_order);
        if ($sort_order !== 'asc' && $sort_order !== 'desc') {
            $sort_order = 'asc';
        }

        if (in_array($sort_by, $valid_sort_fields, true)) {
            $this->db->order_by($sort_by, $sort_order);
        } else {
            $this->db->order_by('barcode', 'asc');
        }

        $this->db->limit((int) $limit, (int) $offset);
        return $this->db->get()->result_array();
    }

    public function getMasterfileCountWithFilters(
        $search = '',
        $locname = '',
        $dept = '',
        $ast_type = '',
        $cat_type = '',
        $currentBarpost = '',
        $currentCasBarpost = ''
    ) {
        $this->db->from('masterfile');
        $this->applyMasterfileFilters(
            $search,
            $locname,
            $dept,
            $ast_type,
            $cat_type,
            $currentBarpost,
            $currentCasBarpost
        );
        return $this->db->count_all_results();
    }

    public function getUniqueLocations()
    {
        return $this->getDistinctColumn('locname');
    }

    public function getUniqueDepartments()
    {
        return $this->getDistinctColumn('dept');
    }

    public function getDepartmentsByLocation($location)
    {
        $this->db->distinct();
        $this->db->select('dept');
        $this->db->from('masterfile');
        $this->db->where('locname', $location);
        $this->db->where('dept !=', '');
        $this->db->order_by('dept', 'asc');
        return $this->db->get()->result_array();
    }

    public function getUniqueAssetTypes()
    {
        return $this->getDistinctColumn('ast_type');
    }

    public function getUniqueCategoryTypes()
    {
        return $this->getDistinctColumn('cat_type');
    }

    public function getUniqueBarposts()
    {
        return $this->getDistinctColumn('barpost');
    }

    public function getBarpostByDepartment($dept)
    {
        $this->db->select('barpost, cas_barpost');
        $this->db->from('source_barpost');
        $this->db->where('dept', $dept);
        $this->db->limit(1);
        return $this->db->get()->row_array();
    }
}
