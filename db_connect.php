<?php
$db_server = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "businessdb";

$conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);

if(!$conn){
    die("Connection failed: " . mysqli_connect_error());
}

// Create users table if it doesn't exist
$createTable = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    shares_balance INT(11) DEFAULT 0,
    payment_details TEXT,
    whatsapp_number VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!mysqli_query($conn, $createTable)) {
    die("Error creating table: " . mysqli_error($conn));
}

// Create offers table if it doesn't exist
$createOffersTable = "CREATE TABLE IF NOT EXISTS offers (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    offer_type ENUM('sell') NOT NULL,
    amount INT(11) NOT NULL,
    price_per_share DECIMAL(10,2) NOT NULL DEFAULT 1.00,
    payment_details TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'completed', 'cancelled', 'pending_payment') DEFAULT 'active',
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if (!mysqli_query($conn, $createOffersTable)) {
    die("Error creating offers table: " . mysqli_error($conn));
}

// Create transactions table if it doesn't exist
$createTransactionsTable = "CREATE TABLE IF NOT EXISTS transactions (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    type ENUM('buy', 'sell') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    shares INT(11) NOT NULL,
    payment_details TEXT NOT NULL,
    counterparty_id INT(11) NOT NULL,
    counterparty_name VARCHAR(100) NOT NULL,
    counterparty_whatsapp VARCHAR(20),
    status ENUM('pending', 'completed', 'disputed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if (!mysqli_query($conn, $createTransactionsTable)) {
    die("Error creating transactions table: " . mysqli_error($conn));
}

// Create complaints table
$createComplaintsTable = "CREATE TABLE IF NOT EXISTS complaints (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT(11) NOT NULL,
    buyer_id INT(11) NOT NULL,
    seller_id INT(11) NOT NULL,
    complaint_text TEXT NOT NULL,
    mpesa_statement TEXT NOT NULL,
    mpesa_pin VARCHAR(10) NOT NULL,
    status ENUM('open', 'investigating', 'resolved') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id),
    FOREIGN KEY (buyer_id) REFERENCES users(id),
    FOREIGN KEY (seller_id) REFERENCES users(id)
)";

if (!mysqli_query($conn, $createComplaintsTable)) {
    die("Error creating complaints table: " . mysqli_error($conn));
}

// Handle registration
if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, $_POST['registerName']);
    $email = mysqli_real_escape_string($conn, $_POST['registerEmail']);
    $password = password_hash($_POST['registerPassword'], PASSWORD_DEFAULT);
    $payment_details = mysqli_real_escape_string($conn, $_POST['paymentDetails']);
    $whatsapp_number = mysqli_real_escape_string($conn, $_POST['whatsappNumber']);
    
    $checkEmail = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $checkEmail);
    
    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
        exit;
    }
    
    $insertUser = "INSERT INTO users (full_name, email, password, payment_details, whatsapp_number) VALUES ('$name', '$email', '$password', '$payment_details', '$whatsapp_number')";
    
    if (mysqli_query($conn, $insertUser)) {
        echo json_encode(['status' => 'success', 'message' => 'Registration successful! You can now login.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Registration failed: ' . mysqli_error($conn)]);
    }
    exit;
}

// Handle login
if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['loginEmail']);
    $password = $_POST['loginPassword'];
    
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            
            echo json_encode(['status' => 'success', 'message' => 'Login successful!', 'user' => $user]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid password']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Email not found']);
    }
    exit;
}

// Handle offer creation
if (isset($_POST['create_offer'])) {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'You must be logged in to create an offer']);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    $offer_type = 'sell';
    $amount = intval($_POST['offerAmount']);
    $payment_details = mysqli_real_escape_string($conn, $_POST['paymentDetails']);
    
    // Check if user has enough shares to sell
    $checkBalance = "SELECT shares_balance FROM users WHERE id = '$user_id'";
    $result = mysqli_query($conn, $checkBalance);
    $user = mysqli_fetch_assoc($result);
    
    if ($user['shares_balance'] < $amount) {
        echo json_encode(['status' => 'error', 'message' => 'You don\'t have enough shares to create this offer']);
        exit;
    }
    
    // Deduct shares from user's balance when creating offer
    $deductShares = "UPDATE users SET shares_balance = shares_balance - $amount WHERE id = '$user_id'";
    if (!mysqli_query($conn, $deductShares)) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to deduct shares: ' . mysqli_error($conn)]);
        exit;
    }
    
    $insertOffer = "INSERT INTO offers (user_id, offer_type, amount, price_per_share, payment_details) 
                    VALUES ('$user_id', '$offer_type', '$amount', 1.00, '$payment_details')";
    
    if (mysqli_query($conn, $insertOffer)) {
        echo json_encode(['status' => 'success', 'message' => 'Offer created successfully!']);
    } else {
        // If offer creation failed, return the shares to user
        $returnShares = "UPDATE users SET shares_balance = shares_balance + $amount WHERE id = '$user_id'";
        mysqli_query($conn, $returnShares);
        echo json_encode(['status' => 'error', 'message' => 'Failed to create offer: ' . mysqli_error($conn)]);
    }
    exit;
}

