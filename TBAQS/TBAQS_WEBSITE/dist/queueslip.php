<?php
// Report all errors
error_reporting(E_ALL);

// Display errors (for development only)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

session_start();
include './db_connection/connection.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    die("You must log in first.");
}

$loggedInUser = $_SESSION['username'];

// Gets the age, Birthday, Mobile of the user
$sql = "SELECT Age, Birthday, MobileNum, Status, Acc_number FROM new_registered_user WHERE Username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $loggedInUser);
$stmt->execute();
$stmt->bind_result($age, $birthday, $mobile, $status, $acc_number);
$stmt->fetch();
$stmt->close();


// Store user info in an array
$UserInfo = [
    'Age' => $age ?? 0,
    'Birthday' => $birthday ?? '',
    'MobileNum' => $mobile ?? '',
    'status' => $status ?? '',
    'Acc_number' => $acc_number ?? ''
];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transaction'])) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {

        // Get selected transaction type from POST
        $selected = $_POST['transaction'];

        // Map to prefix and transaction type
        switch ($selected) {
            case 'deposit':
                $transactionType = 'Deposit';
                $prefix = 'D';
                break;
            case 'withdraw':
                $transactionType = 'Withdraw';
                $prefix = 'W';
                break;
            case 'loan':
                $transactionType = 'Loan Service';
                $prefix = 'L';
                break;
            case 'inquiry':
                $transactionType = 'Inquiry';
                $prefix = 'I';
                break;
            default:
                $transactionType = 'Unknown';
                $prefix = '';
                break;
        }

        // Connect to MySQL
        $mysqli = new mysqli('localhost', 'root', '', 'tbaqs');
        if ($mysqli->connect_error) {
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed']);
            exit;
        }

        // Select the latest queue number for the specific prefix
        $stmt = $mysqli->prepare("SELECT QueNum FROM quenum WHERE QueNum LIKE CONCAT(?, '%') ORDER BY id DESC LIMIT 1");
        if (!$stmt) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to prepare statement']);
            exit;
        }

        $stmt->bind_param('s', $prefix);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $latestQueNum = $row ? $row['QueNum'] : null;

        // Generate next queue number
        if ($latestQueNum) {
            if (preg_match('/' . preg_quote($prefix, '/') . '\s*-\s*(\d+)/', $latestQueNum, $matches)) {
                $lastNumber = (int)$matches[1];
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }
        } else {
            $nextNumber = 1;
        }

        $queueNumber = $prefix . " - " . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        date_default_timezone_set('Asia/Manila');
        $dateTime = date("F j, Y • h:i A");

        //transactiontype
        $transacttype = $transactionType;


        // Get amount from POST and validate
        $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0.00;

        // Insert the new queue record
        $insertStmt = $mysqli->prepare("INSERT INTO quenum (Username, MobileNum, QueNum, Transaction_Type, Amount, Acc_number, Date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$insertStmt) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to prepare insert statement']);
            exit;
        }

        $insertStmt->bind_param('sssssss', $loggedInUser, $mobile, $queueNumber, $transacttype, $amount, $acc_number, $dateTime);

        if (!$insertStmt->execute()) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to insert queue record']);
            exit;
        }

        echo json_encode([
            'queueNumber' => $queueNumber,
            'dateTime' => $dateTime,
            'transaction' => $transactionType
        ]);
        exit;

    }
}

if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
    $fileType = mime_content_type($_FILES['photo']['tmp_name']);
    $fileSize = $_FILES['photo']['size'];

    if ($fileType !== 'image/jpeg') {
        echo "Only JPEG images are allowed.";
        exit;
    }

    if ($fileSize > 2 * 1024 * 1024) { // 2MB max
        echo "File size must be less than 2MB.";
        exit;
    }

    $imgData = file_get_contents($_FILES['photo']['tmp_name']);

    // Store using proper BLOB handling
    $sql = "UPDATE new_registered_user SET profile = ? WHERE Username = ?";
    $stmt = $conn->prepare($sql);
    $null = NULL; 
    $stmt->bind_param("bs", $null, $loggedInUser); 
    $stmt->send_long_data(0, $imgData); 
    $stmt->execute();
    $stmt->close();

    // Redirect immediately to reload the page and show the new profile picture
    header("Location: queueslip.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update database to set default profile picture
    $sql = "UPDATE new_registered_user SET profile = NULL WHERE Username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $loggedInUser);
    $stmt->execute();
    $stmt->close();

    header("Location: Dashboard.php"); // redirect back to profile
    exit;
}

