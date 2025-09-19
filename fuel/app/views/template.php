<!DOCTYPE html>
<html>

<head>
  <?php

  use Fuel\Core\Asset;
  use Fuel\Core\Security;
  use Fuel\Core\Config;
  ?>
  <?= Asset::js('knockout-3.5.1.js'); ?>
  <?= Asset::css(['header.css']); ?>
</head>

<body>
  <header>
    <?= $header; ?>
  </header>
  <div id="content">
    <?= $content; ?>
  </div>
</body>

<script>
  window.CSRF_TOKEN = <?= json_encode(Security::fetch_token()) ?>;
  window.CSRF_KEY = <?= json_encode(Config::get('security.csrf_token_key', 'fuel_csrf_token')) ?>;
</script>

</html>
