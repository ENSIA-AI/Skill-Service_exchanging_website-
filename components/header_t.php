<?php
require_once dirname(__DIR__) . '/dashboard/profile/includes/dbh.inc.php';

$Id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] :
            (isset($_GET['id']) ? (int)$_GET['id'] : 0);

try {
    $stmt = $connection->prepare("SELECT * FROM Users WHERE UserId = :id");
    $stmt->execute([':id' => $Id]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $currentHeaderPhoto    = !empty($user['ProfilePicture']) ? htmlspecialchars($user['ProfilePicture']) : '../../assets/images/Default_pfp.svg';
        $userCredits = isset($user['CreditBalance']) ? (int)$user['CreditBalance'] : 0;
    } else {
        $currentHeaderPhoto = '../../assets/images/Default_pfp.svg';
        $userCredits = 0;
    }
} catch (PDOException $e) {
    echo "Query failed: " . $e->getMessage();
    $userCredits = 0;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../assets/css/header.css">
    <link rel="icon" type="image/png" href="../../assets/images/favicon.png">
    <style>
        .header-avatar {
            grid-area: avatar;
            border: 2px solid #ffa546;
            border-radius: 50%;
            width: 55px;
            height: 55px;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            align-self: center;
        }

        .header-avatar img {
            width: 55px;
            height: 55px;
            object-fit: cover;
            object-position: center;
            cursor: pointer;
            transition: opacity 0.2s;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="header-left">
            <div class="humberger">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-menu h-6 w-6" aria-hidden="true">
                    <path d="M4 5h16"></path>
                    <path d="M4 12h16"></path>
                    <path d="M4 19h16"></path>
                </svg>
            </div>
            <a href="../post/posts.php" class="header-logo">
                <img src="../../assets/images/homeinp/Swaplogo.png">
            </a>
            <!--<a href="/dashboard/home.html"class="Swap">Swap</a>-->

        </div>
        <div class="header-right">
            <span class="credit-span-small"><?= $userCredits ?></span>
            <div class="header-credit">
                Credits:
                <span class="credit-span"><?= $userCredits ?></span>
            </div>
            <a href="../notification/notifications.php"
                class="header-notification">

                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bell h-5 w-5"
                    aria-hidden="true">
                    <path d="M10.268 21a2 2 0 0 0 3.464 0"></path>
                    <path
                        d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326">
                    </path>
                </svg>

            </a>
            <a href="../profile/profile.php"
                class="header-profile header-avatar">
                <img src="<?= $currentHeaderPhoto; ?>" alt="Profile Picture">
            </a>
        </div>
    </div>
    <script>
        // Function to refresh credits display from server
        async function refreshCreditsDisplay() {
            try {
                const response = await fetch('../notification/getCredits.php');
                const data = await response.json();
                if (data.success) {
                    // Update all credit displays
                    document.querySelectorAll('.credit-span, .credit-span-small').forEach(el => {
                        el.textContent = data.balance;
                    });
                    console.log('Credits updated:', data.balance);
                }
            } catch (error) {
                console.error('Failed to refresh credits:', error);
            }
        }

        // Make it globally available
        window.refreshCreditsDisplay = refreshCreditsDisplay;

        // Header functionality
        document.addEventListener('DOMContentLoaded', function() {
            const hamburger = document.querySelector('.humberger');

            console.log('Header script loaded, hamburger found:', !!hamburger);

            // Hamburger menu click event
            if (hamburger) {
                hamburger.addEventListener('click', function(e) {
                    e.stopPropagation();
                    console.log('Hamburger clicked');

                    // Dispatch custom event to toggle sidebar
                    const toggleEvent = new CustomEvent('toggleSidebar');
                    document.dispatchEvent(toggleEvent);
                });
            }

            // Add hover effects to header elements
            const headerElements = document.querySelectorAll('.header-credit, .header-notification, .header-profile');
            headerElements.forEach(element => {
                element.addEventListener('mouseenter', function() {
                    this.style.opacity = '0.8';
                });
                element.addEventListener('mouseleave', function() {
                    this.style.opacity = '1';
                });
            });

            // Refresh credits on page load to ensure sync
            refreshCreditsDisplay();
            
            // Also refresh credits every 30 seconds (optional polling)
            setInterval(refreshCreditsDisplay, 30000);
        });
    </script>
</body>

</html>