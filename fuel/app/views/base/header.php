<header id="header" class="main-header">
  <div class="header-container">
    <div class="logo">
      <a data-bind="attr:{href: urls.dashboard}">個人開発物投稿プラットフォーム</a>
    </div>

    <nav class="main-nav">
      <form action="/search" method="get" class="header-search"
        data-bind="visible: loggedIn"
        style="display:flex; gap:8px; align-items:center;">
        <input type="text" name="q" placeholder="キーワード" class="search-input" />
        <input type="text" name="tags" placeholder="タグ（カンマ区切り）" class="search-input" />
        <button type="submit" class="btn btn-sm">検索</button>
      </form>

      <a data-bind="attr:{href: urls.mypage}, visible: loggedIn" class="nav-link">マイページ</a>
      <a href="#" data-bind="click: logout, visible: loggedIn" class="nav-link">ログアウト</a>

      <a data-bind="attr:{href: urls.login}, visible: !loggedIn()" class="nav-link">ログイン</a>
      <a data-bind="attr:{href: urls.register}, visible: !loggedIn()" class="nav-link">新規登録</a>
    </nav>

  </div>

</header>

<script>
  window.SESSION = <?= $session_boot ?>;

  function HeaderViewModel(session) {
    const self = this;

    self.loggedIn = ko.observable(!!session.loggedIn);
    self.urls = session.urls || {};

    // 可読性のために別名とゲスト判定を用意
    self.isLoggedIn = self.loggedIn;
    self.isGuest = ko.pureComputed(() => !self.loggedIn());

    // ログアウト
    self.logout = async function(formData, event) {
      if (event) event.preventDefault();

      try {
        const res = await fetch('/api/auth/logout', {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-Token': window.CSRF_TOKEN, // CSRFトークンを追加
          },
          credentials: 'same-origin', // cookieの送信を追加
        });
      } catch (e) {
        console.error('logout error:', e);
      } finally {
        self.loggedIn(false);
        window.location.href = self.urls.dashboard || '/';
      }
    };
  }

  ko.applyBindings(new HeaderViewModel(window.SESSION), document.getElementById('header'));
</script>
