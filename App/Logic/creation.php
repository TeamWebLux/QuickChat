<?php
ob_start();

use function PHPSTORM_META\type;

include "../db/db_connect.php";
session_start();
class Creation
{
    private $susername, $srole;
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->susername = $_SESSION['username'];
        $this->srole = $_SESSION['role'];
    }

    public function addUser()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input data
            $name = $this->sanitizeInput($_POST['name']);
            $username = $this->sanitizeInput($_POST['username']);
            $password = ($_POST['password']);
            $role = $this->sanitizeInput($_POST['role']);
            $managerid = isset($_POST['managerid']) ? $this->sanitizeInput($_POST['managerid']) : null;
            $agentid = isset($_POST['agentid']) ? $this->sanitizeInput($_POST['agentid']) : null;
            $pageId = isset($_POST['pagename']) ? $this->sanitizeInput($_POST['pagename']) : null;

            // Set branchId to null initially
            $branchId = 123;

            // Check if the branchname is provided
            // if (isset($_POST['branchname']) && $_POST['branchname'] !== '') {
            //     // If branchname is provided, sanitize and set the branchId
            //     $branchId = $this->sanitizeInput($_POST['branchname']);
            // } else {
            //     // If branchname is not provided, fetch the branchId based on pageId
            //     $branchId = $this->getBranchNameByPageName($pageId, $this->conn);
            // }

            // Check if the username is unique
            if ($this->isUsernameUnique($username)) {
                $query = "INSERT INTO user (name, username, password, role, branchname, pagename) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($this->conn, $query);
                mysqli_stmt_bind_param($stmt, "ssssss", $name, $username, $password, $role, $branchId, $pageId);
                $result = mysqli_stmt_execute($stmt);

                if ($result) {
                    echo "User added successfully.";
                } else {
                    echo "Error adding user: " . mysqli_error($this->conn);
                }
                mysqli_stmt_close($stmt);
            } else {
                echo "Username is not unique. Please choose another username.";
            }
        }
    }
    public function Platform()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $platformName = $this->conn->real_escape_string($_POST['platformname']);
            $status = isset($_POST['status']) ? 1 : 0;
            $currentBalance = $this->conn->real_escape_string($_POST['currentbalance']);
            $addedBy = $this->susername;

            $sql = "INSERT INTO platform (name, status, current_balance, by_u, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())";

            if ($stmt = $this->conn->prepare($sql)) {
                $stmt->bind_param("sids", $platformName, $status, $currentBalance, $addedBy);

                if ($stmt->execute()) {
                    $this->createRecord("platformRecord", "platform", $platformName, $currentBalance, "Recharge", $addedBy, 0, $currentBalance, "");
                    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Platform added successfully.'];
                    header("location: ../../index.php/Portal_Platform_Management");
                    exit();
                } else {
                    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error adding platform: ' . $stmt->error];
                }
                $stmt->close();
            } else {
                $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error preparing statement: ' . $this->conn->error];
            }
        }
    }
    public function createRecord($rtname, $name, $namef, $amount, $type, $addedBy, $openingBalance, $closingBalance, $remark)
    {
        $sql = "INSERT INTO $rtname ($name, amount, type, by_name, opening_balance, closing_balance, created_at, updated_at, remark) 
        VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW(), ?)";
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param("sdssdss", $namef, $amount, $type, $addedBy, $openingBalance, $closingBalance, $remark);

            if ($stmt->execute()) {
                $_SESSION['toast'] = ['type' => 'success', 'message' => 'Platform recharged successfully.'];
            } else {
                $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error recharging Platform: ' . $stmt->error];
            }
            $stmt->close();
        } else {
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error preparing statement: ' . $this->conn->error];
        }
    }
    public function RechargePlatform()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $platformName = $this->conn->real_escape_string($_POST['platform']);
            $amount = $this->conn->real_escape_string($_POST['amount']);
            $remark = $this->conn->real_escape_string($_POST['remark']);
            $type = $this->conn->real_escape_string($_POST['type']);

            // Fetch previous closing balance
            $previousClosingBalance = 0;
            // if ($type == "Recharge") {
            $query = "SELECT closing_balance FROM platformRecord WHERE platform = ? ORDER BY created_at DESC LIMIT 1";
            if ($stmt = $this->conn->prepare($query)) {
                $stmt->bind_param("s", $platformName);
                $stmt->execute();
                $stmt->bind_result($previousClosingBalance);
                $stmt->fetch();
                $stmt->close();
            }
            // }

            // Calculate new opening balance if the type is "Recharge"
            $openingBalance = 0;
            if ($type == "Recharge") {
                $openingBalance = $previousClosingBalance;
                $closingBalance = $openingBalance + $amount;
                $this->updateCurrentBalance("platform", $platformName, $closingBalance);
                // Closing balance will be the opening balance plus the recharge amount

            } elseif ($type == "Redeem") {
                $openingBalance = $previousClosingBalance;
                if ($openingBalance >= $amount) {
                    $closingBalance = $openingBalance - $amount;
                    $this->updateCurrentBalance("platform", $platformName, $closingBalance);
                    // Closing balance will be the opening balance plus the recharge amount
                } else {
                    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Not Enough Money to do Transaction.'];
                    header("Location: ../../index.php/Portal_Platform_Management");
                    exit();
                }
            }

            $addedBy = $this->susername;

            // Insert new record with updated balances
            $sql = "INSERT INTO platformRecord (platform, amount, type, by_name, opening_balance, closing_balance, created_at, updated_at, remark) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW(), ?)";

            if ($stmt = $this->conn->prepare($sql)) {
                $stmt->bind_param("sdssdss", $platformName, $amount, $type, $addedBy, $openingBalance, $closingBalance, $remark);

                if ($stmt->execute()) {

                    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Platform recharged successfully.'];
                    header("Location: ../../index.php/Portal_Platform_Management");
                    exit();
                } else {
                    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error recharging Platform: ' . $stmt->error];
                }
                $stmt->close();
            } else {
                $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error preparing statement: ' . $this->conn->error];
            }
        }
    }
    public function updateCurrentBalance($table, $platformName, $newBalance)
    {
        $sql = "UPDATE $table SET current_balance = ? WHERE name = ?";
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param("ds", $newBalance, $platformName);
            if ($stmt->execute()) {
                $stmt->close();
                return true; // Return true indicating success
            } else {
                $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error updating current balance: ' . $stmt->error];
            }
        } else {
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error preparing statement: ' . $this->conn->error];
        }

        return false; // Return false indicating failure
    }

    public function CashApp()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $name = $this->conn->real_escape_string($_POST['cashAppname']);
            $cashtag = $this->conn->real_escape_string($_POST['cashApptag']);
            $currentBalance = $this->conn->real_escape_string($_POST['currentbalance']);
            $email = $this->conn->real_escape_string($_POST['email']);
            $remark = $this->conn->real_escape_string($_POST['remark']);
            $addedBy = $this->susername;


            $status = isset($_POST['active']) ? 1 : 0;

            $sql = "INSERT INTO cashapp (name, cashtag,start,email, current_balance,remark, status, created_at, updated_at) VALUES (?, ?,NOW(), ?,?,?, ?, NOW(), NOW())";

            if ($stmt = $this->conn->prepare($sql)) {
                $stmt->bind_param("sssdsi", $name, $cashtag, $email, $currentBalance, $remark, $status);

                if ($stmt->execute()) {
                    $this->createRecord("cashappRecord", "name", $name, $currentBalance, "Recharge", $addedBy, 0, $currentBalance, $remark);

                    $_SESSION['toast'] = ['type' => 'success', 'message' => 'CashApp details added successfully.'];
                    header("location: ../../index.php/Portal_Cashup_Management");
                    exit();
                } else {
                    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error adding CashApp details: ' . $stmt->error];
                }
                $stmt->close();
            } else {
                $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error preparing statement: ' . $this->conn->error];
            }
        }
    }
    public function RechargeCashApp()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $cashAppName = $this->conn->real_escape_string($_POST['cashapp']);
            $amount = $this->conn->real_escape_string($_POST['amount']);
            $remark = $this->conn->real_escape_string($_POST['remark']);
            $type = $this->conn->real_escape_string($_POST['type']);
            $addedBy = $_SESSION['username'];
            $previousClosingBalance = 0;
            // if ($type == "Recharge") {
            $query = "SELECT closing_balance FROM cashappRecord WHERE name = ? ORDER BY created_at DESC LIMIT 1";
            if ($stmt = $this->conn->prepare($query)) {
                $stmt->bind_param("s", $cashAppName);
                $stmt->execute();
                $stmt->bind_result($previousClosingBalance);
                $stmt->fetch();
                $stmt->close();
            }
            // }

            // Calculate new opening balance if the type is "Recharge"
            $openingBalance = 0;
            if ($type == "Recharge") {
                $openingBalance = $previousClosingBalance;
                $closingBalance = $openingBalance + $amount;
                $this->updateCurrentBalance("cashapp", $cashAppName, $closingBalance);
                // Closing balance will be the opening balance plus the recharge amount

            } elseif ($type == "Redeem") {
                $openingBalance = $previousClosingBalance;
                if ($openingBalance >= $amount) {
                    $closingBalance = $openingBalance - $amount;
                    $this->updateCurrentBalance("cashapp", $cashAppName, $closingBalance);
                    // Closing balance will be the opening balance plus the recharge amount
                } else {
                    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Not Enough Money to do Transaction.'];
                    header("Location: ../../index.php/Portal_Platform_Management");
                    exit();
                }
            }

            $addedBy = $this->susername;

            echo $addedBy;

            $sql = "INSERT INTO cashappRecord (name, amount, type, by_name, opening_balance, closing_balance, created_at, updated_at, remark) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW(), ?)";

            if ($stmt = $this->conn->prepare($sql)) {
                $stmt->bind_param("sdssdss", $cashAppName, $amount, $type, $addedBy, $openingBalance, $closingBalance, $remark);

                if ($stmt->execute()) {
                    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Platform recharged successfully.'];
                    header("Location: ../../index.php/Portal_Cashup_Management");
                    exit();
                } else {
                    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error recharging Platform: ' . $stmt->error];
                }
                $stmt->close();
            } else {
                $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error preparing statement: ' . $this->conn->error];
            }
        }
    }

    public function CashOut()
    {
        if (isset($_POST)) {
            $username = $_POST['username'];
            $cashoutamount = $_POST['reedemamount'];
            $fbid = $_POST['pagename'];
            $accessamount = $_POST['excessamount'];
            $platformName = ($_POST['platformname'] !== 'other') ? $_POST['platformname'] : $_POST['platformname_other'];
            $cashupName = ($_POST['cashAppname'] !== 'other') ? $_POST['cashAppname'] : $_POST['cashAppname_other'];
            $remark = $_POST['remark'];
            $tip = $_POST['tip'];
            $type = "Credit";
            $by_role = $this->srole;
            $by_username = $this->susername;
            $userData=$this->getUserDataByUsername($username);
            $branchId=$userData['branchname'];
            $pagename=$userData['pagename'];


            $sql = "Insert into transaction (username,redeem,page,branch,excess,cashapp,platform,tip,type,remark,by_u) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
            if ($stmt = mysqli_prepare($this->conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "sississssss", $username, $cashoutamount, $pagename,$branchId, $accessamount, $cashupName, $platformName, $tip, $type, $remark, $by_username);
                if ($stmt->execute()) {
                    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Reedem Added Sucessfully '];

                    echo "Transaction added successfully. Redirecting...<br>";
                    header("Location: ../../index.php/Portal_User_Management");
                    exit();
                } else {
                    echo "Error adding transaction details: " . $stmt->error . "<br>";
                    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error adding transaction details: ' . $stmt->error];
                }
                $stmt->close();
            } else {
                echo "Error preparing statement: " . $this->conn->error . "<br>";
                $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error preparing statement: ' . $this->conn->error];
            }
        }
    }

    //pending
    public function Deposit()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Validate input fields
            if (empty($_POST['username']) || empty($_POST['platformname']) || empty($_POST['cashAppname']) || empty($_POST['bonusamount'])) {
                echo "Validation failed. Redirecting...<br>";
                echo "Current URL: " . $_SERVER['REQUEST_URI'] . "<br>";

                $_SESSION['toast'] = ['type' => 'error', 'message' => 'Please fill in all required fields.'];
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit();
            }

            $username = $this->conn->real_escape_string($_POST['username']);
            $recharge = $this->conn->real_escape_string($_POST['depositamount']);
            $pageId = 1;
            // $pagename = $_POST['pagename'] === 'other' ? $_POST['pagename_other'] : $_POST['pagename'];
            $platform = $_POST['platformname'] === 'other' ? $_POST['platformname_other'] : $_POST['platformname'];
            $cashName = $_POST['cashAppname'] === 'other' ? $_POST['cashAppname_other'] : $_POST['cashAppname'];
            $bonus = $this->conn->real_escape_string($_POST['bonusamount']);
            $remark = $this->conn->real_escape_string($_POST['remark']);
            $byId = 1; // Assuming a default value for byId
            $byUsername = $this->susername;
            $conn = $this->conn;
            $userData=$this->getUserDataByUsername($username);
            $branchId=$userData['branchname'];
            $pagename=$userData['pagename'];



            $type = "Debit"; // Adjust the type as needed

            $sql = "INSERT INTO transaction (username, recharge, page_id,page, platform,branch, cashapp, bonus, remark, by_id, by_u, type, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?,?, ?, ?,?, ?, ?, NOW(), NOW())";

            if ($stmt = $this->conn->prepare($sql)) {
                $stmt->bind_param("ssssssssssss", $username, $recharge, $pageId, $pagename, $platform, $branchId, $cashName, $bonus, $remark, $byId, $byUsername, $type);

                if ($stmt->execute()) {
                    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Recharge Added Sucessfully '];
                    $this->recordReferralAndAffiliateBonus($conn, $username, $recharge);
                    echo "Transaction added successfully. Redirecting...<br>";
                    header("Location: ../../index.php/Portal_User_Management");
                    exit();
                } else {
                    echo "Error adding transaction details: " . $stmt->error . "<br>";
                    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error adding transaction details: ' . $stmt->error];
                }
                $stmt->close();
            } else {
                echo "Error preparing statement: " . $this->conn->error . "<br>";
                $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error preparing statement: ' . $this->conn->error];
            }
        }
    }
    function recordReferralAndAffiliateBonus($conn, $username, $amount)
    {
        // Fetch the referred_by and affiliated_by usernames
        $query = "SELECT refered_by, afilated_by FROM refferal WHERE name = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $referralResult = $stmt->get_result();
        $referralData = $referralResult->fetch_assoc();

        if ($referralData) {
            // Fetch the bonus percentages
            $bonusQuery = "SELECT referal, affiliate FROM refferal_bonus LIMIT 1";
            $bonusResult = $conn->query($bonusQuery);
            $bonusData = $bonusResult->fetch_assoc();

            if ($bonusData) {
                // Calculate the bonuses
                $referralBonus = $amount * ($bonusData['referal'] / 100.0);
                $affiliateBonus = $amount * ($bonusData['affiliate'] / 100.0);

                // Record the referral bonus
                if ($referralData['refered_by']) {
                    $this->recordBonus($conn, $referralData['refered_by'], $referralBonus, 'Referred Bonus', $username);
                }

                // Record the affiliate bonus
                if ($referralData['afilated_by']) {
                    $this->recordBonus($conn, $referralData['afilated_by'], $affiliateBonus, 'Affiliate Bonus', $username);
                }
            } else {
                echo "No bonus data found.";
            }
        } else {
            echo "No referral data found for this user.";
        }
    }

    function recordBonus($conn, $username, $amount, $type, $name)
    {
        $trans = 'Credit';
        $insertQuery = "INSERT INTO referrecord (username, amount, type,byname,trans) VALUES (?, ?, ?,?,?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("sdsss", $username, $amount, $type, $name, $trans);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo "{$type} of {$amount} recorded for {$username}.\n";
        } else {
            echo "Error recording {$type}: " . $conn->error . "\n";
        }
    }


    function getBranchNameByPageName($pageName, $conn)
    {
        // Sanitize input to prevent SQL injection
        $pageName = mysqli_real_escape_string($conn, $pageName);

        // SQL query to retrieve branch name based on page name
        $query = "SELECT branch.name AS branch_name
              FROM page
              JOIN branch ON page.bid = branch.bid
              WHERE page.name = '$pageName'";

        // Execute the query
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            // Fetch the branch name from the result
            $row = $result->fetch_assoc();
            $branchId = $row['branch_name'];

            // Free the result set
            $result->free_result();

            // Return the branch name
            return $branchId;
        } else {
            // Return null if no result found
            return null;
        }
    }
    public function getUserDataByUsername($username)
    {
        // Sanitize input to prevent SQL injection
        $username = $this->conn->real_escape_string($username);

        // SQL query to retrieve user data by username
        $query = "SELECT * FROM user WHERE username = '$username'";

        // Execute the query
        $result = $this->conn->query($query);

        if ($result && $result->num_rows > 0) {
            // Fetch the user data from the result
            $userData = $result->fetch_assoc();

            // Free the result set
            $result->free_result();

            // Return the user data as an array
            return $userData;
        } else {
            // Return null if no result found
            return null;
        }
    }





    public function Redeem()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Retrieve form data
            $name = $_POST['username'] ?? '';
            $platformName = $_POST['platformname'] === 'other' ? $_POST['platformname_other'] : $_POST['platformname'];
            $cashupName = $_POST['cashupname'] === 'other' ? $_POST['cashupname_other'] : $_POST['cashupname'];
            $cashtag = $_POST['cashtag'] ?? '';
            $amount = $_POST['amount'] ?? 0;
            $remark = $_POST['remark'] ?? '';
            $by_role = $this->srole;
            $by_username = $this->susername;
            $conn = $this->conn;
            $user = $this->getUserDataByUsername($name);


            // $branchName = $this->getBranchNameByPageName($pagename, $conn);


            // Prepare an SQL statement to insert the form data into the database
            $sql = "INSERT INTO withdrawl (username, platformname, cashupname, cashtag, amount, remark,by_username) VALUES (?, ?, ?, ?, ?, ?,?)";

            try {
                // Prepare the SQL statement
                $stmt = mysqli_prepare($this->conn, $sql);
                $stmt->bind_param("ssssiss", $name, $platformName, $cashupName, $cashtag, $amount, $remark, $by_username);

                // Execute the statement
                $stmt->execute();

                // Close statement
                $stmt->close();

                // Redirect or inform the user of success
                echo "Record added successfully.";
                // Optionally, redirect to another page
                // header('Location: success_page.php');
            } catch (Exception $e) {
                // Close statement if it's set
                if (isset($stmt)) {
                    $stmt->close();
                }
                die("Error: " . $e->getMessage());
            }
        } else {
            // Handle incorrect access or display a specific error message
            echo "Invalid request.";
        }
    }




    public function CashupAction()
    {
        if (isset($_POST)) {
            $cashupname = $_POST['cashupname'];
            $cashuptag = $_POST['cashuptag'];
            $active = $_POST['active'] ? 1 : 0;
            $currentbalance = $_POST['currentbalance'];
            $sql = "Insert into CashupAction (cashupname,cashuptag,active,currentbalance) VALUES (?,?,?,?)";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssii", $cashupname, $cashuptag, $active, $currentbalance);
            $result = mysqli_stmt_execute($stmt);
            if ($result) {
                echo "CashupAction added successfully.";
            }
        }
    }
    public function AddBranch()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $name = $this->conn->real_escape_string($_POST['name']);
            $status = isset($_POST['status']) ? 1 : 0; // Assuming 'status' is a checkbox

            $sql = "INSERT INTO branch (name, status, created_at, updated_at) VALUES (?, ?, NOW(), NOW())";

            if ($stmt = $this->conn->prepare($sql)) {
                $stmt->bind_param("si", $name, $status);

                if ($stmt->execute()) {
                    // Success: Redirect or display a success message
                    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Branch added successfully.'];
                    header("location: ../../index.php/Portal_Branch_Management");
                    exit();
                } else {
                    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error adding branch: ' . $stmt->error];
                }
                $stmt->close();
            } else {
                $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error preparing statement: ' . $this->conn->error];
            }
        }
    }



    // Redirect back to the form page in case of an error or invalid method
    // header("location: " . $postUrl);
    // exit();

    public function EditBranch()
    {
        $name = $this->conn->real_escape_string($_POST['name']);
        $status = isset($_POST['status']) ? 1 : 0; // Assuming 'status' is a checkbox
        $bid = $_POST['bid'];
        echo $bid;

        $sql = "UPDATE branch SET name=?, status=?, updated_at=NOW() WHERE bid=?";

        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param("sii", $name, $status, $bid);

            if ($stmt->execute()) {
                // Success: Redirect or display a success message
                $_SESSION['toast'] = ['type' => 'success', 'message' => 'Branch Updated Successfully.'];
                header("location: ../../index.php/Portal_Branch_Management");
                exit();
            } else {
                $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error updating branch: ' . $stmt->error];
            }
            $stmt->close();
        } else {
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error preparing statement: ' . $this->conn->error];
        }
    }

    public function EditCashApp()
    {
        $name = $this->conn->real_escape_string($_POST['name']);

        $cashtag = $this->conn->real_escape_string($_POST['cashtag']);
        $email = $this->conn->real_escape_string($_POST['email']);
        $status = isset($_POST['status']) ? 1 : 0;
        $current_balance = $this->conn->real_escape_string($_POST['current_balance']);
        $remark = $this->conn->real_escape_string($_POST['remark']);

        $sql = "UPDATE cashapp SET cashtag=?, email=?, status=?, current_balance=?, remark=?, updated_at = NOW() WHERE name=?";
        $update_stmt = $this->conn->prepare($sql);
        $update_stmt->bind_param("ssidss", $cashtag, $email, $status, $current_balance, $remark, $name);


        // Execute the statement
        $update_stmt->execute();


        if ($update_stmt->execute()) {
            // Success: Redirect or display a success message

            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Details Updated Successfully.'];
            header("location: ../../index.php/Portal_Cashup_Management");
            exit();
        } else {
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error updating Details: ' . $update_stmt->error];
        }

        $update_stmt->close();
    }

    public function AddPage()
    {

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // $pid = $this->conn->real_escape_string($_POST['pid']);
            $bid = $this->conn->real_escape_string($_POST['bid']);
            $name = $this->conn->real_escape_string($_POST['name']);
            $status = isset($_POST['status']) ? 1 : 0; // Assuming 'status' is a checkbox
            $by = $this->susername;

            $sql = "INSERT INTO page (bid, name, status, by_u, created_at, updated_at) VALUES (?, ?, ?, ?,  NOW(), NOW())";

            if ($stmt = $this->conn->prepare($sql)) {
                $stmt->bind_param("ssis", $bid, $name, $status, $by);

                if ($stmt->execute()) {
                    // Success: Redirect or display a success message
                    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Page added successfully.'];
                    header("location: ../../index.php/Portal_Page_Management");
                    exit();
                } else {
                    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error adding page: ' . $stmt->error];
                }
                $stmt->close();
            } else {
                $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error preparing statement: ' . $this->conn->error];
            }
        }
    }
    public function Free_Play()
    {
        $username = $this->conn->real_escape_string($_POST['username']);
        $amount = $this->conn->real_escape_string($_POST['amount']);
        $remark = $this->conn->real_escape_string($_POST['remark']);
        $platform = $this->conn->real_escape_string($_POST['platform']);
        $addby = $this->susername;
        $type = "Free Play";
        // Fetch user data
        $userData = $this->fetchUserData($this->conn, $username);

        // Check if user data is fetched
        if (!empty($userData)) {
            // Loop through each row of user data
            foreach ($userData as $row) {
                // Extract required fields from the row
                $uid = $row['id'];
                $page = $row['pagename'];
                $branch = $row['branchname'];

                // Prepare SQL statement
                $sql = "INSERT INTO transaction (username, freepik,remark, platform, by_u,type,user_id,page,branch,created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

                // Prepare and execute the SQL statement
                if ($stmt = $this->conn->prepare($sql)) {
                    $stmt->bind_param("sisssssss", $username, $amount, $remark, $platform, $addby, $type, $uid, $page, $branch);

                    if ($stmt->execute()) {
                        // Success: Redirect or display a success message
                        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Free Play.'];
                        header("location: ../../index.php/Portal_User_Management");
                        exit();
                    } else {

                        $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error adding page: ' . $stmt->error];
                        print_r($_SESSION);
                    }
                    $stmt->close();
                } else {
                    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error preparing statement: ' . $this->conn->error];
                    print_r($_SESSION);
                }
            }
        } else {
            // No user data found
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'No user data found for the specified username.'];
            print_r($_SESSION);
        }
    }
    function fetchUserData($conn, $username)
    {
        // Initialize an empty array to store user data
        $userData = [];

        // Prepare and execute a query to fetch data for the given username
        $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch data row by row and store it in the array
        while ($row = $result->fetch_assoc()) {
            $userData[] = $row;
        }

        // Free the result set and close the statement
        $stmt->close();

        return $userData;
    }



    private function addToTree($newUserId, $role, $managerid, $agentid)
    {
        $id = $_SESSION['userid'];
        if ($newUserId != null && $role != null && $managerid != null && $agentid != null && $id != null) {
            $query = "INSERT INTO tree (agentid, adminid, managerid,userid	) VALUES (?, ?, ?,?)";
            $stmt = mysqli_prepare($this->conn, $query);
            mysqli_stmt_bind_param($stmt, "iiii", $agentid, $id, $managerid, $newUserId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        $parentId = 0; // Default to 0 or a suitable default parent ID
        $currentUserRole = $_SESSION['role'] ?? 'Admin'; // Defaulting to Admin if session role is not set
        $currentUserId = $_SESSION['user_id'] ?? 1; // Defaulting to 1 or a suitable admin ID if session user_id is not set

        // Determine the parent ID based on the current user's role and the new user's role
        if ($role == 'User') {
            if ($currentUserRole == 'Admin') {
                $parentId = $currentUserId; // The admin themselves are the parent
            } elseif ($currentUserRole == 'Manager' || $currentUserRole == 'Agent') {
                $parentId = $this->findParentId($currentUserId, $currentUserRole);
            }
            // For users, the parent could be an Admin, Manager, or Agent
        } elseif ($role == 'Manager' || $role == 'Agent') {
            // For managers and agents, the parent is assumed to be an Admin or higher level manager
            $parentId = $this->findParentId($currentUserId, $currentUserRole);
        }

        // Insert into the tree table
        // $query = "INSERT INTO tree (user_id, parent_id, role) VALUES (?, ?, ?)";
        // $stmt = mysqli_prepare($this->conn, $query);
        // mysqli_stmt_bind_param($stmt, "iis", $newUserId, $parentId, $role);
        // mysqli_stmt_execute($stmt);
        // mysqli_stmt_close($stmt);
    }

    private function findParentId($userId, $userRole)
    {
        // Logic to find the parent ID based on current user role and ID
        // This could involve querying the tree table to find the appropriate parent
        // For simplicity, return a default or queried parent ID
        return $userId; // Placeholder return
    }

    private function isUsernameUnique($username)
    {
        $query = "SELECT COUNT(*) FROM user WHERE username = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $count);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        return $count == 0; // If count is 0, the username is unique
    }

    private function sanitizeInput($input)
    {
        return htmlspecialchars(strip_tags(trim($input)));
    }
}

$creation = new Creation($conn);
if (isset($_GET['action']) && $_GET['action'] == "UserAdd") {
    $creation->addUser();
} else if (isset($_GET['action']) && $_GET['action'] == "platform") {
    $creation->Platform();
} else if (isset($_GET['action']) && $_GET['action'] == "CashApp") {
    $creation->CashApp();
} else if (isset($_GET['action']) && $_GET['action'] == "CashOut") {
    $creation->CashOut();
} else if (isset($_GET['action']) && $_GET['action'] == "Deposit") {
    $creation->Deposit();
} else if (isset($_GET['action']) && $_GET['action'] == "CashupAction") {
    $creation->CashupAction();
} else if (isset($_GET['action']) && $_GET['action'] == "Withdrawl") {
    $creation->Redeem();
} else if (isset($_GET['action']) && $_GET['action'] == "AddBranch") {
    $creation->AddBranch();
} else if (isset($_GET['action']) && $_GET['action'] == "AddPage") {
    $creation->AddPage();
} else if (isset($_GET['action']) && $_GET['action'] == "Recharge_Cashup") {
    $creation->RechargeCashApp();
} else if (isset($_GET['action']) && $_GET['action'] == "Recharge_platform") {
    $creation->RechargePlatform();
} else if (isset($_GET['action']) && $_GET['action'] == "EditCashApp") {
    $creation->EditCashApp();
} else if (isset($_GET['action']) && $_GET['action'] == "EditBranch") {
    $creation->EditBranch();
} else if (isset($_GET['action']) && $_GET['action'] == "Free_Play") {
    $creation->Free_Play();
}



// Close the database connection
mysqli_close($conn);
