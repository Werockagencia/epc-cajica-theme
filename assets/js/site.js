(function () {
  'use strict';
  document.querySelectorAll('.acordeon-pub-btn').forEach(function (b) {
    b.addEventListener('click', function () {
      b.closest('.acordeon-pub').classList.toggle('abierto');
    });
  });

  var btnAbrir = document.querySelector('.menu-hamburguesa');
  var menu = document.getElementById('menuMovil');
  if (btnAbrir && menu) {
    var btnCerrar = menu.querySelector('.menu-movil-cerrar');
    var abrir = function () {
      menu.classList.add('abierto');
      document.body.classList.add('menu-movil-abierto');
      btnAbrir.setAttribute('aria-expanded', 'true');
    };
    var cerrar = function () {
      menu.classList.remove('abierto');
      document.body.classList.remove('menu-movil-abierto');
      btnAbrir.setAttribute('aria-expanded', 'false');
    };
    btnAbrir.addEventListener('click', abrir);
    if (btnCerrar) btnCerrar.addEventListener('click', cerrar);
    menu.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', cerrar); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') cerrar(); });
  }
})();
