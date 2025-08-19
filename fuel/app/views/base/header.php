<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Header</title>

  <?php echo Asset::css('header.css'); ?>
  <?php echo Asset::js('knockout-3.5.1.js'); ?>
</head>

<header class="main-header">
  <div class="header-container">
    <div class="logo">
      <a href="/">個人開発物投稿プラットフォーム</a>
    </div>
    <nav class="main-nav">
      <a href="/login" class="nav-link">ログイン</a>
      <a href="/register" class="nav-link">新規登録</a>
    </nav>
  </div>
</header>

<body>
</body>

</html>