// Fetch profile picture to display
$sql = "SELECT profile FROM new_registered_user WHERE Username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $loggedInUser);
$stmt->execute();
$stmt->bind_result($profileData);
$stmt->fetch();
$stmt->close();

$profilePic = $profileData
    ? 'data:image/jpeg;base64,' . base64_encode($profileData)
    : '../Profiles/avatar.jpg'; // Default avatar image

?>

<!doctype html>
<html lang="en">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>TBAQS | Queue Slip</title>

    <!--begin::Accessibility Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
    <!--end::Accessibility Meta Tags-->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <!--begin::Primary Meta Tags-->
    <meta name="title" content="AdminLTE v4 | Dashboard" />
    <meta name="author" content="ColorlibHQ" />
    <meta
      name="description"
      content="AdminLTE is a Free Bootstrap 5 Admin Dashboard, 30 example pages using Vanilla JS. Fully accessible with WCAG 2.1 AA compliance."
    />
    <meta
      name="keywords"
      content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard, accessible admin panel, WCAG compliant"
    />
    <!--end::Primary Meta Tags-->

    <!--begin::Accessibility Features-->
    <!-- Skip links will be dynamically added by accessibility.js -->
    <meta name="supported-color-schemes" content="light dark" />
    <link rel="preload" href="./css/adminlte.css" as="style" />
    <!--end::Accessibility Features-->

    <!--begin::Fonts-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
      integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
      crossorigin="anonymous"
      media="print"
      onload="this.media='all'"
    />
    <!--end::Fonts-->

    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->

    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->

    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="./css/adminlte.css" />
    <!--end::Required Plugin(AdminLTE)-->

    <!-- apexcharts -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css"
      integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0="
      crossorigin="anonymous"
    />

    <!-- jsvectormap -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css"
      integrity="sha256-+uGLJmmTKOqBr+2E6KDYs/NRsHxSkONXFHUL0fy2O/4="
      crossorigin="anonymous"
    />
  </head>
  <!--end::Head-->
  <!--begin::Body-->
  <body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
      <!--begin::Header-->
      <nav class="app-header navbar navbar-expand navbar-dark" style="background-color:#001f3f;">
        <!--begin::Container-->
        <div class="container-fluid">
          <!--begin::Start Navbar Links-->
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                <i class="bi bi-list"></i>
              </a>
            </li>
            <!-- LOGO -->

     <!-- Account bal -->
    <li class="nav-item d-flex align-items-center">
      <a href="Dashboard.php" class="nav-link">TBAQS WEBSITE</a>
    </li>
  </ul>

  <!-- Right side -->
  <ul class="navbar-nav ms-auto d-flex align-items-center">
    <!-- Search -->
<li class="nav-item">
  <a href="Dashboard.php" class="nav-link" title="Dashboard">
    <i class="bi bi-house-door"></i>
  </a>
</li>

    <!-- Fullscreen -->
    <li class="nav-item">
      <a class="nav-link" href="#" data-lte-toggle="fullscreen">
        <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
        <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none"></i>
      </a>
    </li>

    <!-- User Menu Dropdown -->
