<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>DashBoard</title>
  <?= Asset::css('dashboard.css'); ?>
</head>

<body>
  <div id="dashboard-root" class="container" data-bind="with: viewModel">
    <h1>Dashboard</h1>

    <div class="all-portfolios" data-bind="foreach: items">
      <a class="portfolio" data-bind="attr: { href: '/portfolios/detail/' + id }">
        <div class="portfolio-image">
          <img data-bind="attr:{src: screenshot_url, alt: title}">
        </div>
        <div class="portfolio-title" data-bind="text: title"></div>
      </a>
    </div>

    <p data-bind="visible: isLoading">読み込み中...</p>
    <p data-bind="visible: !isLoading() && items().length === 0">
      まだ作品がありません。右下の「＋」から追加しましょう。
    </p>
    <p data-bind="visible: errorMessage, text: errorMessage" style="color:#c00;"></p>

    <a class="create-button" data-bind="attr: { href: '/portfolios/create' }">＋</a>
  </div>

  <script>
    function DashboardViewModel() {
      const self = this;

      // state
      self.items = ko.observableArray([]);
      self.isLoading = ko.observable(false);
      self.errorMessage = ko.observable('');

      // methods
      self.load = async () => {
        self.isLoading(true);
        self.errorMessage('');
        try {
          const res = await fetch('/api/dashboard/items', {
            method: 'GET',
            headers: {
              'Accept': 'application/json'
            },
            credentials: 'same-origin' // Cookie送信
          });
          const json = await res.json();
          if (json.status !== 'success') {
            throw new Error(json.message || 'load failed');
          }
          self.items(json.data || []);
        } catch (err) {
          console.error(err);
          self.errorMessage('一覧の取得に失敗しました。');
        } finally {
          self.isLoading(false);
        }
      }
    };

    const viewModel = new DashboardViewModel();
    ko.applyBindings(viewModel, document.getElementById('dashboard-root'));
    viewModel.load();
  </script>
</body>

</html>
