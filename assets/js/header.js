(function () {
  'use strict';
  var btn = document.querySelector('.bd-mega-btn');
  var menu = document.getElementById('megaTransparencia');
  if (!btn || !menu) return;

  var cerrar = function () {
    menu.classList.remove('abierto');
    btn.setAttribute('aria-expanded', 'false');
  };
  var abrir = function () {
    menu.classList.add('abierto');
    btn.setAttribute('aria-expanded', 'true');
  };

  btn.addEventListener('click', function (e) {
    e.stopPropagation();
    if (menu.classList.contains('abierto')) cerrar(); else abrir();
  });
  document.addEventListener('click', function (e) {
    if (!menu.contains(e.target) && e.target !== btn) cerrar();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') cerrar();
  });
})();
