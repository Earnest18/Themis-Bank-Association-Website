
<?php
session_start();
include './db_connection/connection.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    die("You must log in first.");
}

$loggedInUser = $_SESSION['username'];

// Gets the age, Birthday, Mobile of the user
$sql = "SELECT Age, Birthday, MobileNum, Status, Email FROM new_registered_user WHERE Username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $loggedInUser);
$stmt->execute();
$stmt->bind_result($age, $birthday, $mobile, $status, $email);
$stmt->fetch();
$stmt->close();

// Store user info in an array
$UserInfo = [
    'Age' => $age ?? 0,
    'Birthday' => $birthday ?? '',
    'MobileNum' => $mobile ?? '',
    'status' => $status ??'',
    'email'=> $email ??''
];

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
    header("Location: Dashboard.php");
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


    //helps with login history
if (isset($_SESSION['username'])) {
    $loggedInUser = $_SESSION['username'];

    date_default_timezone_set('Asia/Manila'); 
    $date = date("Y-m-d"); // e.g. 2025-09-23
    $time = date("h:i A");

    // Detect device (very simple example using user agent)
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    if (stripos($userAgent, "Windows") !== false) {
        $device = "Windows PC";
    } elseif (stripos($userAgent, "Mac") !== false) {
        $device = "Mac";
    } elseif (stripos($userAgent, "Android") !== false) {
        $device = "Android Phone";
    } elseif (stripos($userAgent, "iPhone") !== false) {
        $device = "iPhone";
    } else {
        $device = "Unknown Device";
    }

    // Insert login record
    $sql = "INSERT INTO web_login_history (Username, Date, Time, Device) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $loggedInUser, $date, $time, $device);
    $stmt->execute();
    $stmt->close();

    //if ($profilePic && file_exists($profilePic)) {
    //    unlink($profilePic); // delete file from server
    //}

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

?>

<!doctype html>
<html lang="en">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>TBAQS | Dashboard</title>

    <!--begin::Accessibility Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
    <!--end::Accessibility Meta Tags-->

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
      <a href="#" class="nav-link">TBAQS WEBSITE</a>
     </li>
     </ul>

<!-- Right side -->
    <ul class="navbar-nav ms-auto d-flex align-items-center">
<!-- Search -->


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
      src="<?= htmlspecialchars($profilePic) ?>"
      id="profilePicNav"
      class="user-image rounded-circle shadow me-2"
      alt=""
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
            <li class="d-flex justify-content-between"><span><strong>Email:</strong></span> <span><?= $email ?></span></li>
          </ul>
        </li>

      <!-- Footer -->
      <li class="user-footer p-2 border-top text-center">
        <a href="../dist/securities/index.php" class="btn btn-default btn-flat d-block logout-btn">
          Log Out
        </a>
      </li>

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
          <a href="./index.html" class="brand-link">
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
                    <a href="Dashboard.php" class="nav-link active">
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
                    <a href="queueslip.php" class="nav-link">
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
              display: none; 
              justify-content: center;
              align-items: center;
              margin-left: 40px;
            }
            </style>

              <li class="nav-header">LABELS</li>
          <li class="nav-item">
            <a href="important.php" class="nav-link" style="display: gap: px;">
              <i class="nav-icon bi bi-bell"></i>
              <p class="text" style="margin: 0; display: flex; align-items: ;: center; gap: 15px;">
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
                <h3 class="mb-0" style="font-weight: 990; color: #003366;">Dashboard</h3>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="#">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                </ol>
              </div>
            </div>
<!--end::Row-->
          </div>
<!--end::Container-->
        </div>
<!--end::App Content Header-->
<!--begin::App Content-->
        <div class="app-content">
<!--begin::Container-->
          <div class="container-fluid">
<!--begin::Row-->
            <div class="row">
              <!--begin::Col-->
              <div class="col-lg-3 col-6">
                <!--begin::Small Box Widget 1-->
                <div class="small-box text-bg-primary">
                  <div class="inner">
                    <h3 id = "queNumber">None</h3>
                    <p>Current Queue</p>
                  </div>
                  <svg
                    class="small-box-icon"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                    xmlns=""
                    aria-hidden="true"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" />
