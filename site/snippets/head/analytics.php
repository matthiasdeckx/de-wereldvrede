<?php if ($site->google_analytics_id()->isNotEmpty() || $site->meta_pixel()->isNotEmpty()): ?>
<script>
  window.__analyticsConfig = {
    gaId: <?= json_encode($site->google_analytics_id()->value()) ?>,
    metaPixel: <?= json_encode($site->meta_pixel()->value()) ?>
  };
</script>
<?php endif ?>
