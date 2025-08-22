<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Register</title>
  <?= Asset::css('style.css'); ?>
</head>

<body>
  <?php
  $initial = [
    'username' => $old['username'] ?? '',
    'bio'      => $old['bio'] ?? '',
  ];
  ?>
  <div class="login-container" id="register-root">
    <h1>新規登録</h1>

    <?php if (!empty($errors)): ?>
      <div>
        <?php foreach ($errors as $msg): ?>
          <div><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" action="<?= \Uri::current(); ?>">
      <div class="form-group">
        <label for="username">ユーザー名（3〜255文字）</label>
        <input type="text" id="username" name="username"
          data-bind="value: username, valueUpdate: 'afterkeydown'"
          required>
      </div>

      <div class="form-group">
        <label for="password">パスワード（8文字以上）</label>
        <input type="password" id="password" name="password"
          data-bind="value: password, valueUpdate: 'afterkeydown'"
          required>
      </div>

      <div class="form-group">
        <label for="email">メールアドレス（8文字以上）</label>
        <input type="text" id="email" name="email"
          data-bind="value: email, valueUpdate: 'afterkeydown'"
          required>
      </div>

      <div class="form-group">
        <label for="bio">自己紹介（任意）</label>
        <textarea id="bio" name="bio" rows="4"
          data-bind="value: bio, valueUpdate: 'afterkeydown'"></textarea>
      </div>

      <div class="button-container">
        <button type="submit" class="submit-button"
          data-bind="enable: canSubmit()">新規登録</button>
      </div>

      <!-- クライアント側の簡易メッセージ（任意） -->
      <p data-bind="visible: !canSubmit(), text: helperMessage"></p>
    </form>
  </div>

  <script>
    const initialRegister = <?= json_encode($initial, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    function RegisterViewModel(data) {
      const self = this;
      this.username = ko.observable(data.username || '');
      this.password = ko.observable('');
      this.email = ko.observable(data.email || '');
      this.bio = ko.observable(data.bio || '');

      this.canSubmit = ko.computed(() =>
        (self.username() || '').length >= 3 && (self.password() || '').length >= 8
      );

      this.helperMessage = ko.computed(() => {
        if ((self.username() || '').length < 3) return 'ユーザー名は3文字以上で入力してください。';
        if ((self.password() || '').length < 8) return 'パスワードは8文字以上で入力してください。';
        return '';
      });
    }

    ko.applyBindings(new RegisterViewModel(initialRegister), document.getElementById('register-root'));
  </script>
</body>

</html>
