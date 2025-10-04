<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "connection.php"; 
session_start();
$loggedInUser = $_SESSION['username'] ?? '';
$useremail = $_SESSION['Acc_num'] ?? '';

$response = [
    "transactions" => "",
    "liveQueue" => ""
];

// --- Transaction History ---
$sql = "SELECT Username, Que_Num, Transaction_Type, Date, Time
        FROM completed 
        WHERE Username = ? 
        ORDER BY Que_Num DESC LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $loggedInUser);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $response['transactions'] .= "<tr>
            <td>{$row['Username']}</td>
            <td>{$row['Que_Num']}</td>
            <td>{$row['Transaction_Type']}</td>
            <td>{$row['Date']}</td>
            <td>{$row['Time']}</td>
        </tr>";
    }
} else {
    $response['transactions'] = "<tr><td colspan='5' class='text-center'>No transactions found</td></tr>";
}
$stmt->close();

// --- Live Queue ---
$sql = "SELECT QueNum, Transaction_Type 
        FROM quenum 
        WHERE Transaction_Type != 'INQUIRE'
        ORDER BY id ASC";
$result = $conn->query($sql);

$queues = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $type = ucfirst(strtolower($row['Transaction_Type']));
        $queues[$type][] = $row['QueNum'];
    }
}

function buildRows($label, $values) {
    $html = "";
    if (empty($values)) {
        $html .= "<tr><td><strong>$label</strong></td>
                  <td colspan='10' class='text-center'>No queue</td></tr>";
        return $html;
    }

    $chunks = array_chunk($values, 10);
    foreach ($chunks as $index => $chunk) {
        $html .= "<tr>";
        if ($index == 0) {
            $html .= "<td rowspan='".count($chunks)."'><strong>$label</strong></td>";
        }
        foreach ($chunk as $val) {
            $html .= "<td>$val</td>";
        }
        $remaining = 10 - count($chunk);
        if ($remaining > 0) {
            $html .= str_repeat("<td></td>", $remaining);
        }
        $html .= "</tr>";
    }
    return $html;
}

$response['liveQueue'] .= buildRows("Withdraw", $queues['Withdraw'] ?? []);
$response['liveQueue'] .= buildRows("Deposit", $queues['Deposit'] ?? []);
$response['liveQueue'] .= buildRows("Loan", $queues['Loan service'] ?? []);
$response['liveQueue'] .= buildRows("Inquire", $queues['Inquiry'] ?? []);

// --- Totals ---
// Total in Queue
$sql = "SELECT COUNT(*) AS total FROM quenum";
$result = $conn->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $response['totalQueue'] = $row['total'];
}

// Total notifications (user-specific)
$sql = "SELECT COUNT(*) AS total FROM notifications WHERE Username = ? OR username = 'ALL'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $loggedInUser);
$stmt->execute();
$stmt->bind_result($totalnotifications);
$stmt->fetch();
$stmt->close();

$response['totalNotifications'] = $totalnotifications ?? 0;

// Total Transactions (user-specific)
$sql = "SELECT COUNT(*) AS total FROM completed WHERE Username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $loggedInUser);
$stmt->execute();
$stmt->bind_result($totalTransactions);
$stmt->fetch();
$stmt->close();

$response['totalTransactions'] = $totalTransactions ?? 0;

//Total balance of user
$sql = "SELECT Balance FROM new_registered_user WHERE Username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $loggedInUser);
$stmt->execute();
$stmt->bind_result( $totalbalance);
$stmt->fetch();
$stmt->close();

$response["totalbalance"] = "₱ " . number_format($totalbalance ?? 0, 2);

// --- Login History ---
$sql = "SELECT Date, Time, Device
        FROM web_login_history
        WHERE Username = ?
        ORDER BY Date DESC, Time DESC
        LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $loggedInUser);
$stmt->execute();
$result = $stmt->get_result();

$response['logins'] = ""; // initialize first

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $response['logins'] .= "<tr>
            <td>{$row['Date']}</td>
            <td>{$row['Time']}</td>
            <td>{$row['Device']}</td>
        </tr>";
    }
} else {
    $response['logins'] = "<tr><td colspan='3' class='text-center'>No login history found</td></tr>";
}
$stmt->close();

//Get user quenum in db
$sql = "SELECT QueNum FROM quenum WHERE Username = ? LIMIT 1";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("s", $loggedInUser);
    $stmt->execute();
    $stmt->bind_result($quenum);
    $stmt->fetch();
    $stmt->close();

    // If $quenum is empty or null, set response to string "null"
    $response["quenum"] = $quenum !== null ? $quenum : "None";
} else {
    $response["error"] = "None";
}

echo json_encode($response);

