<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include '../db_connection/connection.php';

$message = "";
$message2 = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
$username = $_POST['username'];
$birthday = $_POST['birthday'];
$age = $_POST['age'];
$mobile = $_POST['mobile'];
$password = $_POST['password'];
$confirmPassword = $_POST['confirm_password'];
$amount = $_POST['amount'];
$email = $_POST['email'];
$status = "Member";

    if ($password !== $confirmPassword) {
        $message = "Passwords do not match!";
    } else {
        // check if username exists in new_registered_user or pending_users
        $stmt = $conn->prepare("SELECT Username FROM new_registered_user WHERE Username = ? 
                        UNION ALL 
                        SELECT Username FROM pending_users WHERE Username = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "Username already exists!";
        } else {
            // insert new user in peding_users db 
           $stmt = $conn->prepare("INSERT INTO pending_users 
            (Username, Birthday, Age, MobileNum, Password, Amount, Email, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $username, $birthday, $age, $mobile, $password, $amount, $email, $status);

            if ($stmt->execute()) {
                $message = "Account created successfully!";
                $message2 = "Please wait for admin approval.";

            echo "<script>
                setTimeout(function() {
                    window.location.href = 'index.php';
                }, 3000); // 3-second delay
              </script>";

                
            } else {
                $message = "Registration failed. Try again.";
            }
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>TBAQS | Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css"/>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap"/>
  <style>
    html, body { height: 100%; margin: 0; font-family: 'Roboto', sans-serif; background: linear-gradient(135deg, #95b8f1ff, #f4f6f9); overflow: hidden; }
    .main-content { display: flex; justify-content: center; align-items: center; height: 100vh; }
    .login-card { background: rgba(119, 103, 103, 0.15); backdrop-filter: blur(5px); border-radius: 20px; padding: 40px 35px; max-width: 420px; width: 100%; position: relative; z-index: 10; }
    .btn-primary { background-color: #0d3380; border: none; border-radius: 12px; }
    .btn-primary:hover { background-color: #09235a; }
    /* Background Balls */
    .ball { position: absolute; border-radius: 50%; opacity: 0.7; z-index: 0; pointer-events: none; }
    .ball1 { width: 50px; height: 50px; background: #ff6ec4; bottom: 60%; left: 0; animation: edgeTravel1 50s linear infinite; }
    .ball2 { width: 30px; height: 30px; background: #6ec6ff; top: 0; right: 0; animation: edgeTravel2 20s linear infinite; }
    .ball3 { width: 60px; height: 60px; background: #ffc66e; bottom: 0; right: 0; animation: edgeTravel3 30s linear infinite; }
    .ball4 { width: 40px; height: 40px; background: #b76eff; bottom: 0; left: 10px; animation: edgeTravel4 30s linear infinite; }
    .ball5 { width: 20px; height: 20px; background: #2ce69b; top: 20%; left: 0; animation: edgeTravel5 30s linear infinite; }
    .ball6 { width: 45px; height: 45px; background: #ff9a6c; top: 0; left: 50%; animation: edgeTravel6 30s linear infinite; }
    .ball7 { width: 35px; height: 35px; background: #6effa3; bottom: 0; left: 30%; animation: edgeTravel7 30s linear infinite; }
    .ball8 { width: 25px; height: 25px; background: #6e92ff; top: 50%; right: 0; animation: edgeTravel8 30s linear infinite; }
    /* Example Animations */
    @keyframes edgeTravel1 { 0%{transform:translate(0,0);} 25%{transform:translate(80vw,0);} 50%{transform:translate(80vw,80vh);} 75%{transform:translate(0,80vh);} 100%{transform:translate(0,0);} }
    @keyframes edgeTravel2 { 0%{transform:translate(0,0);} 25%{transform:translate(-80vw,0);} 50%{transform:translate(-80vw,80vh);} 75%{transform:translate(0,80vh);} 100%{transform:translate(0,0);} }
    @keyframes edgeTravel3 { 0%{transform:translate(0,0);} 25%{transform:translate(-80vw,0);} 50%{transform:translate(-80vw,-80vh);} 75%{transform:translate(0,-80vh);} 100%{transform:translate(0,0);} }
    @keyframes edgeTravel4 { 0%{transform:translate(0,0);} 25%{transform:translate(80vw,0);} 50%{transform:translate(80vw,-80vh);} 75%{transform:translate(0,-80vh);} 100%{transform:translate(0,0);} }
    @keyframes edgeTravel5 { 0%{transform:translate(0,0);} 25%{transform:translate(0,40vh);} 50%{transform:translate(40vw,40vh);} 75%{transform:translate(40vw,0);} 100%{transform:translate(0,0);} }
    @keyframes edgeTravel6 { 0%{transform:translate(0,0);} 25%{transform:translate(40vw,0);} 50%{transform:translate(40vw,40vh);} 75%{transform:translate(0,40vh);} 100%{transform:translate(0,0);} }
    @keyframes edgeTravel7 { 0%{transform:translate(0,0);} 25%{transform:translate(0,-40vh);} 50%{transform:translate(40vw,-40vh);} 75%{transform:translate(27vw,0);} 100%{transform:translate(0,0);} }
    @keyframes edgeTravel8 { 0%{transform:translate(0,0);} 25%{transform:translate(-40vw,0);} 50%{transform:translate(-40vw,-40vh);} 75%{transform:translate(0,-30vh);} 100%{transform:translate(0,0);} }
  </style>
</head>
<body>
  <!-- Floating Balls -->
  <div class="ball ball1"></div>
  <div class="ball ball2"></div>
  <div class="ball ball3"></div>
  <div class="ball ball4"></div>
  <div class="ball ball5"></div>
  <div class="ball ball6"></div>
  <div class="ball ball7"></div>
        <div class="ball ball8"></div>

        <!-- Register Form -->
        <div class="main-content">
            <div class="login-card">
            <div class="text-center">
                <img src="../UI/tbalogo.png" alt="TBAQS Logo" class="login-logo" style="width:80px;">
            </div>
            <h1 class="text-center">Register</h1>

            <span class="text-center d-block text-success mb-3">
                <?= $message ?><br>
                <?= $message2 ?>
            </span>

            <form action="" method="post">
                <div class="row mb-3">
        <!-- Username -->
        <div class="col-md-6">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required>
        </div>

        <!-- Age -->
        <div class="col-md-6">
            <label for="age" class="form-label">Age</label>
            <input type="number" class="form-control" id="age" name="age" placeholder="Enter age" required>
        </div>
        </div>

        <div class="row mb-3">
        <!-- Birthday -->
        <div class="col-md-6">
            <label for="birthday" class="form-label">Birthday</label>
            <input type="date" class="form-control" id="birthday" name="birthday" required>
        </div>
                <!-- Mobile Number -->
        <div class="col-md-6">
            <label for="mobile" class="form-label">Mobile Number</label>
            <input type="tel" pattern="[0-9]*" inputmode="numeric" class="form-control" id="mobile" name="mobile" placeholder="09xxxxxxxxx" maxlength="11" required>
        </div>
        </div>

        <div class="row mb-3">
        <!-- Gmail -->
        <div class="col-md-12">
            <label for="email" class="form-label">Gmail</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Enter Gmail address" required>
        </div>
        </div>

        <div class="row mb-3">
        <!-- Password -->
        <div class="col-md-6">
            <label for="password" class="form-label">Password</label>
            <input type="tel" pattern="[0-9]*" inputmode="numeric" class="form-control" id="password" name="password" placeholder="Enter password" required>
        </div>

        <!-- Confirm Password -->
        <div class="col-md-6">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <input type="tel" pattern="[0-9]*" inputmode="numeric"class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm password" required>
        </div>
        </div>

        <div class="row mb-3">
        <!-- Amount -->
        <div class="col-md-13">
            <label for="amount" class="form-label">Amount</label>
            <div class="input-group">
            <span class="input-group-text">₱</span>
            <input type="number" class="form-control" id="amount" name="amount" placeholder="Enter amount" required>
            </div>
        </div>
        </div>
        <button type="submit" class="btn btn-primary w-100 mb-3" id="register_btn">Register</button>
      </form>


      <p class="text-center mt-3">
        Already have an account? <a href="index.php">Login here</a>
      </p>
    </div>
  </div>

  <footer class="app-footer text-center mt-4">
    <div>Themis Bank Association</div>
    <strong>© 2025 TBAQS.</strong> All Rights Reserved.
  </footer>
</body>
</html>

<script>
  const birthdayInput = document.getElementById("birthday");
  const ageInput = document.getElementById("age");
  const form = document.querySelector("form");

  // Restrict birthday to 18 years ago (max)
  const today = new Date();
  const year = today.getFullYear() - 18;
  const month = String(today.getMonth() + 1).padStart(2, '0');
  const day = String(today.getDate()).padStart(2, '0');
  const maxDate = `${year}-${month}-${day}`;
  birthdayInput.setAttribute("max", maxDate);

  form.addEventListener("submit", function(e) {
    const birthDate = new Date(birthdayInput.value);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
      age--;
    }

    // Update age field automatically (optional)
    ageInput.value = age;

    if (age < 18) {
      e.preventDefault();
      alert("You must be at least 18 years old to register.");
    }
  });
</script>
