<!DOCTYPE html>
<html>

<head>
    <div>

        <link rel="shortcut icon" href="<?= base_url('favicon.ico') ?>">
        <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">

        <title>FAD PCount LOCAL</title>

        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <style>
            * {
                box-sizing: border-box;
            }

            body {
                font-family: Arial, sans-serif;
                background: #eef3f8;
                margin: 0;
                min-height: 100vh;
            }

            .menu-container {
                width: 100%;
                min-height: 100vh;
                padding: 30px 50px 50px;
            }

            .logo-container {
                text-align: center;
                margin-bottom: 25px;
            }

            .logo {
                width: min(320px, 90%);
                height: auto;
                display: block;
                margin: 0 auto 15px;
            }

            .subtitle {
                margin: 0;
                color: #6c757d;
                font-size: 15px;
                font-weight: 500;
            }

            h3 {
                text-align: center;
                margin: 5px 0 2px;
                font-size: 20px;
                letter-spacing: .8px;
                color: #6c757d;
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
            }

            .import-btn,
            button.import-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                width: auto;
                min-width: 260px;
                /* optional */
                max-width: 100%;
                min-height: 64px;
                margin: 5px auto;
                padding: 15px 25px;
                border: none;
                border-radius: 14px;
                background: linear-gradient(135deg, #007bff, #0056b3);
                color: #fff;
                font-size: 18px;
                font-weight: 600;
                text-decoration: none;
                transition: .25s;
                cursor: pointer;
            }

            .import-btn:hover {
                transform: translateY(-3px);
                box-shadow: 0 8px 18px rgba(0, 123, 255, .25);
            }

            .import-btn:active {
                transform: scale(.98);
            }

            hr {
                margin: 5px 0;
                border: none;
                border-top: 1px solid #e5e7eb;
            }

            /* Loading Overlay */
            #loadingOverlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(255, 255, 255, .96);
                backdrop-filter: blur(4px);
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

            .menu-container {
                width: 100%;
                max-width: 900px;
                margin: auto;
                padding: 35px 20px 50px;
            }

            .logout-btn {
                display: flex;
                align-items: center;
                justify-content: center;

                width: 46px;
                height: 46px;

                border-radius: 50%;

                background: #fff;
                color: #495057;

                box-shadow: 0 3px 12px rgba(0, 0, 0, .08);

                transition: .25s;
            }

            .logout-btn:hover {
                background: #dc3545;
                color: #fff;
                transform: translateX(-2px);
                box-shadow: 0 6px 15px rgba(220, 53, 69, .25);
            }

            .logout-btn:active {
                transform: scale(.95);
            }

            .welcome-container {
                margin: 10px 0 30px;
                text-align: center;
            }

            .profile-photo {
                width: 110px;
                height: 110px;
                border-radius: 50%;
                object-fit: cover;
                border: 4px solid #fff;
                box-shadow: 0 4px 15px rgba(0, 0, 0, .15);
                margin-bottom: 18px;
            }

            .welcome-title {
                font-size: 17px;
                color: #6c757d;
                margin-bottom: 4px;
            }

            .welcome-name {
                font-size: 25px;
                font-weight: 700;
                color: #2d3748;
                line-height: 1.3;
                margin-bottom: 4px;
            }

            .welcome-user {
                font-size: 14px;
                color: #007bff;
                font-weight: 600;
                margin-bottom: 12px;
            }

            .welcome-subtitle {
                font-size: 15px;
                color: #8a94a6;
            }

            .section-divider {
                margin: 5px 0;
                border-top: 3px solid #dbe3ec;
            }

            .header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 35px;
            }

            .app-title {
                flex: 1;
                text-align: center;
                font-size: 34px;
                font-weight: 700;
                color: #0056b3;
            }

            .header-spacer {
                width: 46px;
                height: 46px;
            }

            /* Small phones */
            @media(max-width:360px) {
                body {
                    padding: 10px;
                }

                .import-btn {
                    max-width: none;
                    width: 100%;
                    justify-content: flex-start;
                    padding-left: 25px;
                }
            }


            /* Landscape phones */
            @media(max-height:500px) and (orientation:landscape) {
                body {
                    align-items: flex-start;
                }

                .menu-container {
                    margin-top: 20px;
                }
            }
        </style>

</head>

<body>

    <div class="menu-container">

        <div class="header">

            <a href="<?= base_url('logout') ?>"
                class="logout-btn"
                title="Logout"
                onclick="return confirm('Are you sure you want to logout?')">

                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H6"></path>
                    <path d="M12 5L5 12L12 19"></path>
                </svg>

            </a>

            <div class="app-title">
                FAD PCount Dashboard
            </div>

            <!-- Spacer so the title stays centered -->
            <div class="header-spacer"></div>

        </div>

        <?php if (!empty($msg)): ?>

            <p class="msg">
                <?= $msg ?>
            </p>

        <?php endif; ?>

        <div class="welcome-container">

            <div class="welcome-title">
                Welcome,
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

            <div class="menu-buttons">

                <?php if ($this->session->userdata('is_admin')): ?>

                    <h3>Administration</h3>

                    <button id="importMasterfile" class="import-btn">
                        🗃️ Import Masterfile CSV
                    </button>

                    <button id="importUsers" class="import-btn">
                        👥 Import Users CSV
                    </button>

                    <div class="section-divider"></div>

                <?php endif; ?>

                <h3>Reports</h3>

                <a href="<?= base_url('csvmonitor/view') ?>" class="import-btn">
                    📊 Actual Count CSV Viewing
                </a>

                <a href="<?= base_url('nfitemmonitor/nfitem') ?>" class="import-btn">
                    🗂️ NFItems CSV Viewing
                </a>

            </div>

            <div id="loadingOverlay">

                <img
                    src="<?= base_url('assets/pacman.gif') ?>"
                    alt="Loading..."
                    class="loading-gif">

                <div class="loading-text">
                    Importing <span id="importType">data</span>, please wait...
                </div>

            </div>

        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
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
                    ?.addEventListener('click', function() {
                        verifyPin(() => {
                            importTypeText.textContent = 'Masterfile';
                            loadingOverlay.style.display = 'flex';
                            window.location.href =
                                '<?= base_url("MasterfileImport/importCsv") ?>';
                        });
                    });

                document.getElementById('importUsers')
                    ?.addEventListener('click', function() {
                        verifyPin(() => {
                            importTypeText.textContent = 'Users';
                            loadingOverlay.style.display = 'flex';
                            window.location.href =
                                '<?= base_url("UserImport/importCsv") ?>';
                        });
                    });

            });
        </script>

</body>

</html>