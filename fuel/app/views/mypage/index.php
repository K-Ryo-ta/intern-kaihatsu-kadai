<?php

function h($s)
{
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
?>

<?= \Asset::css('mypage.css') ?>

<div class="mypage-container">

  <h1 class="page-title">マイページ</h1>

  <?php if (!empty($messages)): ?>
    <div class="message-box success">
      <?php foreach ($messages as $m): ?>
        <div class="message-item"><?= h($m) ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
    <div class="message-box error">
      <?php foreach ($errors as $e): ?>
        <div class="message-item"><?= h($e) ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="content-grid">

    <section class="profile-section">
      <h2 class="section-title">プロフィール</h2>

      <form method="post" action="<?= \Uri::current(); ?>" class="profile-form">
        <input type="hidden" name="form" value="profile">

        <div class="form-group">
          <label class="form-label">
            ユーザー名（変更は管理側で）
          </label>
          <input type="text"
            class="form-input disabled"
            value="<?= h($user['username']) ?>"
            disabled>
        </div>

        <div class="form-group">
          <label class="form-label">
            メールアドレス
          </label>
          <input type="email"
            name="email"
            class="form-input"
            value="<?= h($user['email']) ?>"
            required>
        </div>

        <div class="form-group">
          <label class="form-label">
            自己紹介
          </label>
          <textarea name="bio"
            class="form-textarea"
            rows="5"><?= h($user['bio']) ?></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">
            スキル（カンマ区切り）
          </label>
          <input type="text"
            name="skills"
            class="form-input"
            placeholder="PHP, TypeScript, JavaScript"
            value="<?= h($user['skills']) ?>">
        </div>

        <div class="form-group">
          <label class="form-label">
            新しいパスワード（任意・8文字以上）
          </label>
          <input type="password"
            name="new_password"
            class="form-input">
        </div>

        <div class="form-group">
          <label class="form-label">
            パスワード確認
          </label>
          <input type="password"
            name="new_password_confirm"
            class="form-input">
        </div>

        <div class="form-submit">
          <button type="submit" class="btn btn-primary">
            保存する
          </button>
        </div>
      </form>
    </section>

    <section class="portfolio-section">
      <div class="section-header">
        <h2 class="section-title">あなたの作品</h2>
      </div>

      <?php if (empty($list)): ?>
        <p class="no-items-message">まだ作品がありません。</p>
      <?php else: ?>
        <div class="portfolio-grid">
          <?php foreach ($list as $p): ?>
            <?php $img = $p['screenshot_url'] ?: $placeholder; ?>

            <div class="portfolio-card">
              <a href="<?= \Uri::create('portfolios/view/' . $p['id']) ?>"
                class="portfolio-image-link">
                <img src="<?= h($img) ?>"
                  alt="<?= h($p['title']) ?>"
                  class="portfolio-image">
              </a>

              <div class="portfolio-info">
                <div class="portfolio-title">
                  <?= h($p['title']) ?>
                </div>

                <div class="portfolio-actions">
                  <a href="<?= \Uri::create('portfolios/edit/' . $p['id']) ?>"
                    class="btn btn-edit">
                    編集
                  </a>

                  <form method="post"
                    action="<?= \Uri::create('portfolios/delete/' . $p['id']) ?>"
                    class="delete-form"
                    onsubmit="return confirm('本当に削除しますか？');">
                    <button type="submit" class="btn btn-delete">
                      削除
                    </button>
                  </form>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </div>
</div>