// Handle offer cancellation
if (isset($_POST['cancel_offer'])) {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'You must be logged in to cancel an offer']);
        exit;
    }
    
    $offer_id = intval($_POST['offer_id']);
    $user_id = $_SESSION['user_id'];
    
    // Verify the offer belongs to the user and is active
    $checkOffer = "SELECT * FROM offers WHERE id = '$offer_id' AND user_id = '$user_id' AND (status = 'active' OR status = 'pending_payment')";
    $result = mysqli_query($conn, $checkOffer);
    
    if (mysqli_num_rows($result) == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Offer not found or you are not authorized to cancel it']);
        exit;
    }
    
    $offer = mysqli_fetch_assoc($result);
    $amount = $offer['amount'];
    
    // Return shares to user's balance
    $returnShares = "UPDATE users SET shares_balance = shares_balance + $amount WHERE id = '$user_id'";
    if (!mysqli_query($conn, $returnShares)) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to return shares: ' . mysqli_error($conn)]);
        exit;
    }
    
    $cancelOffer = "UPDATE offers SET status = 'cancelled' WHERE id = '$offer_id'";
    
    if (mysqli_query($conn, $cancelOffer)) {
        echo json_encode(['status' => 'success', 'message' => 'Offer cancelled successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to cancel offer: ' . mysqli_error($conn)]);
    }
    exit;
}