</svg>

                  <a
                    href="#"
                    class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover"
                  >
                    <i class="bi">&nbsp;</i>
                  </a>
                </div>
                <!--end::Small Box Widget 1-->
              </div>
              <!--end::Col-->
              <div class="col-lg-3 col-6">
                <!--begin::Small Box Widget 2-->
                <div class="small-box text-bg-success">
                  <div class="inner">
                    <h3 id="userbalance">0</h3>

                    <p>Balance</p>
                  </div>
                  <svg
                    class="small-box-icon"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                  <a
                    href="#"
                    class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover"
                  >
                    <i class="bi">&nbsp;</i>
                  </a>
                </div>
                <!--end::Small Box Widget 2-->
              </div>
              <!--end::Col-->
              <div class="col-lg-3 col-6">
                <!--begin::Small Box Widget 3-->
                <div class="small-box bg-warning text-white">
                  <div class="inner">
                    <h3 id="total-transactions">0</h3>

                    <p>Transaction History</p>
                  </div>
                  <svg
                    class="small-box-icon"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                  >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                  </svg>
                  <a
                    href="#"
                    class="small-box-footer link-dark link-underline-opacity-0 link-underline-opacity-50-hover"
                  >
                    <i class="bi">&nbsp;</i>
                  </a>
                </div>
                <!--end::Small Box Widget 3-->
              </div>
              <!--end::Col-->
              <div class="col-lg-3 col-6">
                <!--begin::Small Box Widget 4-->
                <div class="small-box text-bg-danger">
                  <div class="inner">
                    <h3 id="total-queue">0</h3>

                    <p>In Queue</p>
                  </div>
                  <svg
                    class="small-box-icon"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                  >
                   <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                   <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                  <a
                    href="#"
                    class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover"
                  >
                    <i class="bi">&nbsp;</i>
                  </a>
                </div>
                <!--end::Small Box Widget 4-->
              </div>
              <!--end::Col-->
            </div>
            <!--end::Row-->
            <!--begin::Row-->
            <div class="row">
              <!-- Start col -->
              
                <!-- /.card -->

                <!-- DIRECT CHAT -->
<div class="card bg-light border-0 mb-4">
  <div class="card-body py-4 px-3 d-flex flex-column align-items-center text-center">
    <h5 class="card-title fw-bold">Ready to get in line?</h5>
    <p class="card-text text-secondary mb-4">
      Click the button below to get your queue slip and receive real-time updates.
    </p>
      <a href="queueslip.php" class="btn btn-primary btn-lg px-5 mx-auto" style="width: fit-content;">
      Get a Queue Slip
    </a>
  </div>
</div>
<div class="card direct-chat direct-chat-primary mb-4">
    <div class="card-header">
        <h3 class="card-title" style="font-family: 'Montserrat', sans-serif; font-weight: 700;">History Transaction</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse">
                <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
            </button>
        </div>
    </div>

    <div class="card-body mb-4 collapse show">
        <div class="direct-chat-messages">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Queue Number</th>
                        <th>Transaction Type</th>
                    </tr>
                </thead>
                <tbody id="transaction-history-body">
                    </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card direct-chat direct-chat-primary mb-4">
    <div class="card-header">
        <h3 class="card-title" style="font-family: 'Montserrat', sans-serif; font-weight: 700;">Live Queue</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse">
                <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
            </button>
        </div>
    </div>

    <div class="card-body mb-4 collapse show">
        <div class="direct-chat-messages mt-3">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>Transaction Type</th>
                        <th colspan="10" class="text-center">Queue Numbers</th>
                    </tr>
                </thead>
                <tbody id="live-queue-body">
                    </tbody>
            </table>
        </div>
    </div>
</div>
      <!--end::App Main-->
      
      <!--begin::Footer-->
            </div>
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
    
    <!--end::OverlayScrollbars Configure-->

    <!-- OPTIONAL SCRIPTS -->

    <!-- sortablejs -->
    <script
      src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"
      crossorigin="anonymous"
    ></script>

    <!-- sortablejs -->

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

    <script>
function loadDashboard() {
  fetch("../dist/db_connection/fetch_dashboard.php")
    .then(response => response.json())
    .then(data => {
  document.getElementById("transaction-history-body").innerHTML = data.transactions;
  document.getElementById("live-queue-body").innerHTML = data.liveQueue;
  document.getElementById("total-queue").textContent = data.totalQueue;
  document.getElementById("total-transactions").textContent = data.totalTransactions;
  document.getElementById("userbalance").textContent = data.totalbalance;
  document.getElementById("queNumber").textContent = data.quenum;
  
    })
    .catch(error => console.error("Error loading dashboard:", error));
}

loadDashboard();
setInterval(loadDashboard, 1000);
</script>
    

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
