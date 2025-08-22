<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Edit-Portfolios</title>
  <?= Asset::css('edit.css'); ?>
</head>

<body>
  <?php function h($s)
  {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
  } ?>
  <div class="edit-container">
    <h1>投稿を編集</h1>

    <?php if (!empty($errors)): ?>
      <div style="color:#c00;margin-bottom:12px;">
        <?php foreach ($errors as $m): ?><div><?= h($m) ?></div><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" action="<?= \Uri::current(); ?>" enctype="multipart/form-data">
      <div class="form-group">
        <label>タイトル</label>
        <input type="text" name="title" value="<?= h($data['title']) ?>" required>
      </div>

      <div class="form-group">
        <label>概要</label>
        <textarea name="description" rows="5" required><?= h($data['description']) ?></textarea>
      </div>

      <div class="form-group">
        <label>作品URL（任意）</label>
        <input type="url" name="project_url" value="<?= h($data['project_url']) ?>">
      </div>

      <div class="form-group">
        <label>ソースコードURL（任意）</label>
        <input type="url" name="source_code_url" value="<?= h($data['source_code_url']) ?>">
      </div>

      <div class="form-group">
        <label>タグ（カンマ区切り）</label>
        <input type="text" name="tags" value="<?= h($data['tags']) ?>">
      </div>

      <div class="form-group">
        <label>現在のサムネ</label><br>
        <?php if ($data['screenshot_url']): ?>
          <img src="<?= h($data['screenshot_url']) ?>" alt="">
        <?php else: ?>
          <span>未設定</span>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label>サムネ差し替え（任意）</label>
        <input type="file" name="screenshot" accept=".jpg,.jpeg,.png,.webp">
      </div>

      <div class="button-container">
        <button type="submit" class="edit-button">更新</button>
        <a href="<?= \Uri::create('dashboard'); ?>" class="cancel-button">戻る</a>
      </div>
    </form>
  </div>
</body>

</html>
