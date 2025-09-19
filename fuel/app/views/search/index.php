<html>

<head>
  <meta charset="utf-8">
  <title>Register</title>
  <?= Asset::css('search.css'); ?>
</head>

</html>
<div id="search" class="container">
  <h1>検索結果</h1>

  <div data-bind="visible: isLoading">読み込み中...</div>
  <div data-bind="visible: !isLoading() && results().length === 0">該当する作品はありません。</div>

  <div class="all-portfolios" data-bind="foreach: results">
    <a data-bind="attr:{ href: '/portfolios/detail/' + id }" class="portfolio">
      <div class="portfolio-image">
        <img data-bind="attr:{src: screenshot_url, alt: title}">
      </div>
      <div class="portfolio-title" data-bind="text: title"></div>
    </a>
  </div>
</div>

<script>
  function SearchViewModel() {
    const self = this;
    self.q = ko.observable('');
    self.tags = ko.observable('');

    self.isLoading = ko.observable(false);
    self.results = ko.observableArray([]);

    self.load = async function() {
      self.isLoading(true);
      try {
        const params = new URLSearchParams(window.location.search);
        self.q(params.get('q') || '');
        self.tags(params.get('tags') || '');

        const res = await fetch('/api/search?' + (params.toString() ? '?' + params.toString() : ''), {
          method: 'GET',
          headers: {
            'Accept': 'application/json'
          },
          credentials: 'same-origin',
        });
        const json = await res.json();
        if (json.status === 'success') {
          self.results(json.data || []);
        } else {
          self.results([]);
        }
      } catch (e) {
        console.error(e);
        self.results([]);
      } finally {
        self.isLoading(false);
      }
    };
  }
  const viewModel = new SearchViewModel();
  ko.applyBindings(viewModel, document.getElementById('search'));
  viewModel.load();
</script>
