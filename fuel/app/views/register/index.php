<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Register</title>
  <?php

  use Fuel\Core\Asset;

  Asset::css('style.css');
  ?>
</head>

<body>
  <div class="auth-container" id="register">
    <h1>新規登録</h1>

    <div data-bind="visible: successMessage">
      <div class="alert alert-success" data-bind="text: successMessage"></div>
    </div>

    <div data-bind="visible: errorMessage">
      <div class="alert alert-danger" data-bind="text: errorMessage"></div>
    </div>

    <form id="register" data-bind="event: { submit: register }">
      <div class="form-group">
        <label for="username">ユーザー名（3〜255文字）</label>
        <input type="text" id="username" name="username"
          data-bind="value: username, valueUpdate: 'input'" required autocomplete="username">
      </div>

      <div class="form-group">
        <label for="password">パスワード（8文字以上）</label>
        <input type="password" id="password" name="password"
          data-bind="value: password, valueUpdate: 'input'" required autocomplete="username">
      </div>

      <div class="form-group">
        <label for="email">メールアドレス（8文字以上）</label>
        <input type="text" id="email" name="email"
          data-bind="value: email, valueUpdate: 'input'" required autocomplete="email">
      </div>

      <div class="form-group">
        <label for="bio">自己紹介（任意）</label>
        <textarea id="bio" name="bio" rows="4"
          data-bind="value: bio,valueUpdate:'input'" required autocomplete="bio"></textarea>
      </div>

      <div class="button-container">
        <button type="submit" class="submit-button"
          data-bind="enable: canSubmit(), text: isSaving() ? '新規登録中...' : '新規登録'"></button>
      </div>
    </form>
  </div>

  <script>
    function RegisterViewModel() {
      const self = this;
      this.username = ko.observable('');
      this.password = ko.observable('');
      this.email = ko.observable('');
      this.bio = ko.observable('');
      this.isSaving = ko.observable(false);
      this.errorMessage = ko.observable('');
      this.successMessage = ko.observable('');
      self.canSubmit = ko.computed(() => !!self.username() && !!self.password() && !!self.email() && !self.isSaving());

      self.register = async function(formElement, event) {

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
            email: self.email(),
            password: self.password(),
            $bio: self.bio() || null,
          };

          const response = await fetch('/api/auth/register', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-Token': widow.CSRF_TOKEN, // CSRFトークンを追加
            },
            credentials: 'same-origin', // クッキーの送信を追加
            body: JSON.stringify(data)
          });

          const result = await response.json();

          if (result.status === 'success') {
            self.successMessage('新規登録しました。ログイン画面にリダイレクト中...');
            setTimeout(() => {
              window.location.href = '/login';
            }, 1500);
          } else {
            self.errorMessage(result.message || '新規登録に失敗しました');
          }
        } catch (error) {
          console.error('Register error:', error);
          self.errorMessage('新規登録エラー');
        } finally {
          self.isSaving(false);
        }
      };
    }

    const viewModel = new RegisterViewModel();
    ko.applyBindings(viewModel, document.getElementById('register'));
  </script>
</body>

</html>
