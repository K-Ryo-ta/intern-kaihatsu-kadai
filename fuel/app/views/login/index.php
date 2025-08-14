<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Login</title>

  <?php echo Asset::css('style.css'); ?>
  <?php echo Asset::js('knockout-3.5.1.js'); ?>
</head>

<body>

  <div class="login-container">
    <div class="form-group">
      <label for="username">ユーザー名</label>
      <input type="text" id="username" placeholder="ユーザー名" data-bind="value: userName">
    </div>

    <div class="form-group">
      <label for="password">パスワード</label>
      <input type="text" id="password" placeholder="パスワード" data-bind="value: password">
    </div>

    <div class="login-button-container">
      <button class="login-button">ログイン</button>
    </div>

  </div>


  <script type="text/javascript">
    const initialData = <?php echo $userData; ?>;

    function LoginViewModel(data) {
      this.userName = ko.observable(data.userName);
      this.password = ko.observable(data.password);
    }

    ko.applyBindings(new LoginViewModel(initialData));
  </script>

</body>

</html>
