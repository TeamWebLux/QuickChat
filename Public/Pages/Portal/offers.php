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

</head>

<body class="  ">
    <?php
    include("./Public/Pages/Common/sidebar.php");

    ?>

    <main class="main-content">
        <?php
        include("./Public/Pages/Common/main_content.php");
        ?>
        <div class="content-inner container-fluid pb-0" id="page_layout">
            <?php
            include './App/db/db_connect.php';

            // Assuming $conn is your database connection
            $query = "SELECT id,name, content, image FROM offers";
            $result = mysqli_query($conn, $query);

            if (mysqli_num_rows($result) > 0) {
                echo '<div class="row">'; // Start the Bootstrap row
                while ($row = mysqli_fetch_assoc($result)) {
                    $title = htmlspecialchars($row["name"]); // Escape special characters to prevent XSS
                    $content = htmlspecialchars($row["content"]);
                    $image = htmlspecialchars($row["image"]);
                    $id = htmlspecialchars($row["id"]);

                    $imagePath = "../uploads/" . $image; // Adjust the path as needed

                    // Display the data in a Bootstrap card
                    echo "
                    <div class='col-md-4'> <!-- Adjust the column size as needed -->
                        <div class='card position-relative'>
                            <div class='delete-button-container position-absolute top-0 end-0 p-2'>
                                <button class='btn btn-danger btn-sm' onclick='delete1(\"{$row["id"]}\", \"offers\", \"id\")'>Delete</button>
                            </div>
                            <img src='{$imagePath}' class='card-img-top' alt='{$title}'>
                            <div class='card-body'>
                                <h5 class='card-title'>{$title}</h5>
                                <p class='card-text'>{$content}</p>
                            </div>
                        </div>
                    </div>
                    ";
                }
                echo '</div>'; // End the Bootstrap row
            } else {
                echo "No results found.";
            }
            ?>

        </div>




        </div>
    </main>
    <?php
    include("./Public/Pages/Common/theme_custom.php");
    ?>
    <?php
    include("./Public/Pages/Common/settings_link.php");

    ?>
    <?php
    include("./Public/Pages/Common/scripts.php");
    ?>

</body>

</html>