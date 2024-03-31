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
    if (isset($_GET['start']) && isset($_GET['end'])) {
        // Database connection details
        $host = 'your_host';
        $user = 'your_username';
        $password = 'your_password';
        $dbname = 'your_db';

        // Create connection
        $conn = new mysqli($host, $user, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $startTime = $conn->real_escape_string($_GET['start']);
        $endTime = $conn->real_escape_string($_GET['end']);

        // Query to fetch transactions within the specified time period
        $sql = "SELECT * FROM transactions WHERE created_at BETWEEN '$startTime' AND '$endTime'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Setting headers to force download of the report
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="report.csv"');

            $output = fopen('php://output', 'w');

            // Assuming you know the column names of the transactions table
            $columns = ['Column1', 'Column2', 'Column3']; // Replace with actual column names
            fputcsv($output, $columns); // Header row

            while ($row = $result->fetch_assoc()) {
                fputcsv($output, $row);
            }

            fclose($output);
            exit;
        } else {
            echo "No records found.";
        }
        $conn->close();
    }

    ?>

</head>

<body class="  ">
    <!-- loader Start -->
    <?php
    //  include("./Public/Pages/Common/loader.php");
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
            <br>
            <br>
            <button id="downloadReportBtn">Download Report</button>

            <div class="row">
                <div class="col-lg-8">
                    <div class="row">
                        <h4 class="mb-5">Analytics Overview</h4>
                        <div class="col-lg-3 col-md-6">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h2 class="mb-3">862</h2>
                                    <h5>Users</h5>
                                    <small>10% Decrease last week</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h2 class="mb-3">4.1M</h2>
                                    <h5>Sessions</h5>
                                    <small>24% Decrease last week</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h2 class="mb-3">143</h2>
                                    <h5>Visit Duration</h5>
                                    <small>10% change</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h2 class="mb-3">32%</h2>
                                    <h5>Bounce Rate</h5>
                                    <small>10% change</small>
                                </div>
                            </div>
                        </div>
                    </div>
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
    <script>
        document.getElementById('downloadReportBtn').addEventListener('click', function() {
            var startTime = prompt("Enter the start time (YYYY-MM-DD HH:MM:SS)", "");
            var endTime = prompt("Enter the end time (YYYY-MM-DD HH:MM:SS)", "");

            if (startTime && endTime) {
                // Constructing a URL to download the report
                var downloadUrl = `?start=${encodeURIComponent(startTime)}&end=${encodeURIComponent(endTime)}`;
                window.location.href = downloadUrl; // This will trigger the download
            } else {
                alert("You must enter both start and end times.");
            }
        });
    </script>


    <!-- Settings sidebar end here -->

    <?php
    include("./Public/Pages/Common/settings_link.php");
    ?>
    <!-- Live Customizer end -->

    <?php
    include("./Public/Pages/Common/scripts.php");
    ?>

</body>

</html>