<li class="nav-item dropdown user-menu">
  <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
    <img
      src="<?= $profilePic ?>"
      id="profilePicNav"
      class="user-image rounded-circle shadow me-2"
      alt="User Image"
      style="width:40px; height:40px; object-fit:cover;"
    />
    <span class="d-none d-md-inline">Welcome,&nbsp;<?= $loggedInUser ?>!</span>
  </a>

  <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
    <!-- Profile Header -->
    <li class="user-header text-bg-secondary p-3 text-center">
      <form id="profileForm" action="" method="POST" enctype="multipart/form-data">
        <!-- Hidden file input -->
        <input type="file" id="photoInput" name="photo" accept="image/jpeg"
               style="display:none;" onchange="document.getElementById('profileForm').submit()">

        <!-- Profile Image -->
        <img 
          src="<?= $profilePic ?>" 
          class="rounded-circle shadow mb-2" 
          style="width:100px;height:100px;object-fit:cover;cursor:pointer;" 
          onclick="document.getElementById('photoInput').click()"
        >

        <!-- Trash Icon on Bottom-Right Edge -->
    <form action="delete_profile_pic.php" method="post" 
          class="position-absolute" 
          style="bottom:0; right:0; transform: translate(25%, 25%);" 
          onsubmit="return confirm('Are you sure you want to delete your profile picture?');">
        <input type="hidden" name="user_id" value="<?= $userId ?>">
        <button type="submit" class="btn btn-danger btn-sm p-1 rounded-circle" title="Delete Profile Picture">
            <i class="fa fa-trash" style="font-size:12px;"></i>
        </button>
    </form>


        <!-- User Info -->
        <h5 class="mb-0"><?= $loggedInUser ?></h5>
        <small><?= $status ?></small>
      </form>
    </li>

        <style>
         user-body ul li {
         padding: 5px 0;
         font-size: 14px;
        }

        .user-body ul li span:first-child {
         color: #555;
         font-weight: 500;
        }

        .user-body ul li span:last-child {
        font-weight: 400;
        }
        </style>
         
                    <!-- Profile Details -->
        <li class="user-body p-3 text-start">
          <ul class="list-unstyled mb-0">
            <li class="d-flex justify-content-between"><span><strong>Age:</strong></span> <span><?= $age ?></span></li>
            <li class="d-flex justify-content-between"><span><strong>Birthday:</strong></span> <span><?= $birthday ?></span></li>
            <li class="d-flex justify-content-between"><span><strong>Mobile:</strong></span> <span><?= $mobile ?></span></li>
          </ul>
        </li>

      <!-- Footer -->
      <li class="user-footer p-2 border-top text-center">
        <a href="../dist/securities/index.php" class="btn btn-default btn-flat d-block logout-btn">
          Log Out
        </a>
      </li>
                <!--end::Menu Footer-->
              </ul>
            </li>
            <!--end::User Menu Dropdown-->
          </ul>
          <!--end::End Navbar Links-->
        </div>
        <!--end::Container-->
      </nav>
      <!--end::Header-->
      <!--begin::Sidebar-->
      <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <!--begin::Sidebar Brand-->
        <div class="sidebar-brand">
          <!--begin::Brand Link-->
          <a href="Dashboard.php" class="brand-link">
            <!--begin::Brand Image-->
            <img
              src="ui/tbalogo.png"
              alt="AdminLTE Logo"
              class="brand-image opacity-75 shadow"
            />
            <!--end::Brand Image-->
            <!--begin::Brand Text-->
            <span class="brand-text fw-light">TBAQS</span>
            <!--end::Brand Text-->
          </a>
          <!--end::Brand Link-->
        </div>
        <!--end::Sidebar Brand-->
        <!--begin::Sidebar Wrapper-->
        <div class="sidebar-wrapper">
          <nav class="mt-2">
            <!--begin::Sidebar Menu-->
<script>
function loadDashboard() {
  fetch("../dist/db_connection/fetch_dashboard.php")
    .then(response => response.json())
    .then(data => {
      const notifBadge = document.getElementById("notif");

      if (data.totalNotifications > 0) {
        notifBadge.textContent = data.totalNotifications;
        notifBadge.style.display = "flex"; // show badge
      } else {
        notifBadge.style.display = "none"; // hide badge
      }
    })
    .catch(error => console.error("Error loading dashboard:", error));
}

