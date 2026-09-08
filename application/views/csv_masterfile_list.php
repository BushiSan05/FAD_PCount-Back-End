<?php
header('Content-Type: text/html; charset=utf-8');

if (!function_exists('masterfile_h')) {
    function masterfile_h($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('masterfile_sort_arrow')) {
    function masterfile_sort_arrow($column, $sort_by, $sort_order)
    {
        if ($sort_by !== $column) {
            return '';
        }
        return $sort_order === 'asc' ? '▲' : '▼';
    }
}

if (!function_exists('get_masterfile_page_url')) {
    function get_masterfile_page_url($page, $params)
    {
        $params['page'] = (int) $page;
        return '?' . http_build_query($params);
    }
}

$filters_active = !empty($search) || !empty($locname) || !empty($dept)
    || !empty($ast_type) || !empty($cat_type)
    || !empty($currentBarpost) || !empty($currentCasBarpost);

$show_from = (($current_page - 1) * $per_page) + 1;
$show_to = min($current_page * $per_page, $total_count);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
    <title>Fixed Asset List</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/csv_masterfile_list.css') ?>">
</head>

<body>
    <div class="page-container">
        <div class="page-header">
            <a href="<?= base_url('menu') ?>" class="back-btn" title="Back to Menu">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5"></path>
                    <path d="M12 19L5 12L12 5"></path>
                </svg>
            </a>
            <div class="page-heading">
                <h1 class="page-title">Fixed Asset List</h1>
                <p class="page-subtitle">Search, filter and browse fixed asset records</p>
            </div>
            <div class="header-spacer"></div>
        </div>

        <div class="filter-card">
            <div class="filter-header">
                <div>
                    <h2 class="filter-title">Filter Records</h2>
                    <p class="filter-description">
                        Search by barcode or description and filter by location and department.
                    </p>
                </div>
            </div>

            <form method="GET" action="" id="filterForm">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label for="search">Search</label>
                        <div class="input-wrapper">
                            <span class="input-icon">🔍</span>
                            <input
                                type="text"
                                id="search"
                                name="search"
                                value="<?= masterfile_h($search) ?>"
                                placeholder="Barcode or description..."
                                autocomplete="off">
                        </div>
                    </div>

                    <div class="filter-group">
                        <label for="locname">Location</label>
                        <select id="locname" name="locname" onchange="updateDepartments()">
                            <option value="">All Locations</option>
                            <?php foreach ($locations as $loc): ?>
                                <?php $locationValue = isset($loc['locname']) ? $loc['locname'] : ''; ?>
                                <option value="<?= masterfile_h($locationValue) ?>" <?= $locname === $locationValue ? 'selected' : '' ?>>
                                    <?= masterfile_h($locationValue) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="dept">Department</label>
                        <select id="dept" name="dept" onchange="updateBarpostInfo()" <?= empty($locname) ? 'disabled' : '' ?>>
                            <option value="">All Departments</option>
                            <?php if (!empty($locname)): ?>
                                <?php foreach ($departments as $dep): ?>
                                    <?php $departmentValue = isset($dep['dept']) ? $dep['dept'] : ''; ?>
                                    <option value="<?= masterfile_h($departmentValue) ?>" <?= $dept === $departmentValue ? 'selected' : '' ?>>
                                        <?= masterfile_h($departmentValue) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="ast_type">Asset Type</label>
                        <select id="ast_type" name="ast_type">
                            <option value="">All Asset Types</option>
                            <?php if (!empty($asset_types)): ?>
                                <?php foreach ($asset_types as $type): ?>
                                    <?php $assetTypeValue = isset($type['ast_type']) ? $type['ast_type'] : ''; ?>
                                    <option value="<?= masterfile_h($assetTypeValue) ?>" <?= $ast_type === $assetTypeValue ? 'selected' : '' ?>>
                                        <?= masterfile_h($assetTypeValue) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="cat_type">Category Type</label>
                        <select id="cat_type" name="cat_type">
                            <option value="">All Category Types</option>
                            <?php if (!empty($category_types)): ?>
                                <?php foreach ($category_types as $category): ?>
                                    <?php $categoryValue = isset($category['cat_type']) ? $category['cat_type'] : ''; ?>
                                    <option value="<?= masterfile_h($categoryValue) ?>" <?= $cat_type === $categoryValue ? 'selected' : '' ?>>
                                        <?= masterfile_h($categoryValue) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div class="filter-actions">
                    <div class="filter-buttons">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="<?= current_url() ?>" class="btn btn-secondary">Clear Filters</a>
                    </div>
                    <div class="filter-hint">
                        Select a location first to enable Department filtering.
                    </div>
                </div>

                <?php if (!empty($search) || !empty($locname) || !empty($dept) || !empty($ast_type) || !empty($cat_type)): ?>
                    <div class="active-filters">
                        <span class="active-label">Active Filters:</span>
                        <?php if (!empty($search)): ?>
                            <span class="filter-badge"><strong>Search:</strong> <?= masterfile_h($search) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($locname)): ?>
                            <span class="filter-badge"><strong>Location:</strong> <?= masterfile_h($locname) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($dept)): ?>
                            <span class="filter-badge"><strong>Department:</strong> <?= masterfile_h($dept) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($ast_type)): ?>
                            <span class="filter-badge"><strong>Asset Type:</strong> <?= masterfile_h($ast_type) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($cat_type)): ?>
                            <span class="filter-badge"><strong>Category Type:</strong> <?= masterfile_h($cat_type) ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <div class="info-card">
            <div class="info-left">
                <div class="info-item">
                    <strong>Total Records:</strong> <?= number_format($total_count) ?>
                </div>
                <?php if ($total_count > 0): ?>
                    <div class="info-item">
                        <strong>Showing:</strong>
                        <?= number_format($show_from) ?> - <?= number_format($show_to) ?>
                    </div>
                <?php endif; ?>
                <div class="info-item">
                    <strong>Page:</strong>
                    <?= number_format($current_page) ?> of <?= number_format(max(1, $total_pages)) ?>
                </div>
                <?php if (!empty($currentBarpost) || !empty($currentCasBarpost)): ?>
                    <div class="barpost-info">
                        <?php if (!empty($currentBarpost)): ?>
                            <span class="barpost-badge">Source: <?= masterfile_h($currentBarpost) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($currentCasBarpost)): ?>
                            <span class="barpost-badge">CAS: <?= masterfile_h($currentCasBarpost) ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ($filters_active): ?>
                <div class="filter-status">Filters Applied</div>
            <?php endif; ?>
        </div>

        <div class="table-card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th class="number-col">#</th>
                            <th class="barcode-col sortable" onclick="sortTable('barcode')">
                                Barcode <span class="sort-arrow"><?= masterfile_sort_arrow('barcode', $sort_by, $sort_order) ?></span>
                            </th>
                            <th class="description-col sortable" onclick="sortTable('desc')">
                                Description <span class="sort-arrow"><?= masterfile_sort_arrow('desc', $sort_by, $sort_order) ?></span>
                            </th>
                            <th class="cost-col sortable" onclick="sortTable('acost')">
                                Cost <span class="sort-arrow"><?= masterfile_sort_arrow('acost', $sort_by, $sort_order) ?></span>
                            </th>
                            <th class="date-col sortable" onclick="sortTable('adate')">
                                Date <span class="sort-arrow"><?= masterfile_sort_arrow('adate', $sort_by, $sort_order) ?></span>
                            </th>
                            <th class="ast_type sortable" onclick="sortTable('ast_type')">
                                Asset Type <span class="sort-arrow"><?= masterfile_sort_arrow('ast_type', $sort_by, $sort_order) ?></span>
                            </th>
                            <th class="category-col sortable" onclick="sortTable('cat_type')">
                                Category Type <span class="sort-arrow"><?= masterfile_sort_arrow('cat_type', $sort_by, $sort_order) ?></span>
                            </th>
                            <th class="category-col sortable" onclick="sortTable('est_life')">
                                Estimated Life <span class="sort-arrow"><?= masterfile_sort_arrow('est_life', $sort_by, $sort_order) ?></span>
                            </th>
                            <th class="location-col sortable" onclick="sortTable('locname')">
                                Location <span class="sort-arrow"><?= masterfile_sort_arrow('locname', $sort_by, $sort_order) ?></span>
                            </th>
                            <th class="department-col sortable" onclick="sortTable('dept')">
                                Department <span class="sort-arrow"><?= masterfile_sort_arrow('dept', $sort_by, $sort_order) ?></span>
                            </th>
                            <th class="status-col sortable" onclick="sortTable('status')">
                                Status <span class="sort-arrow"><?= masterfile_sort_arrow('status', $sort_by, $sort_order) ?></span>
                            </th>
                            <th class="source-col sortable" onclick="sortTable('barpost')">
                                Source File <span class="sort-arrow"><?= masterfile_sort_arrow('barpost', $sort_by, $sort_order) ?></span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($masterfile)): ?>
                            <tr>
                                <td colspan="12" class="empty-row">
                                    <div class="empty-icon">No records</div>
                                    <div class="empty-text">No fixed asset records found.</div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $row_number = $show_from; ?>
                            <?php foreach ($masterfile as $item): ?>
                                <?php
                                $barcode = isset($item['barcode']) ? $item['barcode'] : '';
                                $description = isset($item['desc']) ? $item['desc'] : '';
                                $cost = isset($item['acost']) ? $item['acost'] : 0;
                                $date = isset($item['adate']) ? $item['adate'] : '';
                                $row_ast_type = isset($item['ast_type']) ? $item['ast_type'] : '';
                                $category = isset($item['cat_type']) ? $item['cat_type'] : '';
                                $est_life = isset($item['est_life']) ? $item['est_life'] : '';
                                $location = isset($item['locname']) ? $item['locname'] : '';
                                $department = isset($item['dept']) ? $item['dept'] : '';
                                $status = isset($item['status']) ? $item['status'] : '';
                                $barpost = isset($item['barpost']) ? $item['barpost'] : '';
                                ?>
                                <tr>
                                    <td class="number-col"><?= number_format($row_number++) ?></td>
                                    <td class="barcode-col"><?= masterfile_h($barcode) ?></td>
                                    <td class="description-col" title="<?= masterfile_h($description) ?>"><?= masterfile_h($description) ?></td>
                                    <td class="cost-col"><?= number_format((float) $cost, 2) ?></td>
                                    <td class="date-col"><?= masterfile_h($date) ?></td>
                                    <td class="ast_type-col"><?= masterfile_h($row_ast_type) ?></td>
                                    <td class="category-col"><?= masterfile_h($category) ?></td>
                                    <td class="category-col"><?= masterfile_h($est_life) ?></td>
                                    <td class="location-col" title="<?= masterfile_h($location) ?>"><?= masterfile_h($location) ?></td>
                                    <td class="department-col" title="<?= masterfile_h($department) ?>"><?= masterfile_h($department) ?></td>
                                    <td class="status-col"><?= masterfile_h($status) ?></td>
                                    <td class="source-col"><?= masterfile_h($barpost) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <div>
                    <?php if ($total_count > 0): ?>
                        Showing <strong><?= number_format($show_from) ?> - <?= number_format($show_to) ?></strong>
                        of <strong><?= number_format($total_count) ?></strong> records
                    <?php else: ?>
                        No records found
                    <?php endif; ?>
                </div>
                <div class="scroll-hint">Scroll horizontally to view more columns</div>
            </div>
        </div>

        <?php if ($total_pages > 1): ?>
            <?php
            $pagination_params = [];
            if (!empty($search)) $pagination_params['search'] = $search;
            if (!empty($locname)) $pagination_params['locname'] = $locname;
            if (!empty($dept)) $pagination_params['dept'] = $dept;
            if (!empty($ast_type)) $pagination_params['ast_type'] = $ast_type;
            if (!empty($cat_type)) $pagination_params['cat_type'] = $cat_type;
            $pagination_params['sort_by'] = !empty($sort_by) ? $sort_by : 'barcode';
            $pagination_params['sort_order'] = !empty($sort_order) ? $sort_order : 'asc';
            $start_page = max(1, $current_page - 2);
            $end_page = min($total_pages, $current_page + 2);
            ?>
            <div class="pagination-card">
                <div class="pagination">
                    <?php if ($current_page > 1): ?>
                        <a href="<?= get_masterfile_page_url(1, $pagination_params) ?>">
                            « <span class="pagination-text">First</span>
                        </a>
                        <a href="<?= get_masterfile_page_url($current_page - 1, $pagination_params) ?>">
                            ‹ <span class="pagination-text">Previous</span>
                        </a>
                    <?php endif; ?>

                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <?php if ($i == $current_page): ?>
                            <span class="current"><?= $i ?></span>
                        <?php else: ?>
                            <a href="<?= get_masterfile_page_url($i, $pagination_params) ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($current_page < $total_pages): ?>
                        <a href="<?= get_masterfile_page_url($current_page + 1, $pagination_params) ?>">
                            <span class="pagination-text">Next</span> ›
                        </a>
                        <a href="<?= get_masterfile_page_url($total_pages, $pagination_params) ?>">
                            <span class="pagination-text">Last</span> »
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function sortTable(column) {
            const urlParams = new URLSearchParams(window.location.search);
            const currentSortBy = urlParams.get('sort_by') || 'barcode';
            const currentSortOrder = urlParams.get('sort_order') || 'asc';
            let newSortOrder = 'asc';

            if (currentSortBy === column) {
                newSortOrder = currentSortOrder === 'asc' ? 'desc' : 'asc';
            }

            urlParams.set('sort_by', column);
            urlParams.set('sort_order', newSortOrder);
            urlParams.set('page', '1');
            window.location.href = '?' + urlParams.toString();
        }

        function updateDepartments() {
            const locationSelect = document.getElementById('locname');
            const deptSelect = document.getElementById('dept');
            const selectedLocation = locationSelect.value;
            const urlParams = new URLSearchParams(window.location.search);

            deptSelect.disabled = true;
            deptSelect.value = '';

            if (selectedLocation) {
                urlParams.set('locname', selectedLocation);
            } else {
                urlParams.delete('locname');
            }

            urlParams.delete('dept');
            urlParams.set('page', '1');
            window.location.href = '?' + urlParams.toString();
        }

        function updateBarpostInfo() {
            const deptSelect = document.getElementById('dept');
            const locationSelect = document.getElementById('locname');
            const selectedDept = deptSelect.value;
            const selectedLocation = locationSelect.value;
            const urlParams = new URLSearchParams(window.location.search);

            if (selectedLocation) {
                urlParams.set('locname', selectedLocation);
            } else {
                urlParams.delete('locname');
            }

            if (selectedDept) {
                urlParams.set('dept', selectedDept);
            } else {
                urlParams.delete('dept');
            }

            urlParams.set('page', '1');
            window.location.href = '?' + urlParams.toString();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const filterForm = document.getElementById('filterForm');
            const deptSelect = document.getElementById('dept');

            if (filterForm) {
                filterForm.addEventListener('submit', function() {
                    if (deptSelect && deptSelect.disabled) {
                        deptSelect.value = '';
                    }
                });
            }
        });
    </script>
</body>

</html>