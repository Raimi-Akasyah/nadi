/*
 * Registration form: Negeri -> Daerah -> Pusat NADI, and the "Lain-lain" text fields.
 * The page lists every district and centre in <optgroup data-state data-district>, so the form also works without
 * JavaScript. This script reads those lists once and then shows only the districts of the chosen state and the
 * centres of the chosen district, with "Lain-lain" at the end.
 * Shared byte for byte with docs/assets/js/register.js (the docs/ preview calls window.nadiRegisterForm itself).
 */
(function () {
  'use strict';
  var OTHER = 'lain-lain';

  // Placeholder (first option), the optgroups and any options after them (Lain-lain).
  function readSelect(select) {
    var out = { placeholder: null, groups: [], extra: [] };
    var kids = select.children;
    for (var i = 0; i < kids.length; i++) {
      var el = kids[i];
      if (el.tagName === 'OPTGROUP') {
        var opts = [];
        for (var j = 0; j < el.children.length; j++) opts.push({ value: el.children[j].value, text: el.children[j].textContent });
        out.groups.push({ state: el.getAttribute('data-state'), district: el.getAttribute('data-district'), options: opts });
      } else if (el.value === '' && out.placeholder === null) {
        out.placeholder = el.textContent;
      } else {
        out.extra.push({ value: el.value, text: el.textContent });
      }
    }
    return out;
  }

  function fill(select, placeholder, options, keep) {
    while (select.firstChild) select.removeChild(select.firstChild);
    var first = document.createElement('option');
    first.value = '';
    first.textContent = placeholder;
    select.appendChild(first);
    var found = false;
    for (var i = 0; i < options.length; i++) {
      var o = document.createElement('option');
      o.value = options[i].value;
      o.textContent = options[i].text;
      if (options[i].value === keep) found = true;
      select.appendChild(o);
    }
    select.value = found ? keep : '';
  }

  // Show the text field of a select's "Lain-lain" choice only when it is chosen (and make it required then).
  function bindOther(select) {
    var field = document.getElementById(select.getAttribute('data-other'));
    if (!field) return function () {};
    var input = field.querySelector('input');
    function sync() {
      var on = select.value === OTHER;
      field.hidden = !on;
      if (input) input.required = on;
    }
    select.addEventListener('change', sync);
    sync();
    return sync;
  }

  function nadiRegisterForm(form) {
    if (!form || form.getAttribute('data-nadi-ready')) return;
    form.setAttribute('data-nadi-ready', '1');
    var state = form.elements.state;
    var district = form.elements.district;
    var nadi = form.elements.nadi;
    var occupation = form.elements.occupation;
    if (occupation) bindOther(occupation);
    if (!state || !district || !nadi) return;
    var districts = readSelect(district);
    var centers = readSelect(nadi);
    var syncNadiOther = bindOther(nadi);

    function update(keepDistrict, keepNadi) {
      var s = state.value;
      var dOpts = [];
      for (var i = 0; i < districts.groups.length; i++) {
        if (districts.groups[i].state === s) dOpts = dOpts.concat(districts.groups[i].options);
      }
      fill(district, s ? 'Pilih daerah' : 'Pilih negeri dahulu', dOpts, keepDistrict);
      district.disabled = !s;
      var d = district.value;
      var nOpts = [];
      for (var k = 0; k < centers.groups.length; k++) {
        var g = centers.groups[k];
        if (g.state === s && g.district === d) nOpts = nOpts.concat(g.options);
      }
      fill(nadi, d ? 'Pilih pusat NADI' : 'Pilih daerah dahulu', d ? nOpts.concat(centers.extra) : [], keepNadi);
      nadi.disabled = !d;
      syncNadiOther();
    }
    state.addEventListener('change', function () { update('', ''); });
    district.addEventListener('change', function () { update(district.value, ''); });
    update(district.value, nadi.value);
  }

  window.nadiRegisterForm = nadiRegisterForm;
  nadiRegisterForm(document.getElementById('register-form'));
})();
