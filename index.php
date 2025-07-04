<?php require_once 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WaveUnits P2P Exchange</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        gold: {
                            100: '#fdf4d9',
                            200: '#fae8b1',
                            300: '#f7dc89',
                            400: '#f4d061',
                            500: '#f1c439',
                            600: '#c19d2e',
                            700: '#917623',
                            800: '#604f17',
                            900: '#30270b',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #f1c439 0%, #d4af37 50%, #b78d12 100%);
        }
        .card-glow:hover {
            box-shadow: 0 0 15px rgba(241, 196, 57, 0.7);
        }
        .floating { 
            animation-name: floating;
            animation-duration: 3s;
            animation-iteration-count: infinite;
            animation-timing-function: ease-in-out;
        }
        @keyframes floating {
            0% { transform: translate(0,  0px); }
            50%  { transform: translate(0, 15px); }
            100%   { transform: translate(0, -0px); }
        }
        .error-message {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        input:invalid {
            border-color: #ef4444;
        }
        .logged-in {
            display: none;
        }
        .logged-out {
            display: block;
        }
        .user-logged-in .logged-in {
            display: block;
        }
        .user-logged-in .logged-out {
            display: none;
        }
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background-color: white;
            padding: 2rem;
            border-radius: 0.5rem;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
    </style>
</head>
<body class="bg-gold-100 min-h-screen">
    <!-- Navigation -->
    <nav class="gradient-bg text-white shadow-lg">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-wave-square text-2xl"></i>
                    <span class="text-xl font-bold">WaveUnits Exchange</span>
                </div>
                <div class="hidden md:flex space-x-6">
                    <a href="#market" class="hover:text-gold-200 transition">Market</a>
                    <a href="#portfolio" class="hover:text-gold-200 transition">Portfolio</a>
                    <a href="#dividends" class="hover:text-gold-200 transition">Dividends</a>
                    <a href="#create-offer" class="hover:text-gold-200 transition">Create Offer</a>
                </div>
                <div class="flex items-center space-x-4" id="authButtons">
                    <div class="logged-out">
                        <button id="loginBtn" class="px-4 py-2 bg-white text-gold-700 rounded-lg font-semibold hover:bg-gold-200 transition">Login</button>
                        <button id="registerBtn" class="px-4 py-2 bg-gold-700 text-white rounded-lg font-semibold hover:bg-gold-800 transition">Register</button>
                    </div>
                    <div class="logged-in hidden">
                        <span id="userGreeting" class="text-white">Hello, <span id="userName"></span></span>
                        <button id="logoutBtn" class="px-4 py-2 bg-white text-gold-700 rounded-lg font-semibold hover:bg-gold-200 transition ml-4">Logout</button>
                    </div>
                </div>
                <button id="mobileMenuBtn" class="md:hidden text-xl">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <div id="mobileMenu" class="hidden md:hidden mt-4 pb-2">
                <a href="#market" class="block py-2 hover:bg-gold-500 px-2 rounded">Market</a>
                <a href="#portfolio" class="block py-2 hover:bg-gold-500 px-2 rounded">Portfolio</a>
                <a href="#dividends" class="block py-2 hover:bg-gold-500 px-2 rounded">Dividends</a>
                <a href="#create-offer" class="block py-2 hover:bg-gold-500 px-2 rounded">Create Offer</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="gradient-bg text-white py-16">
        <div class="container mx-auto px-4 flex flex-col md:flex-row items-center">
            <div class="md:w-1/2 mb-10 md:mb-0">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">Trade WaveUnits Shares P2P</h1>
                <p class="text-xl mb-6">Peer-to-peer exchange platform for WaveUnits.com shares with direct payments between users.</p>
                <div class="flex space-x-4">
                    <button id="heroRegisterBtn" class="px-6 py-3 bg-white text-gold-700 rounded-lg font-semibold hover:bg-gold-200 transition logged-out">Get Started</button>
                    <button class="px-6 py-3 border-2 border-white text-white rounded-lg font-semibold hover:bg-white hover:text-gold-700 transition">Learn More</button>
                    <button id="dashboardBtn" class="px-6 py-3 bg-white text-gold-700 rounded-lg font-semibold hover:bg-gold-200 transition logged-in hidden">Dashboard</button>
                </div>
            </div>
            <div class="md:w-1/2 flex justify-center">
                <div class="relative w-64 h-64">
                    <div class="absolute top-0 left-0 w-64 h-64 bg-gold-400 rounded-full opacity-20 floating"></div>
                    <div class="absolute top-4 left-4 w-56 h-56 bg-gold-300 rounded-full opacity-30 floating" style="animation-delay: 0.5s;"></div>
                    <div class="absolute top-8 left-8 w-48 h-48 bg-gold-200 rounded-full opacity-40 floating" style="animation-delay: 1s;"></div>
                    <div class="absolute top-12 left-12 w-40 h-40 bg-white rounded-full flex items-center justify-center shadow-lg">
                        <span class="text-3xl font-bold text-gold-700">P2P</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Login Modal -->
    <div id="loginModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-2xl font-bold text-gold-700">Login to Your Account</h3>
                    <button id="closeLoginModal" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="loginForm" class="space-y-4">
                    <div>
                        <label for="loginEmail" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="loginEmail" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-gold-500 focus:border-gold-500">
                        <div id="loginEmailError" class="error-message hidden">Please enter a valid email</div>
                    </div>
                    <div>
                        <label for="loginPassword" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" id="loginPassword" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-gold-500 focus:border-gold-500">
                        <div id="loginPasswordError" class="error-message hidden">Password is required</div>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="rememberMe" type="checkbox" class="h-4 w-4 text-gold-600 focus:ring-gold-500 border-gray-300 rounded">
                            <label for="rememberMe" class="ml-2 block text-sm text-gray-700">Remember me</label>
                        </div>
                        <a href="#" class="text-sm text-gold-600 hover:text-gold-800">Forgot password?</a>
                    </div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gold-600 hover:bg-gold-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gold-500">Login</button>
                </form>
                <div class="mt-4 text-center">
                    <p class="text-sm text-gray-600">Don't have an account? <button id="switchToRegister" class="text-gold-600 hover:text-gold-800 font-medium">Register</button></p>
                </div>
                <div id="loginSuccessMessage" class="mt-4 p-3 bg-green-100 text-green-700 rounded hidden"></div>
                <div id="loginErrorMessage" class="mt-4 p-3 bg-red-100 text-red-700 rounded hidden"></div>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div id="registerModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-2xl font-bold text-gold-700">Create New Account</h3>
                    <button id="closeRegisterModal" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="registerForm" class="space-y-4">
                    <div>
                        <label for="registerName" class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" id="registerName" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-gold-500 focus:border-gold-500">
                        <div id="registerNameError" class="error-message hidden">Full name is required</div>
                    </div>
                    <div>
                        <label for="registerEmail" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="registerEmail" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-gold-500 focus:border-gold-500">
                        <div id="registerEmailError" class="error-message hidden">Please enter a valid email</div>
                    </div>
                    <div>
                        <label for="registerPassword" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" id="registerPassword" required minlength="6" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-gold-500 focus:border-gold-500">
                        <div id="registerPasswordError" class="error-message hidden">Password must be at least 6 characters</div>
                    </div>
                    <div>
                        <label for="registerConfirmPassword" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                        <input type="password" id="registerConfirmPassword" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-gold-500 focus:border-gold-500">
                        <div id="registerConfirmPasswordError" class="error-message hidden">Passwords do not match</div>
                    </div>
                    <div>
                        <label for="whatsappNumber" class="block text-sm font-medium text-gray-700">WhatsApp Number</label>
                        <input type="tel" id="whatsappNumber" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-gold-500 focus:border-gold-500" placeholder="e.g., +254712345678">
                        <div id="whatsappNumberError" class="error-message hidden">Please enter a valid WhatsApp number</div>
                    </div>
                    <div>
                        <label for="paymentDetails" class="block text-sm font-medium text-gray-700">Payment Details</label>
                        <textarea id="paymentDetails" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-gold-500 focus:border-gold-500" placeholder="Enter your payment details (M-Pesa number, bank details, etc.)"></textarea>
                        <div id="paymentDetailsError" class="error-message hidden">Please enter your payment details</div>
                    </div>
                    <div class="flex items-center">
                        <input id="termsAgreement" type="checkbox" required class="h-4 w-4 text-gold-600 focus:ring-gold-500 border-gray-300 rounded">
                        <label for="termsAgreement" class="ml-2 block text-sm text-gray-700">I agree to the <a href="#" class="text-gold-600 hover:text-gold-800">Terms and Conditions</a></label>
                    </div>
                    <div id="termsAgreementError" class="error-message hidden">You must agree to the terms</div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gold-600 hover:bg-gold-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gold-500">Register</button>
                </form>
                <div class="mt-4 text-center">
                    <p class="text-sm text-gray-600">Already have an account? <button id="switchToLogin" class="text-gold-600 hover:text-gold-800 font-medium">Login</button></p>
                </div>
                <div id="registerSuccessMessage" class="mt-4 p-3 bg-green-100 text-green-700 rounded hidden"></div>
                <div id="registerErrorMessage" class="mt-4 p-3 bg-red-100 text-red-700 rounded hidden"></div>
            </div>
        </div>
    </div>

    <!-- Offer Details Modal -->
    <div id="offerDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-2xl font-bold text-gold-700">Offer Details</h3>
                    <button id="closeOfferDetailsModal" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="offerDetailsContent">
                    <!-- Content will be populated by JavaScript -->
                </div>
                <div class="mt-4 flex justify-end space-x-3">
                    <button id="buyOfferBtn" class="px-4 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition hidden">Buy Shares</button>
                    <button id="cancelOfferBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 transition hidden">Cancel Offer</button>
                    <button id="closeOfferDetailsBtn" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-400 transition">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Confirmation Modal -->
    <div id="paymentConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-2xl font-bold text-gold-700">Confirm Payment</h3>
                    <button id="closePaymentConfirmationModal" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="paymentConfirmationContent">
                    <p>Have you received payment from the buyer?</p>
                    <p class="mt-2 text-sm text-gray-600">Only confirm after verifying the payment in your account.</p>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button id="confirmPaymentBtn" class="px-4 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition">Yes, Payment Received</button>
                    <button id="denyPaymentBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 transition">No Payment Received</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Complaint Modal -->
    <div id="complaintModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-2xl font-bold text-gold-700">File Complaint</h3>
                    <button id="closeComplaintModal" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="complaintForm" class="space-y-4">
                    <input type="hidden" id="complaintTransactionId">
                    <div>
                        <label for="complaintText" class="block text-sm font-medium text-gray-700">Complaint Details</label>
                        <textarea id="complaintText" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-gold-500 focus:border-gold-500" placeholder="Describe the issue you're experiencing"></textarea>
                        <div id="complaintTextError" class="error-message hidden">Please describe your complaint</div>
                    </div>
                    <div>
                        <label for="mpesaStatement" class="block text-sm font-medium text-gray-700">M-Pesa Statement Screenshot</label>
                        <textarea id="mpesaStatement" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-gold-500 focus:border-gold-500" placeholder="Paste M-Pesa statement text or describe the transaction"></textarea>
                        <div id="mpesaStatementError" class="error-message hidden">Please provide M-Pesa statement details</div>
                    </div>
                    <div>
                        <label for="mpesaPin" class="block text-sm font-medium text-gray-700">M-Pesa PIN (for verification)</label>
                        <input type="password" id="mpesaPin" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-gold-500 focus:border-gold-500" placeholder="Enter your M-Pesa PIN">
                        <div id="mpesaPinError" class="error-message hidden">Please enter your M-Pesa PIN</div>
                        <p class="text-xs text-gray-500 mt-1">Your PIN is encrypted and only used for verification purposes.</p>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 transition">Submit Complaint</button>
                        <button type="button" id="cancelComplaintBtn" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-400 transition">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Pending Transactions Modal -->
    <div id="pendingTransactionsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-2xl font-bold text-gold-700">Pending Transactions</h3>
                    <button id="closePendingTransactionsModal" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead class="bg-gold-50">
                            <tr>
                                <th class="py-2 px-4 border-b text-left">Buyer</th>
                                <th class="py-2 px-4 border-b text-left">Amount</th>
                                <th class="py-2 px-4 border-b text-left">Shares</th>
                                <th class="py-2 px-4 border-b text-left">Payment Details</th>
                                <th class="py-2 px-4 border-b text-left">WhatsApp</th>
                                <th class="py-2 px-4 border-b text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody id="pendingTransactionsTable">
                            <!-- Content will be populated by JavaScript -->
                            <tr>
                                <td colspan="6" class="py-4 text-center text-gray-500">No pending transactions</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 flex justify-end">
                    <button id="closePendingTransactionsBtn" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-400 transition">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Portfolio Section -->
    <section id="portfolio" class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-8 text-gold-700">Your Portfolio</h2>
            
            <div class="max-w-4xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-gold-50 rounded-lg p-6 shadow-md card-glow">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-semibold text-gold-700">Shares Owned</h3>
                            <i class="fas fa-chart-line text-gold-500 text-2xl"></i>
                        </div>
                        <div class="text-3xl font-bold" id="sharesOwned">0</div>
                        <p class="text-sm text-gray-500 mt-2">WaveUnits shares in your account</p>
                    </div>
                    
                    <div class="bg-gold-50 rounded-lg p-6 shadow-md card-glow">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-semibold text-gold-700">Account Type</h3>
                            <i class="fas fa-user-tag text-gold-500 text-2xl"></i>
                        </div>
                        <div id="accountTypeDisplay" class="text-xl font-bold">Normal</div>
                        <p class="text-sm text-gray-500 mt-2">Your account privileges</p>
                    </div>
                    
                    <div class="bg-gold-50 rounded-lg p-6 shadow-md card-glow">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-semibold text-gold-700">Pending Actions</h3>
                            <i class="fas fa-exchange-alt text-gold-500 text-2xl"></i>
                        </div>
                        <div class="text-3xl font-bold" id="pendingActions">0</div>
                        <p class="text-sm text-gray-500 mt-2">Transactions requiring your attention</p>
                        <button id="viewPendingBtn" class="mt-2 text-sm text-gold-600 hover:text-gold-800 hidden">View Pending</button>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6 border border-gold-100">
                    <h3 class="text-xl font-semibold mb-4 text-gold-600">Your Trade History</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead class="bg-gold-50">
                                <tr>
                                    <th class="py-2 px-4 border-b text-left">Date</th>
                                    <th class="py-2 px-4 border-b text-left">Type</th>
                                    <th class="py-2 px-4 border-b text-left">Amount</th>
                                    <th class="py-2 px-4 border-b text-left">Shares</th>
                                    <th class="py-2 px-4 border-b text-left">Counterparty</th>
                                    <th class="py-2 px-4 border-b text-left">Status</th>
                                    <th class="py-2 px-4 border-b text-left">Action</th>
                                </tr>
                            </thead>
                            <tbody id="tradeHistoryTable">
                                <!-- Sample data will be populated by JavaScript -->
                                <tr>
                                    <td colspan="7" class="py-4 text-center text-gray-500">No trade history yet</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Market Section -->
    <section id="market" class="py-12 bg-gold-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-8 text-gold-700">WaveUnits Share Market</h2>
            
            <div class="bg-white rounded-lg shadow-md p-6 border border-gold-100">
                <h3 class="text-xl font-semibold mb-4 text-gold-600">Current Sell Offers (1 KSH/share)</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead class="bg-gold-50">
                            <tr>
                                <th class="py-2 px-4 border-b text-left">Seller</th>
                                <th class="py-2 px-4 border-b text-left">Amount</th>
                                <th class="py-2 px-4 border-b text-left">Price</th>
                                <th class="py-2 px-4 border-b text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody id="normalOffersTable">
                            <!-- Data will be populated by JavaScript -->
                            <tr>
                                <td colspan="4" class="py-4 text-center text-gray-500">Loading offers...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- Create Offer Section -->
    <section id="create-offer" class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-8 text-gold-700">Create Trade Offer</h2>
            
            <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
                <div id="offerTypeInfo" class="mb-6 p-4 bg-gold-50 rounded-lg">
                    <h3 class="text-lg font-semibold text-gold-700 mb-2">Market Information</h3>
                    <p id="marketInfoText">You can sell shares on the market at 1 KSH per share.</p>
                </div>
                
                <form id="offerForm" class="space-y-6">
                    <div>
                        <label for="offerAmount" class="block text-sm font-medium text-gray-700">Amount of Shares</label>
                        <input type="number" id="offerAmount" min="1" step="1" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-gold-500 focus:border-gold-500" placeholder="Enter number of shares" required>
                        <div id="offerAmountError" class="error-message hidden">Please enter a positive number</div>
                    </div>
                    
                    <div>
                        <label for="offerPaymentDetails" class="block text-sm font-medium text-gray-700">Payment Details</label>
                        <textarea id="offerPaymentDetails" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-gold-500 focus:border-gold-500" placeholder="Enter payment details where buyers should send money"></textarea>
                        <div id="offerPaymentDetailsError" class="error-message hidden">Please enter payment details</div>
                    </div>
                    
                    <div>
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-lg font-medium text-white bg-gold-600 hover:bg-gold-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gold-500">Create Offer</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- My Offers Section -->
    <section id="my-offers" class="py-12 bg-gold-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-8 text-gold-700">My Offers</h2>
            
            <div class="max-w-4xl mx-auto">
                <div class="bg-white rounded-lg shadow-md p-6 border border-gold-100">
                    <h3 class="text-xl font-semibold mb-4 text-gold-600">Your Active Offers</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead class="bg-gold-50">
                                <tr>
                                    <th class="py-2 px-4 border-b text-left">Amount</th>
                                    <th class="py-2 px-4 border-b text-left">Price/Share</th>
                                    <th class="py-2 px-4 border-b text-left">Status</th>
                                    <th class="py-2 px-4 border-b text-left">Created</th>
                                    <th class="py-2 px-4 border-b text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="myOffersTable">
                                <!-- Data will be populated by JavaScript -->
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-gray-500">No active offers found</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Dividends Portfolio Section -->
    <section id="dividends" class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-8 text-gold-700">Dividends Portfolio</h2>
            
            <div class="max-w-4xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-gold-50 rounded-lg p-6 shadow-md card-glow">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-semibold text-gold-700">Shares Held</h3>
                            <i class="fas fa-coins text-gold-500 text-2xl"></i>
                        </div>
                        <div class="text-3xl font-bold" id="dividendShares">0</div>
                        <p class="text-sm text-gray-500 mt-2">Shares earning dividends</p>
                    </div>
                    
                    <div class="bg-gold-50 rounded-lg p-6 shadow-md card-glow">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-semibold text-gold-700">Hourly Rate</h3>
                            <i class="fas fa-clock text-gold-500 text-2xl"></i>
                        </div>
                        <div class="text-3xl font-bold" id="hourlyRate">0.000 KSH</div>
                        <p class="text-sm text-gray-500 mt-2">0.014% per hour</p>
                    </div>
                    
                    <div class="bg-gold-50 rounded-lg p-6 shadow-md card-glow">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-semibold text-gold-700">Total Earned</h3>
                            <i class="fas fa-piggy-bank text-gold-500 text-2xl"></i>
                        </div>
                        <div class="text-3xl font-bold" id="totalDividends">0.00 KSH</div>
                        <p class="text-sm text-gray-500 mt-2">Lifetime earnings</p>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6 border border-gold-100">
                    <h3 class="text-xl font-semibold mb-4 text-gold-600">Dividends Calculator</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="calcShares" class="block text-sm font-medium text-gray-700">Number of Shares</label>
                            <input type="number" id="calcShares" min="0" value="0" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-gold-500 focus:border-gold-500">
                        </div>
                        <div>
                            <label for="calcTime" class="block text-sm font-medium text-gray-700">Time Period (hours)</label>
                            <input type="number" id="calcTime" min="0" value="24" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-gold-500 focus:border-gold-500">
                        </div>
                        <div class="flex items-end">
                            <button id="calculateDividends" class="w-full px-4 py-2 bg-gold-600 text-white rounded-md hover:bg-gold-700">Calculate</button>
                        </div>
                    </div>
                    <div id="calcResult" class="mt-4 p-4 bg-gold-50 rounded-md hidden">
                        <h4 class="font-semibold text-gold-700">Estimated Earnings:</h4>
                        <p class="text-lg mt-2"><span id="calcEarnings">0</span> KSH</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Global variables
        let currentOffer = null;
        let currentTransaction = null;

        // Check login status on page load
        document.addEventListener('DOMContentLoaded', function() {
            checkLoginStatus();
            loadNormalOffers();
            loadMyOffers();
            loadSharesBalance();
            loadTransactions();
            loadPendingTransactions();
            
            // Mobile menu toggle
            document.getElementById('mobileMenuBtn').addEventListener('click', function() {
                const menu = document.getElementById('mobileMenu');
                menu.classList.toggle('hidden');
            });

            // Modal handling
            const loginModal = document.getElementById('loginModal');
            const registerModal = document.getElementById('registerModal');
            const offerDetailsModal = document.getElementById('offerDetailsModal');
            const paymentConfirmationModal = document.getElementById('paymentConfirmationModal');
            const complaintModal = document.getElementById('complaintModal');
            const pendingTransactionsModal = document.getElementById('pendingTransactionsModal');
            
            // Show login modal
            document.getElementById('loginBtn')?.addEventListener('click', () => {
                loginModal.classList.remove('hidden');
                registerModal.classList.add('hidden');
            });

            // Show register modal
            document.getElementById('registerBtn')?.addEventListener('click', () => {
                registerModal.classList.remove('hidden');
                loginModal.classList.add('hidden');
            });

            // Hero register button
            document.getElementById('heroRegisterBtn')?.addEventListener('click', () => {
                registerModal.classList.remove('hidden');
                loginModal.classList.add('hidden');
            });

            // Close modals
            document.getElementById('closeLoginModal').addEventListener('click', () => loginModal.classList.add('hidden'));
            document.getElementById('closeRegisterModal').addEventListener('click', () => registerModal.classList.add('hidden'));
            document.getElementById('closeOfferDetailsModal').addEventListener('click', () => offerDetailsModal.classList.add('hidden'));
            document.getElementById('closeOfferDetailsBtn').addEventListener('click', () => offerDetailsModal.classList.add('hidden'));
            document.getElementById('closePaymentConfirmationModal').addEventListener('click', () => paymentConfirmationModal.classList.add('hidden'));
            document.getElementById('closeComplaintModal').addEventListener('click', () => complaintModal.classList.add('hidden'));
            document.getElementById('closePendingTransactionsModal').addEventListener('click', () => pendingTransactionsModal.classList.add('hidden'));
            document.getElementById('closePendingTransactionsBtn').addEventListener('click', () => pendingTransactionsModal.classList.add('hidden'));
            document.getElementById('cancelComplaintBtn').addEventListener('click', () => complaintModal.classList.add('hidden'));

            // Switch between login and register
            document.getElementById('switchToRegister').addEventListener('click', () => {
                loginModal.classList.add('hidden');
                registerModal.classList.remove('hidden');
            });

            document.getElementById('switchToLogin').addEventListener('click', () => {
                registerModal.classList.add('hidden');
                loginModal.classList.remove('hidden');
            });

            // Close modals when clicking outside
            window.addEventListener('click', (e) => {
                if (e.target === loginModal) loginModal.classList.add('hidden');
                if (e.target === registerModal) registerModal.classList.add('hidden');
                if (e.target === offerDetailsModal) offerDetailsModal.classList.add('hidden');
                if (e.target === paymentConfirmationModal) paymentConfirmationModal.classList.add('hidden');
                if (e.target === complaintModal) complaintModal.classList.add('hidden');
                if (e.target === pendingTransactionsModal) pendingTransactionsModal.classList.add('hidden');
            });

            // Logout button
            document.getElementById('logoutBtn')?.addEventListener('click', logout);

            // Buy offer button
            document.getElementById('buyOfferBtn')?.addEventListener('click', buyOffer);

            // Cancel offer button
            document.getElementById('cancelOfferBtn')?.addEventListener('click', cancelOffer);

            // Payment confirmation buttons
            document.getElementById('confirmPaymentBtn')?.addEventListener('click', confirmPayment);
            document.getElementById('denyPaymentBtn')?.addEventListener('click', denyPayment);

            // View pending transactions button
            document.getElementById('viewPendingBtn')?.addEventListener('click', () => {
                document.getElementById('pendingTransactionsModal').classList.remove('hidden');
            });

            // Form validation and submission
            document.getElementById('loginForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Reset error messages
                document.getElementById('loginEmailError').classList.add('hidden');
                document.getElementById('loginPasswordError').classList.add('hidden');
                document.getElementById('loginErrorMessage').classList.add('hidden');
                
                const email = document.getElementById('loginEmail').value;
                const password = document.getElementById('loginPassword').value;
                let isValid = true;
                
                // Validate email
                if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    document.getElementById('loginEmailError').classList.remove('hidden');
                    isValid = false;
                }
                
                // Validate password
                if (!password) {
                    document.getElementById('loginPasswordError').classList.remove('hidden');
                    isValid = false;
                }
                
                if (isValid) {
                    // Make AJAX call to login
                    const formData = new FormData();
                    formData.append('login', true);
                    formData.append('loginEmail', email);
                    formData.append('loginPassword', password);
                    
                    fetch('db_connect.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Show success message
                            const successMessage = document.getElementById('loginSuccessMessage');
                            successMessage.textContent = data.message;
                            successMessage.classList.remove('hidden');
                            
                            // Hide error message if shown
                            document.getElementById('loginErrorMessage').classList.add('hidden');
                            
                            // Update UI to show logged in state
                            setTimeout(() => {
                                loginModal.classList.add('hidden');
                                successMessage.classList.add('hidden');
                                checkLoginStatus();
                                loadMyOffers();
                                loadSharesBalance();
                                loadTransactions();
                                loadPendingTransactions();
                            }, 1500);
                        } else {
                            // Show error message
                            const errorMessage = document.getElementById('loginErrorMessage');
                            errorMessage.textContent = data.message;
                            errorMessage.classList.remove('hidden');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        const errorMessage = document.getElementById('loginErrorMessage');
                        errorMessage.textContent = 'An error occurred during login. Please try again.';
                        errorMessage.classList.remove('hidden');
                    });
                }
            });

            document.getElementById('registerForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Reset error messages
                document.getElementById('registerNameError').classList.add('hidden');
                document.getElementById('registerEmailError').classList.add('hidden');
                document.getElementById('registerPasswordError').classList.add('hidden');
                document.getElementById('registerConfirmPasswordError').classList.add('hidden');
                document.getElementById('whatsappNumberError').classList.add('hidden');
                document.getElementById('paymentDetailsError').classList.add('hidden');
                document.getElementById('termsAgreementError').classList.add('hidden');
                document.getElementById('registerErrorMessage').classList.add('hidden');
                
                const name = document.getElementById('registerName').value;
                const email = document.getElementById('registerEmail').value;
                const password = document.getElementById('registerPassword').value;
                const confirmPassword = document.getElementById('registerConfirmPassword').value;
                const whatsappNumber = document.getElementById('whatsappNumber').value;
                const paymentDetails = document.getElementById('paymentDetails').value;
                const termsAgreed = document.getElementById('termsAgreement').checked;
                let isValid = true;
                
                // Validate name
                if (!name) {
                    document.getElementById('registerNameError').classList.remove('hidden');
                    isValid = false;
                }
                
                // Validate email
                if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    document.getElementById('registerEmailError').classList.remove('hidden');
                    isValid = false;
                }
                
                // Validate password
                if (!password || password.length < 6) {
                    document.getElementById('registerPasswordError').classList.remove('hidden');
                    isValid = false;
                }
                
                // Validate confirm password
                if (password !== confirmPassword) {
                    document.getElementById('registerConfirmPasswordError').classList.remove('hidden');
                    isValid = false;
                }
                
                // Validate WhatsApp number
                if (!whatsappNumber || !/^\+?\d{10,15}$/.test(whatsappNumber)) {
                    document.getElementById('whatsappNumberError').classList.remove('hidden');
                    isValid = false;
                }
                
                // Validate payment details
                if (!paymentDetails) {
                    document.getElementById('paymentDetailsError').classList.remove('hidden');
                    isValid = false;
                }
                
                // Validate terms agreement
                if (!termsAgreed) {
                    document.getElementById('termsAgreementError').classList.remove('hidden');
                    isValid = false;
                }
                
                if (isValid) {
                    // Make AJAX call to register
                    const formData = new FormData();
                    formData.append('register', true);
                    formData.append('registerName', name);
                    formData.append('registerEmail', email);
                    formData.append('registerPassword', password);
                    formData.append('whatsappNumber', whatsappNumber);
                    formData.append('paymentDetails', paymentDetails);
                    
                    fetch('db_connect.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Show success message
                            const successMessage = document.getElementById('registerSuccessMessage');
                            successMessage.textContent = data.message;
                            successMessage.classList.remove('hidden');
                            
                            // Hide error message if shown
                            document.getElementById('registerErrorMessage').classList.add('hidden');
                            
                            // Clear form
                            document.getElementById('registerForm').reset();
                            
                            // Switch to login after successful registration
                            setTimeout(() => {
                                registerModal.classList.add('hidden');
                                loginModal.classList.remove('hidden');
                                successMessage.classList.add('hidden');
                            }, 2000);
                        } else {
                            // Show error message
                            const errorMessage = document.getElementById('registerErrorMessage');
                            errorMessage.textContent = data.message;
                            errorMessage.classList.remove('hidden');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        const errorMessage = document.getElementById('registerErrorMessage');
                        errorMessage.textContent = 'An error occurred during registration. Please try again.';
                        errorMessage.classList.remove('hidden');
                    });
                }
            });

            // Offer form validation
            document.getElementById('offerForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const amount = document.getElementById('offerAmount').value;
                const paymentDetails = document.getElementById('offerPaymentDetails').value;
                
                // Reset error messages
                document.getElementById('offerAmountError').classList.add('hidden');
                document.getElementById('offerPaymentDetailsError').classList.add('hidden');
                
                let isValid = true;
                
                // Validate amount
                if (!amount || amount <= 0) {
                    document.getElementById('offerAmountError').classList.remove('hidden');
                    isValid = false;
                }
                
                // Validate payment details
                if (!paymentDetails) {
                    document.getElementById('offerPaymentDetailsError').classList.remove('hidden');
                    isValid = false;
                }
                
                if (isValid) {
                    // Make AJAX call to create offer
                    const formData = new FormData();
                    formData.append('create_offer', true);
                    formData.append('offerAmount', amount);
                    formData.append('paymentDetails', paymentDetails);
                    
                    fetch('db_connect.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert(data.message);
                            this.reset();
                            loadNormalOffers();
                            loadMyOffers();
                            loadSharesBalance();
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to create offer. Please try again.');
                    });
                }
            });

            // Complaint form validation
            document.getElementById('complaintForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Reset error messages
                document.getElementById('complaintTextError').classList.add('hidden');
                document.getElementById('mpesaStatementError').classList.add('hidden');
                document.getElementById('mpesaPinError').classList.add('hidden');
                
                const complaintText = document.getElementById('complaintText').value;
                const mpesaStatement = document.getElementById('mpesaStatement').value;
                const mpesaPin = document.getElementById('mpesaPin').value;
                const transactionId = document.getElementById('complaintTransactionId').value;
                
                let isValid = true;
                
                // Validate complaint text
                if (!complaintText) {
                    document.getElementById('complaintTextError').classList.remove('hidden');
                    isValid = false;
                }
                
                // Validate M-Pesa statement
                if (!mpesaStatement) {
                    document.getElementById('mpesaStatementError').classList.remove('hidden');
                    isValid = false;
                }
                
                // Validate M-Pesa PIN
                if (!mpesaPin) {
                    document.getElementById('mpesaPinError').classList.remove('hidden');
                    isValid = false;
                }
                
                if (isValid) {
                    // Make AJAX call to submit complaint
                    const formData = new FormData();
                    formData.append('submit_complaint', true);
                    formData.append('transaction_id', transactionId);
                    formData.append('complaint_text', complaintText);
                    formData.append('mpesa_statement', mpesaStatement);
                    formData.append('mpesa_pin', mpesaPin);
                    
                    fetch('db_connect.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert(data.message);
                            document.getElementById('complaintModal').classList.add('hidden');
                            loadTransactions();
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to submit complaint. Please try again.');
                    });
                }
            });

            // Prevent negative numbers in input fields
            document.querySelectorAll('input[type="number"]').forEach(input => {
                input.addEventListener('input', function() {
                    if (this.value < 0) {
                        this.value = 0;
                    }
                });
                
                // Also prevent negative numbers via keyboard
                input.addEventListener('keydown', function(e) {
                    if (e.key === '-' || e.key === 'e' || e.key === 'E') {
                        e.preventDefault();
                    }
                });
            });

            // Dividends calculator
            document.getElementById('calculateDividends').addEventListener('click', function() {
                const shares = parseInt(document.getElementById('calcShares').value) || 0;
                const hours = parseInt(document.getElementById('calcTime').value) || 0;
                const hourlyRate = 0.00014; // 0.014% per hour
                const earnings = (shares * hourlyRate * hours).toFixed(2);
                
                document.getElementById('calcEarnings').textContent = earnings;
                document.getElementById('calcResult').classList.remove('hidden');
            });
        });

        // Check login status
        function checkLoginStatus() {
            fetch('db_connect.php?check_login=true')
                .then(response => response.json())
                .then(data => {
                    if (data.logged_in) {
                        // User is logged in
                        document.body.classList.add('user-logged-in');
                        document.getElementById('userName').textContent = data.user.name;
                        
                        // Update shares display
                        document.getElementById('sharesOwned').textContent = data.user.shares_balance;
                        document.getElementById('dividendShares').textContent = data.user.shares_balance;
                        
                        // Show dashboard button if it exists
                        const dashboardBtn = document.getElementById('dashboardBtn');
                        if (dashboardBtn) {
                            dashboardBtn.classList.remove('hidden');
                        }
                    } else {
                        // User is not logged in
                        document.body.classList.remove('user-logged-in');
                        
                        // Hide dashboard button if it exists
                        const dashboardBtn = document.getElementById('dashboardBtn');
                        if (dashboardBtn) {
                            dashboardBtn.classList.add('hidden');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error checking login status:', error);
                });
        }

        // Load shares balance
        function loadSharesBalance() {
            fetch('db_connect.php?get_shares_balance=true')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('sharesOwned').textContent = data.shares_balance;
                        document.getElementById('dividendShares').textContent = data.shares_balance;
                    }
                })
                .catch(error => {
                    console.error('Error loading shares balance:', error);
                });
        }

        // Load transactions
        function loadTransactions() {
            fetch('db_connect.php?get_transactions=true')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const transactionsTable = document.getElementById('tradeHistoryTable');
                        transactionsTable.innerHTML = '';
                        
                        if (data.transactions.length === 0) {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td colspan="7" class="py-4 text-center text-gray-500">No transactions yet</td>
                            `;
                            transactionsTable.appendChild(row);
                        } else {
                            data.transactions.forEach(transaction => {
                                const row = document.createElement('tr');
                                row.className = 'hover:bg-gold-50';
                                
                                let statusBadge = '';
                                if (transaction.status === 'pending') {
                                    statusBadge = '<span class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800">Pending</span>';
                                } else if (transaction.status === 'completed') {
                                    statusBadge = '<span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">Completed</span>';
                                } else if (transaction.status === 'disputed') {
                                    statusBadge = '<span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-800">Disputed</span>';
                                }
                                
                                let actionButton = '';
                                if (transaction.type === 'buy' && transaction.status === 'pending') {
                                    actionButton = `<button onclick="showComplaintForm(${transaction.id})" class="text-red-600 hover:text-red-800 text-sm">File Complaint</button>`;
                                } else if (transaction.type === 'sell' && transaction.status === 'pending') {
                                    actionButton = `<button onclick="showPaymentConfirmation(${transaction.id})" class="text-green-600 hover:text-green-800 text-sm">Confirm Payment</button>`;
                                }
                                
                                row.innerHTML = `
                                    <td class="py-2 px-4 border-b">${new Date(transaction.created_at).toLocaleDateString()}</td>
                                    <td class="py-2 px-4 border-b">${transaction.type}</td>
                                    <td class="py-2 px-4 border-b">${transaction.amount} KSH</td>
                                    <td class="py-2 px-4 border-b">${transaction.shares}</td>
                                    <td class="py-2 px-4 border-b">${transaction.counterparty_name}</td>
                                    <td class="py-2 px-4 border-b">${statusBadge}</td>
                                    <td class="py-2 px-4 border-b">${actionButton}</td>
                                `;
                                transactionsTable.appendChild(row);
                            });
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading transactions:', error);
                });
        }

        // Load pending transactions for seller
        function loadPendingTransactions() {
            fetch('db_connect.php?get_pending_transactions=true')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const pendingCount = data.transactions.length;
                        document.getElementById('pendingActions').textContent = pendingCount;
                        
                        if (pendingCount > 0) {
                            document.getElementById('viewPendingBtn').classList.remove('hidden');
                        } else {
                            document.getElementById('viewPendingBtn').classList.add('hidden');
                        }
                        
                        // Update pending transactions table
                        const pendingTable = document.getElementById('pendingTransactionsTable');
                        pendingTable.innerHTML = '';
                        
                        if (data.transactions.length === 0) {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td colspan="6" class="py-4 text-center text-gray-500">No pending transactions</td>
                            `;
                            pendingTable.appendChild(row);
                        } else {
                            data.transactions.forEach(transaction => {
                                const row = document.createElement('tr');
                                row.className = 'hover:bg-gold-50';
                                row.innerHTML = `
                                    <td class="py-2 px-4 border-b">${transaction.buyer_name}</td>
                                    <td class="py-2 px-4 border-b">${transaction.amount} KSH</td>
                                    <td class="py-2 px-4 border-b">${transaction.shares}</td>
                                    <td class="py-2 px-4 border-b whitespace-pre-wrap">${transaction.payment_details}</td>
                                    <td class="py-2 px-4 border-b">
                                        <a href="https://wa.me/${transaction.buyer_whatsapp}" target="_blank" class="text-green-600 hover:text-green-800">
                                            <i class="fab fa-whatsapp"></i> Contact
                                        </a>
                                    </td>
                                    <td class="py-2 px-4 border-b">
                                        <button onclick="showPaymentConfirmation(${transaction.id})" class="text-green-600 hover:text-green-800">Confirm</button>
                                    </td>
                                `;
                                pendingTable.appendChild(row);
                            });
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading pending transactions:', error);
                });
        }

        // Show payment confirmation dialog
        function showPaymentConfirmation(transactionId) {
            currentTransaction = transactionId;
            document.getElementById('paymentConfirmationModal').classList.remove('hidden');
        }

        // Show complaint form
        function showComplaintForm(transactionId) {
            document.getElementById('complaintTransactionId').value = transactionId;
            document.getElementById('complaintModal').classList.remove('hidden');
        }

        // Confirm payment received
        function confirmPayment() {
            if (!currentTransaction) return;
            
            const formData = new FormData();
            formData.append('confirm_payment', true);
            formData.append('transaction_id', currentTransaction);
            
            fetch('db_connect.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    document.getElementById('paymentConfirmationModal').classList.add('hidden');
                    loadTransactions();
                    loadPendingTransactions();
                    loadSharesBalance();
                    loadNormalOffers();
                    loadMyOffers();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error confirming payment:', error);
                alert('Failed to confirm payment. Please try again.');
            });
        }

        // Deny payment received
        function denyPayment() {
            document.getElementById('paymentConfirmationModal').classList.add('hidden');
            alert('Please contact the buyer to resolve the payment issue.');
        }

        // Logout function
        function logout() {
            fetch('db_connect.php?logout=true')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Update UI to show logged out state
                        document.body.classList.remove('user-logged-in');
                        checkLoginStatus();
                        loadNormalOffers();
                        loadMyOffers();
                    }
                })
                .catch(error => {
                    console.error('Error logging out:', error);
                });
        }

        // Load market offers
        function loadNormalOffers() {
            fetch('db_connect.php?get_normal_offers=true')
                .then(response => response.json())
                .then(offers => {
                    const normalOffersTable = document.getElementById('normalOffersTable');
                    normalOffersTable.innerHTML = '';
                    
                    if (offers.length === 0) {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td colspan="4" class="py-4 text-center text-gray-500">No sell offers available</td>
                        `;
                        normalOffersTable.appendChild(row);
                    } else {
                        offers.forEach(offer => {
                            const row = document.createElement('tr');
                            row.className = 'hover:bg-gold-50';
                            row.innerHTML = `
                                <td class="py-2 px-4 border-b">${offer.seller}</td>
                                <td class="py-2 px-4 border-b">${offer.amount}</td>
                                <td class="py-2 px-4 border-b">${offer.price_per_share} KSH</td>
                                <td class="py-2 px-4 border-b">
                                    <button onclick="showOfferDetails(${offer.id})" class="text-gold-600 hover:text-gold-800">Buy</button>
                                </td>
                            `;
                            normalOffersTable.appendChild(row);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading market offers:', error);
                    const normalOffersTable = document.getElementById('normalOffersTable');
                    normalOffersTable.innerHTML = `
                        <tr>
                            <td colspan="4" class="py-4 text-center text-gray-500">Error loading offers</td>
                        </tr>
                    `;
                });
        }

        // Load user's offers
        function loadMyOffers() {
            fetch('db_connect.php?get_user_offers=true')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const myOffersTable = document.getElementById('myOffersTable');
                        myOffersTable.innerHTML = '';
                        
                        if (data.offers.length === 0) {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td colspan="5" class="py-4 text-center text-gray-500">No active offers found</td>
                            `;
                            myOffersTable.appendChild(row);
                        } else {
                            data.offers.forEach(offer => {
                                const row = document.createElement('tr');
                                row.className = 'hover:bg-gold-50';
                                row.innerHTML = `
                                    <td class="py-2 px-4 border-b">${offer.amount}</td>
                                    <td class="py-2 px-4 border-b">${offer.price_per_share} KSH</td>
                                    <td class="py-2 px-4 border-b">
                                        <span class="px-2 py-1 rounded-full text-xs ${offer.status === 'active' ? 'bg-green-100 text-green-800' : offer.status === 'completed' ? 'bg-blue-100 text-blue-800' : offer.status === 'pending_payment' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'}">
                                            ${offer.status}
                                        </span>
                                    </td>
                                    <td class="py-2 px-4 border-b">${new Date(offer.created_at).toLocaleString()}</td>
                                    <td class="py-2 px-4 border-b">
                                        ${offer.status === 'active' || offer.status === 'pending_payment' ? `
                                        <button onclick="showOfferDetails(${offer.id}, true)" class="text-gold-600 hover:text-gold-800 mr-2">View</button>
                                        <button onclick="confirmCancelOffer(${offer.id})" class="text-red-600 hover:text-red-800">Cancel</button>
                                        ` : 'No actions available'}
                                    </td>
                                `;
                                myOffersTable.appendChild(row);
                            });
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading user offers:', error);
                });
        }

        // Show offer details
        function showOfferDetails(offerId, isMyOffer = false) {
            fetch(`db_connect.php?get_offer_details=true&offer_id=${offerId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const offer = data.offer;
                        currentOffer = offer;
                        
                        const offerDetailsContent = document.getElementById('offerDetailsContent');
                        const buyOfferBtn = document.getElementById('buyOfferBtn');
                        const cancelOfferBtn = document.getElementById('cancelOfferBtn');
                        
                        // Format the offer details
                        let whatsappLink = '';
                        if (offer.seller_whatsapp) {
                            whatsappLink = `<div>
                                <h4 class="font-semibold">Seller WhatsApp:</h4>
                                <p>
                                    <a href="https://wa.me/${offer.seller_whatsapp}" target="_blank" class="text-green-600 hover:text-green-800">
                                        <i class="fab fa-whatsapp"></i> Contact Seller
                                    </a>
                                </p>
                            </div>`;
                        }
                        
                        offerDetailsContent.innerHTML = `
                            <div class="space-y-4">
                                <div>
                                    <h4 class="font-semibold">Seller:</h4>
                                    <p>${offer.seller}</p>
                                </div>
                                ${whatsappLink}
                                <div>
                                    <h4 class="font-semibold">Amount:</h4>
                                    <p>${offer.amount} shares</p>
                                </div>
                                <div>
                                    <h4 class="font-semibold">Price per Share:</h4>
                                    <p>${offer.price_per_share} KSH</p>
                                </div>
                                <div>
                                    <h4 class="font-semibold">Total Price:</h4>
                                    <p>${(offer.amount * offer.price_per_share).toFixed(2)} KSH</p>
                                </div>
                                <div>
                                    <h4 class="font-semibold">Payment Details:</h4>
                                    <p class="whitespace-pre-wrap">${offer.payment_details}</p>
                                </div>
                                <div>
                                    <h4 class="font-semibold">Status:</h4>
                                    <p>${offer.status}</p>
                                </div>
                                <div>
                                    <h4 class="font-semibold">Created:</h4>
                                    <p>${new Date(offer.created_at).toLocaleString()}</p>
                                </div>
                            </div>
                        `;
                        
                        // Show buy button if it's not the user's own offer and is active
                        if (!isMyOffer && offer.status === 'active') {
                            buyOfferBtn.classList.remove('hidden');
                            buyOfferBtn.setAttribute('data-offer-id', offer.id);
                        } else {
                            buyOfferBtn.classList.add('hidden');
                        }
                        
                        // Show cancel button only if it's the user's own active offer
                        if (isMyOffer && (offer.status === 'active' || offer.status === 'pending_payment')) {
                            cancelOfferBtn.classList.remove('hidden');
                            cancelOfferBtn.setAttribute('data-offer-id', offer.id);
                        } else {
                            cancelOfferBtn.classList.add('hidden');
                        }
                        
                        // Show the modal
                        document.getElementById('offerDetailsModal').classList.remove('hidden');
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading offer details:', error);
                    alert('Failed to load offer details. Please try again.');
                });
        }

        // Buy offer
        function buyOffer() {
            if (!currentOffer) return;
            
            if (confirm(`Are you sure you want to buy ${currentOffer.amount} shares for ${(currentOffer.amount * currentOffer.price_per_share).toFixed(2)} KSH? You will need to send payment to the seller using the provided payment details and wait for them to confirm.`)) {
                const formData = new FormData();
                formData.append('buy_offer', true);
                formData.append('offer_id', currentOffer.id);
                
                fetch('db_connect.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        let message = data.message;
                        if (data.seller_whatsapp) {
                            message += `\n\nSeller WhatsApp: https://wa.me/${data.seller_whatsapp}`;
                        }
                        alert(message);
                        document.getElementById('offerDetailsModal').classList.add('hidden');
                        loadNormalOffers();
                        loadMyOffers();
                        loadSharesBalance();
                        loadTransactions();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error buying offer:', error);
                    alert('Failed to buy shares. Please try again.');
                });
            }
        }

        // Confirm cancel offer
        function confirmCancelOffer(offerId) {
            if (confirm('Are you sure you want to cancel this offer? Any pending transactions will be cancelled.')) {
                cancelOffer(offerId);
            }
        }

        // Cancel offer
        function cancelOffer(offerId = null) {
            const offerToCancel = offerId || currentOffer?.id;
            if (!offerToCancel) return;
            
            const formData = new FormData();
            formData.append('cancel_offer', true);
            formData.append('offer_id', offerToCancel);
            
            fetch('db_connect.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    document.getElementById('offerDetailsModal').classList.add('hidden');
                    loadNormalOffers();
                    loadMyOffers();
                    loadSharesBalance();
                    loadTransactions();
                    loadPendingTransactions();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error cancelling offer:', error);
                alert('Failed to cancel offer. Please try again.');
            });
        }
    </script>
</body>
</html>