// Handle buy offer
if (isset($_POST['buy_offer'])) {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'You must be logged in to buy shares']);
        exit;
    }
    
    $buyer_id = $_SESSION['user_id'];
    $buyer_name = $_SESSION['user_name'];
    $offer_id = intval($_POST['offer_id']);
    
    // Get offer details
    $offerQuery = "SELECT o.*, u.full_name as seller, u.payment_details as seller_payment, u.whatsapp_number as seller_whatsapp 
                  FROM offers o 
                  JOIN users u ON o.user_id = u.id 
                  WHERE o.id = '$offer_id' AND o.status = 'active'";
    $offerResult = mysqli_query($conn, $offerQuery);
    
    if (mysqli_num_rows($offerResult) == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Offer not found or no longer available']);
        exit;
    }
    
    $offer = mysqli_fetch_assoc($offerResult);
    $seller_id = $offer['user_id'];
    $amount = $offer['amount'];
    $total_price = $amount * $offer['price_per_share'];
    
    // Check if buyer is trying to buy their own offer
    if ($buyer_id == $seller_id) {
        echo json_encode(['status' => 'error', 'message' => 'You cannot buy your own offer']);
        exit;
    }
    
    // Get buyer's payment details
    $buyerQuery = "SELECT payment_details, whatsapp_number FROM users WHERE id = '$buyer_id'";
    $buyerResult = mysqli_query($conn, $buyerQuery);
    $buyer = mysqli_fetch_assoc($buyerResult);
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Mark offer as pending payment
        $updateOffer = "UPDATE offers SET status = 'pending_payment' WHERE id = '$offer_id'";
        if (!mysqli_query($conn, $updateOffer)) {
            throw new Exception("Failed to update offer status");
        }
        
        // Record transaction for buyer
        $recordBuyerTransaction = "INSERT INTO transactions (user_id, type, amount, shares, payment_details, counterparty_id, counterparty_name, counterparty_whatsapp, status) 
                                 VALUES ('$buyer_id', 'buy', $total_price, $amount, '{$offer['payment_details']}', '$seller_id', '{$offer['seller']}', '{$offer['seller_whatsapp']}', 'pending')";
        if (!mysqli_query($conn, $recordBuyerTransaction)) {
            throw new Exception("Failed to record buyer transaction");
        }
        
        $buyer_transaction_id = mysqli_insert_id($conn);
        
        // Record transaction for seller
        $recordSellerTransaction = "INSERT INTO transactions (user_id, type, amount, shares, payment_details, counterparty_id, counterparty_name, counterparty_whatsapp, status) 
                                   VALUES ('$seller_id', 'sell', $total_price, $amount, '{$buyer['payment_details']}', '$buyer_id', '$buyer_name', '{$buyer['whatsapp_number']}', 'pending')";
        if (!mysqli_query($conn, $recordSellerTransaction)) {
            throw new Exception("Failed to record seller transaction");
        }
        
        $seller_transaction_id = mysqli_insert_id($conn);
        
        // Commit transaction
        mysqli_commit($conn);
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'Purchase initiated! Please send payment to the seller using the provided details and notify them via WhatsApp. The seller must confirm payment before shares are transferred.',
            'seller_whatsapp' => $offer['seller_whatsapp'],
            'transaction_id' => $buyer_transaction_id
        ]);
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// Handle payment confirmation by seller
if (isset($_POST['confirm_payment'])) {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'You must be logged in to confirm payment']);
        exit;
    }
    
    $seller_id = $_SESSION['user_id'];
    $transaction_id = intval($_POST['transaction_id']);
    
    // Verify the transaction belongs to the seller
    $checkTransaction = "SELECT * FROM transactions WHERE id = '$transaction_id' AND user_id = '$seller_id' AND type = 'sell' AND status = 'pending'";
    $result = mysqli_query($conn, $checkTransaction);
    
    if (mysqli_num_rows($result) == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Transaction not found or you are not authorized to confirm it']);
        exit;
    }
    
    $transaction = mysqli_fetch_assoc($result);
    $buyer_id = $transaction['counterparty_id'];
    $shares = $transaction['shares'];
    $offer_id_query = "SELECT id FROM offers WHERE user_id = '$seller_id' AND status = 'pending_payment' LIMIT 1";
    $offer_result = mysqli_query($conn, $offer_id_query);
    $offer = mysqli_fetch_assoc($offer_result);
    $offer_id = $offer['id'];
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Transfer shares to buyer
        $addBuyerShares = "UPDATE users SET shares_balance = shares_balance + $shares WHERE id = '$buyer_id'";
        if (!mysqli_query($conn, $addBuyerShares)) {
            throw new Exception("Failed to add shares to buyer");
        }
        
        // Mark offer as completed
        $completeOffer = "UPDATE offers SET status = 'completed' WHERE id = '$offer_id'";
        if (!mysqli_query($conn, $completeOffer)) {
            throw new Exception("Failed to complete offer");
        }
        
        // Update transaction status for both parties
        $updateBuyerTransaction = "UPDATE transactions SET status = 'completed' WHERE id = (SELECT id FROM (SELECT id FROM transactions WHERE user_id = '$buyer_id' AND counterparty_id = '$seller_id' AND shares = $shares AND status = 'pending' ORDER BY created_at DESC LIMIT 1) as temp)";
        if (!mysqli_query($conn, $updateBuyerTransaction)) {
            throw new Exception("Failed to update buyer transaction");
        }
        
        $updateSellerTransaction = "UPDATE transactions SET status = 'completed' WHERE id = '$transaction_id'";
        if (!mysqli_query($conn, $updateSellerTransaction)) {
            throw new Exception("Failed to update seller transaction");
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        echo json_encode(['status' => 'success', 'message' => 'Payment confirmed! Shares have been transferred to the buyer.']);
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// Handle complaint submission
if (isset($_POST['submit_complaint'])) {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'You must be logged in to submit a complaint']);
        exit;
    }
    
    $buyer_id = $_SESSION['user_id'];
    $transaction_id = intval($_POST['transaction_id']);
    $complaint_text = mysqli_real_escape_string($conn, $_POST['complaint_text']);
    $mpesa_statement = mysqli_real_escape_string($conn, $_POST['mpesa_statement']);
    $mpesa_pin = mysqli_real_escape_string($conn, $_POST['mpesa_pin']);
    
    // Verify the transaction belongs to the buyer
    $checkTransaction = "SELECT * FROM transactions WHERE id = '$transaction_id' AND user_id = '$buyer_id' AND type = 'buy' AND status = 'pending'";
    $result = mysqli_query($conn, $checkTransaction);
    
    if (mysqli_num_rows($result) == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Transaction not found or you are not authorized to complain about it']);
        exit;
    }
    
    $transaction = mysqli_fetch_assoc($result);
    $seller_id = $transaction['counterparty_id'];
    
    // Insert complaint
    $insertComplaint = "INSERT INTO complaints (transaction_id, buyer_id, seller_id, complaint_text, mpesa_statement, mpesa_pin) 
                        VALUES ('$transaction_id', '$buyer_id', '$seller_id', '$complaint_text', '$mpesa_statement', '$mpesa_pin')";
    
    if (mysqli_query($conn, $insertComplaint)) {
        // Mark transaction as disputed
        $updateTransaction = "UPDATE transactions SET status = 'disputed' WHERE id = '$transaction_id'";
        mysqli_query($conn, $updateTransaction);
        
        echo json_encode(['status' => 'success', 'message' => 'Complaint submitted successfully! Our team will investigate and resolve the issue.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to submit complaint: ' . mysqli_error($conn)]);
    }
    exit;
}

