<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pending Approval - TBAQS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css"/>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap"/>
  <style>
    body {
      font-family: 'Roboto', sans-serif;
      background: linear-gradient(135deg, #e0ecff, #f4f6f9);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      margin: 0;
    }

    .main-content {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }

    .card {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 20px;
      padding: 35px 25px;
      max-width: 420px;
      width: 100%;
      box-shadow: 0 8px 25px rgba(0,0,0,0.1);
      text-align: center;
    }

    h2 {
      font-weight: 700;
      font-size: 1.8rem;
      margin-bottom: 25px;
    }

    p {
      font-size: 1rem;
      line-height: 1.6; /* More space between lines */
      margin-bottom: 20px;
    }

    .btn-primary {
      background-color: #0d3380;
      border: none;
      border-radius: 12px;
      padding: 12px 0;
      font-size: 1rem;
      width: 100%;
      margin-top: 15px;
      transition: background 0.3s;
    }

    .btn-primary:hover {
      background-color: #09235a;
    }

    footer {
      text-align: center;
      padding: 12px 10px;
      background: #fff;
      border-top: 1px solid #ddd;
      font-size: 0.85rem;
    }

    /* Animate hourglass */
    .hourglass {
      display: inline-block;
      font-size: 2.2rem;
      animation: spinHourglass 2s linear infinite;
      color: #ffc107;
      margin-bottom: 15px;
    }

    @keyframes spinHourglass {
      0%   { transform: rotate(0deg); }
      50%  { transform: rotate(180deg); }
      100% { transform: rotate(360deg); }
    }

    @media (max-width: 480px) {
      .card {
        padding: 30px 20px;
      }

      h2 {
        font-size: 1.5rem;
        margin-bottom: 20px;
      }

      p {
        font-size: 0.95rem;
        line-height: 1.5;
        margin-bottom: 18px;
      }

      .btn-primary {
        padding: 10px 0;
        font-size: 0.95rem;
      }
    }
  </style>
</head>
<body>

<!-- Main Content -->
<div class="main-content">
  <div class="card">
    <div class="hourglass"><i class="bi bi-hourglass-split"></i></div>
    <h2>Account Pending</h2>
    <p>Hello <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>,</p>
    <p>Your account is currently <span class="text-danger fw-bold">not yet verified</span> by an administrator. Please wait for approval before you can access the dashboard.</p>
    <a href="../dist/securities/index.php" class="btn btn-primary">Logout</a>
  </div>
</div>

<!-- Footer -->
<footer>
  <div>Themis Bank Association</div>
  <strong>© 2025 TBAQS.</strong> All Rights Reserved.
</footer>

</body>
</html>