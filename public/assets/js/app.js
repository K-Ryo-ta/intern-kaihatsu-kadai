// コンポーネントを登録
ko.components.register('header-component', {
  viewModel: HeaderViewModel,
  template: { require: 'text!components/header-component.html' }
});

// アプリケーション全体のViewModel (今回は空でOK)
function AppViewModel() {
  // ...
}

// Knockout.jsを適用
ko.applyBindings(new AppViewModel());
