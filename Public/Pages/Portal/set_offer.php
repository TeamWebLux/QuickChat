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
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        include './App/db/db_connect.php';

        // Capture form data
        $title = mysqli_real_escape_string($conn, $_POST['tittle']);
        $content = mysqli_real_escape_string($conn, $_POST['content']);
        $page = $_SESSION['page'];
        $by = $_SESSION['username'];

        // Handle file upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $fileName = mysqli_real_escape_string($conn, $_FILES['image']['name']);
            $fileTmpName = $_FILES['image']['tmp_name'];
            $fileType = $_FILES['image']['type'];
            $fileSize = $_FILES['image']['size'];

            // Define your target directory and file path
            $targetDir = "uploads/";
            $targetFile = $targetDir . basename($fileName);

            // You can add file validation here (e.g., file size, type)

            // Move the file to your target directory
            if (move_uploaded_file($fileTmpName, $targetFile)) {
                // File is successfully uploaded
                // Insert form data and file name into the database
                $sql = "INSERT INTO offers (name, content, image,by_u,page) VALUES ('$title', '$content', '$fileName','$by','$page')";

                if (mysqli_query($conn, $sql)) {
                    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Details submitted successfully!'];
                } else {
                    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error: ' . mysqli_error($conn)];
                }
            } else {
                // Handle error
                $_SESSION['toast'] = ['type' => 'error', 'message' => 'There was an error uploading your file.'];
            }
        } else {
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'No file was uploaded or there was an upload error.'];
        }

        // Redirect to avoid form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
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
            <div class="container mt-5">
                <h2 class="mb-3">Submit Your Details</h2>
                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="titleInput" class="form-label">Title</label>
                        <input type="text" name="tittle" class="form-control" id="titleInput" placeholder="Enter title">
                    </div>
                    <div class="mb-3">
                        <label for="contentTextarea" class="form-label">Content</label>
                        <textarea class="form-control" name="content" id="contentTextarea" rows="4" placeholder="Enter content"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="formFile" class="form-label">Upload Image</label>
                        <input class="form-control" name="image" type="file" id="formFile">
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
            <?php
            include './App/db/db_connect.php';

            // Assuming $conn is your database connection
            $query = "SELECT name, content, image FROM offers";
            $result = mysqli_query($conn, $query);

            if (mysqli_num_rows($result) > 0) {
                echo '<div class="row">'; // Start the Bootstrap row
                while ($row = mysqli_fetch_assoc($result)) {
                    $title = htmlspecialchars($row["name"]); // Escape special characters to prevent XSS
                    $content = htmlspecialchars($row["content"]);
                    $image = htmlspecialchars($row["image"]);
                    $imagePath = "../uploads/" . $image; // Adjust the path as needed

                    // Display the data in a Bootstrap card
                    echo "
                    <div class='col-md-4'> <!-- Adjust the column size as needed -->
                        <div class='card'>
                            <img src='$imagePath' class='card-img-top' alt='$title'>
                            <div class='card-body'>
                                <h5 class='card-title'>$title</h5>
                                <p class='card-text'>$content</p>
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