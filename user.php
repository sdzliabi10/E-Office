    <?php
    include 'db.php';
    session_start();

    // Check if the user is logged in and is a user
    if (!isset($_SESSION['login']) || $_SESSION['login'] !== true || $_SESSION['level'] !== 'user') {
        // Redirect to login page if not logged in or not a user
        $_SESSION['redirect_message'] = 'Anda belum login';
        header("Location: index.php");
        exit();
    }

    $result = $conn->query("SELECT * FROM menus");
    ?>

    <!DOCTYPE html>
    <html lang="id">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
        <link rel="icon" type="image/png" href="/magang/new/images/bps.png" />
        <title>E-Office | BPS Brebes</title>
        <style>
            /* Reset CSS */
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: "Poppins", sans-serif;
            }

            body {
                overflow-x: hidden;
            }

            /* Hero Section Styles */
            .hero {
                position: relative;
                width: 100%;
                height: 100vh;
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                background-attachment: fixed;
                display: flex;
                flex-direction: column;
                align-items: center;
                overflow: hidden;
            }

            .hero::before {
                content: "";
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.4);
                filter: blur(1px);
                /* Menambahkan blur pada background */
            }


            .header {
                display: flex;
                align-items: center;
                justify-content: center;
                margin-top: 60px;
                margin-bottom: 30px;
                position: relative;
                z-index: 2;
            }



            .logo {
                height: 120px;
                margin-right: 10px;
                border: 3px solid white;
                padding: 5px;
                border-radius: 10px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
                background-color: rgba(0, 0, 0, 0.5);
            }

            .title {
                color: white;
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
            }

            .app-name {
                font-size: 3rem;
                font-weight: bold;
                line-height: 1;
                margin-bottom: 0;
            }

            .app-subtitle {
                font-size: 1.4rem;
                font-weight: normal;
                line-height: 1.2;
                margin-top: 0;
            }

            /* Menu Grid Styles */
            .menu-grid {
                display: flex;
                flex-wrap: wrap;
                justify-content: flex-start;
                width: calc(150px * 4 + 60px);
                max-width: 100%;
                margin-top: 50px;
                gap: 20px;
                position: relative;
                z-index: 2;
            }

            .menu-item {
                background-color: #FFC107;
                border-radius: 8px;
                padding: 12px;
                width: 120px;
                height: 110px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: space-between;
                text-align: center;
                text-decoration: none;
                color: #333;
                font-size: 12px;
                box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
                transition: transform 0.3s ease;
            }

            .menu-item:hover {
                transform: translateY(-3px);
            }

            .menu-icon {
                width: 50px;
                height: 50px;
                object-fit: contain;
                flex-shrink: 0;
            }

            .menu-text {
                font-size: 12px;
                font-weight: bold;
                min-height: 16px;
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
                flex-grow: 1;
            }


            /* Footer Styles */
            .footer {
                position: absolute;
                bottom: 20px;
                left: 0;
                width: 100%;
                text-align: center;
                color: white;
                font-size: 14px;
                z-index: 2;
                text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
            }

            /* Responsive Styles */
            @media (max-width: 768px) {
                .hero {
                    height: auto;
                    min-height: 100vh;
                    background-attachment: scroll;
                    /* Better performance on mobile */
                }

                .header {
                    flex-direction: column;
                    text-align: center;
                    margin-top: 30px;
                }

                .app-name {
                    font-size: 2.5rem;
                    margin-top: 1rem;
                }

                .app-subtitle {
                    font-size: 1.2rem;
                }

                .menu-grid {
                    width: 90%;
                    gap: 10px;
                    margin-top: 30px;
                    margin-bottom: 60px;
                    /* Space for footer */
                }

                .menu-item {
                    width: 120px;
                    height: 100px;
                    padding: 15px;
                }

                .menu-icon {
                    width: 50px;
                    height: 50px;
                }

                .menu-text {
                    font-size: 12px;
                    text-align: center;
                }

                .admin-btn {
                    top: 10px;
                    right: 10px;
                    padding: 8px 15px;
                    font-size: 12px;
                }
            }

            @media (max-width: 480px) {
                .app-name {
                    font-size: 2rem;
                }

                .app-subtitle {
                    font-size: 1rem;
                }

                .menu-grid {
                    gap: 8px;
                }

                .menu-item {
                    width: 100px;
                    height: 90px;
                    padding: 10px;
                }

                .menu-icon {
                    width: 40px;
                    height: 40px;
                    margin-bottom: 8px;
                }

                .menu-text {
                    font-size: 11px;
                    text-align: center;
                }
            }

            .admin-btn {
                position: absolute;
                top: 20px;
                right: 20px;
                background-color: #ff4d4d;
                color: white;
                padding: 10px 20px;
                border-radius: 5px;
                text-decoration: none;
                font-size: 14px;
                font-weight: bold;
                transition: background-color 0.3s ease;
            }

            .admin-btn:hover {
                background-color: rgb(246, 13, 13);
            }
        </style>
    </head>

    <body>
        <div class="hero" id="hero-background">
            <div class="header">
                <img src="/images/bps.png" alt="Logo Kabupaten Wonosobo" class="logo" id="logo-image" />
                <div class="title">
                    <h1 class="app-name">E-Office</h1>
                    <h2 class="app-subtitle">Badan Pusat Statistik Brebes</h2>
                </div>
            </div>
            <a href="logout.php" class="admin-btn">LOGOUT</a>

            <div class="menu-grid">
                <?php while ($row = $result->fetch_assoc()) {
                    $hasLink = !empty($row['link']) && $row['link'] !== '#';
                    $link = $hasLink ? htmlspecialchars($row['link']) : '';
                ?>
                    <?php if ($hasLink) { ?>
                        <!-- Jika ada link, gunakan <a> -->
                        <a href="<?= $link ?>" class="menu-item" target="_blank">
                            <img src="uploads/<?= htmlspecialchars($row['icon']) ?>" class="menu-icon" alt="<?= htmlspecialchars($row['name']) ?>" />
                            <span class="menu-text"><?= htmlspecialchars($row['name']) ?></span>
                        </a>
                    <?php } else { ?>
                        <!-- Jika tidak ada link, gunakan <div> agar tidak bisa diklik -->
                        <div class="menu-item">
                            <img src="uploads/<?= htmlspecialchars($row['icon']) ?>" class="menu-icon" alt="<?= htmlspecialchars($row['name']) ?>" />
                            <span class="menu-text"><?= htmlspecialchars($row['name']) ?></span>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>


            <!-- <div class="menu-grid">
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <a href="<?= $row['link'] ?>" class="menu-item" target="_blank">
                        <img src="uploads/<?= $row['icon'] ?>" class="menu-icon" alt="<?= $row['name'] ?>" />
                        <span class="menu-text"><?= $row['name'] ?></span>
                    </a>
                <?php } ?>
            </div> -->
        </div>

        <!-- <div class="footer">
            <p>&copy; 2025 E-Office BPS Brebes</p>
        </div> -->

        <script>
            // Logo image path update
            const logoImage = document.getElementById("logo-image");
            logoImage.src = "images/bps.png";

            // Set the background image with responsive handling
            const heroBackground = document.getElementById("hero-background");

            // Function to set responsive background
            function setResponsiveBackground() {
                const windowWidth = window.innerWidth;
                let imageSize;

                // Choose appropriate image size based on screen width
                if (windowWidth <= 640) {
                    imageSize = "800x600"; // Smaller size for mobile
                } else if (windowWidth <= 1024) {
                    imageSize = "1280x720"; // Medium size for tablets
                } else {
                    imageSize = "1920x1080"; // Larger size for desktops
                }

                heroBackground.style.backgroundImage = "url('/magang/new/images/waduk.webp')";
            }

            // Set initial background
            setResponsiveBackground();

            // Update background on window resize
            window.addEventListener("resize", setResponsiveBackground);
        </script>
    </body>

    </html>