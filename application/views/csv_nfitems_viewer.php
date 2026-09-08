<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| SAFE DEFAULTS
|--------------------------------------------------------------------------
*/

$rows = isset($rows) ? $rows : array();

$total_count = isset($total_count)
    ? (int) $total_count
    : 0;

$current_page = isset($current_page)
    ? (int) $current_page
    : 1;

$total_pages = isset($total_pages)
    ? (int) $total_pages
    : 1;

$per_page = isset($per_page)
    ? (int) $per_page
    : 50;

$search = isset($search)
    ? $search
    : '';

$asset_type_filter = isset($asset_type_filter)
    ? $asset_type_filter
    : '';

$cat_type_filter = isset($cat_type_filter)
    ? $cat_type_filter
    : '';

$asset_types = isset($asset_types)
    ? $asset_types
    : array();

$cat_types = isset($cat_types)
    ? $cat_types
    : array();

$sort_by = isset($sort_by)
    ? $sort_by
    : '';

$sort_order = isset($sort_order)
    ? $sort_order
    : 'asc';


/*
|--------------------------------------------------------------------------
| BUILD QUERY STRING
|--------------------------------------------------------------------------
*/

$queryParams = array();

if ($search !== '') {
    $queryParams['search'] = $search;
}

if ($asset_type_filter !== '') {
    $queryParams['asset_type'] = $asset_type_filter;
}

if ($cat_type_filter !== '') {
    $queryParams['cat_type'] = $cat_type_filter;
}

if ($sort_by !== '') {
    $queryParams['sort_by'] = $sort_by;
    $queryParams['sort_order'] = $sort_order;
}


/*
|--------------------------------------------------------------------------
| PAGINATION URL
|--------------------------------------------------------------------------
*/

$paginationUrl = base_url(
    'CsvMonitor/viewNfCsv/' .
        rawurlencode($filename)
);


/*
|--------------------------------------------------------------------------
| HELPER FOR PAGINATION LINKS
|--------------------------------------------------------------------------
*/

function nfPaginationUrl(
    $baseUrl,
    $queryParams,
    $page
) {
    $params = $queryParams;

    $params['page'] = $page;

    return $baseUrl .
        '?' .
        http_build_query($params);
}


/*
|--------------------------------------------------------------------------
| SHOWING RANGE
|--------------------------------------------------------------------------
*/

if ($total_count > 0) {

    $showingStart =
        (($current_page - 1) * $per_page) + 1;

    $showingEnd =
        min(
            $current_page * $per_page,
            $total_count
        );
} else {

    $showingStart = 0;
    $showingEnd = 0;
}


/*
|--------------------------------------------------------------------------
| PAGE NUMBER WINDOW
|--------------------------------------------------------------------------
*/

$pageStart = max(
    1,
    $current_page - 2
);

