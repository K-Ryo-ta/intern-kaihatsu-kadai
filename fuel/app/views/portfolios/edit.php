<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Edit-Portfolios</title>
  <?= Asset::css('edit.css'); ?>
</head>

<body>
  <div id="pf-edit" class="edit-container">
    <h1>投稿を編集</h1>
    <div data-bind="visible: errorMessage">
      <div data-bind="text: errorMessage"></div>
    </div>
    <div data-bind="visible: successMessage">
      <span data-bind="text: successMessage"></span>
    </div>

    <form data-bind="submit: savePortfolio" class="edit-form" enctype="multipart/form-data" novalidate>
      <div class="form-group">
        <label>タイトル</label>
        <input type="text" data-bind="value: title" required>
      </div>

      <div class="form-group">
        <label>概要</label>
        <textarea rows="5" data-bind="value: description" required></textarea>
      </div>

      <div class="form-group">
        <label>作品URL（任意）</label>
        <input type="url" data-bind="value: projectUrl">
      </div>

      <div class="form-group">
        <label>ソースコードURL（任意）</label>
        <input type="url" data-bind="value: sourceCodeUrl">
      </div>

      <div class="form-group">
        <label>タグ（カンマ区切り）</label>
        <input type="text" placeholder="React, Next.js, Firebase" data-bind="value: tags">
      </div>

      <div class="form-group">
        <label>現在のサムネ</label>
        <img class="thumb" data-bind="attr:{src: imagePreview, alt: title}">
      </div>

      <div class="form-group">
        <label>サムネ（任意）<br>jpg / png / webp（～5MB）</label>
        <input type="file" accept=".jpg,.jpeg,.png,.webp"
          data-bind="event: { change: selectImage }">
      </div>

      <div class="button-container">
        <button type="submit" class="edit-button" data-bind="disable: isSaving">
          <span data-bind="text: isSaving() ? '更新中...' : '更新する'"></span>
        </button>
        <a href="<?= \Uri::create('/mypage'); ?>" class="cancel-button" data-bind="css:{disabled: isSaving}">戻る</a>
      </div>
    </form>
  </div>

  <script>
    function PortfolioEditViewModel() {
      const self = this;

      self.portfolioId = null;

      self.title = ko.observable('');
      self.description = ko.observable('');
      self.projectUrl = ko.observable('');
      self.sourceCodeUrl = ko.observable('');
      self.tags = ko.observable('');

      self.currentImageUrl = ko.observable('');
      self.selectedFile = ko.observable(null);
      self.newImageName = ko.observable('');

      self.errorMessage = ko.observable('');
      self.successMessage = ko.observable('');
      self.isSaving = ko.observable(false);
      self.imagePreview = ko.computed(function() {
        if (self.selectedFile()) {
          return URL.createObjectURL(self.selectedFile());
        }
        return self.currentImageUrl();
      });

      self.getPortfolioId = function() {
        const path = location.pathname;
        const match = path.match(/(\d+)$/);
        return match ? parseInt(match[1]) : null;
      };

      self.loadPortfolio = async function() {
        const id = self.getPortfolioId();
        if (!id) {
          self.errorMessage('URLが正しくありません');
          return;
        }

        self.portfolioId = id;

        try {
          const response = await fetch(`/api/portfolio/detail/${id}`, {
            method: 'GET',
            headers: {
              'Accept': 'application/json'
            }
          });

          const data = await response.json();

          if (data.status === 'success') {
            const portfolio = data.data;
            self.title(portfolio.title || '');
            self.description(portfolio.description || '');
            self.projectUrl(portfolio.project_url || '');
            self.sourceCodeUrl(portfolio.source_code_url || '');
            self.currentImageUrl(portfolio.screenshot_url || '');

            // タグを配列から文字列に変換
            if (Array.isArray(portfolio.tags)) {
              self.tags(portfolio.tags.join(', '));
            } else {
              self.tags(portfolio.tags || '');
            }
          } else {
            self.errorMessage('データの取得に失敗しました');
          }
        } catch (error) {
          console.error('Load error:', error);
          self.errorMessage('データの読み込みエラー');
        }
      };

      //画像
      self.selectImage = function(data, event) {
        const file = event.target.files[0];

        if (!file) {
          self.selectedFile(null);
          self.newImageName('');
          return;
        }

        // ファイルサイズチェック（5MB以下）
        if (file.size > 5 * 1024 * 1024) {
          self.errorMessage('画像は5MB以下にしてください');
          event.target.value = '';
          return;
        }

        // 拡張子チェック
        const fileName = file.name.toLowerCase();
        if (!fileName.match(/\.(jpg|jpeg|png|webp)$/)) {
          self.errorMessage('jpg, jpeg, png, webp形式のみアップロード可能です');
          event.target.value = '';
          return;
        }

        self.errorMessage('');
        self.selectedFile(file);
        self.newImageName('新しい画像: ' + file.name);
      };

      self.savePortfolio = async function() {
        self.errorMessage('');
        self.successMessage('');
        self.isSaving(true);

        try {
          const formData = new FormData();
          formData.append('title', self.title());
          formData.append('description', self.description());
          formData.append('project_url', self.projectUrl() || '');
          formData.append('source_code_url', self.sourceCodeUrl() || '');
          formData.append('tags', self.tags() || '');

          if (self.selectedFile()) {
            formData.append('screenshot', self.selectedFile());
          }

          formData.append(window.CSRF_TOKEN, window.CSRF_KEY); // CSRFトークンを追加

          const response = await fetch(`/api/portfolio/update/${self.portfolioId}`, {
            method: 'POST',
            headers: {
              'Accept': 'application/json',
            },
            body: formData,
            credentials: 'same-origin', // クッキーを送信
          });

          const result = await response.json();

          if (result.status === 'success') {
            self.successMessage('保存しました！');
            setTimeout(function() {
              window.location.href = '/';
            }, 2000);
          } else {
            self.errorMessage(result.message || '保存に失敗しました');
          }

        } catch (error) {
          console.error('Save error:', error);
          self.errorMessage('保存エラーが発生しました');
        } finally {
          self.isSaving(false);
        }
      };

      self.init = function() {
        self.loadPortfolio();
      };
    }

    const viewModel = new PortfolioEditViewModel();
    ko.applyBindings(viewModel, document.getElementById('pf-edit'));
    viewModel.init();
  </script>
</body>

</html>
