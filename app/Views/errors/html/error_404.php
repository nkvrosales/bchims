<?php
$heading = $heading ?? '404 Page Not Found';
$isLoggedIn = function_exists('session') && session()->get('logged_in');
$dashboardUrl = $isLoggedIn ? base_url('dashboard') : base_url('auth/login');
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>404 Page Not Found</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    background: #f8fafc;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Inter', sans-serif;
    padding: 1.5rem;
}
.error-container {
    text-align: center;
    max-width: 480px;
}
.error-code {
    font-family: 'Outfit', sans-serif;
    font-size: 8rem;
    font-weight: 700;
    background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    line-height: 1;
    margin-bottom: 0.5rem;
}
.error-title {
    font-family: 'Outfit', sans-serif;
    font-size: 1.5rem;
    font-weight: 600;
    color: #0f172a;
    margin-bottom: 0.5rem;
}
.error-message {
    font-size: 1rem;
    color: #64748b;
    line-height: 1.6;
    margin-bottom: 2rem;
}
.btn-dashboard {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #fff;
    text-decoration: none;
    font-family: 'Inter', sans-serif;
    font-size: 0.95rem;
    font-weight: 600;
    padding: 0.8rem 1.8rem;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    transition: background 0.2s, box-shadow 0.2s;
    box-shadow: 0 4px 14px rgba(16,185,129,0.3);
}
.btn-dashboard:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    box-shadow: 0 4px 14px rgba(16,185,129,0.3);
}
.btn-dashboard:active {
    transform: translateY(0);
}
</style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">404</div>
        <h1 class="error-title">Page Not Found</h1>
        <p class="error-message">Sorry, the page you're looking for doesn't exist or has been moved.</p>
        <a href="<?php echo $dashboardUrl; ?>" class="btn-dashboard">
            Go to Dashboard
        </a>
    </div>
</body>
</html>
