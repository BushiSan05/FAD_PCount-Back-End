<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="utf-8">

<title><?= htmlspecialchars($filename) ?></title>

<style>

body{
    font-family: Arial, sans-serif;
    background:#f5f5f5;
    margin:0;
    padding:20px;
}

.container{
    width:100%;
    max-width:none;
    margin:0 auto;
}

h2{
    margin:0 0 20px;
    color:#333;
}

.back-btn{
    display:inline-block;
    margin-bottom:20px;
    padding:10px 18px;
    background:#6c757d;
    color:#fff;
    text-decoration:none;
    border-radius:5px;
    font-weight:bold;
}

.back-btn:hover{
    background:#545b62;
}

table{
    width:100%;
    border-collapse:collapse;
    background:#fff;
    box-shadow:0 2px 8px rgba(0,0,0,.08);
    table-layout:auto;
}

th{
    background:#f2f2f2;
    color:#333;
    text-align:left;
    padding:14px;
    border:1px solid #ddd;
    white-space:nowrap;
}

th {
    cursor:pointer;
    user-select:none;
    position:relative;
}

.sort-arrow {
    display:inline-block;
    margin-left:5px;
    width:10px;
}


.sort-asc::after {
    content:"▲";
    font-size:10px;
}


.sort-desc::after {
    content:"▼";
    font-size:10px;
}

td{
    border:1px solid #ddd;
    padding:8px;
    vertical-align:middle;
}

tr:nth-child(even){
    background:#fafafa;
}

tr:hover{
    background:#67b2f5;
}

.number-col{
    width:50px;
    text-align:center;
}

.barcode-col{
    width:15%;
    line-height:1.5;
    word-break:break-word;
}

.description-col{
    width:25%;
    line-height:1.5;
    word-break:break-word;
}

.remarks-col{
    width:30%;
    line-height:1.5;
    word-break:break-word;
}

.asset-col{
    width:12%;
}

.cat-col{
    width:12%;
}

.image-col{
    width:15%;
    text-align:center;
}

.datetime-col{
    width:15%;
    white-space:nowrap;
}


/* IMAGE MODAL */

.modal {
    display:none;
    position:fixed;
    z-index:9999;
    left:0;
    top:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.8);

    align-items:center;
    justify-content:center;
}

.modal-content {
    max-width:90%;
    max-height:85vh;
    object-fit:contain;
    border-radius:8px;
}

.close {
    position:absolute;
    right:30px;
    top:20px;
    color:white;
    font-size:40px;
    font-weight:bold;
    cursor:pointer;
}

.close:hover{
    color:#ccc;
}

</style>

</head>


<body>

<div class="container">

<h2><?= htmlspecialchars($filename) ?></h2>

<a href="<?= base_url('CsvMonitor/nfitem') ?>" class="back-btn">
    ← Back
</a>


<table>

<thead>

<tr>
    <th class="number-col">
        #<span class="sort-arrow"></span>
    </th>
    <th class="barcode-col">
        Barcode<span class="sort-arrow"></span>
    </th>
    <th class="description-col">
        Description<span class="sort-arrow"></span>
    </th>
    <th class="remarks-col">
        Remarks<span class="sort-arrow"></span>
    </th>
    <th class="asset-col">
        Asset Type<span class="sort-arrow"></span>
    </th>
    <th class="cat-col">
        Cat Type<span class="sort-arrow"></span>
    </th>
    <th class="image-col">
        Image<span class="sort-arrow"></span>
    </th>
    <th class="datetime-col">
        Date Time Saved<span class="sort-arrow"></span>
    </th>
</tr>

</thead>


<tbody>


<?php $i = 1; ?>


<?php foreach ($rows as $row): ?>


<?php

$barcode = isset($row['barcode']) ? $row['barcode'] : '';

$description = isset($row['desc']) ? $row['desc'] : '';

$remarks = isset($row['remarks']) ? $row['remarks'] : '';

$assetType = isset($row['asset_type']) ? $row['asset_type'] : '';

