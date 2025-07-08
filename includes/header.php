
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'EVA - Emergency Voice Alert System'; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/images/eva-favicon.ico">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Core EVA Styles -->
    <style>
        :root {
            --eva-primary: #4285f4;
            --eva-primary-dark: #1976d2;
            --eva-secondary: #6c757d;
            --eva-success: #28a745;
            --eva-danger: #dc3545;
            --eva-warning: #ffc107;
            --eva-info: #17a2b8;
            --eva-light: #f8f9fa;
            --eva-dark: #343a40;
            --eva-gradient: linear-gradient(135deg, #4285f4 0%, #1976d2 100%);
            --eva-sidebar-bg: rgba(255, 255, 255, 0.98);
            --eva-content-bg: rgba(255, 255, 255, 0.95);
            --eva-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            --eva-shadow-hover: 0 12px 40px rgba(0, 0, 0, 0.15);
            --eva-border-radius: 16px;
            --eva-transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            box-sizing: border-box;
        }

        body {
            background: var(--eva-gradient);
            min-height: 100vh;
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .dashboard-layout {
    display: flex;
    min-height: 100vh;
    background: #fff;
    font-family: 'Inter', sans-serif;
    position: relative;
}

.dashboard-layout, .main-content {
    margin-top: 70px; /* Adjust this value to match your topbar height */
}

.dashboard-layout::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 300px; /* Adjust height as needed */
    /* background-image: url('/assets/images/top-bg.png'); */
    background-repeat: no-repeat;
    background-position: top center;
    background-size: cover; /* or contain, depending on your preference */
    z-index: 0; /* Put it behind the content */
}
        .main-content {
            flex: 1;
            padding: 20px;
            margin-left: 280px;
            transition: var(--eva-transition);
            position: relative;
        }

        /* Page Header */
        .page-header {
            background: var(--eva-content-bg);
            backdrop-filter: blur(15px);
            border-radius: var(--eva-border-radius);
            padding: 25px 30px;
            margin-bottom: 25px;
            box-shadow: var(--eva-shadow);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .header-content {
            
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .page-title {
            color: var(--eva-primary-dark);
            font-weight: 700;
            font-size: 28px;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .breadcrumb-eva {
            color: var(--eva-secondary);
            font-size: 14px;
            margin: 5px 0 0 0;
            font-weight: 400;
        }

        .header-actions {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        /* EVA Cards */
        .eva-card {
            background: var(--eva-content-bg);
            backdrop-filter: blur(15px);
            border: none;
            border-radius: var(--eva-border-radius);
            box-shadow: var(--eva-shadow);
            transition: var(--eva-transition);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .eva-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--eva-shadow-hover);
        }

        .eva-card .card-body {
            padding: 25px;
        }

        /* Buttons */
        .btn-eva {
            border-radius: 12px;
            font-weight: 600;
            padding: 12px 24px;
            transition: var(--eva-transition);
            border: none;
            font-size: 14px;
            letter-spacing: 0.2px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-eva:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-eva-primary {
            background: var(--eva-gradient);
            color: white;
        }

        .btn-eva-secondary {
            background: var(--eva-secondary);
            color: white;
        }

        .btn-eva-success {
            background: var(--eva-success);
            color: white;
        }

        .btn-eva-danger {
            background: var(--eva-danger);
            color: white;
        }

        .btn-eva-warning {
            background: var(--eva-warning);
            color: var(--eva-dark);
        }

        /* Form Elements */
        .form-control-eva {
            border: 2px solid rgba(66, 133, 244, 0.1);
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 14px;
            transition: var(--eva-transition);
            background: white;
        }

        .form-control-eva:focus {
            border-color: var(--eva-primary);
            box-shadow: 0 0 0 0.2rem rgba(66, 133, 244, 0.15);
        }

        /* Status Indicators */
        .status-online {
            color: var(--eva-success);
        }

        .status-offline {
            color: var(--eva-danger);
        }

        .status-warning {
            color: var(--eva-warning);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .page-header {
                padding: 20px;
                margin-bottom: 20px;
            }

            .page-title {
                font-size: 24px;
            }

            .header-content {
                flex-direction: column;
                align-items: flex-start;
            }

            .header-actions {
                width: 100%;
                justify-content: flex-start;
            }
        }

        /* Loading Animation */
        .eva-loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(66, 133, 244, 0.3);
            border-radius: 50%;
            border-top-color: var(--eva-primary);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Utility Classes */
        .text-eva-primary { color: var(--eva-primary) !important; }
        .text-eva-secondary { color: var(--eva-secondary) !important; }
        .bg-eva-light { background-color: var(--eva-light) !important; }
        .shadow-eva { box-shadow: var(--eva-shadow) !important; }
        .rounded-eva { border-radius: var(--eva-border-radius) !important; }
    </style>
    
    <!-- Additional CSS -->
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>" type="text/css">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>

<?php

require_once '../helpers/auth_helper.php';
// Set global variables for components
$isAdmin = isset($_SESSION['Email']) || (isset($_SESSION['Email']) && $_SESSION['Email']['IsAdmin'] == 1);

$currentUser = getCurrentUserEmail();
$userId = getCurrentUserID();

?>