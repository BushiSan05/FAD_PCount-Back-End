<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CsvMonitor extends CI_Controller {

    private $dir;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }   
        date_default_timezone_set('Asia/Manila');
        $this->dir = FCPATH . 'pcountdata/';
        $this->nfdir = FCPATH . 'nfitems/';
    }

    // public function menu()
    // {
    //     if (!$this->session->userdata('logged_in')) {
    //         redirect('login');
    //     }
    
    //     $data['msg'] = $this->session->flashdata('msg');
    //     $this->load->view('csv_menu', $data);
    // }


    // 📄 Main monitoring page
    public function monitoring()
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



    public function convertNfCsvToXlsx($fileName)
    {
        // =========================================================
        // 1. DECODE FILE NAME
        // =========================================================
    
        $fileName = rawurldecode($fileName);
    
        $csvPath = FCPATH . 'nfitems/' . $fileName;
    
        if (!is_file($csvPath)) {
            show_error('CSV file not found: ' . $fileName);
            return;
        }
    
        // =========================================================
        // 2. CHECK ZIP EXTENSION
        // =========================================================
    
        if (!class_exists('ZipArchive')) {
            show_error(
                'PHP ZipArchive extension is not enabled on this server.'
            );
            return;
        }
    
        // =========================================================
        // 3. OPEN CSV
        // =========================================================
    
        $handle = fopen($csvPath, 'r');
    
        if ($handle === false) {
            show_error('Unable to open CSV file.');
            return;
        }
    
        // =========================================================
        // 4. READ CSV HEADER
        // =========================================================
    
        $originalHeaders = fgetcsv($handle);
    
        if ($originalHeaders === false) {
            fclose($handle);
            show_error('CSV file is empty.');
            return;
        }
    
        // Remove UTF-8 BOM from first header
        if (isset($originalHeaders[0])) {
            $originalHeaders[0] = preg_replace(
                '/^\xEF\xBB\xBF/',
                '',
                $originalHeaders[0]
            );
        }
    
        // =========================================================
        // 5. FIND IMAGE COLUMN
        // =========================================================
    
        $imageColumnOriginalIndex = false;
    
        foreach ($originalHeaders as $index => $header) {
    
            $headerName = strtolower(trim($header));
    
            if ($headerName === 'image') {
                $imageColumnOriginalIndex = $index;
                break;
            }
        }
    
        // =========================================================
        // 6. BUILD FINAL HEADERS
        //
        // image_url IS EXCLUDED COMPLETELY
        // =========================================================
    
        $headers = [];
    
        $originalToNewIndex = [];
    
        foreach ($originalHeaders as $originalIndex => $header) {
    
            $headerName = strtolower(trim($header));
    
            // NEVER include image_url
            if ($headerName === 'image_url') {
                continue;
            }
    
            $newIndex = count($headers);
    
            $headers[] = $header;
    
            $originalToNewIndex[$originalIndex] = $newIndex;
        }
    
        // =========================================================
        // 7. DETERMINE FINAL IMAGE COLUMN INDEX
        // =========================================================
    
        $imageColumnIndex = false;
    
        if ($imageColumnOriginalIndex !== false) {
    
            if (isset($originalToNewIndex[$imageColumnOriginalIndex])) {
    
                $imageColumnIndex =
                    $originalToNewIndex[$imageColumnOriginalIndex];
            }
        }
    
        // =========================================================
        // 8. READ ALL CSV DATA
        // =========================================================
    
        $rows = [];
    
        while (($row = fgetcsv($handle)) !== false) {
    
            $hasData = false;
    
            foreach ($row as $value) {
    
                if (trim((string)$value) !== '') {
                    $hasData = true;
                    break;
                }
            }
    
            if (!$hasData) {
                continue;
            }
    
            $rows[] = $row;
        }
    
        fclose($handle);
    
        // =========================================================
        // 9. CREATE TEMP DIRECTORY
        // =========================================================
    
        $tempDir =
            sys_get_temp_dir() .
            DIRECTORY_SEPARATOR .
            'nf_xlsx_' .
            uniqid();
    
        if (!mkdir($tempDir, 0777, true)) {
    
            show_error(
                'Unable to create temporary XLSX directory.'
            );
    
            return;
        }
    
        // =========================================================
        // 10. CREATE XLSX DIRECTORY STRUCTURE
        // =========================================================
    
        mkdir($tempDir . '/_rels', 0777, true);
    
        mkdir($tempDir . '/xl', 0777, true);
    
        mkdir($tempDir . '/xl/_rels', 0777, true);
    
        mkdir($tempDir . '/xl/worksheets', 0777, true);
    
        mkdir(
            $tempDir . '/xl/worksheets/_rels',
            0777,
            true
        );
    
        mkdir($tempDir . '/xl/drawings', 0777, true);
    
        mkdir(
            $tempDir . '/xl/drawings/_rels',
            0777,
            true
        );
    
        mkdir($tempDir . '/xl/media', 0777, true);
    
        // =========================================================
        // 11. XML ESCAPE HELPER
        // =========================================================
    
        $xmlEscape = function ($value) {
    
            return htmlspecialchars(
                (string)$value,
                ENT_XML1 | ENT_COMPAT,
                'UTF-8'
            );
        };
    
        // =========================================================
        // 12. EXCEL COLUMN NAME HELPER
        // =========================================================
    
        $columnName = function ($number) {
    
            $name = '';
    
            while ($number > 0) {
    
                $mod = ($number - 1) % 26;
    
                $name =
                    chr(65 + $mod) .
                    $name;
    
                $number =
                    intval(
                        ($number - $mod) / 26
                    );
            }
    
            return $name;
        };
    
        // =========================================================
        // 13. SAFE EXCEL VALUE HELPER
        // =========================================================
    
        $safeExcelValue = function ($value) {
    
            $value = (string)$value;
    
            // Prevent Excel from interpreting
            // values beginning with these characters as formulas.
            if (
                strlen($value) > 0 &&
                in_array(
                    $value[0],
                    array('=', '+', '-', '@'),
                    true
                )
            ) {
                return "'" . $value;
            }
    
            return $value;
        };
    
        // =========================================================
        // 14. CONTENT TYPES
        // =========================================================
    
        $contentTypes =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types '
            . 'xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
    
            . '<Default Extension="rels" '
            . 'ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
    
            . '<Default Extension="xml" '
            . 'ContentType="application/xml"/>'
    
            . '<Default Extension="jpg" '
            . 'ContentType="image/jpeg"/>'
    
            . '<Default Extension="jpeg" '
            . 'ContentType="image/jpeg"/>'
    
            . '<Default Extension="png" '
            . 'ContentType="image/png"/>'
    
            . '<Default Extension="gif" '
            . 'ContentType="image/gif"/>'
    
            . '<Default Extension="bmp" '
            . 'ContentType="image/bmp"/>'
    
            . '<Override '
            . 'PartName="/xl/workbook.xml" '
            . 'ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
    
            . '<Override '
            . 'PartName="/xl/worksheets/sheet1.xml" '
            . 'ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
    
            . '<Override '
            . 'PartName="/xl/styles.xml" '
            . 'ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
    
        // Drawing content type will only be added if images exist later.
    
        // =========================================================
        // 15. ROOT RELATIONSHIPS
        // =========================================================
    
        $rootRels =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships '
            . 'xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    
            . '<Relationship '
            . 'Id="rId1" '
            . 'Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" '
            . 'Target="xl/workbook.xml"/>'
    
            . '</Relationships>';
    
        file_put_contents(
            $tempDir . '/_rels/.rels',
            $rootRels
        );
    
        // =========================================================
        // 16. WORKBOOK
        // =========================================================
    
        $workbook =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    
            . '<workbook '
            . 'xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
    
            . '<sheets>'
    
            . '<sheet '
            . 'name="NFItems" '
            . 'sheetId="1" '
            . 'r:id="rId1"/>'
    
            . '</sheets>'
    
            . '</workbook>';
    
        file_put_contents(
            $tempDir . '/xl/workbook.xml',
            $workbook
        );
    
        // =========================================================
        // 17. WORKBOOK RELATIONSHIPS
        // =========================================================
    
        $workbookRels =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    
            . '<Relationships '
            . 'xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    
            . '<Relationship '
            . 'Id="rId1" '
            . 'Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" '
            . 'Target="worksheets/sheet1.xml"/>'
    
            . '</Relationships>';
    
        file_put_contents(
            $tempDir . '/xl/_rels/workbook.xml.rels',
            $workbookRels
        );
    
        // =========================================================
        // 18. STYLES
        // =========================================================
    
        $styles =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    
            . '<styleSheet '
            . 'xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
    
            . '<fonts count="2">'
    
            . '<font>'
            . '<sz val="11"/>'
            . '<name val="Arial"/>'
            . '</font>'
    
            . '<font>'
            . '<b/>'
            . '<sz val="11"/>'
            . '<name val="Arial"/>'
            . '</font>'
    
            . '</fonts>'
    
            . '<fills count="2">'
    
            . '<fill>'
            . '<patternFill patternType="none"/>'
            . '</fill>'
    
            . '<fill>'
            . '<patternFill patternType="gray125"/>'
            . '</fill>'
    
            . '</fills>'
    
            . '<borders count="1">'
    
            . '<border>'
            . '<left/>'
            . '<right/>'
            . '<top/>'
            . '<bottom/>'
            . '<diagonal/>'
            . '</border>'
    
            . '</borders>'
    
            . '<cellStyleXfs count="1">'
    
            . '<xf '
            . 'numFmtId="0" '
            . 'fontId="0" '
            . 'fillId="0" '
            . 'borderId="0"/>'
    
            . '</cellStyleXfs>'
    
            . '<cellXfs count="2">'
    
            . '<xf '
            . 'numFmtId="0" '
            . 'fontId="0" '
            . 'fillId="0" '
            . 'borderId="0" '
            . 'xfId="0"/>'
    
            . '<xf '
            . 'numFmtId="0" '
            . 'fontId="1" '
            . 'fillId="0" '
            . 'borderId="0" '
            . 'xfId="0"/>'
    
            . '</cellXfs>'
    
            . '</styleSheet>';
    
        file_put_contents(
            $tempDir . '/xl/styles.xml',
            $styles
        );
    
        // =========================================================
        // 19. START DRAWING XML
        //
        // xmlns:r is required because we use r:embed.
        // =========================================================
    
        $drawingXml =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    
            . '<xdr:wsDr '
            . 'xmlns:xdr="http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing" '
            . 'xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';
    
        $drawingRels =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    
            . '<Relationships '
            . 'xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
    
        $imageCounter = 0;
    
        // =========================================================
        // 20. START WORKSHEET
        // =========================================================
    
        $worksheet =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    
            . '<worksheet '
            . 'xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';
    
        // =========================================================
        // 21. COLUMN WIDTHS
        // =========================================================
    
        $worksheet .= '<cols>';
    
        foreach ($headers as $index => $header) {
    
            if ($index === $imageColumnIndex) {
    
                $width = 16;
    
            } else {
    
                $width = 20;
            }
    
            $columnNumber = $index + 1;
    
            $worksheet .=
                '<col '
                . 'min="' . $columnNumber . '" '
                . 'max="' . $columnNumber . '" '
                . 'width="' . $width . '" '
                . 'customWidth="1"/>';
        }
    
        $worksheet .= '</cols>';
    
        // =========================================================
        // 22. START SHEET DATA
        // =========================================================
    
        $worksheet .= '<sheetData>';
    
        // =========================================================
        // 23. HEADER ROW
        // =========================================================
    
        $worksheet .=
            '<row '
            . 'r="1" '
            . 'ht="25" '
            . 'customHeight="1">';
    
        foreach ($headers as $index => $header) {
    
            $excelColumn =
                $columnName($index + 1);
    
            $value =
                $xmlEscape($header);
    
            $worksheet .=
                '<c '
                . 'r="' . $excelColumn . '1" '
                . 's="1" '
                . 't="inlineStr">'
    
                . '<is>'
    
                . '<t xml:space="preserve">'
                . $value
                . '</t>'
    
                . '</is>'
    
                . '</c>';
        }
    
        $worksheet .= '</row>';
    
        // =========================================================
        // 24. DATA ROWS
        // =========================================================
    
        foreach ($rows as $rowIndex => $row) {
    
            $excelRow =
                $rowIndex + 2;
    
            // =====================================================
            // DETERMINE IMAGE STATUS
            // =====================================================
    
            $image = '';
    
            $imagePath = '';
    
            $hasImage = false;
    
            $imageMime = '';
    
            if ($imageColumnOriginalIndex !== false) {
    
                $image =
                    isset(
                        $row[$imageColumnOriginalIndex]
                    )
                        ? trim(
                            $row[$imageColumnOriginalIndex]
                        )
                        : '';
    
                // Remove quotes
                $image =
                    trim(
                        $image,
                        "\"'"
                    );
    
                if ($image !== '') {
    
                    // Only use filename.
                    // Prevent paths such as ../ from being used.
                    $image =
                        basename($image);
    
                    $imagePath =
                        FCPATH .
                        'nfitems_images/' .
                        $image;
    
                    // =================================================
                    // CHECK FILE EXISTS
                    // =================================================
    
                    if (is_file($imagePath)) {
    
                        // =================================================
                        // CHECK VALID IMAGE
                        // =================================================
    
                        $imageInfo =
                            @getimagesize(
                                $imagePath
                            );
    
                        if ($imageInfo !== false) {
    
                            $imageMime =
                                isset(
                                    $imageInfo['mime']
                                )
                                    ? strtolower(
                                        $imageInfo['mime']
                                    )
                                    : '';
    
                            // Supported image types
                            if (
                                $imageMime === 'image/jpeg' ||
                                $imageMime === 'image/png' ||
                                $imageMime === 'image/gif' ||
                                $imageMime === 'image/bmp'
                            ) {
                                $hasImage = true;
                            }
                        }
                    }
                }
            }
    
            // =====================================================
            // ROW HEIGHT
            // =====================================================
    
            $rowHeight =
                $hasImage
                    ? 80
                    : 20;
    
            $worksheet .=
                '<row '
                . 'r="' . $excelRow . '" '
                . 'ht="' . $rowHeight . '" '
                . 'customHeight="1">';
    
            // =====================================================
            // WRITE EACH COLUMN
            // =====================================================
    
            foreach ($headers as $newIndex => $header) {
    
                $excelColumn =
                    $columnName(
                        $newIndex + 1
                    );
    
                // -------------------------------------------------
                // Find original CSV column index
                // -------------------------------------------------
    
                $originalIndex = null;
    
                foreach (
                    $originalToNewIndex
                    as $oldIndex => $mappedIndex
                ) {
    
                    if ($mappedIndex === $newIndex) {
    
                        $originalIndex =
                            $oldIndex;
    
                        break;
                    }
                }
    
                $value =
                    (
                        $originalIndex !== null &&
                        isset($row[$originalIndex])
                    )
                        ? $row[$originalIndex]
                        : '';
    
                // =================================================
                // IMAGE COLUMN
                // =================================================
    
                if ($newIndex === $imageColumnIndex) {
    
                    if ($hasImage) {
    
                        // -------------------------------------------------
                        // IMAGE EXISTS
                        //
                        // Leave Excel cell empty because the actual image
                        // will be embedded through drawing1.xml.
                        // -------------------------------------------------
    
                        $worksheet .=
                            '<c '
                            . 'r="' . $excelColumn . $excelRow . '"/>';
    
                    } else {
    
                        // -------------------------------------------------
                        // IMAGE DOES NOT EXIST
                        //
                        // Even if the CSV contains a filename,
                        // show "No image".
                        // -------------------------------------------------
    
                        $worksheet .=
                            '<c '
                            . 'r="' . $excelColumn . $excelRow . '" '
                            . 't="inlineStr">'
    
                            . '<is>'
    
                            . '<t xml:space="preserve">'
                            . 'No image'
                            . '</t>'
    
                            . '</is>'
    
                            . '</c>';
                    }
    
                    continue;
                }
    
                // =================================================
                // NORMAL COLUMN
                // =================================================
    
                $safeValue =
                    $safeExcelValue(
                        $value
                    );
    
                $worksheet .=
                    '<c '
                    . 'r="' . $excelColumn . $excelRow . '" '
                    . 't="inlineStr">'
    
                    . '<is>'
    
                    . '<t xml:space="preserve">'
                    . $xmlEscape($safeValue)
                    . '</t>'
    
                    . '</is>'
    
                    . '</c>';
            }
    
            $worksheet .= '</row>';
    
            // =====================================================
            // EMBED IMAGE
            // =====================================================
    
            if ($hasImage) {
    
                $imageCounter++;
    
                // =================================================
                // DETERMINE EXTENSION
                // =================================================
    
                switch ($imageMime) {
    
                    case 'image/jpeg':
                        $extension = 'jpg';
                        break;
    
                    case 'image/png':
                        $extension = 'png';
                        break;
    
                    case 'image/gif':
                        $extension = 'gif';
                        break;
    
                    case 'image/bmp':
                        $extension = 'bmp';
                        break;
    
                    default:
                        $extension = 'jpg';
                        break;
                }
    
                $mediaName =
                    'image' .
                    $imageCounter .
                    '.' .
                    $extension;
    
                $mediaPath =
                    $tempDir .
                    '/xl/media/' .
                    $mediaName;
    
                // =================================================
                // COPY IMAGE
                // =================================================
    
                $copied =
                    @copy(
                        $imagePath,
                        $mediaPath
                    );
    
                if ($copied) {
    
                    // =================================================
                    // DRAWING RELATIONSHIP
                    // =================================================
    
                    $drawingRels .=
                        '<Relationship '
                        . 'Id="rId' . $imageCounter . '" '
                        . 'Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" '
                        . 'Target="../media/' . $mediaName . '"/>';
    
                    // =================================================
                    // IMAGE POSITION
                    // =================================================
    
                    // Image column is zero-based
                    $fromColumn =
                        $imageColumnIndex;
    
                    // Excel row is zero-based
                    $fromRow =
                        $excelRow - 1;
    
                    // Image dimensions
                    $widthPx = 70;
                    $heightPx = 70;
    
                    // Convert pixels to EMU
                    $cx =
                        $widthPx * 9525;
    
                    $cy =
                        $heightPx * 9525;
    
                    // =================================================
                    // DRAWING XML
                    // =================================================
    
                    $drawingXml .=
    
                        '<xdr:oneCellAnchor>'
    
                        . '<xdr:from>'
    
                        . '<xdr:col>'
                        . $fromColumn
                        . '</xdr:col>'
    
                        . '<xdr:colOff>'
                        . '47625'
                        . '</xdr:colOff>'
    
                        . '<xdr:row>'
                        . $fromRow
                        . '</xdr:row>'
    
                        . '<xdr:rowOff>'
                        . '47625'
                        . '</xdr:rowOff>'
    
                        . '</xdr:from>'
    
                        . '<xdr:ext '
                        . 'cx="' . $cx . '" '
                        . 'cy="' . $cy . '"/>'
    
                        . '<xdr:pic>'
    
                        . '<xdr:nvPicPr>'
    
                        . '<xdr:cNvPr '
                        . 'id="' . $imageCounter . '" '
                        . 'name="Image ' . $imageCounter . '"/>'
    
                        . '<xdr:cNvPicPr/>'
    
                        . '</xdr:nvPicPr>'
    
                        . '<xdr:blipFill>'
    
                        . '<a:blip '
                        . 'r:embed="rId' . $imageCounter . '"/>'
    
                        . '<a:stretch>'
    
                        . '<a:fillRect/>'
    
                        . '</a:stretch>'
    
                        . '</xdr:blipFill>'
    
                        . '<xdr:spPr>'
    
                        . '<a:prstGeom prst="rect">'
    
                        . '<a:avLst/>'
    
                        . '</a:prstGeom>'
    
                        . '</xdr:spPr>'
    
                        . '</xdr:pic>'
    
                        . '<xdr:clientData/>'
    
                        . '</xdr:oneCellAnchor>';
                }
            }
        }
    
        // =========================================================
        // 25. END SHEET DATA
        // =========================================================
    
        $worksheet .= '</sheetData>';
    
        // =========================================================
        // 26. FREEZE HEADER ROW
        // =========================================================
    
        $worksheet .=
    
            '<sheetViews>'
    
            . '<sheetView workbookViewId="0">'
    
            . '<pane '
            . 'ySplit="1" '
            . 'topLeftCell="A2" '
            . 'activePane="bottomLeft" '
            . 'state="frozen"/>'
    
            . '</sheetView>'
    
            . '</sheetViews>';
    
        // =========================================================
        // 27. ADD DRAWING REFERENCE
        // =========================================================
    
        if ($imageCounter > 0) {
    
            $worksheet .=
                '<drawing r:id="rId1"/>';
        }
    
        // =========================================================
        // 28. END WORKSHEET
        // =========================================================
    
        $worksheet .= '</worksheet>';
    
        // =========================================================
        // 29. SAVE WORKSHEET
        // =========================================================
    
        file_put_contents(
            $tempDir .
            '/xl/worksheets/sheet1.xml',
            $worksheet
        );
    
        // =========================================================
        // 30. IF IMAGES EXIST
        // CREATE DRAWING FILES
        // =========================================================
    
        if ($imageCounter > 0) {
    
            // Finish drawing XML
            $drawingXml .= '</xdr:wsDr>';
    
            // Finish drawing relationships
            $drawingRels .= '</Relationships>';
    
            // Save drawing XML
            file_put_contents(
                $tempDir .
                '/xl/drawings/drawing1.xml',
                $drawingXml
            );
    
            // Save drawing relationships
            file_put_contents(
                $tempDir .
                '/xl/drawings/_rels/drawing1.xml.rels',
                $drawingRels
            );
    
            // Add drawing content type
            $contentTypes .=
    
                '<Override '
                . 'PartName="/xl/drawings/drawing1.xml" '
                . 'ContentType="application/vnd.openxmlformats-officedocument.drawing+xml"/>';
    
            // Worksheet relationship
            $sheetRels =
    
                '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    
                . '<Relationships '
                . 'xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    
                . '<Relationship '
                . 'Id="rId1" '
                . 'Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/drawing" '
                . 'Target="../drawings/drawing1.xml"/>'
    
                . '</Relationships>';
    
            file_put_contents(
                $tempDir .
                '/xl/worksheets/_rels/sheet1.xml.rels',
                $sheetRels
            );
        }
    
        // =========================================================
        // 31. FINISH CONTENT TYPES
        // =========================================================
    
        $contentTypes .= '</Types>';
    
        file_put_contents(
            $tempDir . '/[Content_Types].xml',
            $contentTypes
        );
    
        // =========================================================
        // 32. CREATE XLSX FILE
        // =========================================================
    
        $xlsxFileName =
            pathinfo(
                $fileName,
                PATHINFO_FILENAME
            ) .
            '.xlsx';
    
        $xlsxPath =
            $tempDir .
            DIRECTORY_SEPARATOR .
            $xlsxFileName;
    
        $zip = new ZipArchive();
    
        $zipResult =
            $zip->open(
                $xlsxPath,
                ZipArchive::CREATE |
                ZipArchive::OVERWRITE
            );
    
        if ($zipResult !== true) {
    
            show_error(
                'Unable to create XLSX file. ZIP error code: ' .
                $zipResult
            );
    
            return;
        }
    
        // =========================================================
        // 33. ADD ALL XLSX FILES TO ZIP
        // =========================================================
    
        $iterator =
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $tempDir,
                    RecursiveDirectoryIterator::SKIP_DOTS
                ),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
    
        foreach ($iterator as $file) {
    
            if (!$file->isFile()) {
                continue;
            }
    
            $fullPath =
                $file->getPathname();
    
            // Do not include the XLSX itself
            if ($fullPath === $xlsxPath) {
                continue;
            }
    
            $relativePath =
                substr(
                    $fullPath,
                    strlen($tempDir) + 1
                );
    
            $relativePath =
                str_replace(
                    '\\',
                    '/',
                    $relativePath
                );
    
            $zip->addFile(
                $fullPath,
                $relativePath
            );
        }
    
        $zip->close();
    
        // =========================================================
        // 34. VERIFY XLSX
        // =========================================================
    
        if (!is_file($xlsxPath)) {
    
            show_error(
                'XLSX file was not created.'
            );
    
            return;
        }
    
        // =========================================================
        // 35. DOWNLOAD XLSX
        // =========================================================
    
        if (ob_get_length()) {
            ob_end_clean();
        }
    
        header(
            'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
    
        header(
            'Content-Disposition: attachment; filename="' .
            $xlsxFileName .
            '"'
        );
    
        header(
            'Content-Length: ' .
            filesize($xlsxPath)
        );
    
        header(
            'Cache-Control: max-age=0'
        );
    
        readfile($xlsxPath);
    
        // =========================================================
        // 36. CLEAN TEMP DIRECTORY
        // =========================================================
    
        $iterator =
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $tempDir,
                    RecursiveDirectoryIterator::SKIP_DOTS
                ),
                RecursiveIteratorIterator::CHILD_FIRST
            );
    
        foreach ($iterator as $item) {
    
            if ($item->isDir()) {
    
                @rmdir(
                    $item->getPathname()
                );
    
            } else {
    
                @unlink(
                    $item->getPathname()
                );
            }
        }
    
        @rmdir($tempDir);
    
        exit;
    }

    private function _deleteXlsxTempDirectory($directory)
    {
        if (!is_dir($directory)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $directory,
                RecursiveDirectoryIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {

            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }

        rmdir($directory);
    }

    
}
