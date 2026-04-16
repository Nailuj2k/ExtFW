<script>
document.addEventListener('DOMContentLoaded', function () {
    Telegram.init({
        ajaxUrl: '<?= Vars::mkUrl(MODULE, 'ajax') ?>',
        userId: <?= (int)($_SESSION['userid'] ?? 0) ?>,
        isConfigured: <?= TelegramBot::isConfigured() ? 'true' : 'false' ?>
    });
});
</script>
