<?php
// Fetch all files from the directory
header('Content-Type: text/html; charset=utf-8');

$dirPath = FCPATH . 'pcountdata/';
$allFiles = [];

if (is_dir($dirPath)) {
    foreach (scandir($dirPath) as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $filePath = $dirPath . $file;

        if (is_file($filePath)) {
            $allFiles[] = $file;
        }
    }
}

// Fetch uploader information from DB
$dbFiles = $this->db
    ->select('filename, uploader, fullname, uploaded_at')
    ->get('uploaded_csvs')
    ->result_array();

// Map uploader information by filename
$uploaderMap = [];

foreach ($dbFiles as $f) {
    $uploaderMap[$f['filename']] = [
        'uploader' => $f['uploader'],
        'fullname' => $f['fullname'],
        'uploaded_at' => $f['uploaded_at']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <link rel="shortcut icon"
        href="<?= base_url('favicon.ico') ?>">

    <link rel="icon"
        type="image/x-icon"
        href="<?= base_url('favicon.ico') ?>">

    <title>FAD PCount CSV's</title>

    <link rel="stylesheet" href="<?= base_url('assets/css/csv_monitor.css') ?>">

</head>

<body>

    <div class="page-container">

        <!-- ==============================
             HEADER
        =============================== -->

        <div class="page-header">

            <a
                href="<?= base_url('menu') ?>"
                class="back-btn"
                title="Back to Menu">

                <svg
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round">

                    <path d="M19 12H5"></path>
                    <path d="M12 19L5 12L12 5"></path>

                </svg>

            </a>

            <h1 class="page-title">
                📊 FAD PCount CSV Viewing
            </h1>

            <div class="header-spacer"></div>

        </div>


        <!-- ==============================
             INFORMATION CARD
        =============================== -->

        <div class="info-card">

            <div class="info-left">

                <div class="info-icon">
                    📁
                </div>

                <div class="info-content">

                    <div class="info-title">
                        CSV Upload Directory
                    </div>

                    <div class="info-path">
                        /pcountdata/
                    </div>

                </div>

            </div>

            <div class="file-count">

                <?= count($allFiles) ?>
                <?= count($allFiles) == 1 ? 'File' : 'Files' ?>

            </div>

        </div>


        <!-- ==============================
             SEARCH
        =============================== -->

        <div class="search-card">

            <div class="search-wrapper">

                <span class="search-icon">
                    🔎
                </span>

                <input
                    type="text"
                    id="fileSearch"
                    placeholder="Search filename, uploader, or full name..."
                    autocomplete="off">

                <button
                    type="button"
                    id="clearSearch"
                    class="clear-search"
                    title="Clear search">

                    ×

                </button>

            </div>

        </div>


        <!-- ==============================
             TABLE
        =============================== -->

        <div class="table-card">

            <div class="table-container">

                <table id="csvTable">

                    <thead>

                        <tr>

                            <th data-column="0">
                                #
                                <span class="sort-arrow"></span>
                            </th>

                            <th data-column="1">
                                Filename
                                <span class="sort-arrow"></span>
                            </th>

                            <th data-column="2">
                                Last Uploader
                                <span class="sort-arrow"></span>
                            </th>

                            <th data-column="3">
                                Full Name
                                <span class="sort-arrow"></span>
                            </th>

                            <th data-column="4">
                                Size (KB)
                                <span class="sort-arrow"></span>
                            </th>

                            <th data-column="5">
                                Uploaded
                                <span class="sort-arrow"></span>
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php if (empty($allFiles)): ?>

                            <tr class="empty-message">

                                <td colspan="7" class="empty-row">

                                    <div class="empty-icon">
                                        📂
                                    </div>

                                    <div class="empty-text">
                                        No CSV files uploaded
                                    </div>

                                </td>

                            </tr>

                        <?php else: ?>

                            <?php $count = 1; ?>

                            <?php foreach ($allFiles as $fileName): ?>

                                <?php

                                $filePath = $dirPath . $fileName;

                                $sizeKB = file_exists($filePath)
                                    ? round(filesize($filePath) / 1024, 2)
                                    : 0;

                                $uploader = isset($uploaderMap[$fileName])
                                    ? $uploaderMap[$fileName]['uploader']
                                    : 'Unknown';

                                $fullname = isset($uploaderMap[$fileName])
                                    ? $uploaderMap[$fileName]['fullname']
                                    : 'Unknown';

                                $uploadedAt = isset($uploaderMap[$fileName])
                                    ? $uploaderMap[$fileName]['uploaded_at']
                                    : '-';

                                $uploadedTimestamp = strtotime($uploadedAt);

                                ?>

                                <tr
                                    data-search="<?= htmlspecialchars(
                                                        strtolower(
                                                            $fileName . ' ' .
                                                                $uploader . ' ' .
                                                                $fullname
                                                        )
                                                    ) ?>">

                                    <td data-sort="<?= $count ?>">
                                        <?= $count++ ?>
                                    </td>

                                    <td
                                        data-sort="<?= htmlspecialchars($fileName) ?>"
                                        class="filename">

                                        <?= htmlspecialchars($fileName) ?>

                                    </td>

                                    <td
                                        data-sort="<?= htmlspecialchars($uploader) ?>">

                                        <?= htmlspecialchars($uploader) ?>

                                    </td>

                                    <td
                                        data-sort="<?= htmlspecialchars($fullname) ?>">

                                        <?= htmlspecialchars($fullname) ?>

                                    </td>

                                    <td data-sort="<?= $sizeKB ?>">

                                        <?= number_format($sizeKB, 2) ?>

                                    </td>

                                    <td data-sort="<?= $uploadedTimestamp ?>">

                                        <?= htmlspecialchars($uploadedAt) ?>

                                    </td>

                                    <td>

                                        <a
                                            href="<?= base_url('pcountdata/' . rawurlencode($fileName)) ?>"
                                            target="_blank"
                                            class="download-btn"
                                            title="Open CSV file">

                                            📥 Download

                                        </a>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>


            <!-- TABLE FOOTER -->

            <div class="table-footer">

                <div class="result-count">

                    Showing
                    <span id="visibleCount">
                        <?= count($allFiles) ?>
                    </span>
                    of
                    <?= count($allFiles) ?>
                    files

                </div>

                <div class="scroll-hint">

                    ↔ Scroll horizontally to view more columns

                </div>

            </div>

        </div>

    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const table =
                document.getElementById('csvTable');

            const tbody =
                table.querySelector('tbody');

            const headers =
                table.querySelectorAll('thead th[data-column]');

            const searchInput =
                document.getElementById('fileSearch');

            const clearSearch =
                document.getElementById('clearSearch');

            const visibleCount =
                document.getElementById('visibleCount');


            /* =========================================
               SEARCH
            ========================================= */

            function filterTable() {

                const searchTerm =
                    searchInput.value
                    .trim()
                    .toLowerCase();

                const rows =
                    tbody.querySelectorAll(
                        'tr[data-search]'
                    );

                let visible = 0;

                rows.forEach(function(row) {

                    const searchableText =
                        row.dataset.search || '';

                    const matches =
                        searchableText.includes(searchTerm);

                    row.style.display =
                        matches ? '' : 'none';

                    if (matches) {
                        visible++;
                    }

                });

                visibleCount.textContent = visible;

                clearSearch.style.display =
                    searchTerm !== '' ?
                    'flex' :
                    'none';

            }


            searchInput.addEventListener(
                'input',
                filterTable
            );


            clearSearch.addEventListener(
                'click',
                function() {

                    searchInput.value = '';

                    filterTable();

                    searchInput.focus();

                }
            );


            /* =========================================
               SORTING
            ========================================= */

            function getCellValue(row, index) {

                const cell =
                    row.children[index];

                if (!cell) {
                    return '';
                }

                return cell.dataset.sort ||
                    cell.innerText.trim();
            }


            function compareValues(
                valueA,
                valueB,
                ascending
            ) {

                const numA =
                    Number(valueA);

                const numB =
                    Number(valueB);


                const bothNumeric =
                    valueA !== '' &&
                    valueB !== '' &&
                    !isNaN(numA) &&
                    !isNaN(numB);


                if (bothNumeric) {

                    return ascending ?
                        numA - numB :
                        numB - numA;

                }


                return ascending ?
                    valueA.toString()
                    .localeCompare(
                        valueB.toString(),
                        undefined, {
                            numeric: true,
                            sensitivity: 'base'
                        }
                    ) :
                    valueB.toString()
                    .localeCompare(
                        valueA.toString(),
                        undefined, {
                            numeric: true,
                            sensitivity: 'base'
                        }
                    );

            }


            headers.forEach(function(header) {

                header.asc = undefined;


                header.addEventListener(
                    'click',
                    function() {

                        const columnIndex =
                            parseInt(
                                header.dataset.column,
                                10
                            );


                        /*
                         * First click = ascending
                         * Next click = descending
                         */

                        header.asc =
                            header.asc === undefined ?
                            true :
                            !header.asc;


                        const ascending =
                            header.asc;


                        /*
                         * Remove arrows
                         */

                        headers.forEach(
                            function(h) {

                                const arrow =
                                    h.querySelector(
                                        '.sort-arrow'
                                    );

                                if (arrow) {

                                    arrow.classList.remove(
                                        'sort-asc',
                                        'sort-desc'
                                    );

                                }

                            }
                        );


                        /*
                         * Add arrow
                         */

                        const arrow =
                            header.querySelector(
                                '.sort-arrow'
                            );

                        if (arrow) {

                            arrow.classList.add(
                                ascending ?
                                'sort-asc' :
                                'sort-desc'
                            );

                        }


                        /*
                         * Get rows
                         */

                        const rows =
                            Array.from(
                                tbody.querySelectorAll(
                                    'tr[data-search]'
                                )
                            );


                        /*
                         * Sort rows
                         */

                        rows.sort(
                            function(a, b) {

                                return compareValues(
                                    getCellValue(
                                        a,
                                        columnIndex
                                    ),
                                    getCellValue(
                                        b,
                                        columnIndex
                                    ),
                                    ascending
                                );

                            }
                        );


                        /*
                         * Reinsert rows
                         */

                        rows.forEach(
                            function(row) {

                                tbody.appendChild(row);

                            }
                        );


                        /*
                         * Update row numbers
                         */

                        const visibleRows =
                            Array.from(
                                tbody.querySelectorAll(
                                    'tr[data-search]'
                                )
                            );


                        visibleRows.forEach(
                            function(row, index) {

                                const numberCell =
                                    row.children[0];

                                if (numberCell) {

                                    numberCell.textContent =
                                        index + 1;

                                    numberCell.dataset.sort =
                                        index + 1;

                                }

                            }
                        );

                    }
                );

            });

        });
    </script>

</body>

</html>