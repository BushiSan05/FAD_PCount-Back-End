<!DOCTYPE html>
<html lang="en">

<head>

    <link rel="shortcut icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">

    <title>FAD PCount LOCAL</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            box-sizing: border-box;
        }

        html {
            font-size: 16px;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #eef3f8;
            color: #2d3748;
            margin: 0;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ==============================
           MAIN CONTAINER
        ============================== */

        .menu-container {
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            padding: 25px 25px 50px;
        }

        /* ==============================
           HEADER
        ============================== */

        .header {
            display: grid;
            grid-template-columns: 46px 1fr 46px;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .app-title {
            text-align: center;
            font-size: 30px;
            font-weight: 700;
            color: #0056b3;
            letter-spacing: .3px;
        }

        .header-spacer {
            width: 46px;
            height: 46px;
        }

        /* ==============================
           LOGOUT BUTTON
        ============================== */

        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;

            width: 46px;
            height: 46px;

            border-radius: 50%;

            background: #ffffff;
            color: #495057;

            border: 1px solid #e1e6eb;

            box-shadow: 0 3px 12px rgba(0, 0, 0, .06);

            text-decoration: none;

            transition:
                background .2s ease,
                color .2s ease,
                transform .2s ease,
                box-shadow .2s ease;
        }

        .logout-btn:hover {
            background: #dc3545;
            color: #ffffff;
            transform: translateX(-2px);
            box-shadow: 0 7px 18px rgba(220, 53, 69, .25);
        }

        .logout-btn:active {
            transform: scale(.94);
        }

        /* ==============================
           WELCOME CARD
        ============================== */

        .welcome-container {
            background: #ffffff;
            border: 1px solid #e1e6eb;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 25px;

            text-align: center;

            box-shadow: 0 5px 18px rgba(0, 0, 0, .05);
        }

        .welcome-title {
            font-size: 14px;
            color: #7a8694;
            margin-bottom: 10px;
        }

        .profile-photo {
            width: 105px;
            height: 105px;

            border-radius: 50%;

            object-fit: cover;

            border: 4px solid #ffffff;

            box-shadow:
                0 4px 15px rgba(0, 0, 0, .15);

            margin-bottom: 12px;
        }

        .welcome-name {
            font-size: 24px;
            font-weight: 700;
            color: #2d3748;
            line-height: 1.3;
            margin-bottom: 5px;
        }

        .welcome-user {
            display: inline-block;

            padding: 5px 12px;

            background: #eef5ff;
            color: #007bff;

            border-radius: 20px;

            font-size: 13px;
            font-weight: 600;

            margin-bottom: 10px;
        }

        /* ==============================
           SECTION
        ============================== */

        .menu-section {
            margin-bottom: 25px;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 12px;

            margin-bottom: 12px;
        }

        .section-icon {
            width: 38px;
            height: 38px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #e8f2ff;
            color: #007bff;

            border-radius: 10px;

            font-size: 18px;
        }

        .section-title {
            margin: 0;

            font-size: 17px;
            font-weight: 700;

            color: #343a40;
        }

        .section-subtitle {
            margin: 2px 0 0;

            font-size: 12px;
            color: #8a94a6;
        }

        /* ==============================
           MENU GRID
        ============================== */

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        /* ==============================
           MENU CARD
        ============================== */

        .menu-card {
            display: flex;
            align-items: center;

            min-height: 82px;

            padding: 16px 18px;

            background: #ffffff;

            border: 1px solid #dfe5eb;
            border-radius: 12px;

            color: #343a40;
            text-decoration: none;

            box-shadow: 0 3px 10px rgba(0, 0, 0, .04);

            transition:
                background .2s ease,
                border-color .2s ease,
                transform .2s ease,
                box-shadow .2s ease;
        }

        .menu-card:hover {
            background: #d9e8f7;
            border-color: #b9d0e8;

            transform: translateY(-3px);

            box-shadow:
                0 8px 20px rgba(0, 86, 179, .12);
        }

        .menu-card:active {
            transform: translateY(-1px) scale(.99);
        }

        /* ==============================
           MENU ICON
        ============================== */

        .menu-icon {
            flex: 0 0 50px;

            width: 50px;
            height: 50px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #eef5ff;

            border-radius: 12px;

            font-size: 24px;

            margin-right: 15px;

            transition:
                background .2s ease,
                transform .2s ease;
        }

        .menu-card:hover .menu-icon {
            background: #ffffff;
            transform: scale(1.06);
        }

        /* ==============================
           MENU TEXT
        ============================== */

        .menu-content {
            min-width: 0;
            flex: 1;
        }

        .menu-title {
            font-size: 15px;
            font-weight: 700;
            color: #343a40;

            margin-bottom: 4px;
        }

        .menu-description {
            font-size: 12px;
            line-height: 1.4;
            color: #7a8694;
        }

        .menu-arrow {
            margin-left: 10px;

            font-size: 20px;
            color: #9aa4af;

            transition:
                color .2s ease,
                transform .2s ease;
        }

        .menu-card:hover .menu-arrow {
            color: #0056b3;
            transform: translateX(3px);
        }

        /* ==============================
           IMPORT BUTTON
        ============================== */

        button.menu-card {
            width: 100%;

            font-family: inherit;
            text-align: left;

            cursor: pointer;
        }

        /* ==============================
           DIVIDER
        ============================== */

        .section-divider {
            height: 1px;

            background: #dbe3ec;

            margin: 5px 0 25px;
        }

        /* ==============================
           MESSAGE
        ============================== */

        .msg {
            background: #e9f8ef;
            color: #198754;

            border: 1px solid #bce3ca;
            border-radius: 8px;

            padding: 12px 15px;

            margin: 0 0 20px;

            font-size: 14px;
            font-weight: 600;
        }

        /* ==============================
           LOADING OVERLAY
        ============================== */

        #loadingOverlay {
            display: none;

            position: fixed;
            inset: 0;

            background: rgba(255, 255, 255, .96);

            backdrop-filter: blur(5px);

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

        /* ==============================
           TABLET
        ============================== */

        @media (max-width: 800px) {

            .menu-container {
                padding: 20px 18px 40px;
            }

            .app-title {
                font-size: 25px;
            }

            .welcome-container {
                padding: 22px;
            }

            .menu-grid {
                grid-template-columns: 1fr;
            }

            .menu-card {
                min-height: 78px;
            }
        }

        /* ==============================
           PHONE
        ============================== */

        @media (max-width: 600px) {

            .menu-container {
                padding: 15px 12px 30px;
            }

            .header {
                grid-template-columns: 42px 1fr 42px;
                gap: 8px;

                margin-bottom: 18px;
            }

            .app-title {
                font-size: 21px;
            }

            .logout-btn,
            .header-spacer {
                width: 42px;
                height: 42px;
            }

            .welcome-container {
                border-radius: 13px;
                padding: 20px 15px;
                margin-bottom: 20px;
            }

            .profile-photo {
                width: 90px;
                height: 90px;
            }

            .welcome-name {
                font-size: 21px;
            }

            .welcome-user {
                font-size: 12px;
            }

            .section-header {
                margin-bottom: 10px;
            }

            .section-icon {
                width: 34px;
                height: 34px;
                font-size: 16px;
            }

            .section-title {
                font-size: 16px;
            }

            .section-subtitle {
                font-size: 11px;
            }

            .menu-card {
                min-height: 72px;
                padding: 13px 14px;
                border-radius: 11px;
            }

            .menu-icon {
                flex: 0 0 44px;

                width: 44px;
                height: 44px;

                font-size: 21px;

                margin-right: 12px;
            }

            .menu-title {
                font-size: 14px;
            }

            .menu-description {
                font-size: 11px;
            }

            .menu-arrow {
                font-size: 18px;
            }
        }

        /* ==============================
           SMALL PHONE
        ============================== */

        @media (max-width: 380px) {

            .menu-container {
                padding: 10px 9px 25px;
            }

            .app-title {
                font-size: 18px;
            }

            .welcome-container {
                padding: 17px 12px;
            }

            .profile-photo {
                width: 78px;
                height: 78px;
            }

            .welcome-name {
                font-size: 19px;
            }

            .menu-card {
                padding: 11px;
            }

            .menu-icon {
                flex-basis: 40px;
                width: 40px;
                height: 40px;

                font-size: 19px;
                margin-right: 10px;
            }

            .menu-description {
                display: none;
            }

            .menu-arrow {
                display: none;
            }
        }

        /* ==============================
           LANDSCAPE PHONE
        ============================== */

        @media (max-height: 500px) and (orientation: landscape) {

            .menu-container {
                max-width: 1000px;
                padding-top: 15px;
            }

            .welcome-container {
                padding: 15px;
            }

            .profile-photo {
                width: 70px;
                height: 70px;
            }

            .menu-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .menu-card {
                min-height: 65px;
            }
        }

        /* ==============================
           REDUCED MOTION
        ============================== */

        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                transition: none !important;
            }
        }
    </style>

