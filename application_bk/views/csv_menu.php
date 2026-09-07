<!DOCTYPE html>
<html lang="en">

<head>

    <link rel="shortcut icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">

    <title>FAD PCount LOCAL</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <link rel="stylesheet"
        href="<?= base_url('assets/css/csv_menu.css') ?>">

</head>


<body>


    <div class="dashboard-container">


        <!-- ==============================
         HEADER
    ============================== -->

        <div class="header">

            <a href="<?= base_url('logout') ?>"
                class="logout-btn"
                title="Logout"
                onclick="return confirm('Are you sure you want to logout?')">

                <svg width="20"
                    height="20"
                    viewBox="0 0 24 24"
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


            <!-- Spacer keeps title centered -->

            <div class="header-spacer"></div>

        </div>



        <!-- ==============================
         MESSAGE
    ============================== -->

        <?php if (!empty($msg)): ?>

            <div class="msg">
                <?= $msg ?>
            </div>

        <?php endif; ?>



        <!-- ==============================
         WELCOME CARD
    ============================== -->

        <div class="welcome-card">


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


            <div class="welcome-content">

                <div class="welcome-title">
                    Welcome,
                </div>


                <div class="welcome-name">
                    <?= htmlspecialchars($this->session->userdata('fullname')); ?>
                </div>


                <div class="welcome-user">
                    @<?= htmlspecialchars($this->session->userdata('username')); ?>
                </div>


                <div class="welcome-subtitle">
                    FAD PCount Local Management System
                </div>

            </div>

        </div>



        <!-- ==============================
         ADMINISTRATION
    ============================== -->

        <?php if ($this->session->userdata('is_admin')): ?>

            <div class="section">


                <div class="section-header">

                    <div class="section-title">
                        Administration
                    </div>

                    <div class="section-line"></div>

                </div>



                <div class="dashboard-grid">


                    <!-- IMPORT MASTERFILE -->

                    <button
                        id="importMasterfile"
                        class="dashboard-card admin-card">

                        <div class="card-icon">
                            🗃️
                        </div>


                        <div class="card-content">

                            <div class="card-title">
                                Import Masterfile
                            </div>

                            <div class="card-description">
                                Import or update the masterfile data using a CSV file.
                            </div>

                        </div>


                        <div class="card-arrow">
                            ›
                        </div>

                    </button>



                    <!-- IMPORT USERS -->

                    <button
                        id="importUsers"
                        class="dashboard-card admin-card">

                        <div class="card-icon">
                            👥
                        </div>


                        <div class="card-content">

                            <div class="card-title">
                                Import Users
                            </div>

                            <div class="card-description">
                                Import and update user account information from CSV.
                            </div>

                        </div>


                        <div class="card-arrow">
                            ›
                        </div>

                    </button>

                    <button
                        id="editUsers"
                        class="dashboard-card admin-card">

                        <div class="card-icon">
                            🙍‍♂️
                        </div>


                        <div class="card-content">

                            <div class="card-title">
                                User Settings
                            </div>

                            <div class="card-description">
                                Edit user settings.
                            </div>

                        </div>


                        <div class="card-arrow">
                            ›
                        </div>

                    </button>


                </div>

            </div>

        <?php endif; ?>



        <!-- ==============================
         REPORTS
    ============================== -->

        <div class="section">


            <div class="section-header">

                <div class="section-title">
                    Reports
                </div>

                <div class="section-line"></div>

            </div>



            <div class="dashboard-grid">

                <!-- ASSET MASTERFILE -->

                <a
                    href="<?= base_url('nfitemmonitor/nfitem') ?>"
                    class="dashboard-card report-card">


                    <div class="card-icon">
                        📋
                    </div>


                    <div class="card-content">

                        <div class="card-title">
                            Asset Masterfile
                        </div>

                        <div class="card-description">
                            View fixed assets masterfile.
                        </div>

                    </div>


                    <div class="card-arrow">
                        ›
                    </div>


                </a>


                <!-- ACTUAL COUNT -->

                <a
                    href="<?= base_url('csvmonitor/view') ?>"
                    class="dashboard-card report-card">


                    <div class="card-icon">
                        📊
                    </div>


                    <div class="card-content">

                        <div class="card-title">
                            Actual Count CSV
                        </div>

                        <div class="card-description">
                            View and monitor actual count CSV files.
                        </div>

                    </div>


                    <div class="card-arrow">
                        ›
                    </div>


                </a>



                <!-- NFITEMS -->

                <a
                    href="<?= base_url('nfitemmonitor/nfitem') ?>"
                    class="dashboard-card report-card">


                    <div class="card-icon">
                        🗂️
                    </div>


                    <div class="card-content">

                        <div class="card-title">
                            NFItems CSV
                        </div>

                        <div class="card-description">
                            View and monitor NFItems CSV files.
                        </div>

                    </div>


                    <div class="card-arrow">
                        ›
                    </div>


                </a>


            </div>

        </div>



        <!-- ==============================
         FOOTER
    ============================== -->

        <div class="footer">

            FAD PCount Local System

        </div>



    </div>



    <!-- ==============================
     LOADING OVERLAY
============================== -->

    <div id="loadingOverlay">


        <img
            src="<?= base_url('assets/pacman.gif') ?>"
            alt="Loading..."
            class="loading-gif">


        <div class="loading-text">

            Importing
            <span id="importType">data</span>,
            please wait...

        </div>


    </div>



    <script>
        document.addEventListener('DOMContentLoaded', function() {


            /* ==============================
               IMPORT PIN
            ============================== */

            const IMPORT_PIN = "0000";


            const loadingOverlay =
                document.getElementById('loadingOverlay');


            const importTypeText =
                document.getElementById('importType');



            /* ==============================
               VERIFY PIN
            ============================== */

            function verifyPin(callback) {


                const pin =
                    prompt("Enter PIN to continue:");


                if (pin === null) {
                    return;
                }


                if (pin === IMPORT_PIN) {

                    callback();

                } else {

                    alert("Invalid PIN.");

                }

            }



            /* ==============================
               MASTERFILE IMPORT
            ============================== */

            document
                .getElementById('importMasterfile')
                ?.addEventListener('click', function() {


                    verifyPin(() => {


                        importTypeText.textContent =
                            'Masterfile';


                        loadingOverlay.style.display =
                            'flex';


                        window.location.href =
                            '<?= base_url("MasterfileImport/checkCsv") ?>';


                    });

                });



            /* ==============================
               USERS IMPORT
            ============================== */

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