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
        .card-img-top {
            width: 100%;
            /* makes image responsive */
            height: 15vw;
            /* you can set it to a fixed height if you prefer */
            object-fit: contain;
            /* ensures the whole image fits within the box */
            background-color: #FFF;
            /* or any color that matches your design */
        }

        .content-collapse {
            max-height: 4.5em;
            /* Adjust as necessary for the number of lines you want to show */
            overflow: hidden;
            transition: max-height 0.5s ease;
        }

        .content-collapse:hover {
            max-height: none;
            /* When hovered, show all content */
        }
    </style>


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
                    <div class='col-md-4'>
                        <div class='card'>
                            <div class='delete-button-container position-absolute top-0 end-0 p-2'>
                                <button class='btn btn-danger btn-sm' onclick='delete1(\"$id\", \"offers\", \"id\")'>Delete</button>
                            </div>
                            <img src='{$imagePath}' class='card-img-top' alt='{$title}'>
                            <div class='card-body'>
                                <h5 class='card-title'>{$title}</h5>
                                <div class='content-collapse'>
                                <p class='card-text'>{$content}</p>
                                </div>
                                <button class='btn btn-link' onclick='expandText(this)'>More</button>
                            </div>
                        </div>
                    </div>
                    ";

                    echo "
                    <script>
                    function expandText(button) {
                        var content = button.previousElementSibling;
                        if (button.innerText === 'More') {
                            content.style.maxHeight = 'none';
                            button.innerText = 'Less';
                        } else {
                            content.style.maxHeight = '4.5em';
                            button.innerText = 'More';
                        }
                    }
                    </script>
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