</head>

<body>

    <div class="menu-container">

        <!-- HEADER -->
        <div class="header">

            <a href="<?= base_url('logout') ?>"
                class="logout-btn"
                title="Logout"
                onclick="return confirm('Are you sure you want to logout?')">

                <svg width="20" height="20" viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round">

                    <path d="M19 12H6"></path>
                    <path d="M12 5L5 12L12 19"></path>

                </svg>

            </a>

            <div class="app-title">
                FAD PCount Dashboard
            </div>

            <div class="header-spacer"></div>

        </div>


        <!-- MESSAGE -->
        <?php if (!empty($msg)): ?>

            <p class="msg">
                <?= htmlspecialchars($msg) ?>
            </p>

        <?php endif; ?>


        <!-- WELCOME -->
        <div class="welcome-container">

            <div class="welcome-title">
                Welcome back
            </div>

            <?php
            $photo = $this->session->userdata('photo');

            if (empty($photo)) {
                $photo = base_url('assets/user_photos/default.png');
            }
            ?>

            <img
                src="<?= htmlspecialchars($photo); ?>"
                class="profile-photo"
                alt="Profile Photo"
                onerror="this.src='<?= base_url('assets/user_photos/default.png'); ?>';">

            <div class="welcome-name">
                <?= htmlspecialchars($this->session->userdata('fullname')); ?>
            </div>

            <div class="welcome-user">
                @<?= htmlspecialchars($this->session->userdata('username')); ?>
            </div>

        </div>


        <!-- ==========================
             ADMINISTRATION
        =========================== -->

        <?php if ($this->session->userdata('is_admin')): ?>

            <div class="menu-section">

                <div class="section-header">

                    <div class="section-icon">
                        ⚙️
                    </div>

                    <div>
                        <h3 class="section-title">
                            Administration
                        </h3>

                        <p class="section-subtitle">
                            Manage system data and users
                        </p>
                    </div>

                </div>


                <div class="menu-grid">

                    <!-- MASTERFILE IMPORT -->

                    <button
                        id="importMasterfile"
                        class="menu-card">

                        <div class="menu-icon">
                            🗃️
                        </div>

                        <div class="menu-content">

                            <div class="menu-title">
                                Import Masterfile CSV
                            </div>

                            <div class="menu-description">
                                Import and update masterfile records
                            </div>

                        </div>

                        <div class="menu-arrow">
                            ›
                        </div>

                    </button>


                    <!-- USER IMPORT -->

                    <button
                        id="importUsers"
                        class="menu-card">

                        <div class="menu-icon">
                            👥
                        </div>

                        <div class="menu-content">

                            <div class="menu-title">
                                Import Users CSV
                            </div>

                            <div class="menu-description">
                                Import and update user accounts
                            </div>

                        </div>

                        <div class="menu-arrow">
                            ›
                        </div>

                    </button>

                </div>

            </div>

            <div class="section-divider"></div>

        <?php endif; ?>

        <!-- ==========================
     REPORTS
