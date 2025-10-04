<?php
session_start();
include './db_connection/connection.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    die("You must log in first.");
}

$loggedInUser = $_SESSION['username'];

// Gets the age, Birthday, Mobile of the user
$sql = "SELECT Age, Birthday, MobileNum, Status FROM new_registered_user WHERE Username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $loggedInUser);
$stmt->execute();
$stmt->bind_result($age, $birthday, $mobile, $status);
$stmt->fetch();
$stmt->close();

// Store user info in an array
$UserInfo = [
    'Age' => $age ?? 0,
    'Birthday' => $birthday ?? '',
    'MobileNum' => $mobile ?? '',
    'status' => $status ??''
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
    header("Location: History.php");
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
    <title>TBAQS | History</title>

    <!--begin::Accessibility Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
    <!--end::Accessibility Meta Tags-->

    <!--begin::Primary Meta Tags-->
    <meta name="title" content="AdminLTE | Dashboard v2" />
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
  </head>

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

            <!--begin::Sidebar Menu-->
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
                    <a href="History.php" class="nav-link active">
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
                display: none; /*hidden by default */
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
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6">
          <h3 class="mb-0" style="font-weight: 990; color: #003366;">History Transaction</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">History Transaction</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Transaction History -->
  <div class="card-body mb-4">
    <div class="direct-chat-messages">
      <table class="table table-striped table-bordered">
        <thead>
          <tr>
            <th>Username</th>
            <th>Queue Number</th>
            <th>Transaction Type</th>
            <th>Date</th>
            <th>Time</th>
          </tr>
        </thead>
        <tbody id="transaction-history-body">
          <!-- Transaction history contents -->
        </tbody>
      </table>
    </div>


  <!-- Login History Section (with spacing) -->
  <div class="app-content-header mt-5">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6">
          <h3 class="mb-0" style="font-weight: 990; color: #003366;">Login History</h3>
        </div>
      </div>
    </div>
  </div>

  <div class="card-body mb-4">
    <div class="direct-chat-messages">
      <table class="table table-striped table-bordered">
        <thead>
          <tr>
            <th>Date</th>
            <th>Time</th>
            <th>Device Type</th>
          </tr>
        </thead>
        <tbody id="login-history">
          <!-- Login history contents -->
        </tbody>
      </table>
    </div>
  </div>
</main>
      </main>
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

      <script>
function loadDashboard() {
  fetch("../dist/db_connection/fetch_dashboard.php")
    .then(response => response.json())
    .then(data => {
      document.getElementById("transaction-history-body").innerHTML = data.transactions;
      document.getElementById("login-history").innerHTML = data.logins;
    })
    .catch(error => console.error("Error loading dashboard:", error));
}

loadDashboard();
setInterval(loadDashboard, 0000);
</script>

    <!--end::OverlayScrollbars Configure-->

    <!-- OPTIONAL SCRIPTS -->

    <!-- apexcharts -->
    <script
      src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
      integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8="
      crossorigin="anonymous">
      

      // NOTICE!! DO NOT USE ANY OF THIS JAVASCRIPT
      // IT'S ALL JUST JUNK FOR DEMO
      // ++++++++++++++++++++++++++++++++++++++++++

      /* apexcharts
       * -------
       * Here we will create a few charts using apexcharts
       */

      //-----------------------
      // - MONTHLY SALES CHART -
      //-----------------------

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
          height: 180,
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
        document.querySelector('#sales-chart'),
        sales_chart_options,
      );
      sales_chart.render();

      //---------------------------
      // - END MONTHLY SALES CHART -
      //---------------------------

      function createSparklineChart(selector, data) {
        const options = {
          series: [{ data }],
          chart: {
            type: 'line',
            width: 150,
            height: 30,
            sparkline: {
              enabled: true,
            },
          },
          colors: ['var(--bs-primary)'],
          stroke: {
            width: 2,
          },
          tooltip: {
            fixed: {
              enabled: false,
            },
            x: {
              show: false,
            },
            y: {
              title: {
                formatter() {
                  return '';
                },
              },
            },
            marker: {
              show: false,
            },
          },
        };

        const chart = new ApexCharts(document.querySelector(selector), options);
        chart.render();
      }

      const table_sparkline_1_data = [25, 66, 41, 89, 63, 25, 44, 12, 36, 9, 54];
      const table_sparkline_2_data = [12, 56, 21, 39, 73, 45, 64, 52, 36, 59, 44];
      const table_sparkline_3_data = [15, 46, 21, 59, 33, 15, 34, 42, 56, 19, 64];
      const table_sparkline_4_data = [30, 56, 31, 69, 43, 35, 24, 32, 46, 29, 64];
      const table_sparkline_5_data = [20, 76, 51, 79, 53, 35, 54, 22, 36, 49, 64];
      const table_sparkline_6_data = [5, 36, 11, 69, 23, 15, 14, 42, 26, 19, 44];
      const table_sparkline_7_data = [12, 56, 21, 39, 73, 45, 64, 52, 36, 59, 74];

      createSparklineChart('#table-sparkline-1', table_sparkline_1_data);
      createSparklineChart('#table-sparkline-2', table_sparkline_2_data);
      createSparklineChart('#table-sparkline-3', table_sparkline_3_data);
      createSparklineChart('#table-sparkline-4', table_sparkline_4_data);
      createSparklineChart('#table-sparkline-5', table_sparkline_5_data);
      createSparklineChart('#table-sparkline-6', table_sparkline_6_data);
      createSparklineChart('#table-sparkline-7', table_sparkline_7_data);

      //-------------
      // - PIE CHART -
      //-------------

      const pie_chart_options = {
        series: [700, 500, 400, 600, 300, 100],
        chart: {
          type: 'donut',
        },
        labels: ['Chrome', 'Edge', 'FireFox', 'Safari', 'Opera', 'IE'],
        dataLabels: {
          enabled: false,
        },
        colors: ['#0d6efd', '#20c997', '#ffc107', '#d63384', '#6f42c1', '#adb5bd'],
      };

      const pie_chart = new ApexCharts(document.querySelector('#pie-chart'), pie_chart_options);
      pie_chart.render();

      //-----------------
      // - END PIE CHART -
      //-----------------
    </script>
    <!--end::Script-->
  </body>
  <!--end::Body-->
</html>
