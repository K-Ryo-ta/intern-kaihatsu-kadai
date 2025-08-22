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
  ];
  ?>
  <div class="login-container" id="login-root">
    <h1>ログイン</h1>

    <?php if (!empty($errors)): ?>
      <div>
        <?php foreach ($errors as $msg): ?>
          <div><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" action="<?= \Uri::current(); ?>">
      <div class="form-group">
        <label for="username">ユーザー名</label>
        <input type="text" id="username" name="username"
          data-bind="value: username, valueUpdate: 'afterkeydown'"
          required>
      </div>

      <div class="form-group">
        <label for="password">パスワード</label>
        <input type="password" id="password" name="password"
          data-bind="value: password, valueUpdate: 'afterkeydown'"
          required>
      </div>

      <div class="button-container">
        <button type="submit" class="submit-button"
          data-bind="enable: canSubmit()">ログイン</button>
      </div>

      <p data-bind="visible: !canSubmit(), text: helperMessage"></p>
    </form>
  </div>

  <script>
    const initialLogin = <?= json_encode($initial, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    function LoginViewModel(data) {
      const self = this;
      this.username = ko.observable(data.username || '');
      this.password = ko.observable('');

      this.canSubmit = ko.computed(() =>
        (self.username() || '').length >= 1 && (self.password() || '').length >= 1
      );

      this.helperMessage = ko.computed(() => {
        if (!(self.username() || '').length) return 'ユーザー名を入力してください。';
        if (!(self.password() || '').length) return 'パスワードを入力してください。';
        return '';
      });
    }

    ko.applyBindings(new LoginViewModel(initialLogin), document.getElementById('login-root'));
  </script>
</body>

</html>