=========================== -->

        <div class="menu-section">

            <div class="section-header">

                <div class="section-icon">
                    📊
                </div>

                <div>

                    <h3 class="section-title">
                        Reports
                    </h3>

                    <p class="section-subtitle">
                        View and monitor system data
                    </p>

                </div>

            </div>


            <div class="menu-grid">

                <!-- ACTUAL COUNT -->

                <a
                    href="<?= base_url('csvmonitor/view') ?>"
                    class="menu-card">

                    <div class="menu-icon">
                        📝
                    </div>

                    <div class="menu-content">

                        <div class="menu-title">
                            Actual Count CSV
                        </div>

                        <div class="menu-description">
                            View actual inventory count CSV files
                        </div>

                    </div>

                    <div class="menu-arrow">
                        ›
                    </div>

                </a>


                <!-- NF ITEMS -->

                <a
                    href="<?= base_url('nfitemmonitor/nfitem') ?>"
                    class="menu-card">

                    <div class="menu-icon">
                        🗂️
                    </div>

                    <div class="menu-content">

                        <div class="menu-title">
                            NFItems CSV
                        </div>

                        <div class="menu-description">
                            View NFItems CSV records
                        </div>

                    </div>

                    <div class="menu-arrow">
                        ›
                    </div>

                </a>

            </div>

        </div>


        <div class="section-divider"></div>


        <!-- ==========================
     MASTERFILE
