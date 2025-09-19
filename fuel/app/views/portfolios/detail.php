<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Portfolios-Detail</title>
  <?= Asset::css('detail.css'); ?>
</head>

<body>
  <div id="pf-detail" class="pf-detail-card" data-bind="with: viewmodel">
    <div class="pf-hero">
      <img data-bind="attr:{src: imageSrc(), alt: title() || 'portfolio image'}">

      <a class="pf-link pf-project-url"
        data-bind="attr:{href: project_url}, text: project_url, visible: hasProjectUrl"
        target="_blank" rel="noopener"></a>
    </div>

    <div class="pf-meta">
      <div class="pf-row">
        <div class="pf-label">タイトル</div>
        <div class="pf-value" data-bind="text: title"></div>
      </div>

      <div class="pf-row">
        <div class="pf-label">説明</div>
        <div class="pf-value" data-bind="html: descriptionHtml"></div>
      </div>

      <div class="pf-tags" data-bind="visible: showTagsBlock">
        <div data-bind="foreach: tags">
          <span class="pf-tag" data-bind="text: $data"></span>
        </div>

        <a class="pf-fab"
          data-bind="attr:{href: source_code_url}, visible: hasSource"
          target="_blank" rel="noopener" title="ソースコード">

          <svg viewBox="0 0 16 16" width="24" height="24" aria-hidden="true"
            data-bind="visible: isGithub">
            <path fill="currentColor"
              d="M8 0C3.58 0 0 3.58 0 8a8 8 0 0 0 5.47 7.59c.4.07.55-.17.55-.38
               0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13
               -.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66
               .07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15
               -.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27s1.36.09 2 .27
               c1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15
               0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.19
               0 .21.15.46.55.38A8 8 0 0 0 16 8c0-4.42-3.58-8-8-8z" />
          </svg>

          <span data-bind="visible: isNotGithub">Source Code</span>
        </a>
      </div>
    </div>
  </div>

  <script>
    function escapeHtml(string) {
      const escape = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      };
      return String(string).replace(/[&<>"']/g, ch => escape[ch]);
    }

    function nl2brSafe(string) {
      return escapeHtml(string).replace(/\r?\n/g, '<br>');
    }

    function getIdFromPath() {
      const parts = location.pathname.replace(/\/+$/, '').split('/');
      const last = parts[parts.length - 1];
      const n = Number(last);
      return Number.isFinite(n) ? n : null;
    }

    function PortfolioViewModel() {
      this.id = ko.observable(null);
      this.title = ko.observable('');
      this.description = ko.observable('');
      this.project_url = ko.observable('');
      this.source_code_url = ko.observable('');
      this.screenshot_url = ko.observable('');
      this.tags = ko.observableArray([]);

      this.imageSrc = ko.pureComputed(() => this.screenshot_url());
      this.descriptionHtml = ko.pureComputed(() => nl2brSafe(this.description() || ''));

      this.isGithub = ko.pureComputed(() =>
        /github\.com/i.test(this.source_code_url() || '')
      );
      this.isNotGithub = ko.pureComputed(() => !this.isGithub());

      this.hasProjectUrl = ko.pureComputed(() => !!this.project_url());
      this.hasSource = ko.pureComputed(() => !!this.source_code_url());
      this.hasTags = ko.pureComputed(() => this.tags().length > 0);
      this.showTagsBlock = ko.pureComputed(() => this.hasTags() || this.hasSource());
    }

    const viewmodel = new PortfolioViewModel();
    ko.applyBindings({
      viewmodel
    }, document.getElementById('pf-detail'));

    (async function load() {
      const id = getIdFromPath();
      if (!id) {
        alert('不正なURLです');
        return;
      }
      viewmodel.id(id);

      try {
        const res = await fetch(`/api/portfolio/detail/${id}`, {
          method: 'GET',
          headers: {
            'Accept': 'application/json'
          },
          credentials: 'same-origin' // Cookieを送信
        });

        const json = await res.json();
        if (!res.ok || json.status !== 'success') {
          throw new Error(json.message || '取得に失敗しました');
        }
        const d = json.data || {};
        viewmodel.title(d.title || '');
        viewmodel.description(d.description || '');
        viewmodel.project_url(d.project_url || '');
        viewmodel.source_code_url(d.source_code_url || '');
        viewmodel.screenshot_url(d.screenshot_url || '');
        viewmodel.tags(Array.isArray(d.tags) ? d.tags : (d.tags ? d.tags : []));
      } catch (e) {
        console.error(e);
        alert('ページの取得に失敗しました。');
      }
    })();
  </script>
</body>

</html>
