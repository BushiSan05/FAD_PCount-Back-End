<!DOCTYPE html>
<html>
<head>
    <title>CSV Menu</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #f7f7f7;
            margin: 0;
            padding: 20px;
        }


        .menu-container {
            width: 100%;
            max-width: 420px;
            text-align: center;
        }


        h3 {
            margin: 15px 0;
            font-size: clamp(18px, 5vw, 24px);
        }


        .msg {
            color: green;
            margin-bottom: 20px;
            font-weight: bold;
            font-size: 16px;
        }


        .menu-buttons {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            gap: 0;
        }


        .import-btn,
        button.import-btn {

            width: 100%;
            max-width: 350px;

            min-height: 65px;

            margin: 8px 0;

            padding: 15px;

            font-size: clamp(16px, 4vw, 20px);

            border-radius: 12px;

            background-color: #007bff;

            color: white;

            border: none;

            cursor: pointer;

            text-decoration: none;

            display: flex;

            justify-content: center;

            align-items: center;

            text-align: center;
        }


        .import-btn:hover {
            background-color: #0056b3;
        }


        .import-btn:active {
            transform: scale(0.97);
        }


        hr {

            width: 100%;

            margin: 20px 0;

            border: 0;

            border-top: 1px solid #ccc;

        }



        /* Loading Overlay */

        #loadingOverlay {

            display: none;

            position: fixed;

            top: 0;

            left: 0;

            width: 100%;

            height: 100%;

            background: rgba(255,255,255,0.95);

            justify-content: center;

            align-items: center;

            flex-direction: column;

            z-index: 9999;

        }



        .loading-gif {

            width: 100px;

            height: 100px;

            max-width: 30vw;

        }



        .loading-text {

            margin-top: 15px;

            padding: 0 20px;

            font-size: clamp(15px, 4vw, 18px);

            color: #333;

            font-weight: bold;

            text-align: center;

        }



        /* Small phones */

        @media(max-width:360px){

            body {

                padding: 10px;

            }


            .import-btn {

                min-height: 55px;

                font-size: 15px;

            }

        }


        /* Landscape phones */

        @media(max-height:500px) and (orientation:landscape){

            body {

                align-items:flex-start;

            }


            .menu-container {

                margin-top:20px;

            }

        }


    </style>

</head>


<body>


<div class="menu-container">


    <?php if(!empty($msg)): ?>

        <p class="msg">
            <?= $msg ?>
        </p>

    <?php endif; ?>



    <div class="menu-buttons">


        <h3>CSV Import</h3>


        <button id="importMasterfile" class="import-btn">

            🗃️ Import Masterfile CSV

        </button>



        <button id="importUsers" class="import-btn">

            👥 Import Users CSV

        </button>



        <hr>



        <h3>Reports & Viewing</h3>



        <a href="<?= base_url('csvmonitor/view') ?>" class="import-btn">

            📊 FAD PCount CSV Viewing

        </a>



        <a href="<?= base_url('nfitemmonitor/nfitem') ?>" class="import-btn">

            🗃️ NFItems CSV Viewing

        </a>


    </div>



    <div id="loadingOverlay">

        <img 
            src="<?= base_url('assets/pacman.gif') ?>" 
            alt="Loading..." 
            class="loading-gif"
        >


        <div class="loading-text">

            Importing <span id="importType">data</span>, please wait...

        </div>


    </div>


</div>



<script>

document.addEventListener('DOMContentLoaded', function () {


    const IMPORT_PIN = "0000";


    const loadingOverlay = document.getElementById('loadingOverlay');

    const importTypeText = document.getElementById('importType');



    function verifyPin(callback) {


        const pin = prompt("Enter PIN to continue:");


        if (pin === null) return;


        if (pin === IMPORT_PIN) {

            callback();

        } else {

            alert("Invalid PIN.");

        }

    }



    document.getElementById('importMasterfile')
    ?.addEventListener('click', function () {


        verifyPin(() => {


            importTypeText.textContent = 'Masterfile';


            loadingOverlay.style.display = 'flex';


            window.location.href =
            '<?= base_url("masterfileimport/importCsv") ?>';


        });


    });



    document.getElementById('importUsers')
    ?.addEventListener('click', function () {


        verifyPin(() => {


            importTypeText.textContent = 'Users';


            loadingOverlay.style.display = 'flex';


            window.location.href =
            '<?= base_url("userimport/importCsv") ?>';


        });


    });


});

</script>


</body>
</html>