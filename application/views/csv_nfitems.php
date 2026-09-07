<?php
// Fetch all files from the directory
header('Content-Type: text/html; charset=utf-8');
$dirPath = FCPATH . 'nfitems/';
$allFiles = [];
if (is_dir($dirPath)) {
    foreach (scandir($dirPath) as $file) {
        if ($file === '.' || $file === '..') continue;
        $allFiles[] = $file;
    }
}

// Fetch uploader info from DB
$dbFiles = $this->db->select('filename, uploader, fullname, uploaded_at')
    ->get('uploaded_nfitems')
    ->result_array();

// Map uploader info by filename for easy lookup
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
<html>

<head>
    <meta charset="utf-8">
    <title>FAD PCount NFItem CSV's</title>
    <style>
        body {
            font-family: Arial;
            padding: 20px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        th {
            background: #f0f0f0;
        }

        .back-btn {
            position: fixed;
            top: 15px;
            left: 15px;
            background: #6c757d;
            color: white;
            padding: 10px 16px;
            border-radius: 6px;
            text-decoration: none;
            z-index: 999;
        }

        .back-btn:hover {
            background: #545b62;
        }
    </style>
</head>

<body>

    <a href="<?= base_url('menu') ?>" class="back-btn">
        ← Back to Menu
    </a>

    <h2>🗃️ NFItems CSV Viewing</h2>

    <p><b>Upload Path:</b> /nfitems/</p>

    <table>
        <tr>
            <th># <span class="sort-arrow"></span></th>
            <th>Filename <span class="sort-arrow"></span></th>
            <th>Last Uploader <span class="sort-arrow"></span></th>
            <th>Full Name <span class="sort-arrow"></span></th>
            <th>Size (KB) <span class="sort-arrow"></span></th>
            <th>Uploaded <span class="sort-arrow"></span></th>
            <th>Action</th>
        </tr>

        <style>
            th {
                cursor: pointer;
                user-select: none;
                position: relative;
            }

            .sort-arrow {
                display: inline-block;
                margin-left: 5px;
                width: 10px;
            }

            .sort-asc::after {
                content: "▲";
                font-size: 10px;
            }

            .sort-desc::after {
                content: "▼";
                font-size: 10px;
            }

            .nf-action-btn,
            .nf-download-select {
                display: inline-flex;
                align-items: center;
                vertical-align: middle;
                height: 34px;
                box-sizing: border-box;
                border-radius: 6px;
                font-size: 13px;
                font-family: inherit;
            }

            /* View Contents button */
            .nf-view-btn {
                padding: 0 12px;
                background: #f5f5f5;
                border: 1px solid #ccc;
                color: #333;
                text-decoration: none;
                cursor: pointer;
                transition: all 0.2s ease;
            }

            .nf-view-btn:hover {
                background: #e9e9e9;
                border-color: #aaa;
                color: #111;
                text-decoration: none;
            }

            /* Download dropdown */
            .nf-download-select {
                margin-left: 6px;
                padding: 0 10px;
                min-width: 155px;
                background: #fff;
                border: 1px solid #ccc;
                color: #333;
                cursor: pointer;
                outline: none;
                transition: all 0.2s ease;
            }

            .nf-download-select:hover {
                border-color: #888;
            }

            .nf-download-select:focus {
                border-color: #555;
                box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.08);
            }

            .nf-download-select option {
                padding: 8px;
            }

            /* Mobile */
            @media (max-width: 600px) {
                td {
                    white-space: normal !important;
                }

                .nf-action-btn,
                .nf-download-select {
                    margin-top: 3px;
                    margin-bottom: 3px;
                }

                .nf-download-select {
                    min-width: 145px;
                }
            }
        </style>

        <?php if (empty($allFiles)): ?>
            <tr>
                <td colspan="7">No files uploaded</td>
            </tr>
        <?php else: ?>
            <?php $count = 1; ?>
            <?php foreach ($allFiles as $fileName): ?>
                <?php
                $filePath = $dirPath . $fileName;
                $sizeKB = file_exists($filePath) ? round(filesize($filePath) / 1024, 2) : 0;

                $uploader = isset($uploaderMap[$fileName]) ? $uploaderMap[$fileName]['uploader'] : 'Unknown';
                $fullname = isset($uploaderMap[$fileName]) ? $uploaderMap[$fileName]['fullname'] : 'Unknown';
                $uploadedAt = isset($uploaderMap[$fileName]) ? $uploaderMap[$fileName]['uploaded_at'] : '-';
                ?>
                <tr>
                    <td data-sort="<?php echo $count; ?>"><?php echo $count++; ?></td>
                    <td data-sort="<?php echo $fileName; ?>"><?php echo $fileName; ?></td>
                    <td data-sort="<?php echo $uploader; ?>"><?php echo $uploader; ?></td>
                    <td data-sort="<?php echo $fullname; ?>"><?php echo $fullname; ?></td>
                    <td data-sort="<?php echo $sizeKB; ?>"><?php echo $sizeKB; ?></td>
                    <td data-sort="<?php echo strtotime($uploadedAt); ?>"><?php echo $uploadedAt; ?></td>
                    <td style="white-space: nowrap;">

                        <!-- View Contents -->
                        <a
                            href="<?= site_url('CsvMonitor/viewNfCsv/' . rawurlencode($fileName)) ?>"
                            class="nf-action-btn nf-view-btn"
                            title="View CSV contents">
                            👁 View Contents
                        </a>

                        <!-- Download -->
                        <select
                            class="nf-download-select"
                            onchange="downloadNfFile(this, '<?= htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8') ?>')"
                            title="Download file">
                            <option value="">⬇ Download As...</option>
                            <option value="csv">📄 CSV</option>
                            <option value="xlsx">📊 Excel (XLSX)</option>
                        </select>

                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>

