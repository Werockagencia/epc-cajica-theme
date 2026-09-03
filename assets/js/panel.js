(function () {
  'use strict';

  // Menu lateral en movil
  var btn = document.querySelector('.panel-menu-btn');
  var nav = document.querySelector('.panel-nav');
  var overlay = document.querySelector('.panel-nav-overlay');
  if (btn && nav && overlay) {
    btn.addEventListener('click', function () {
      nav.classList.add('abierto');
      overlay.classList.add('activo');
    });
    overlay.addEventListener('click', function () {
      nav.classList.remove('abierto');
      overlay.classList.remove('activo');
    });
  }

  // Acordeones (Historial de consumo, Mis cuentas, etc.)
  document.querySelectorAll('.acordeon-btn').forEach(function (b) {
    b.addEventListener('click', function () {
      b.closest('.acordeon-item').classList.toggle('abierto');
    });
  });

  // Mis cuentas: "Ver detalle" expande el detalle de esa cuenta en la misma página
  document.querySelectorAll('[data-toggle-cuenta]').forEach(function (b) {
    var detalle = document.getElementById('detalle-' + b.dataset.toggleCuenta);
    if (!detalle) return;
    b.addEventListener('click', function () {
      var abrir = detalle.hasAttribute('hidden');
      detalle.toggleAttribute('hidden', !abrir);
      b.classList.toggle('abierto', abrir);
      b.setAttribute('aria-expanded', String(abrir));
      b.childNodes[0].nodeValue = abrir ? 'Ocultar detalle ' : 'Ver detalle ';
      if (abrir) {
        detalle.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
    });
  });

  // Formularios inline (ej. "+ Agregar medidor" dentro del detalle de una cuenta)
  document.querySelectorAll('[data-toggle-form]').forEach(function (b) {
    var form = document.getElementById('form-' + b.dataset.toggleForm);
    if (!form) return;
    b.addEventListener('click', function () {
      if (b.dataset.saveAlias) {
        var input = document.getElementById('alias-input-' + b.dataset.saveAlias);
        var label = document.getElementById('alias-label-' + b.dataset.saveAlias);
        if (input && label && input.value.trim()) label.textContent = input.value.trim();
      }
      var abrir = form.hasAttribute('hidden');
      if (abrir && form.classList.contains('tramite-form')) {
        document.querySelectorAll('.tramite-form').forEach(function (f) {
          if (f !== form) f.setAttribute('hidden', '');
        });
      }
      form.toggleAttribute('hidden', !abrir);
      if (abrir) {
        form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        var primerInput = form.querySelector('input');
        if (primerInput) primerInput.focus();
      }
    });
  });

  // Selector de cuenta: muestra el bloque .datos-cuenta que corresponda
  var selCuenta = document.getElementById('selector-cuenta');
  if (selCuenta) {
    function pintarCuenta(id) {
      document.querySelectorAll('.datos-cuenta').forEach(function (d) {
        d.classList.toggle('activo', d.dataset.cuenta === id);
      });
    }
    selCuenta.addEventListener('change', function () { pintarCuenta(this.value); });
    pintarCuenta(selCuenta.value);
  }

})();
