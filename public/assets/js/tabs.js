(function () {
  'use strict';
  // Tabs on the landing page ("Kandungan Kursus": E-Learning / Self-Learning). Without JavaScript the tab
  // buttons stay hidden (CSS: html.js only) and both panels are shown one after the other.
  var lists = document.querySelectorAll('[data-tabs]');
  for (var i = 0; i < lists.length; i++) setup(lists[i]);

  function setup(list) {
    var tabs = list.querySelectorAll('[role="tab"]');
    if (!tabs.length) return;
    function panelOf(tab) { return document.getElementById(tab.getAttribute('aria-controls')); }
    function select(tab, focus) {
      for (var j = 0; j < tabs.length; j++) {
        var on = tabs[j] === tab;
        tabs[j].setAttribute('aria-selected', on ? 'true' : 'false');
        tabs[j].tabIndex = on ? 0 : -1;
        var panel = panelOf(tabs[j]);
        if (panel) panel.hidden = !on;
      }
      if (focus) tab.focus();
    }
    var initial = tabs[0];
    for (var k = 0; k < tabs.length; k++) {
      if (tabs[k].getAttribute('aria-selected') === 'true') initial = tabs[k];
      tabs[k].addEventListener('click', function (ev) { select(ev.currentTarget, false); });
    }
    list.addEventListener('keydown', function (ev) {
      var idx = Array.prototype.indexOf.call(tabs, document.activeElement);
      if (idx === -1) return;
      var next = null;
      if (ev.key === 'ArrowRight') next = tabs[(idx + 1) % tabs.length];
      else if (ev.key === 'ArrowLeft') next = tabs[(idx - 1 + tabs.length) % tabs.length];
      else if (ev.key === 'Home') next = tabs[0];
      else if (ev.key === 'End') next = tabs[tabs.length - 1];
      if (next) {
        ev.preventDefault();
        select(next, true);
      }
    });
    select(initial, false);
  }
})();
