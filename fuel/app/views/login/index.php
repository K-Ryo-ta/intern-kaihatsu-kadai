<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Login</title>
  <?= Asset::css('style.css'); ?>
</head>

<body>
  <div class="auth-container" id="login">
    <h1>ログイン</h1>

    <div data-bind="visible: successMessage">
      <div class="alert alert-success" data-bind="text: successMessage"></div>
    </div>

    <div data-bind="visible: errorMessage">
      <div class="alert alert-danger" data-bind="text: errorMessage"></div>
    </div>

    <form data-bind="event: { submit: login }">
      <div class="form-group">
        <label for="username">ユーザー名</label>
        <input type="text" id="username" name="username"
          data-bind="value: username, valueUpdate: 'input'" required autocomplete="username">
      </div>

      <div class="form-group">
        <label for="password">パスワード</label>
        <input type="password" id="password" name="password"
          data-bind="value: password, valueUpdate: 'input'" required autocomplete="current-password">
      </div>

      <div class="remember">
        <input type="checkbox" id="remember"
          data-bind="checked: remember">
        <label for="remember">ログイン状態を保持する</label>
      </div>

      <div class="button-container">
        <button type="submit" class="submit-button"
          data-bind="enable: canSubmit(), text: isSaving() ? 'ログイン中...' : 'ログイン'"></button>
      </div>
    </form>
  </div>

  <script>
    function LoginViewModel() {
      const self = this;
      this.username = ko.observable('');
      this.password = ko.observable('');
      this.isSaving = ko.observable(false);
      this.errorMessage = ko.observable('');
      this.successMessage = ko.observable('');
      this.remember = ko.observable(false);
      self.canSubmit = ko.computed(() => !!self.username() && !!self.password() && !self.isSaving());

      self.showError = (msg) => {
        self.errorMessage(msg);
        self.successMessage('');
      };
      self.showSuccess = (msg) => {
        self.successMessage(msg);
        self.errorMessage('');
      };

      self.login = async function(formElement, event) {
        self.errorMessage('');
        self.successMessage('');

        event.preventDefault();
        if (!self.canSubmit()) return; //連打防止

        if (!self.username()) {
          self.showError('ユーザー名を入力してください');
          return;
        }
        if (!self.password()) {
          self.showError('パスワードを入力してください');
          return;
        }

        self.isSaving(true);

        try {
          const data = {
            username: self.username(),
            password: self.password(),
            remember: self.remember()
          };

          const response = await fetch('/api/auth/login', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-Token': window.CSRF_TOKEN, // CSRFトークンを追加
            },
            credentials: 'same-origin', // クッキーの送信を追加
            body: JSON.stringify(data)
          });

          const result = await response.json();

          if (result.status === 'success') {
            self.successMessage('ログインに成功しました。リダイレクト中...');
            setTimeout(() => {
              window.location.href = '/';
            }, 1500);
          } else {
            self.errorMessage(result.message || 'プロフィールの更新に失敗しました');
          }
        } catch (error) {
          console.error('Login error:', error);
          self.errorMessage('ログインエラー');
        } finally {
          self.isSaving(false);
        }
      };
    }

    const viewModel = new LoginViewModel();
    ko.applyBindings(viewModel, document.getElementById('login'));
  </script>
</body>

</html>
