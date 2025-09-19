<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Create-Portfolios</title>
  <?= Asset::css('create.css'); ?>
</head>

<body>
  <div id="pf-create" class="create-container">
    <h1>新規投稿</h1>
    <div data-bind="visible: message">
      <div data-bind="text: message"></div>
    </div>
    <form enctype="multipart/form-data" data-bind="submit: savePortfolio">
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
        <label>サムネ（任意）<br>jpg / png / webp（～5MB）</label>
        <input type="file" accept=".jpg,.jpeg,.png,.webp"
          data-bind="event: { change: selectImage }">
        <div class="preview"><img class="thumb" data-bind="attr:{src: imagePreview, alt: title}" style="max-width:320px"></div>
      </div>

      <div class="button-container">
        <button type="submit" class="submit-button" data-bind="disable:isSaving">
          <span data-bind="text: isSaving() ? '投稿中...' : '投稿する'"></span>
        </button>
        <a href="<?= \Uri::create('/'); ?>" class="cancel-button">キャンセル</a>
      </div>
    </form>
  </div>

  <script>
    function PortfolioCreateViewModel() {
      const self = this;

      self.title = ko.observable('');
      self.description = ko.observable('');
      self.projectUrl = ko.observable('');
      self.sourceCodeUrl = ko.observable('');
      self.tags = ko.observable('');

      self.selectedFile = ko.observable(null);

      self.message = ko.observable('');
      self.isSaving = ko.observable(false);

      // 画像プレビュー
      self.imagePreview = ko.computed(function() {
        if (self.selectedFile()) {
          return URL.createObjectURL(self.selectedFile());
        }
      });

      // 画像
      self.selectImage = function(data, event) {
        const file = event.target.files && event.target.files[0];

        if (!file) {
          self.selectedFile(null);
          return;
        }

        // サイズ上限（5MB）
        if (file.size > 5 * 1024 * 1024) {
          self.message('画像は5MB以下にしてください');
          event.target.value = '';
          return;
        }

        // 拡張子チェック
        const name = (file.name || '').toLowerCase();
        if (!name.match(/\.(jpg|jpeg|png|webp)$/)) {
          self.message('jpg, jpeg, png, webp形式のみアップロード可能です');
          event.target.value = '';
          return;
        }

        self.message('');
        self.selectedFile(file);
      };

      // 登録
      self.savePortfolio = async function() {
        self.message('');
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
          formData.append(window.CSRF_KEY, window.CSRF_TOKEN); // CSRFトークンを追加

          const response = await fetch('/api/portfolio', {
            method: 'POST',
            headers: {
              'Accept': 'application/json',
            },
            body: formData,
            credentials: 'same-origin', // 認証Cookie送信
          });

          const result = await response.json();

          if (result.status === 'success') {
            self.message('保存しました！');
            setTimeout(function() {
              window.location.href = '/';
            }, 2000);
          } else {
            self.message(result.message || '保存に失敗しました');
          }
        } catch (e) {
          console.error('Create error:', e);
          self.message('保存エラーが発生しました');
        } finally {
          self.isSaving(false);
        }
      };
    }

    const viewModel = new PortfolioCreateViewModel();
    ko.applyBindings(viewModel, document.getElementById('pf-create'));
  </script>

</body>

</html>