loadDashboard();
setInterval(loadDashboard, 1000);
</script>

            <ul
              class="nav sidebar-menu flex-column"
              data-lte-toggle="treeview"
              role="navigation"
              aria-label="Main navigation"
              data-accordion="false"
              id="navigation"
            >
              <li class="nav-item menu-open">
                <a href="#" class="nav-link active">
                  <i class="nav-icon bi bi-speedometer"></i>
                  <p>
                    Dashboard
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="Dashboard.php" class="nav-link">
                      <i class="bi bi-house-door-fill"></i>
                      <p>Dashboard</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="History.php" class="nav-link">
                      <i class="bi bi-clock-history"></i>
                      <p>History Transaction</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="queueslip.php" class="nav-link active">
                      <i class="bi bi-receipt-cutoff"></i>
                      <p>Queue Slip</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="Customer.php" class="nav-link">
                      <i class="bi bi-envelope-at-fill"></i>
                      <p>Customer Support</p>
                    </a>
                  </li>
                </ul>
              </li>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="nav-icon bi bi-box-arrow-in-right"></i>
                      <p>
                        Version 1
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview">
                      <li class="nav-item">
                        <a href="./examples/login.html" class="nav-link">
                          <i class="nav-icon bi bi-circle"></i>
                          <p>Login</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="./examples/register.html" class="nav-link">
                          <i class="nav-icon bi bi-circle"></i>
                          <p>Register</p>
                        </a>
                      </li>
                    </ul>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="nav-icon bi bi-box-arrow-in-right"></i>
                      <p>
                        Version 2
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview">
                      <li class="nav-item">
                        <a href="./examples/login-v2.html" class="nav-link">
                          <i class="nav-icon bi bi-circle"></i>
                          <p>Login</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="./examples/register-v2.html" class="nav-link">
                          <i class="nav-icon bi bi-circle"></i>
                          <p>Register</p>
                        </a>
                      </li>
                    </ul>
                  </li>
                  <li class="nav-item">
                    <a href="./examples/lockscreen.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Lockscreen</p>
                    </a>
                  </li>
                </ul>
              </li>

              
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="./docs/components/main-header.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Main Header</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./docs/components/main-sidebar.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Main Sidebar</p>
                    </a>
                  </li>
                </ul>
              </li>
              
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="./docs/javascript/treeview.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Treeview</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-header">WEBSITE INFORMATIONS</li>
              <li class="nav-item">
                <a href="About.php" class="nav-link">
                  <i class="bi bi-question-circle-fill"></i>
                  <p>About</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="Faqs.php" class="nav-link">
                  <i class="bi bi-book-half"></i>
                  <p>
                    Faqs
                  </p>
                </a>
                <li class="nav-item">
                <a href="Help.php" class="nav-link">
                  <i class="bi bi-info-circle-fill"></i>
                  <p>
                    Help?
                  </p>
                </a>
                
                <style>
            .notification-badge {
                background: red;
                color: white;
                border-radius: 50%;
                font-size: 12px;
                font-weight: bold;
                width: 18px;
                height: 18px;
                display: none; /* hidden by default */
                justify-content: center;
                align-items: center;
                margin-left: 40px;
              }
            </style>

              <li class="nav-header">LABELS</li>
          <li class="nav-item">
            <a href="important.php" class="nav-link" style="display: gap: px;">
              <i class="nav-icon bi bi-bell"></i>
              <p class="text" style="margin: 0; display: flex; align-items: center; gap: 15px;">
                Announcements
                <span class="notification-badge" id="notif"></span>
              </p>
            </a>
          </li>
              <li class="nav-item">
                <a href="settings.php" class="nav-link">
                  <i class="nav-icon bi bi-gear"></i>
                  <p>Security</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="developers.php" class="nav-link">
                  <i class="nav-icon bi bi-people"></i>
                  <p>Developers</p>
                </a>
              </li>
            <!--end::Sidebar Menu-->
          </nav>
        </div>
        <!--end::Sidebar Wrapper-->
      </aside>
      <!--end::Sidebar-->
      <!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-sm-6">
                <h3 class="mb-0" style="font-weight: 990; color: #003366;">Queue Slip</h3>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="Dashboard.php">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Queue Slip</li>
                </ol>
              </div>
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content Header-->
        <!--begin::App Content-->
        
                <!-- DIRECT CHAT -->
              <div class="card shadow-sm border rounded">
  <div class="card-header">
    <h3 class="card-title" style="font-family: 'Montserrat', sans-serif; font-weight: 700;">Transactions</h3>
    <div class="card-tools">
      <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse">
        <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
        <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
      </button>
    </div>
  </div>
  
  <div class="card-body">
    <!-- Ticket Form -->
    <div class="ticket-form p-3">
      <p><strong>Fill in the details below.</strong><br>
        Haven’t taken a ticket yet? Save time by entering your information now and secure your queue
        number instantly—no need to wait in line at the store.
      </p>

      <link rel="stylesheet" href="formstyle.css"/>

      <form id="ticketForm" method="POST">
  <div class="form-row mb-3" style="display: flex; gap: 10px;">
  <!-- Details -->
  <div style="flex: 2;">
    <label for="transaction">Details</label>
    <select class="form-control" id="transaction" name="transaction" required>
      <option value="" disabled selected>Please select transaction</option>
      <option value="deposit">Deposit</option>
      <option value="withdraw">Withdraw</option>
      <option value="loan">Loan Service</option>
      <option value="inquiry">Inquiry</option>
    </select>
  </div>

  <!-- Amount -->
  <div style="flex: 1;" id="amount_input">
    <label for="amount">Amount</label>
    <input 
      type="number" 
      class="form-control" 
      id="amount" 
      name="amount" 
      placeholder="₱ 0.00" 
      required>
      <span id="amountMsg"></span>
  </div>
