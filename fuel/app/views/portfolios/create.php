<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Create-Portfolios</title>
  <?= Asset::css('create.css'); ?>
</head>

<body>
  <?php function h($s)
  {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
  } ?>
  <div class="create-container">
    <h1>新規投稿</h1>

    <?php if (!empty($errors)): ?>
      <div>
        <?php foreach ($errors as $m): ?><div><?= h($m) ?></div><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" action="<?= \Uri::current(); ?>" enctype="multipart/form-data">
      <div class="form-group">
        <label>タイトル</label>
        <input type="text" name="title" value="<?= h($old['title'] ?? '') ?>" required>
      </div>

      <div class="form-group">
        <label>概要</label>
        <textarea name="description" rows="5" required><?= h($old['description'] ?? '') ?></textarea>
      </div>

      <div class="form-group">
        <label>作品URL（任意）</label>
        <input type="url" name="project_url" value="<?= h($old['project_url'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label>ソースコードURL（任意）</label>
        <input type="url" name="source_code_url" value="<?= h($old['source_code_url'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label>タグ（カンマ区切り）</label>
        <input type="text" name="tags" placeholder="React, Next.js, Firebase"
          value="<?= h($old['tags'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label>
          サムネ（任意）
          <br>
          <span>投稿数：1枚</span>
          <br>
          <span>形式：jpg/png/webp</span>
          <br>
          <span>サイズ：5MBまで</span>
        </label>
        <input type="file" name="screenshot" accept=".jpg,.jpeg,.png,.webp">
      </div>

      <div class="button-container">
        <button type="submit" class="submit-button">投稿</button>
        <a href="<?= \Uri::create('/'); ?>" class="cancel-button">キャンセル</a>
      </div>
    </form>
  </div>

</body>

</html>