=========================== -->

        <div class="menu-section">

            <div class="section-header">

                <div class="section-icon">
                    🗃️
                </div>

                <div>

                    <h3 class="section-title">
                        Fixed Assets
                    </h3>

                    <p class="section-subtitle">
                        Browse and manage fixed assets data
                    </p>

                </div>

            </div>


            <div class="menu-grid">

                <!-- MASTERFILE LIST -->

                <a
                    href="<?= base_url('masterfilemonitor/list') ?>"
                    class="menu-card">

                    <div class="menu-icon">
                        📋
                    </div>

                    <div class="menu-content">

                        <div class="menu-title">
                            Fixed Asset List
                        </div>

                        <div class="menu-description">
                            Browse and filter fixed asset records
                        </div>

                    </div>

                    <div class="menu-arrow">
                        ›
                    </div>

                </a>

            </div>

        </div>



        <!-- ==========================
             LOADING OVERLAY
        =========================== -->

        <div id="loadingOverlay">

            <img
                src="<?= base_url('assets/pacman.gif') ?>"
                alt="Loading..."
                class="loading-gif">

            <div class="loading-text">

                Importing
                <span id="importType">
                    data
                </span>,

                please wait...

            </div>

        </div>

    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const IMPORT_PIN = "0000";

            const loadingOverlay =
                document.getElementById('loadingOverlay');

            const importTypeText =
                document.getElementById('importType');


            function verifyPin(callback) {

                const pin = prompt(
                    "Enter PIN to continue:"
                );

                if (pin === null) {
                    return;
                }

                if (pin === IMPORT_PIN) {

                    callback();

                } else {

                    alert("Invalid PIN.");

                }

            }


            /* ==========================
               MASTERFILE IMPORT
            =========================== */

            document
                .getElementById('importMasterfile')
                ?.addEventListener('click', function() {

                    verifyPin(() => {

                        importTypeText.textContent =
                            'Masterfile';

                        loadingOverlay.style.display =
                            'flex';

                        window.location.href =
                            '<?= base_url("MasterfileImport/importCsv") ?>';

                    });

                });


            /* ==========================
               USER IMPORT
            =========================== */

            document
                .getElementById('importUsers')
                ?.addEventListener('click', function() {

                    verifyPin(() => {

                        importTypeText.textContent =
                            'Users';

                        loadingOverlay.style.display =
                            'flex';

                        window.location.href =
                            '<?= base_url("UserImport/importCsv") ?>';

                    });

                });

        });
    </script>

</body>

</html>