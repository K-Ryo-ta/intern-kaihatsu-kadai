<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>DashBoard</title>
  <?= Asset::css('dashboard.css'); ?>
</head>

<body>
  <?php
  function h($s)
  {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
  }
  ?>

  <div id="dashboard-root" class="container">
    <h1>Dashboard</h1>

    <div class="all-portfolios" data-bind="foreach: items">
      <a class="portfolio" data-bind="attr:{href: $parent.viewHref($data)}">
        <div class="portfolio-image">
          <img data-bind="attr:{src: screenshot_url || $parent.placeholder, alt: title}">
        </div>
        <div class="portfolio-title" data-bind="text: title"></div>
      </a>
    </div>

    <p data-bind="visible: items().length === 0">
      まだ作品がありません。右下の「＋」から追加しましょう。
    </p>

    <a href="<?= \Uri::create('portfolios/create') ?>"
      data-bind="attr:{href: createUrl}"
      class="create-button">＋</a>

    <div class="pager">
      <?php if ($pager['has_prev']): ?>
        <a href="<?= h($pager['prev_url']) ?>" class="prev-button">« 前へ</a>
      <?php else: ?>
        <span class="prev-button disabled">« 前へ</span>
      <?php endif; ?>

      <span>Page <?= (int)$pager['page'] ?></span>

      <?php if ($pager['has_next']): ?>
        <a href="<?= h($pager['next_url']) ?>" class="next-button">次へ »</a>
      <?php else: ?>
        <span class="next-button disabled">次へ »</span>
      <?php endif; ?>
    </div>
  </div>

  <script>
    window.BOOT = <?= $boot ?>;

    function DashboardViewModel(boot) {
      const self = this;
      this.items = ko.observableArray(boot.portfolios || []);
      this.createUrl = boot.create_url;
      this.placeholder = boot.placeholder;
      this.viewHref = (p) => (boot.view_base + p.id);
    }
    ko.applyBindings(new DashboardViewModel(window.BOOT), document.getElementById('dashboard-root'));
  </script>
</body>

</html>