$pageEnd = min(
    $total_pages,
    $current_page + 2
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <link
        rel="shortcut icon"
        href="<?= base_url('favicon.ico') ?>">

    <link
        rel="icon"
        type="image/x-icon"
        href="<?= base_url('favicon.ico') ?>">

    <title>
        <?= htmlspecialchars($filename) ?>
    </title>

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/csv_nfitems_viewer.css') ?>">

</head>

<body>

    <div class="page-container">


        <!-- =========================================================
         HEADER
    ========================================================== -->

        <div class="page-header">

            <a
                href="<?= base_url('CsvMonitor/nfitem') ?>"
                class="back-btn"
                title="Back to NFItems">

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
                🗃️ NFItems CSV Contents
            </h1>


            <div class="header-spacer"></div>

        </div>


        <!-- =========================================================
         FILE INFORMATION
    ========================================================== -->

        <div class="info-card">

            <div class="info-left">

                <div class="info-icon">
                    📄
                </div>


                <div class="info-content">

                    <div class="info-title">
                        CSV File
                    </div>

                    <div class="info-filename">

                        <?= htmlspecialchars($filename) ?>

                    </div>

                </div>

            </div>


            <div class="record-count">

                <?= number_format($total_count) ?>

                <?= $total_count == 1
                    ? 'Record'
                    : 'Records' ?>

            </div>

        </div>


        <!-- =========================================================
         SEARCH AND FILTERS
    ========================================================== -->

        <div class="filter-card">

            <form
                method="get"
                action="<?= htmlspecialchars($paginationUrl) ?>"
                class="nf-filter-form">


                <!-- SEARCH -->

                <div class="filter-group search-group">

                    <label for="search">
                        Search
                    </label>

                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="<?= htmlspecialchars($search) ?>"
                        placeholder="Search Barcode or Description..."
                        autocomplete="off">

                </div>


                <!-- ASSET TYPE -->

                <div class="filter-group">

                    <label for="asset_type">
                        Asset Type
                    </label>

                    <select
                        id="asset_type"
                        name="asset_type">

                        <option value="">
                            ALL
                        </option>

                        <?php foreach ($asset_types as $assetType): ?>

                            <option
                                value="<?= htmlspecialchars($assetType) ?>"
                                <?= strcasecmp(
                                    $assetType,
                                    $asset_type_filter
                                ) === 0
                                    ? 'selected'
                                    : '' ?>>

                                <?= htmlspecialchars($assetType) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- CAT TYPE -->

                <div class="filter-group">

                    <label for="cat_type">
                        Cat Type
                    </label>

                    <select
                        id="cat_type"
                        name="cat_type">

                        <option value="">
                            ALL
                        </option>

                        <?php foreach ($cat_types as $catType): ?>

                            <option
                                value="<?= htmlspecialchars($catType) ?>"
                                <?= strcasecmp(
                                    $catType,
                                    $cat_type_filter
                                ) === 0
                                    ? 'selected'
                                    : '' ?>>

                                <?= htmlspecialchars($catType) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- BUTTONS -->

                <div class="filter-actions">
                    <div class="filter-buttons">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-filter"></i>
                            🔍 Apply Filters
                        </button>

                        <a href="<?= base_url('CsvMonitor/viewNfCsv/' . rawurlencode($filename)) ?>" class="btn btn-secondary">
                            <i class="fa fa-times"></i>
                            ↻ Clear Filters
                        </a>
                    </div>
                </div>

            </form>


            <!-- =====================================================
             ACTIVE FILTERS
        ====================================================== -->

            <?php if (
                $search !== '' ||
                $asset_type_filter !== '' ||
                $cat_type_filter !== ''
            ): ?>

                <div class="active-filters">

                    <span class="active-label">
                        Active Filters:
                    </span>


                    <?php if ($search !== ''): ?>

                        <span class="filter-badge">

                            🔍

                            <strong>
                                Search:
                            </strong>

                            <?= htmlspecialchars($search) ?>

                        </span>

                    <?php endif; ?>


                    <?php if ($asset_type_filter !== ''): ?>

                        <span class="filter-badge">

                            🗂️

                            <strong>
                                Asset Type:
                            </strong>

                            <?= htmlspecialchars(
                                $asset_type_filter
                            ) ?>

                        </span>

                    <?php endif; ?>


                    <?php if ($cat_type_filter !== ''): ?>

                        <span class="filter-badge">

                            📁

                            <strong>
                                Cat Type:
                            </strong>

                            <?= htmlspecialchars(
                                $cat_type_filter
                            ) ?>

                        </span>

                    <?php endif; ?>

                </div>

            <?php endif; ?>

        </div>


        <!-- =========================================================
         RECORD INFORMATION
    ========================================================== -->

        <div class="info-card">

            <div class="info-left">

                <div class="info-item">

                    <strong>
                        Total Records:
                    </strong>

                    <?= number_format($total_count) ?>

                </div>


                <div class="info-item">

                    <strong>
                        Showing:
                    </strong>

                    <?= number_format($showingStart) ?>

                    -

                    <?= number_format($showingEnd) ?>

                </div>


                <div class="info-item">

                    <strong>
                        Page:
                    </strong>

                    <?= number_format($current_page) ?>

                    of

                    <?= number_format($total_pages) ?>

                </div>

            </div>


            <?php if (
                $search !== '' ||
                $asset_type_filter !== '' ||
                $cat_type_filter !== ''
            ): ?>

                <div class="filter-status">

                    ✓ Filters Applied

                </div>

            <?php endif; ?>

        </div>


        <!-- =========================================================
         TABLE
    ========================================================== -->

        <div class="table-card">

            <div class="table-container">

                <table id="nfItemsTable">

                    <thead>

                        <tr>


                            <!-- NUMBER -->

                            <th class="number-col">
                                #
                            </th>


                            <!-- BARCODE -->

                            <th
                                class="barcode-col sortable">

                                <a
                                    href="<?= htmlspecialchars(
                                                nfPaginationUrl(
                                                    $paginationUrl,
                                                    array_merge(
                                                        $queryParams,
                                                        array(
                                                            'sort_by' => 'barcode',
                                                            'sort_order' =>
                                                            $sort_by === 'barcode' &&
                                                                $sort_order === 'asc'
                                                                ? 'desc'
                                                                : 'asc'
                                                        )
                                                    ),
                                                    1
                                                )
                                            ) ?>">

                                    Barcode

                                    <?php if ($sort_by === 'barcode'): ?>

                                        <span class="sort-arrow">

                                            <?= $sort_order === 'asc'
                                                ? '▲'
                                                : '▼' ?>

                                        </span>

                                    <?php endif; ?>

                                </a>

                            </th>


                            <!-- DESCRIPTION -->

                            <th
                                class="description-col sortable">

                                <a
                                    href="<?= htmlspecialchars(
                                                nfPaginationUrl(
                                                    $paginationUrl,
                                                    array_merge(
                                                        $queryParams,
                                                        array(
                                                            'sort_by' => 'desc',
                                                            'sort_order' =>
                                                            $sort_by === 'desc' &&
                                                                $sort_order === 'asc'
                                                                ? 'desc'
                                                                : 'asc'
                                                        )
                                                    ),
                                                    1
                                                )
                                            ) ?>">

                                    Description

                                    <?php if ($sort_by === 'desc'): ?>

                                        <span class="sort-arrow">

                                            <?= $sort_order === 'asc'
                                                ? '▲'
                                                : '▼' ?>

                                        </span>

                                    <?php endif; ?>

                                </a>

                            </th>


                            <!-- REMARKS -->

                            <th
                                class="remarks-col sortable">

                                <a
                                    href="<?= htmlspecialchars(
                                                nfPaginationUrl(
                                                    $paginationUrl,
                                                    array_merge(
                                                        $queryParams,
                                                        array(
                                                            'sort_by' => 'remarks',
                                                            'sort_order' =>
                                                            $sort_by === 'remarks' &&
                                                                $sort_order === 'asc'
                                                                ? 'desc'
                                                                : 'asc'
                                                        )
                                                    ),
                                                    1
                                                )
                                            ) ?>">

                                    Remarks

                                    <?php if ($sort_by === 'remarks'): ?>

                                        <span class="sort-arrow">

                                            <?= $sort_order === 'asc'
                                                ? '▲'
                                                : '▼' ?>

                                        </span>

                                    <?php endif; ?>

                                </a>

                            </th>


                            <!-- ASSET TYPE -->

                            <th
                                class="asset-col sortable">

                                <a
                                    href="<?= htmlspecialchars(
                                                nfPaginationUrl(
                                                    $paginationUrl,
                                                    array_merge(
                                                        $queryParams,
                                                        array(
                                                            'sort_by' => 'asset_type',
                                                            'sort_order' =>
                                                            $sort_by === 'asset_type' &&
                                                                $sort_order === 'asc'
                                                                ? 'desc'
                                                                : 'asc'
                                                        )
                                                    ),
                                                    1
                                                )
                                            ) ?>">

                                    Asset Type

                                    <?php if ($sort_by === 'asset_type'): ?>

                                        <span class="sort-arrow">

                                            <?= $sort_order === 'asc'
                                                ? '▲'
                                                : '▼' ?>

                                        </span>

                                    <?php endif; ?>

                                </a>

                            </th>


                            <!-- CAT TYPE -->

                            <th
                                class="cat-col sortable">

                                <a
                                    href="<?= htmlspecialchars(
                                                nfPaginationUrl(
                                                    $paginationUrl,
                                                    array_merge(
                                                        $queryParams,
                                                        array(
                                                            'sort_by' => 'cat_type',
                                                            'sort_order' =>
                                                            $sort_by === 'cat_type' &&
                                                                $sort_order === 'asc'
                                                                ? 'desc'
                                                                : 'asc'
                                                        )
                                                    ),
                                                    1
                                                )
                                            ) ?>">

                                    Cat Type

                                    <?php if ($sort_by === 'cat_type'): ?>

                                        <span class="sort-arrow">

                                            <?= $sort_order === 'asc'
                                                ? '▲'
                                                : '▼' ?>

                                        </span>

                                    <?php endif; ?>

                                </a>

                            </th>


                            <!-- IMAGE -->

                            <th
                                class="image-col sortable">

                                <a
                                    href="<?= htmlspecialchars(
                                                nfPaginationUrl(
                                                    $paginationUrl,
                                                    array_merge(
                                                        $queryParams,
                                                        array(
                                                            'sort_by' => 'image',
                                                            'sort_order' =>
                                                            $sort_by === 'image' &&
                                                                $sort_order === 'asc'
                                                                ? 'desc'
                                                                : 'asc'
                                                        )
                                                    ),
                                                    1
                                                )
                                            ) ?>">

                                    Image

                                    <?php if ($sort_by === 'image'): ?>

                                        <span class="sort-arrow">

                                            <?= $sort_order === 'asc'
                                                ? '▲'
                                                : '▼' ?>

                                        </span>

                                    <?php endif; ?>

                                </a>

                            </th>


                            <!-- DATETIME -->

                            <th
                                class="datetime-col sortable">

                                <a
                                    href="<?= htmlspecialchars(
                                                nfPaginationUrl(
                                                    $paginationUrl,
                                                    array_merge(
                                                        $queryParams,
                                                        array(
                                                            'sort_by' => 'datetime_saved',
                                                            'sort_order' =>
                                                            $sort_by === 'datetime_saved' &&
                                                                $sort_order === 'asc'
                                                                ? 'desc'
                                                                : 'asc'
                                                        )
                                                    ),
                                                    1
                                                )
                                            ) ?>">

                                    Date Time Saved

                                    <?php if ($sort_by === 'datetime_saved'): ?>

                                        <span class="sort-arrow">

                                            <?= $sort_order === 'asc'
                                                ? '▲'
                                                : '▼' ?>

                                        </span>

                                    <?php endif; ?>

                                </a>

                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if (empty($rows)): ?>

                            <tr>

                                <td
                                    colspan="8"
                                    class="empty-row">

                                    <div class="empty-icon">
                                        📂
                                    </div>

                                    <div class="empty-text">
                                        No records found.
                                    </div>

                                </td>

                            </tr>

                        <?php else: ?>


                            <?php foreach ($rows as $index => $row): ?>

                                <?php

                                /*
                        --------------------------------------------------
                        Values
                        --------------------------------------------------
                        */

                                $barcode =
                                    isset($row['barcode'])
                                    ? $row['barcode']
                                    : '';

                                /*
                        The controller passes numeric CSV rows,
                        therefore find values by column names is not
                        possible here.

                        Use the known CSV header positions instead.
                        */

                                /*
                        Since the controller sends numeric rows,
                        the easiest robust method is to use the
                        following helper.
                        */

                                $barcode = '';
                                $description = '';
                                $remarks = '';
                                $assetType = '';
                                $catType = '';
                                $image = '';
                                $datetimeSaved = '';

                                /*
                        --------------------------------------------------
                        We will receive normalized associative rows
                        from the controller below.
                        --------------------------------------------------
                        */

                                if (isset($row['barcode'])) {
                                    $barcode = $row['barcode'];
                                }

                                if (isset($row['desc'])) {
                                    $description = $row['desc'];
                                }

                                if (isset($row['remarks'])) {
                                    $remarks = $row['remarks'];
                                }

                                if (isset($row['asset_type'])) {
                                    $assetType = $row['asset_type'];
                                }

                                if (isset($row['cat_type'])) {
                                    $catType = $row['cat_type'];
                                }

                                if (isset($row['image'])) {
                                    $image = trim($row['image']);
                                }

                                if (isset($row['datetime_saved'])) {
                                    $datetimeSaved =
                                        $row['datetime_saved'];
                                }

                                /*
                        --------------------------------------------------
                        Image
                        --------------------------------------------------
                        */

                                $image = basename($image);

                                $imagePath =
                                    FCPATH .
                                    'nfitems_images/' .
                                    $image;

                                $imageUrl =
                                    base_url(
                                        'nfitems_images/' .
                                            rawurlencode($image)
                                    );

                                $timestamp =
                                    strtotime($datetimeSaved);

                                if ($timestamp === false) {
                                    $timestamp = 0;
                                }

                                /*
                        --------------------------------------------------
                        Actual row number
                        --------------------------------------------------
                        */

                                $rowNumber =
                                    (($current_page - 1) * $per_page)
                                    + $index
                                    + 1;

                                ?>

                                <tr>


                                    <!-- NUMBER -->

                                    <td class="number-col">

                                        <?= number_format(
                                            $rowNumber
                                        ) ?>

                                    </td>


                                    <!-- BARCODE -->

                                    <td class="barcode-col">

                                        <?= htmlspecialchars(
                                            $barcode
                                        ) ?>

                                    </td>


                                    <!-- DESCRIPTION -->

                                    <td class="description-col">

                                        <?= htmlspecialchars(
                                            $description
                                        ) ?>

                                    </td>


                                    <!-- REMARKS -->

                                    <td class="remarks-col">

                                        <?= htmlspecialchars(
                                            $remarks
                                        ) ?>

                                    </td>


                                    <!-- ASSET TYPE -->

                                    <td class="asset-col">

                                        <?= htmlspecialchars(
                                            $assetType
                                        ) ?>

                                    </td>


                                    <!-- CAT TYPE -->

                                    <td class="cat-col">

                                        <?= htmlspecialchars(
                                            $catType
                                        ) ?>

                                    </td>


                                    <!-- IMAGE -->

                                    <td class="image-col">

                                        <?php if (
                                            $image !== '' &&
                                            is_file($imagePath)
                                        ): ?>

                                            <img
                                                src="<?= htmlspecialchars(
                                                            $imageUrl,
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ) ?>"
                                                alt="NFItems Image"
                                                class="thumbnail"
                                                loading="lazy"
                                                onclick="showImage(
                                            '<?= htmlspecialchars(
                                                    $imageUrl,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>'
                                        )">

                                        <?php else: ?>

                                            <span class="no-image">
                                                No Image
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- DATE TIME -->

                                    <td class="datetime-col">

                                        <?= htmlspecialchars(
                                            $datetimeSaved
                                        ) ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>


            <!-- =====================================================
             TABLE FOOTER
        ====================================================== -->

            <div class="table-footer">

                <div class="result-count">

                    <?php if ($total_count > 0): ?>

                        Showing
                        <strong>
                            <?= number_format($showingStart) ?>
                        </strong>

                        -
                        <strong>
                            <?= number_format($showingEnd) ?>
                        </strong>

                        of

                        <strong>
                            <?= number_format($total_count) ?>
                        </strong>

                        records

                    <?php else: ?>

                        Showing 0 records

                    <?php endif; ?>

                </div>


                <div class="scroll-hint">

                    ↔ Scroll horizontally to view more columns

                </div>

            </div>


            <!-- =====================================================
             PAGINATION
        ====================================================== -->

            <?php if ($total_pages > 1): ?>

                <div class="pagination-container">


                    <!-- FIRST -->

                    <?php if ($current_page > 1): ?>

                        <a
                            href="<?= htmlspecialchars(
                                        nfPaginationUrl(
                                            $paginationUrl,
                                            $queryParams,
                                            1
                                        )
                                    ) ?>"
                            class="page-btn">

                            First

                        </a>

                    <?php else: ?>

                        <span class="page-btn disabled">
                            First
                        </span>

                    <?php endif; ?>


                    <!-- PREVIOUS -->

                    <?php if ($current_page > 1): ?>

                        <a
                            href="<?= htmlspecialchars(
                                        nfPaginationUrl(
                                            $paginationUrl,
                                            $queryParams,
                                            $current_page - 1
                                        )
                                    ) ?>"
                            class="page-btn">

                            Previous

                        </a>

                    <?php else: ?>

                        <span class="page-btn disabled">
                            Previous
                        </span>

                    <?php endif; ?>


                    <!-- PAGE NUMBERS -->

                    <?php if ($pageStart > 1): ?>

                        <span class="page-ellipsis">
                            ...
                        </span>

                    <?php endif; ?>


                    <?php for (
                        $page = $pageStart;
                        $page <= $pageEnd;
                        $page++
                    ): ?>

                        <?php if ($page == $current_page): ?>

                            <span
                                class="page-btn active">

                                <?= $page ?>

                            </span>

                        <?php else: ?>

                            <a
                                href="<?= htmlspecialchars(
                                            nfPaginationUrl(
                                                $paginationUrl,
                                                $queryParams,
                                                $page
                                            )
                                        ) ?>"
                                class="page-btn">

                                <?= $page ?>

                            </a>

                        <?php endif; ?>

                    <?php endfor; ?>


                    <?php if ($pageEnd < $total_pages): ?>

                        <span class="page-ellipsis">
                            ...
                        </span>

                    <?php endif; ?>


                    <!-- NEXT -->

                    <?php if (
                        $current_page < $total_pages
                    ): ?>

                        <a
                            href="<?= htmlspecialchars(
                                        nfPaginationUrl(
                                            $paginationUrl,
                                            $queryParams,
                                            $current_page + 1
                                        )
                                    ) ?>"
                            class="page-btn">

                            Next

                        </a>

                    <?php else: ?>

                        <span class="page-btn disabled">
                            Next
                        </span>

                    <?php endif; ?>


                    <!-- LAST -->

                    <?php if (
                        $current_page < $total_pages
                    ): ?>

                        <a
                            href="<?= htmlspecialchars(
                                        nfPaginationUrl(
                                            $paginationUrl,
                                            $queryParams,
                                            $total_pages
                                        )
                                    ) ?>"
                            class="page-btn">

                            Last

                        </a>

                    <?php else: ?>

                        <span class="page-btn disabled">
                            Last
                        </span>

                    <?php endif; ?>

                </div>

            <?php endif; ?>

        </div>

    </div>


    <!-- =========================================================
     IMAGE MODAL