</div>

  <div class="form-row mb-3" style="display: flex; gap: 10px;">
    <div style="flex: 1;">
      <label for="age">Age:</label>
      <input 
        type="number" 
        class="form-control" 
        id="age" 
        name="age"
        value="<?= htmlspecialchars($age) ?>" 
        > 
    </div>
    <div style="flex: 2;">
      <label for="mobile">Mobile Number:</label>
      <input 
        type="tel" 
        class="form-control" 
        id="mobile" 
        name="mobile"
        value="<?= htmlspecialchars($mobile) ?>" 
        > 
    </div>
  </div>

  <button type="submit" id="submitbtn" class="btn btn-success w-100">Confirm</button>
</form>


<!-- Queue Slip Card (hidden at first) -->
<div class="card shadow-sm border rounded d-none" id="queueSlip">
  <div class="card-header">
    <h3 class="card-title" style="font-family: 'Montserrat', sans-serif; font-weight: 700;">Queue Slip</h3>
    <div class="card-tools">
      <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse">
        <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
        <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
      </button>
    </div>
  </div>
  <div class="card-body text-center">
    <p class="small">Please present this slip at the counter once your queue number is called.</p>
    <img src="ui/tbalogo.png" alt="Logo" style="max-height:60px; margin: 10px auto;">

    <h2 class="fw-bold text-primary" id="queueNumber"></h2>

    <p id="queueDate">Sept. 19, 2025 • 10:20 AM</p>
    <h5 id="queueTransaction"><?php echo $transactionType; ?></h5>

    <p class="mt-4 small text-muted">
      Please ensure the accuracy of details encoded. TBA will not be liable for errors or for any loss or damage arising from or in connection with reliance on the information provided.
      <br><br>
      This queue ticket is for queuing purposes only and shall not serve as proof of transaction.
    </p>
  </div>
</div>
<script>
const amountInput = document.getElementById("amount");
const amountMsg = document.getElementById("amountMsg");
const form = document.getElementById("ticketForm");
const totalBalanceEl = document.getElementById("totalBalance");
const transactions = document.getElementById("transaction");
const amountInputDiv = document.getElementById("amount_input");

let userBalance = 0; // will be fetched

// Fetch total balance from backend
fetch('db_connection/fetch_dashboard.php', {
    method: 'GET',
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
})
.then(response => response.json())
.then(data => {
    userBalance = parseFloat((data.totalbalance || "0").replace(/[₱,\s]/g, ''));
    totalBalanceEl.textContent = data.totalbalance;
})
.catch(err => console.error('Failed to fetch balance:', err));

