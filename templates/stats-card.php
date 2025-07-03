<div class="stat-card">
    <div class="stat-icon <?= htmlspecialchars($type) ?>">
        <i class="<?= htmlspecialchars($icon) ?>"></i>
    </div>
    <div class="stat-content">
        <div class="stat-label">
            <span class="status-text"><?= htmlspecialchars($title) ?></span>
            <span class="total-text"><?= htmlspecialchars($subtitle) ?></span>
        </div>
        <div class="stat-number <?= htmlspecialchars($type) ?>">
            <?= htmlspecialchars($value) ?>
        </div>
    </div>
</div>
