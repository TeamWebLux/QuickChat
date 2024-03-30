<!doctype html>
<html lang="en" dir="ltr">

<head>
    <?php
    include("./Public/Pages/Common/header.php");
    include "./Public/Pages/Common/auth_user.php";

    // Function to echo the script for toastr
    function echoToastScript($type, $message)
    {
        echo "<script type='text/javascript'>document.addEventListener('DOMContentLoaded', function() { toastr['$type']('$message'); });</script>";
    }

   
    if (isset($_SESSION['toast'])) {
        $toast = $_SESSION['toast'];
        echoToastScript($toast['type'], $toast['message']);
        unset($_SESSION['toast']); // Clear the toast message from session
    }

    if (session_status() !== PHP_SESSION_ACTIVE) session_start();

    // Display error message if available
    if (isset($_SESSION['login_error'])) {
        echo '<p class="error">' . $_SESSION['login_error'] . '</p>';
        unset($_SESSION['login_error']); // Clear the error message
    }

   
    ?>
    <style>
        #referralLinkInput {
            border-color: #007bff;
            /* Bootstrap primary color */
        }

        .referral-section {
            max-width: 800px;
            margin: auto;
            padding: 20px;
        }

        .section-title {
            text-align: center;
            margin-bottom: 30px;
        }

        .referral-details {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .referral-link-card,
        .referral-card {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            background-color: #f9f9f9;
        }

        .input-group {
            display: flex;
            margin-bottom: 15px;
        }

        .input-group-btn {
            display: flex;
        }

        .btn {
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            margin-left: 5px;
        }

        .copy-btn {
            background-color: #007bff;
            color: white;
        }

        .share-btn {
            background-color: #28a745;
            color: white;
        }

        .affiliate-list {
            padding-left: 20px;
        }

        .card-header {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .card-body h5 {
            margin-bottom: 10px;
        }

        .chat-btn {
            background-color: green;
            /* Normal state background color */
            color: white;
            /* Text color */
            padding: 5px 10px;
            /* Reduced padding to make the button smaller */
            /* Padding around the text */
            border: none;
            /* No border */
            border-radius: 5px;
            /* Rounded corners */
            cursor: pointer;
            /* Cursor changes to a pointer to indicate it's clickable */
            transition: background-color 0.3s;
            /* Smooth transition for background color change */
            text-decoration: none;
            /* No underline on text */
            font-weight: bold;
            /* Bold text */
        }

        /* Chat button hover state */
        .chat-btn:hover {
            background-color: red;
            /* Background color on hover */
        }
    </style>

</head>

<body class="  ">
    <!-- loader Start -->
    <?php
    // include("./Public/Pages/Common/loader.php");

    ?>
    <!-- loader END -->

    <!-- sidebar  -->
    <?php
    include("./Public/Pages/Common/sidebar.php");

    ?>

    <main class="main-content">
        <?php
        include("./Public/Pages/Common/main_content.php");
        ?>


        <div class="content-inner container-fluid pb-0" id="page_layout">
            <?php
            include "./App/db/db_connect.php";
            $userId = $_SESSION['user_id'];

            // Prepare the SQL query using mysqli
            $query = "SELECT * FROM user WHERE id = ?";
            $stmt = $conn->prepare($query);

            // Bind the user ID as a parameter to the query
            $stmt->bind_param("i", $userId); // "i" denotes that the parameter is an integer

            // Execute the query
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $refercode = $row['refer_code'];
            $page = $row['pagename'];
            $stmt->close();

            // Generate the referral link
            $referralLink = "https://quickchat.sweepstrac.net/index.php/Register_to_CustCount?r=" . $refercode . "&p=" . $page;
            ?>
            <br> <br>
            <br>
            <div class="container py-5">
                <div class="row justify-content-center">
                    <div class="col-md-10">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Your Referral Details</h5>
                                <p class="card-text">Share your referral link to invite friends and earn rewards!</p>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($referralLink); ?>" id="referralLinkInput" readonly>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" onclick="copyReferralLink()">Copy</button>
                                        <button class="btn btn-outline-primary" type="button" onclick="shareReferralLink()">Share</button>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <h6>Your Referral Code: <strong><?php echo htmlspecialchars($refercode); ?></strong></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            // Ensure database connection is established
            include "./App/db/db_connect.php";

            $username = $_SESSION['username']; // Assuming username is stored in session
            $query = "SELECT SUM(amount) as total_earnings FROM referrecord WHERE username = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            $totalEarnings = $row['total_earnings'] ?? 0; // If there's no earnings, default to 0
            $queryWithdrawAmount = "SELECT * FROM refferal_bonus"; // Adjust this if your table or column name is different.
            $resultWithdrawAmount = $conn->query($queryWithdrawAmount);
            $rowWithdrawAmount = $resultWithdrawAmount->fetch_assoc();
            $withdrawAmount = $rowWithdrawAmount['minimum'] ?? 0;

            // Fetch direct referrals
            $directReferralsQuery = "SELECT * FROM refferal WHERE refered_by = ?";
            $stmt = $conn->prepare($directReferralsQuery);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $directReferralsResult = $stmt->get_result();

            $referrals = [];
            while ($row = $directReferralsResult->fetch_assoc()) {
                $referralUsername = $row['name']; // Assuming 'username' is the field for the referred user's username
                $referrals[$referralUsername] = [
                    'username' => $referralUsername,
                    'affiliates' => []
                ];

                // Fetch affiliates for each referred user
                $affiliatesQuery = "SELECT name FROM refferal WHERE refered_by = ?";
                $affiliateStmt = $conn->prepare($affiliatesQuery);
                $affiliateStmt->bind_param("s", $referralUsername);
                $affiliateStmt->execute();
                $affiliatesResult = $affiliateStmt->get_result();

                while ($affiliateRow = $affiliatesResult->fetch_assoc()) {
                    $referrals[$referralUsername]['affiliates'][] = $affiliateRow['name'];
                }
            }
            $_SESSION['totalEarnings'] = $totalEarnings; // Store total earnings in session

            ?>

            <div class="referrals-list">
                <h3>Your Referrals and Affiliates</h3>
                <p>Total Referral Earnings: $<?php echo htmlspecialchars(number_format((float)$totalEarnings, 2, '.', '')); ?></p>
                <a href="./See_Refer" class="btn btn-primary">View Earnings</a>
                <?php if ($totalEarnings >= $withdrawAmount) : ?>
                    <a href="./Withdraw_Earning" class="btn btn-primary" onclick="<?php $_SESSION['withdrawAmount'] = $totalEarnings; ?>">Withdraw Earnings</a>
                <?php else : ?>
                    <p>You need at least $<?php echo htmlspecialchars(number_format((float)$withdrawAmount, 2, '.', '')); ?> to withdraw.</p>
                <?php endif; ?>


                <?php foreach ($referrals as $userDetails) : ?>
                    <div class="referral-card">
                        <div class="card-header">
                            Referred User: <?= htmlspecialchars($userDetails['username']); ?>
                        </div>
                        <div class="card-body">
                            <h5>Affiliates:</h5>
                            <?php if (!empty($userDetails['affiliates'])) : ?>
                                <ul class="affiliate-list">
                                    <?php foreach ($userDetails['affiliates'] as $affiliateUsername) : ?>
                                        <li>
                                            <?= htmlspecialchars($affiliateUsername); ?>
                                            <button class="chat-btn" onclick="window.location.href='./Chat_Screen?user=<?= urlencode($affiliateUsername); ?>'">Chat</button>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else : ?>
                                <p>No affiliates for this user.</p>
                            <?php endif; ?>
                            <!-- Chat button for the referred user -->
                            <button class="chat-btn" onclick="window.location.href='./Chat_Screen?user=<?= urlencode($userDetails['username']); ?>'">Chat with Referred User</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>


        </div>
        <?
        include("./Public/Pages/Common/footer.php");
       
        ?>

    </main>
    <!-- Wrapper End-->
    <!-- Live Customizer start -->
    <!-- Setting offcanvas start here -->
    <?php
    include("./Public/Pages/Common/theme_custom.php");

    ?>

    <!-- Settings sidebar end here -->

    <?php
    include("./Public/Pages/Common/settings_link.php");

    ?>
    <!-- Live Customizer end -->

    <!-- Library Bundle Script -->
    <?php
    include("./Public/Pages/Common/scripts.php");

    ?>
    <script>
        // Function to copy referral link to clipboard
        function copyReferralLink() {
            var copyText = document.getElementById("referralLinkInput");
            copyText.select(); // Select the text field
            copyText.setSelectionRange(0, 99999); // For mobile devices
            navigator.clipboard.writeText(copyText.value); // Copy the text inside the text field
            alert("Copied the link: " + copyText.value); // Alert the copied text
        }

        function shareReferralLink() {
            var shareUrl = document.getElementById("referralLinkInput").value;
            if (navigator.share) {
                navigator.share({
                        title: 'Join me on CustCount',
                        url: shareUrl
                    }).then(() => {
                        console.log('Thanks for sharing!');
                    })
                    .catch(console.error);
            } else {
                copyReferralLink();
                alert("Link copied to clipboard. Please paste it to share.");
            }
        }
    </script>

</body>

</html>