$catType = isset($row['cat_type']) ? $row['cat_type'] : '';

$image = trim(isset($row['image']) ? $row['image'] : '');

$datetimeSaved = isset($row['datetime_saved']) ? $row['datetime_saved'] : '';

$imagePath = FCPATH . 'nfitems_images/' . $image;

?>


<tr>


<td class="number-col" data-sort="<?= $i ?>">
    <?= $i++ ?>
</td>

<td class="barcode-col" data-sort="<?= htmlspecialchars($barcode) ?>">
    <?= htmlspecialchars($barcode) ?>
</td>


<td class="description-col" data-sort="<?= htmlspecialchars($description) ?>">
    <?= htmlspecialchars($description) ?>
</td>


<td class="remarks-col" data-sort="<?= htmlspecialchars($remarks) ?>">
    <?= htmlspecialchars($remarks) ?>
</td>


<td class="asset-col" data-sort="<?= htmlspecialchars($assetType) ?>">
    <?= htmlspecialchars($assetType) ?>
</td>


<td class="cat-col" data-sort="<?= htmlspecialchars($catType) ?>">
    <?= htmlspecialchars($catType) ?>
</td>


<td class="image-col" data-sort="<?= htmlspecialchars($image) ?>">


<?php if ($image != '' && file_exists($imagePath)): ?>

<a href="#"
onclick="showImage('<?= base_url('nfitems_images/' . rawurlencode($image)) ?>'); return false;">
    <?= htmlspecialchars($image) ?>
</a>


<?php else: ?>

<span>No Image</span>


<?php endif; ?>


</td>


<td class="datetime-col" data-sort="<?= strtotime($datetimeSaved) ?>">
    <?= htmlspecialchars($datetimeSaved) ?>
</td>


</tr>


<?php endforeach; ?>


<?php if(empty($rows)): ?>


<tr>
    <td colspan="7" style="text-align:center;">
        No records found.
    </td>
</tr>


<?php endif; ?>


</tbody>

</table>


</div>



<!-- IMAGE MODAL -->

<div id="imageModal" class="modal">

    <span class="close" onclick="closeImage()">×</span>

    <img id="modalImage" class="modal-content">

</div>



<script>

function showImage(src)
{
    let modal = document.getElementById('imageModal');

    document.getElementById('modalImage').src = src;

    modal.style.display = 'flex';
}


function closeImage()
{
    document.getElementById('imageModal').style.display = 'none';

    document.getElementById('modalImage').src = '';
}


window.onclick = function(event)
{
    let modal = document.getElementById('imageModal');

    if(event.target == modal)
    {
        closeImage();
    }
}

document.addEventListener('DOMContentLoaded', () => {


const table = document.querySelector('table');

const ths = table.querySelectorAll('th');


const getCellValue = (tr, idx) => {

    return tr.children[idx].dataset.sort 
        || tr.children[idx].innerText;

};


const comparer = (idx, asc) => (a, b) => {


    const v1 = getCellValue(a, idx);

    const v2 = getCellValue(b, idx);



    if (!isNaN(v1) && !isNaN(v2)) {

        return asc 
            ? Number(v1) - Number(v2)
            : Number(v2) - Number(v1);

    }


    return asc
        ? v1.toString().localeCompare(v2.toString())
        : v2.toString().localeCompare(v1.toString());

};



ths.forEach((th, idx) => {


    th.addEventListener('click', () => {


        th.asc = !th.asc;


        const asc = th.asc;



        ths.forEach(h => {

            h.querySelector('.sort-arrow')
             ?.classList.remove(
                'sort-asc',
                'sort-desc'
             );

        });



        th.querySelector('.sort-arrow')
          ?.classList.add(
            asc ? 'sort-asc' : 'sort-desc'
          );



        const tbody = table.querySelector('tbody');


        const rows = Array.from(
            tbody.querySelectorAll('tr')
        );



        rows.sort(comparer(idx, asc));



        rows.forEach(row => {

            tbody.appendChild(row);

        });



    });


});


});

</script>


</body>
</html>