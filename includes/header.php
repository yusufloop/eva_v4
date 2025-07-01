
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'EVA System'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS for EVA theme -->
    <style>
        body {
            background: linear-gradient(135deg, #4285f4 0%, #1976d2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 0 20px 20px 0;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            min-height: calc(100vh - 40px);
            margin: 20px 0 20px 20px;
        }
        
        .main-content {
            padding: 40px 10px;
        }
        
        .eva-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .eva-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link {
            color: #666;
            padding: 12px 20px;
            border-radius: 0;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #1976d2;
            background: linear-gradient(90deg, rgba(25, 118, 210, 0.1), transparent);
        }
        
        .sidebar .nav-link.active::after {
            content: '';
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: #1976d2;
        }
        
        .page-title {
            color: white;
            font-weight: 600;
            margin-bottom: 0;
        }
        
        .breadcrumb-eva {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
        }
        
        .stat-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        
        .stat-icon.online { background: linear-gradient(135deg, #28a745, #20c997); }
        .stat-icon.offline { background: linear-gradient(135deg, #dc3545, #e83e8c); }
        .stat-icon.total { background: linear-gradient(135deg, #007bff, #6f42c1); }
        .stat-icon.warning { background: linear-gradient(135deg, #ffc107, #fd7e14); }
        
        .eva-logo {
            font-size: 24px;
            font-weight: bold;
            color: #1976d2;
        }
        
        .eva-icon {
            width: 32px;
            height: 32px;
            background: #1976d2;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 12px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                border-radius: 0;
                margin: 0;
                min-height: auto;
            }
            .main-content {
                padding: 20px 15px;
            }
        }
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




?>

<?php
// Set global variables for components
$isAdmin = isset($_SESSION['admin_username']) || (isset($_SESSION['user_data']) && $_SESSION['user_data']['IsAdmin'] == 1);
$username = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 
           (isset($_SESSION['user_data']) ? $_SESSION['user_data']['Email'] : '');
?>