========================================================== -->

    <div
        id="imageModal"
        class="modal">

        <span
            class="close"
            onclick="closeImage()">

            ×

        </span>


        <img
            id="modalImage"
            class="modal-content"
            alt="NFItems image">

    </div>


    <script>
        /*
|--------------------------------------------------------------------------
| IMAGE MODAL
|--------------------------------------------------------------------------
*/

        function showImage(src) {

            const modal =
                document.getElementById('imageModal');

            const modalImage =
                document.getElementById('modalImage');

            modalImage.src = src;

            modal.style.display = 'flex';
        }


        function closeImage() {

            const modal =
                document.getElementById('imageModal');

            const modalImage =
                document.getElementById('modalImage');

            modal.style.display = 'none';

            modalImage.src = '';
        }


        /*
        |--------------------------------------------------------------------------
        | CLOSE MODAL WHEN CLICKING BACKDROP
        |--------------------------------------------------------------------------
        */

        window.addEventListener(
            'click',
            function(event) {

                const modal =
                    document.getElementById('imageModal');

                if (event.target === modal) {
                    closeImage();
                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | CLOSE MODAL WITH ESC
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'keydown',
            function(event) {

                if (event.key === 'Escape') {
                    closeImage();
                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | AUTO SUBMIT SEARCH
        |--------------------------------------------------------------------------
        |
        | Pressing Enter in the search field submits the filter.
        |
        */

        document.addEventListener(
            'DOMContentLoaded',
            function() {

                const search =
                    document.getElementById('search');

                if (!search) {
                    return;
                }

                search.addEventListener(
                    'keydown',
                    function(event) {

                        if (event.key === 'Enter') {

                            event.preventDefault();

                            this.form.submit();

                        }

                    }
                );

            }
        );
    </script>

</body>

</html>