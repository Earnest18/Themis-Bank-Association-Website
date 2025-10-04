<?php
session_start();
include '../db_connection/connection.php';

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php';

$message = "";

// Function to send OTP via Email
function sendOtpEmail($toEmail, $otp) {
    $mail = new PHPMailer(true);
    try {
        
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   =  true;
        $mail->Username   = 'tbaqs.system@gmail.com'; // Gmail
        $mail->Password   = 'buco ajzy hkyr ortj'; // Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $mail->Port       = 587;

        $mail->setFrom('tbaqs.system@gmail.com', 'TBAQS');
        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->Subject = 'TBAQS OTP CODE';
        $mail->Body = '
        <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
            <title>OTP Code</title>
        </head>
          <body style="font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px; margin: 0;">
            <div style="max-width: 500px; margin: auto; background: #ffffff; border-radius: 8px; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
              <div style="text-align: center; margin-bottom: 20px;">
                <img src="https://i.imgur.com/WPvTe0u.png" alt="TBAQS Logo" style="max-width: 120px; height: auto; display: block; margin: 0 auto;">
              </div>
              <h2 style="color: #333; text-align: center;">Your OTP Code</h2>
              <p style="font-size: 16px; color: #555; text-align: center;">
                Use the following One-Time Password to enter your account:
              </p>
              <p style="font-size: 24px; font-weight: bold; color: #2c3e50; text-align: center; letter-spacing: 3px;">
                ' . $otp . '
              </p>
              <p style="font-size: 14px; color: #777; text-align: center;">
                This code will expire in <b>10 minutes</b>.
              </p>
            </div>
          </body>
        </html>
        ';
        $mail->AltBody = "Your OTP is $otp. It will expire in 10 minutes.";

        $mail->send();
        
        return true;
    } catch (Exception $e) {
        error_log($e->getMessage());
        return false;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['loginusername'];
    $gmail    = $_POST['gmail'];

    // Check main users
    $stmt = $conn->prepare("SELECT * FROM new_registered_user WHERE Username = ? AND  Email = ?");
    $stmt->bind_param("ss", $username, $gmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // OTP already sent
        if (isset($_SESSION['otp_expire']) && time() < $_SESSION['otp_expire']) {
         $message = "An OTP was already sent. Please check your email.";
        
        } else {
           $otp = rand(100000, 999999);

            if (sendOtpEmail($gmail, $otp)) {
              // Only store OTP if email is sent successfully
              $_SESSION['otp'] = $otp;
              $_SESSION['username'] = $username;
              $_SESSION['otp_expire'] = time() + 600; // 10 minutes

              header("Location: verify_otp.php");
              exit();
          } else {
              $message = "Failed to send OTP. Try again.";
          }
         }
    } else {
        // Check pending users
        $stmt = $conn->prepare("SELECT * FROM pending_users WHERE Username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $pendingResult = $stmt->get_result();

        if ($pendingResult->num_rows > 0) {
            $_SESSION['username'] = $username;
            header("Location: ../pending_user_page.php");
            exit();
        } else {
            $message = "Invalid Username or Gmail.";
        }
    }
}
?>

<?php if (!empty($message)) : ?>
    <script>
        setTimeout(function() {
            document.getElementById("message").innerText = "";
        }, 5000); // clears message after 3 seconds
    </script>
<?php endif; ?>


<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>TBAQS | Login</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css"/>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap"/>

  <style>
    /* === Page Background === */
    html, body {
      height: 100%;
      margin: 0;
      font-family: 'Roboto', sans-serif;
      background: linear-gradient(135deg, #e0ecff, #f4f6f9);
      overflow: hidden;
      position: relative;
    }

    .ball {
  position: absolute;
  border-radius: 50%;
  opacity: 0.7;
  z-index: 0;
  pointer-events: none;
}

/* Bigger animation paths to travel around the page */
.ball {
  position: absolute;
  border-radius: 50%;
  opacity: 0.7;
  z-index: 0;
  pointer-events: none;
}

/* Define sizes and colors for 8 balls */
.ball1 {
  width: 50px;
  height: 50px;
  background: #ff6ec4;
  top: 0;
  left: 0;
  animation: edgeTravel1 20s linear infinite;
}

.ball2 {
  width: 30px;
  height: 30px;
  background: #6ec6ff;
  top: 0;
  right: 0;
  animation: edgeTravel2 20s linear infinite;
}

.ball3 {
  width: 60px;
  height: 60px;
  background: #ffc66e;
  bottom: 0;
  right: 0;
  animation: edgeTravel3 20s linear infinite;
}

.ball4 {
  width: 40px;
  height: 40px;
  background: #b76eff;
  bottom: 0;
  left: 0;
  animation: edgeTravel4 20s linear infinite;
}

.ball5 {
  width: 20px;
  height: 20px;
  background: #2ce69b;
  top: 50%;
  left: 0;
  animation: edgeTravel5 20s linear infinite;
}

.ball6 {
  width: 45px;
  height: 45px;
  background: #ff9a6c;
  top: 0;
  left: 50%;
  animation: edgeTravel6 20s linear infinite;
}

.ball7 {
  width: 35px;
  height: 35px;
  background: #6effa3;
  bottom: 0;
  left: 50%;
  animation: edgeTravel7 20s linear infinite;
}

.ball8 {
  width: 25px;
  height: 25px;
  background: #6e92ff;
  top: 50%;
  right: 0;
  animation: edgeTravel8 20s linear infinite;
}

/* Animations: moving around edges in rectangular paths */

@keyframes edgeTravel1 {
  0%   { transform: translate(0, 0); }
  25%  { transform: translate(80vw, 0); }          /* move right */
  50%  { transform: translate(80vw, 80vh); }       /* move down */
  75%  { transform: translate(0, 80vh); }          /* move left */
  100% { transform: translate(0, 0); }             /* move up */
}

@keyframes edgeTravel2 {
  0%   { transform: translate(0, 0); }
  25%  { transform: translate(-80vw, 0); }         /* move left */
  50%  { transform: translate(-80vw, 80vh); }      /* move down */
  75%  { transform: translate(0, 80vh); }          /* move right */
  100% { transform: translate(0, 0); }             /* move up */
}

@keyframes edgeTravel3 {
  0%   { transform: translate(0, 0); }
  25%  { transform: translate(-80vw, 0); }         /* move left */
  50%  { transform: translate(-80vw, -80vh); }     /* move up */
  75%  { transform: translate(0, -80vh); }         /* move right */
  100% { transform: translate(0, 0); }             /* move down */
}

@keyframes edgeTravel4 {
  0%   { transform: translate(0, 0); }
  25%  { transform: translate(80vw, 0); }          /* move right */
  50%  { transform: translate(80vw, -80vh); }      /* move up */
  75%  { transform: translate(0, -80vh); }         /* move left */
  100% { transform: translate(0, 0); }             /* move down */
}

@keyframes edgeTravel5 {
  0%   { transform: translate(0, 0); }
  25%  { transform: translate(0, 40vh); }          /* move down */
  50%  { transform: translate(40vw, 40vh); }       /* move right */
  75%  { transform: translate(40vw, 0); }          /* move up */
  100% { transform: translate(0, 0); }             /* move left */
}

@keyframes edgeTravel6 {
  0%   { transform: translate(0, 0); }
  25%  { transform: translate(40vw, 0); }          /* move right */
  50%  { transform: translate(40vw, 40vh); }       /* move down */
  75%  { transform: translate(0, 40vh); }          /* move left */
  100% { transform: translate(0, 0); }             /* move up */
}

@keyframes edgeTravel7 {
  0%   { transform: translate(0, 0); }
  25%  { transform: translate(0, -40vh); }         /* move up */
  50%  { transform: translate(40vw, -40vh); }      /* move right */
  75%  { transform: translate(40vw, 0); }          /* move down */
  100% { transform: translate(0, 0); }             /* move left */
}

@keyframes edgeTravel8 {
  0%   { transform: translate(0, 0); }
  25%  { transform: translate(-40vw, 0); }         /* move left */
  50%  { transform: translate(-40vw, -40vh); }     /* move up */
  75%  { transform: translate(0, -40vh); }         /* move right */
  100% { transform: translate(0, 0); }             /* move down */
}


    /* === Main Content === */
    .main-content {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      position: relative;
      z-index: 2;
      padding: 20px;
      height: 100vh;
    }

    .login-card {
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(15px);
      border-radius: 20px;
      border: 1px solid rgba(255, 255, 255, 0.3);
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
      padding: 40px 35px;
      width: 100%;
      max-width: 420px;
      z-index: 3;
    }

    .login-logo {
      width: 80px;
      height: auto;
      margin-bottom: 15px;
    }

    .login-card h1 {
      color: #0d3380;
      font-weight: 700;
      text-align: center;
      margin-bottom: 10px;
    }

    .form-control, .input-group-text {
      border-radius: 12px;
    }

    .form-control:focus {
      border-color: #0d3380;
      box-shadow: 0 0 0 0.2rem rgba(13, 51, 128, 0.15);
    }

    .btn-primary {
      background-color: #0d3380;
      border: none;
      border-radius: 12px;
      transition: 0.3s;
    }

    .btn-primary:hover {
      background-color: #09235a;
    }

    .btn-danger {
      border-radius: 12px;
    }

    a {
      color: #0d3380;
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }

    footer.app-footer {
      text-align: center;
      font-size: 0.875rem;
      color: #6c757d;
      padding: 10px 0;
      width: 100%;
      z-index: 2;
      position: relative;
      background-color: transparent;
    }
  </style>
</head>

<body>

<div class="ball ball1"></div>
<div class="ball ball2"></div>
<div class="ball ball3"></div>
<div class="ball ball4"></div>
<div class="ball ball5"></div>
<div class="ball ball6"></div>
<div class="ball ball7"></div>
<div class="ball ball8"></div>
<div class="ball ball9"></div>

  <!-- 🔐 Login Content -->
  <div class="main-content">
    <div class="login-card">
      <div class="text-center">
        <img src="../UI/tbalogo.png" alt="TBAQS Logo" class="login-logo">
      </div>
      <h1>TBAQS</h1>

      <?php if (!empty($error)) : ?>
        <div style="color:red; text-align:center; margin-bottom:10px;">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <p class="text-center mb-1">Sign in to your account</p>

      <span class="text-center d-block text-danger mb-3" id="message">
        <?= $message ?>
      </span>

      <form action="" method="post">
        <div class="mb-3">
          <label for="loginusername" class="form-label">Username</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
            <input type="text" class="form-control" id="loginusername" name="loginusername" placeholder="Enter username" required />
          </div>
        </div>

        <div class="mb-3">
  <label for="gmail" class="form-label">Gmail</label>
  <div class="input-group">
    <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
    <input type="email" class="form-control" id="gmail" name="gmail" placeholder="Enter Gmail" required />
  </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
          <div class="form-check">
            <a href="verify_otp.php">Go to otp page</a>
          </div>
          <a href="index.php">Back to login</a>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">Login</button>
      </form>


  <!-- 🔻 Footer -->
  <footer class="app-footer">
    <div>Themis Bank Association</div>
    <strong>© 2025 TBAQS.</strong> All Rights Reserved.
  </footer>

  <script>
  const pwd = document.getElementById('loginPassword');
  const chk = document.getElementById('showPwdChk');

  chk.addEventListener('change', () => {
    pwd.type = chk.checked ? 'text' : 'password';
  });
</script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3

  