// Live formatting & validation
amountInput.addEventListener("input", () => {
    let numericValue = parseFloat(amountInput.value.replace(/[₱,\s]/g, ''));

    if (isNaN(numericValue) || numericValue <= 0) {
        amountInput.style.borderColor = "#d1d5db"; 
        amountMsg.textContent = "";
        return;
    }

    if (transactions.value === "withdraw") {
        // Only compare to balance for withdrawals
        if (numericValue > userBalance) {
            amountInput.style.borderColor = "red";
            amountMsg.textContent = `Amount exceeds available balance (₱ ${userBalance.toLocaleString()})`;
            amountMsg.style.color = "red";
        } else {
            amountInput.style.borderColor = "green";
            amountMsg.textContent = "Valid amount.";
            amountMsg.style.color = "green";
        }
    } else {
        // deposits and other transactions are always valid
        amountInput.style.borderColor = "green";
        amountMsg.textContent = "Valid amount for deposit.";
        amountMsg.style.color = "green";
    }

    // Format input
    amountInput.value = formatPeso(numericValue);
});

// Hide/show amount input depending on transaction type
transactions.addEventListener("change", function() {
    if (this.value === "inquiry" || this.value === "loan") {
        amountInputDiv.style.display = "none";
        amountInput.value = "0";  // default to 0
        amountInput.style.borderColor = "#d1d5db";
        amountMsg.textContent = `Amount set to 0 for ${this.value}.`;
    } else {
        amountInputDiv.style.display = "block";
        amountInput.value = ""; // reset if user switches back
        amountMsg.textContent = "";
    }
});

