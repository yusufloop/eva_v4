<?php

include 'includes/headers.php';
?>
 <div class="dashboard-layout">
        <!-- Sidebar -->
        <?= $this->insert('sidebar', [
            'userRole' => $userRole ?? 'user',
            'username' => $username ?? '',
            'currentPage' => $currentPage ?? ''
        ]) ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Topbar -->
            <?= $this->insert('topbar', [
                'username' => $username ?? '',
                'userRole' => $userRole ?? 'user'
            ]) ?>
            
            <!-- Alert Messages -->
            <?= $this->insert('components/alerts') ?>
            
            <!-- Page Content -->
            <?= $content ?>
        </div>
    </div>

<?php include 'includes/footer.php';