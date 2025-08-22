<header id="header" class="main-header">
  <div class="header-container">
    <div class="logo">
      <a data-bind="attr:{href: urls.dashboard}">個人開発物投稿プラットフォーム</a>
    </div>

    <nav class="main-nav">
      <!-- ko if: loggedIn() -->
      <a data-bind="attr:{href: urls.mypage}" class="nav-link">マイページ</a>
      <a data-bind="attr:{href: urls.logout}" class="nav-link">ログアウト</a>
      <!-- /ko -->

      <!-- ko ifnot: loggedIn() -->
      <a data-bind="attr:{href: urls.login}" class="nav-link">ログイン</a>
      <a data-bind="attr:{href: urls.register}" class="nav-link">新規登録</a>
      <!-- /ko -->
    </nav>
  </div>
</header>

<script>
  window.SESSION = <?= $session_boot ?>;

  function HeaderViewModel(session) {
    this.loggedIn = ko.observable(!!session.loggedIn);
    this.urls = session.urls || {};
    this.setLoggedIn = () => {
      this.loggedIn(true);
    };
    this.setLoggedOut = () => {
      this.loggedIn(false);
    };
  }

  ko.applyBindings(new HeaderViewModel(window.SESSION), document.getElementById('header'));
</script>