// Form submission
form.addEventListener('submit', function(e) {
    e.preventDefault();

    let numericValue = parseFloat(amountInput.value.replace(/[₱,\s]/g, ''));

    // Only validate withdrawals against balance
    if (transactions.value === "withdraw") {
        if (isNaN(numericValue) || numericValue > userBalance) {
            alert("Invalid amount! Please check your balance.");
            return;
        }
    } else if (transactions.value === "inquiry" || transactions.value === "loan") {
        numericValue = 0; // force amount to 0
    }
    // deposits bypass balance check

    const formData = new FormData(this);
    // ensure numeric value is sent
    formData.set('amount', numericValue);

    fetch('queueslip.php', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert(data.error); // handle PHP errors
            return;
        }

        document.getElementById('queueNumber').textContent = data.queueNumber;
        document.getElementById('queueDate').textContent = data.dateTime;
        document.getElementById('queueTransaction').textContent = data.transaction;
        document.getElementById('queueSlip').classList.remove("d-none");

        setTimeout(() => {
            window.location.href = "Dashboard.php";
        }, 5000);
    })
    .catch(err => console.error(err));
});
</script>


         </div>
        </div>
      <!--end::App Main-->
      <!--begin::Footer-->
       <footer class="app-footer">
    <div class="float-end d-none d-sm-inline">Themis Bank Association</div>
    <strong>
        © 2025 TBAQS.
    </strong>
    All Rights Reserved.
    </footer>
      <!--end::Footer-->
    </div>
    <!--end::App Wrapper-->
    <!--begin::Script-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script
      src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="./js/adminlte.js"></script>
    <!--end::Required Plugin(AdminLTE)--><!--begin::OverlayScrollbars Configure-->
    <script>
      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
      };
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);

        // Disable OverlayScrollbars on mobile devices to prevent touch interference
        const isMobile = window.innerWidth <= 992;

        if (
          sidebarWrapper &&
          OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined &&
          !isMobile
        ) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
              theme: Default.scrollbarTheme,
              autoHide: Default.scrollbarAutoHide,
              clickScroll: Default.scrollbarClickScroll,
            },
          });
        }
      });
    </script>
    <!--end::OverlayScrollbars Configure-->

    <!-- OPTIONAL SCRIPTS -->

    <!-- sortablejs -->
    <script
      src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"
      crossorigin="anonymous"
    ></script>

    <!-- sortablejs -->
    <script>
      new Sortable(document.querySelector('.connectedSortable'), {
        group: 'shared',
        handle: '.card-header',
      });

      const cardHeaders = document.querySelectorAll('.connectedSortable .card-header');
      cardHeaders.forEach((cardHeader) => {
        cardHeader.style.cursor = 'move';
      });
    </script>

    <!-- apexcharts -->
    <script
      src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
      integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8="
      crossorigin="anonymous"
    ></script>

    <!-- ChartJS -->
    <script>
      // NOTICE!! DO NOT USE ANY OF THIS JAVASCRIPT
      // IT'S ALL JUST JUNK FOR DEMO
      // ++++++++++++++++++++++++++++++++++++++++++

      const sales_chart_options = {
        series: [
          {
            name: 'Digital Goods',
            data: [28, 48, 40, 19, 86, 27, 90],
          },
          {
            name: 'Electronics',
            data: [65, 59, 80, 81, 56, 55, 40],
          },
        ],
        chart: {
          height: 300,
          type: 'area',
          toolbar: {
            show: false,
          },
        },
        legend: {
          show: false,
        },
        colors: ['#0d6efd', '#20c997'],
        dataLabels: {
          enabled: false,
        },
        stroke: {
          curve: 'smooth',
        },
        xaxis: {
          type: 'datetime',
          categories: [
            '2023-01-01',
            '2023-02-01',
            '2023-03-01',
            '2023-04-01',
            '2023-05-01',
            '2023-06-01',
            '2023-07-01',
          ],
        },
        tooltip: {
          x: {
            format: 'MMMM yyyy',
          },
        },
      };

      const sales_chart = new ApexCharts(
        document.querySelector('#revenue-chart'),
        sales_chart_options,
      );
      sales_chart.render();
    </script>

    <!-- jsvectormap -->
    <script
      src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/js/jsvectormap.min.js"
      integrity="sha256-/t1nN2956BT869E6H4V1dnt0X5pAQHPytli+1nTZm2Y="
      crossorigin="anonymous"
    ></script>
    <script
      src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/maps/world.js"
      integrity="sha256-XPpPaZlU8S/HWf7FZLAncLg2SAkP8ScUTII89x9D3lY="
      crossorigin="anonymous"
    ></script>

    <!-- jsvectormap -->
    <script>
      // World map by jsVectorMap
      new jsVectorMap({
        selector: '#world-map',
        map: 'world',
      });

      // Sparkline charts
      const option_sparkline1 = {
        series: [
          {
            data: [1000, 1200, 920, 927, 931, 1027, 819, 930, 1021],
          },
        ],
        chart: {
          type: 'area',
          height: 50,
          sparkline: {
            enabled: true,
          },
        },
        stroke: {
          curve: 'straight',
        },
        fill: {
          opacity: 0.3,
        },
        yaxis: {
          min: 0,
        },
        colors: ['#DCE6EC'],
      };

      const sparkline1 = new ApexCharts(document.querySelector('#sparkline-1'), option_sparkline1);
      sparkline1.render();

      const option_sparkline2 = {
        series: [
          {
            data: [515, 519, 520, 522, 652, 810, 370, 627, 319, 630, 921],
          },
        ],
        chart: {
          type: 'area',
          height: 50,
          sparkline: {
            enabled: true,
          },
        },
        stroke: {
          curve: 'straight',
        },
        fill: {
          opacity: 0.3,
        },
        yaxis: {
          min: 0,
        },
        colors: ['#DCE6EC'],
      };

      const sparkline2 = new ApexCharts(document.querySelector('#sparkline-2'), option_sparkline2);
      sparkline2.render();

      const option_sparkline3 = {
        series: [
          {
            data: [15, 19, 20, 22, 33, 27, 31, 27, 19, 30, 21],
          },
        ],
        chart: {
          type: 'area',
          height: 50,
          sparkline: {
            enabled: true,
          },
        },
        stroke: {
          curve: 'straight',
        },
        fill: {
          opacity: 0.3,
        },
        yaxis: {
          min: 0,
        },
        colors: ['#DCE6EC'],
      };

      const sparkline3 = new ApexCharts(document.querySelector('#sparkline-3'), option_sparkline3);
      sparkline3.render();
    </script>
    <!--end::Script-->
  </body>
  <!--end::Body-->
</html>