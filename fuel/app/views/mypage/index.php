<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Create-Portfolios</title>
  <?= \Asset::css('mypage.css') ?>
</head>

<body>

  <div id='mypage' class="mypage-container">

    <h1 class="page-title">マイページ</h1>

    <div data-bind="visible: successMessage">
      <div data-bind="text: successMessage"></div>
    </div>

    <div data-bind="visible: errorMessage">
      <div data-bind="text: errorMessage"></div>
    </div>

    <div class="content">

      <section class="profile-section">
        <h2 class="section-title">プロフィール</h2>

        <form class="profile-form" data-bind="submit: saveProfile">

          <div class="form-group">
            <label class="form-label">
              ユーザー名（変更は管理側で）
            </label>
            <input type="text" class="form-input disabled" data-bind="value: username" readonly>
          </div>

          <div class="form-group">
            <label class="form-label">
              メールアドレス
            </label>
            <input type="email" class="form-input" data-bind="value: email" required>
          </div>

          <div class="form-group">
            <label class="form-label">
              自己紹介
            </label>
            <textarea class="form-textarea" data-bind="value: bio" rows="5"></textarea>
          </div>

          <div class="form-group">
            <label class="form-label">
              スキル（作品のスキルまとめ）
            </label>
            <input type="text"
              class="form-input"
              placeholder="PHP, TypeScript, JavaScript"
              data-bind="value: skills" readonly>
          </div>

          <div class="form-group">
            <label class="form-label">
              現在のパスワード（パスワード変更時は必須）
            </label>
            <input type="password"
              class="form-input"
              data-bind="value: oldPassword"
              placeholder="パスワードを変更する場合のみ入力">
          </div>

          <div class="form-group">
            <label class="form-label">
              新しいパスワード（任意・8文字以上）
            </label>
            <input type="password"
              name="new_password"
              class="form-input"
              data-bind="value: newPassword">
          </div>

          <div class="form-group">
            <label class="form-label">
              パスワード確認
            </label>
            <input type="password"
              name="new_password_confirm"
              class="form-input"
              data-bind="value: newPasswordConfirm">
          </div>


          <div class="form-submit">
            <button type="submit" class="btn btn-primary" data-bind="disable: isSaving">
              <span data-bind="text: isSaving() ? '保存中...' : 'プロフィールを保存'"></span>
            </button>
          </div>
        </form>
      </section>

      <section class="portfolio-section">
        <div class="section-header">
          <h2 class="section-title">あなたの作品</h2>
        </div>

        <div data-bind="visible: isLoading">
          <p class="no-items-message">読み込み中...</p>
        </div>

        <div class="portfolio-content" data-bind="foreach: portfolios">
          <div class="portfolio-card">
            <a data-bind="attr: { href: '/portfolios/detail/' + id }"
              class="portfolio-image-link">
              <img
                data-bind="attr: {src: screenshot_url,alt: title}"
                class="portfolio-image">
            </a>

            <div class="portfolio-info">
              <div class="portfolio-title" data-bind="text: title"></div>

              <div class="portfolio-actions">
                <a data-bind="attr: { href: '/portfolios/edit/' + id }"
                  class="btn btn-edit">
                  編集
                </a>

                <form method="post"
                  class="delete-form"
                  data-bind="event:{ submit: $parent.deletePortfolio }">
                  <button type="submit" class="btn btn-delete">
                    削除
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>

        <div data-bind="visible: portfolios().length === 0 && !isLoading()">
          <p class="no-items-message">ポートフォリオがまだありません。</p>
        </div>
      </section>
    </div>
  </div>

  <script>
    function MypageViewModel() {
      const self = this;

      self.username = ko.observable('');
      self.email = ko.observable('');
      self.bio = ko.observable('');
      self.skills = ko.observable('');

      self.oldPassword = ko.observable('');
      self.newPassword = ko.observable('');
      self.newPasswordConfirm = ko.observable('');

      self.portfolios = ko.observableArray([]);

      self.successMessage = ko.observable('');
      self.errorMessage = ko.observable('');
      self.isSaving = ko.observable(false);
      self.isLoading = ko.observable(false);

      self.showSuccess = function(message) {
        self.successMessage(message);
        self.errorMessage('');
        setTimeout(() => self.successMessage(''), 3000);
      };

      self.showError = function(message) {
        self.errorMessage(message);
        self.successMessage('');
        setTimeout(() => self.errorMessage(''), 3000);
      };

      self.loadProfile = async function() {
        try {
          const response = await fetch('/api/profile', {
            method: 'GET',
            headers: {
              'Accept': 'application/json'
            }
          });

          const data = await response.json();

          if (data.status === 'success') {
            const profile = data.data;
            self.username(profile.username || '');
            self.email(profile.email || '');
            self.bio(profile.bio || '');
            self.skills(profile.skills || '');
          } else {
            self.showError('プロフィールの読み込みに失敗しました');
          }
        } catch (error) {
          console.error('Load profile error:', error);
          self.showError('プロフィールの読み込みエラー');
        }
      };

      self.saveProfile = async function() {
        if (self.newPassword()) {
          if (!self.oldPassword()) {
            self.showError('現在のパスワードを入力してください');
            return;
          }
          if (self.newPassword() !== self.newPasswordConfirm()) {
            self.showError('新しいパスワードが一致しません');
            return;
          }
          if (self.newPassword().length < 8) {
            self.showError('パスワードは8文字以上にしてください');
            return;
          }
        }

        self.isSaving(true);

        try {
          const data = {
            email: self.email(),
            bio: self.bio(),
          };

          if (self.newPassword()) {
            data.old_password = self.oldPassword();
            data.new_password = self.newPassword();
          }

          const response = await fetch('/api/profile', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-Token': window.CSRF_TOKEN, // CSRFトークンを追加
            },
            body: JSON.stringify(data),
            credentials: 'same-origin' // クッキーの送信を追加
          });

          const result = await response.json();

          if (result.status === 'success') {
            self.showSuccess('プロフィールを更新しました');
            self.oldPassword('');
            self.newPassword('');
            self.newPasswordConfirm('');
          } else {
            self.showError(result.message || 'プロフィールの更新に失敗しました');
          }
        } catch (error) {
          console.error('Save profile error:', error);
          self.showError('プロフィールの保存エラー');
        } finally {
          self.isSaving(false);
        }
      };

      self.loadPortfolios = async function() {
        self.isLoading(true);
        try {
          const response = await fetch('api/dashboard/items/user_portfolios', {
            method: 'GET',
            headers: {
              'Accept': 'application/json'
            }
          });

          const data = await response.json();

          if (data.status === 'success') {
            self.portfolios(data.data || []);
          } else {
            self.showError('ポートフォリオの読み込みに失敗しました');
          }
        } catch (error) {
          console.error('Load portfolios error:', error);
          self.showError('ポートフォリオの読み込みエラー');
        } finally {
          self.isLoading(false);
        }
      };

      self.deletePortfolio = async function(portfolio, event) {
        event.preventDefault();

        if (!confirm(`「${portfolio.title}」を削除しますか？`)) {
          return;
        }

        try {
          const response = await fetch(`/api/portfolio/${portfolio.id}`, {
            method: 'DELETE',
            headers: {
              'Accept': 'application/json',
              'X-CSRF-Token': window.CSRF_TOKEN, // CSRFトークンを追加
            },
            credentials: 'same-origin' // クッキーの送信を追加
          });

          const result = await response.json();

          if (result.status === 'success') {
            self.portfolios.remove(portfolio);
            self.showSuccess('ポートフォリオを削除しました');
          } else {
            self.showError('削除に失敗しました');
          }
        } catch (error) {
          console.error('Delete error:', error);
          self.showError('削除エラー');
        }
      };

      self.init = function() {
        self.loadProfile();
        self.loadPortfolios();
      };
    }

    document.addEventListener('DOMContentLoaded', function() {
      const viewModel = new MypageViewModel();
      ko.applyBindings(viewModel, document.getElementById('mypage'));
      viewModel.init();
    });
  </script>
</body>

</html>
