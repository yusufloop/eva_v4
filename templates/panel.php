<div class="dashboard-panel">
    <div class="panel-header">
        <h2 class="panel-title">
            <i class="<?= htmlspecialchars($icon ?? 'fas fa-circle') ?>"></i>
            <?= htmlspecialchars($title) ?>
        </h2>
        <?php if (isset($headerActions) && !empty($headerActions)): ?>
            <div class="panel-actions">
                <?= $headerActions ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="panel-content">
        <?= $content ?>
    </div>
</div>