</body>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const getCellValue = (tr, idx) => tr.children[idx].dataset.sort || tr.children[idx].innerText;

        const comparer = (idx, asc) => (a, b) => {
            const v1 = getCellValue(a, idx);
            const v2 = getCellValue(b, idx);

            // Check if numeric
            if (!isNaN(v1) && !isNaN(v2)) {
                return asc ? v1 - v2 : v2 - v1;
            }
            // String comparison
            return asc ? v1.toString().localeCompare(v2) : v2.toString().localeCompare(v1);
        };

        const table = document.querySelector('table');
        const ths = table.querySelectorAll('th');

        ths.forEach((th, idx) => {
            th.addEventListener('click', () => {
                // Toggle sort direction on this column
                th.asc = !th.asc; // undefined becomes true on first click
                const asc = th.asc;

                // Remove arrows from all headers
                ths.forEach(h => h.querySelector('.sort-arrow')?.classList.remove('sort-asc', 'sort-desc'));

                // Add arrow to clicked column
                th.querySelector('.sort-arrow')?.classList.add(asc ? 'sort-asc' : 'sort-desc');

                // Sort table rows
                const rows = Array.from(table.querySelectorAll('tr:nth-child(n+2)'));
                rows.sort(comparer(idx, asc));
                rows.forEach(row => table.appendChild(row));
            });
        });
    });

    function downloadNfFile(select, fileName) {

        const format = select.value;

        if (!format) {
            return;
        }

        const encodedFileName = encodeURIComponent(fileName);

        let url = '';

        if (format === 'csv') {

            url = '<?= base_url('nfitems/') ?>' + encodedFileName;

        } else if (format === 'xlsx') {

            url = '<?= site_url('CsvMonitor/convertNfCsvToXlsx/') ?>' + encodedFileName;
        }

        if (url !== '') {
            window.location.href = url;
        }

        // Reset dropdown
        select.value = '';
    }
</script>

</html>