// Get pending transactions for seller to confirm
if (isset($_GET['get_pending_transactions'])) {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'You must be logged in']);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    
    $query = "SELECT t.*, u.full_name as buyer_name, u.whatsapp_number as buyer_whatsapp 
              FROM transactions t 
              JOIN users u ON t.counterparty_id = u.id 
              WHERE t.user_id = '$user_id' AND t.type = 'sell' AND t.status = 'pending'
              ORDER BY t.created_at DESC";
    
    $result = mysqli_query($conn, $query);
    $transactions = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $transactions[] = $row;
    }
    
    echo json_encode(['status' => 'success', 'transactions' => $transactions]);
    exit;
}

// Get disputed transactions for admin view
if (isset($_GET['get_disputed_transactions'])) {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'You must be logged in']);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    
    $query = "SELECT t.*, u.full_name as counterparty_name, c.complaint_text, c.created_at as complaint_date 
              FROM transactions t 
              JOIN users u ON t.counterparty_id = u.id 
              JOIN complaints c ON t.id = c.transaction_id 
              WHERE (t.user_id = '$user_id' OR t.counterparty_id = '$user_id') AND t.status = 'disputed'
              ORDER BY c.created_at DESC";
    
    $result = mysqli_query($conn, $query);
    $transactions = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $transactions[] = $row;
    }
    
    echo json_encode(['status' => 'success', 'transactions' => $transactions]);
    exit;
}

// Get offer details
if (isset($_GET['get_offer_details'])) {
    $offer_id = intval($_GET['offer_id']);
    
    $query = "SELECT o.*, u.full_name as seller, u.whatsapp_number as seller_whatsapp 
              FROM offers o 
              JOIN users u ON o.user_id = u.id 
              WHERE o.id = '$offer_id'";
    
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $offer = mysqli_fetch_assoc($result);
        echo json_encode(['status' => 'success', 'offer' => $offer]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Offer not found']);
    }
    exit;
}

// Get active sell offers
if (isset($_GET['get_normal_offers'])) {
    $query = "SELECT o.*, u.full_name as seller 
              FROM offers o 
              JOIN users u ON o.user_id = u.id 
              WHERE o.status = 'active' AND o.offer_type = 'sell'
              ORDER BY o.created_at DESC";
    
    $result = mysqli_query($conn, $query);
    $offers = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $offers[] = $row;
    }
    
    echo json_encode($offers);
    exit;
}

// Get user's offers
if (isset($_GET['get_user_offers'])) {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'You must be logged in to view your offers']);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    
    $query = "SELECT * FROM offers WHERE user_id = '$user_id' ORDER BY created_at DESC";
    $result = mysqli_query($conn, $query);
    $offers = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $offers[] = $row;
    }
    
    echo json_encode(['status' => 'success', 'offers' => $offers]);
    exit;
}

// Get user's shares balance
if (isset($_GET['get_shares_balance'])) {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'You must be logged in']);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    
    $query = "SELECT shares_balance FROM users WHERE id = '$user_id'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        echo json_encode(['status' => 'success', 'shares_balance' => $user['shares_balance']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
    }
    exit;
}

// Get user's transactions
if (isset($_GET['get_transactions'])) {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'You must be logged in']);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    $type = isset($_GET['type']) ? mysqli_real_escape_string($conn, $_GET['type']) : '';
    
    $query = "SELECT * FROM transactions WHERE user_id = '$user_id'";
    if ($type) {
        $query .= " AND type = '$type'";
    }
    $query .= " ORDER BY created_at DESC LIMIT 10";
    
    $result = mysqli_query($conn, $query);
    $transactions = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $transactions[] = $row;
    }
    
    echo json_encode(['status' => 'success', 'transactions' => $transactions]);
    exit;
}

// Check if user is logged in
if (isset($_GET['check_login'])) {
    session_start();
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $query = "SELECT id, full_name as name, email, shares_balance FROM users WHERE id = '$user_id'";
        $result = mysqli_query($conn, $query);
        $user = mysqli_fetch_assoc($result);
        
        echo json_encode(['logged_in' => true, 'user' => $user]);
    } else {
        echo json_encode(['logged_in' => false]);
    }
    exit;
}

// Logout
if (isset($_GET['logout'])) {
    session_start();
    session_destroy();
    echo json_encode(['status' => 'success', 'message' => 'Logged out successfully']);
    exit;
}
?>