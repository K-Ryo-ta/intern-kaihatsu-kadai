<!DOCTYPE html>
<html>

<head>
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

</html>
