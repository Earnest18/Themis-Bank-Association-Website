<?php
session_start();
include("../db_connection/connection.php");

$errorMessage = ""; // For errors
$title = "Entered account using OTP";
$type = "warning";
$notifMessage = "If its not you, please change your password immediately.";
$created_at = date("Y-m-d h:i:s A");

// Make sure user is logged in
$loggedInUser = $_SESSION['username'] ?? null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $enteredOtp = $_POST['otp'] ?? '';

    if (isset($_SESSION['otp']) && isset($_SESSION['otp_expire'])) {
        if (time() < $_SESSION['otp_expire'] && $enteredOtp == $_SESSION['otp']) {

            // Insert into notifications
            $sql = "INSERT INTO notifications (Username, title, message, type, created_at) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $loggedInUser, $title, $notifMessage, $type, $created_at);
            $stmt->execute();
            $stmt->close();

            // OTP is correct, clear session
            unset($_SESSION['otp'], $_SESSION['otp_expire']);
            header("Location: ../Dashboard.php");
            exit();
        } else {
            $errorMessage = "Invalid or expired OTP.";
        }
    } else {
        $errorMessage = "No OTP session found. Please request a new OTP.";
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TBAQS | Verify OTP</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css"/>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap"/>

  <style>
    html, body {
      height: 100%;
      margin: 0;
      font-family: 'Roboto', sans-serif;
      background: linear-gradient(135deg, #e0ecff, #f4f6f9);
      overflow: hidden;
      position: relative;
    }

    /* Floating balls animation */
    .ball {
      position: absolute;
      border-radius: 50%;
      opacity: 0.7;
      z-index: 0;
      pointer-events: none;
    }
    .ball1 { width:50px; height:50px; background:#ff6ec4; top:0; left:0; animation:edgeTravel1 20s linear infinite;}
    .ball2 { width:30px; height:30px; background:#6ec6ff; top:0; right:0; animation:edgeTravel2 20s linear infinite;}
    .ball3 { width:60px; height:60px; background:#ffc66e; bottom:0; right:0; animation:edgeTravel3 20s linear infinite;}
    .ball4 { width:40px; height:40px; background:#b76eff; bottom:0; left:0; animation:edgeTravel4 20s linear infinite;}

    @keyframes edgeTravel1 {0%{transform:translate(0,0);}25%{transform:translate(80vw,0);}50%{transform:translate(80vw,80vh);}75%{transform:translate(0,80vh);}100%{transform:translate(0,0);}}
    @keyframes edgeTravel2 {0%{transform:translate(0,0);}25%{transform:translate(-80vw,0);}50%{transform:translate(-80vw,80vh);}75%{transform:translate(0,80vh);}100%{transform:translate(0,0);}}
    @keyframes edgeTravel3 {0%{transform:translate(0,0);}25%{transform:translate(-80vw,0);}50%{transform:translate(-80vw,-80vh);}75%{transform:translate(0,-80vh);}100%{transform:translate(0,0);}}
    @keyframes edgeTravel4 {0%{transform:translate(0,0);}25%{transform:translate(80vw,0);}50%{transform:translate(80vw,-80vh);}75%{transform:translate(0,-80vh);}100%{transform:translate(0,0);}}

    /* OTP card */
    .main-content {
      display:flex;
      justify-content:center;
      align-items:center;
      height:100vh;
      z-index:2;
      position:relative;
    }
    .otp-card {
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(15px);
      border-radius: 20px;
      border: 1px solid rgba(255, 255, 255, 0.3);
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
      padding: 40px 35px;
      width: 100%;
      max-width: 400px;
      text-align:center;
    }
    .otp-card h1 { color:#0d3380; font-weight:700; margin-bottom:20px;}
    .form-control { border-radius:12px;}
    .form-control:focus { border-color:#0d3380; box-shadow:0 0 0 0.2rem rgba(13,51,128,0.15);}
    .btn-primary { background-color:#0d3380; border-radius:12px; border:none; transition:0.3s; width:100%;}
    .btn-primary:hover { background-color:#09235a;}
    .text-danger { margin-bottom:15px; display:block; }
  </style>
</head>
<body>

<div class="ball ball1"></div>
<div class="ball ball2"></div>
<div class="ball ball3"></div>
<div class="ball ball4"></div>

<div class="main-content">
  <div class="otp-card">
    <h1>Verify OTP</h1>
    <?php if(!empty($message)) : ?>
      <span class="text-danger"><?= htmlspecialchars($message) ?></span>
    <?php endif; ?>

    <form method="post">
      <div class="mb-3">
        <input type="" name="otp" class="form-control text-center fw-bold" placeholder="Enter OTP" required>
      </div>
      <button type="submit" class="btn btn-primary text-center fw-bold">Verify OTP</button>
    </form>
  </div>
</div>

</body>
</html>
