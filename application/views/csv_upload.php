<!DOCTYPE html>
<html>
<head>
    <title>Upload CSV Masterfile</title>
    <style>
        body { font-family: Arial; padding: 40px; }
        .box {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
            text-align: center;
        }
        progress {
            width: 100%;
            height: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        th { background: #f0f0f0; }
    </style>
</head>
<body>

<div class="box">
    <h2>📤 Upload CSV Masterfile</h2>

    <form id="uploadForm">
        <input type="file" id="csvFile" name="csv_file" accept=".csv" required>

        <input type="text" name="new_name"
               placeholder="New filename (without .csv)"
               required><br><br>

        <button type="submit">Upload</button>
    </form>

    <br>
    <progress id="progressBar" value="0" max="100"></progress>
    <div id="percent">0%</div>
    <div id="status"></div>

    <!-- ✅ EXISTING FILES -->
    <h3>📁 Existing CSV Files</h3>

    <table>
        <tr>
            <th>#</th>
            <th>Filename</th>
            <th>Size (KB)</th>
            <th>Uploaded</th>
            <th>Action</th>
        </tr>

        <?php if (empty($files)): ?>
            <tr><td colspan="5">No CSV files found</td></tr>
        <?php endif; ?>

        <?php foreach ($files as $i => $file): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($file['name']) ?></td>
            <td><?= $file['size'] ?></td>
            <td><?= $file['time'] ?></td>
            <td>
                <a href="<?= base_url('csv/' . $file['name']) ?>" download>
                    Download
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<script>
const progressBar = document.getElementById('progressBar');
const percentText = document.getElementById('percent');
const statusDiv = document.getElementById('status');

document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();

    
    const fileInput = document.getElementById('csvFile');
    if (!fileInput.files.length) return;

    const file = fileInput.files[0];
    const ext = file.name.split('.').pop().toLowerCase();

    // If not CSV, reload page
    if (ext !== 'csv') {
        statusDiv.innerText = '❌ Only CSV files are allowed';
        progressBar.value = 0;
        percentText.innerText = '0%';
        return; // stop the upload
    }

    const formData = new FormData(this);
    const xhr = new XMLHttpRequest();

    xhr.open('POST', '<?= base_url('CsvMonitor/doUpload') ?>', true);

    xhr.upload.onprogress = function(e) {
        if (e.lengthComputable) {
            const percent = Math.round((e.loaded / e.total) * 100);
            progressBar.value = percent;
            percentText.innerText = percent + '%';
        }
    };

    xhr.onload = function() {
        if (xhr.status === 200 && xhr.responseText.trim() === 'OK') {
            location.reload(); // success
        } else if (xhr.status === 409) {
            // File exists → ask user
            if (confirm(xhr.responseText + "\nDo you want to overwrite it?")) {
                formData.set('overwrite', 'true');
                // resend request
                const xhr2 = new XMLHttpRequest();
                xhr2.open('POST', '<?= base_url('CsvMonitor/doUpload') ?>', true);

                xhr2.upload.onprogress = xhr.upload.onprogress;

                xhr2.onload = function() {
                    if (xhr2.status === 200 && xhr2.responseText.trim() === 'OK') {
                        location.reload();
                    } else {
                        statusDiv.innerText = '❌ Upload failed: ' + xhr2.responseText;
                        progressBar.value = 0;
                        percentText.innerText = '0%';
                    }
                };
                xhr2.onerror = function() {
                    statusDiv.innerText = '❌ Upload error';
                    progressBar.value = 0;
                    percentText.innerText = '0%';
                };

                xhr2.send(formData);
            } else {
                // User canceled → reset progress
                statusDiv.innerText = 'Upload canceled';
                progressBar.value = 0;
                percentText.innerText = '0%';
            }
        } else {
            statusDiv.innerText = '❌ Upload failed: ' + xhr.responseText;
            progressBar.value = 0;
            percentText.innerText = '0%';
        }
    };

    xhr.onerror = function() {
        statusDiv.innerText = '❌ Upload error';
        progressBar.value = 0;
        percentText.innerText = '0%';
    };

    xhr.send(formData);
});
</script>

</body>
</html>
