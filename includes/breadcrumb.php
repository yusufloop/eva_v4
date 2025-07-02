<div class="breadcrumb-container">
    <nav class="eva-breadcrumb">
        <ol class="breadcrumb-list">
            <?php if (isset($breadcrumbs) && is_array($breadcrumbs)): ?>
                <?php foreach ($breadcrumbs as $index => $crumb): ?>
                    <li class="breadcrumb-item <?php echo $index === count($breadcrumbs) - 1 ? 'active' : ''; ?>">
                        <?php if ($index === count($breadcrumbs) - 1): ?>
                            <span><?php echo htmlspecialchars($crumb['title']); ?></span>
                        <?php else: ?>
                            <a href="<?php echo htmlspecialchars($crumb['url']); ?>">
                                <?php echo htmlspecialchars($crumb['title']); ?>
                            </a>
                            <i class="bi bi-chevron-right separator"></i>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="breadcrumb-item">
                    <a href="../pages/dashboard.php">
                        <i class="bi bi-house"></i>
                        Dashboard
                    </a>
                    <?php if (isset($pageTitle) && $pageTitle !== 'Dashboard'): ?>
                        <i class="bi bi-chevron-right separator"></i>
                    <?php endif; ?>
                </li>
                <?php if (isset($pageTitle) && $pageTitle !== 'Dashboard'): ?>
                    <li class="breadcrumb-item active">
                        <span><?php echo htmlspecialchars($pageTitle); ?></span>
                    </li>
                <?php endif; ?>
            <?php endif; ?>
        </ol>
    </nav>
</div>

<style>
.breadcrumb-container {
    margin-bottom: 20px;
}

.eva-breadcrumb {
    background: var(--eva-content-bg);
    backdrop-filter: blur(15px);
    border-radius: 12px;
    padding: 12px 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.breadcrumb-list {
    display: flex;
    align-items: center;
    margin: 0;
    padding: 0;
    list-style: none;
    gap: 8px;
}

.breadcrumb-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.breadcrumb-item a {
    color: var(--eva-primary);
    text-decoration: none;
    font-weight: 500;
    transition: var(--eva-transition);
    display: flex;
    align-items: center;
    gap: 6px;
}

.breadcrumb-item a:hover {
    color: var(--eva-primary-dark);
}

.breadcrumb-item.active span {
    color: var(--eva-secondary);
    font-weight: 500;
}

.separator {
    color: var(--eva-secondary);
    font-size: 12px;
}
</style>