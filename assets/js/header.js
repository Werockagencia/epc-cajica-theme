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

(function () {
  'use strict';
  // "Servicios" es un enlace normal dentro del bloque de navegación (no hay
  // forma nativa de inyectarle un mega-menú de varias columnas al bloque),
  // así que se ubica después de renderizado y se le engancha el panel
  // .servicios-mega ya presente en el header como hermano de <header>.
  var enlaces = document.querySelectorAll('.nav-principal-epc a');
  var link = null;
  for (var i = 0; i < enlaces.length; i++) {
    var href = enlaces[i].getAttribute('href') || '';
    if (/\/servicios\/?$/.test(href)) { link = enlaces[i]; break; }
  }
  var menu = document.getElementById('megaServicios');
  if (!link || !menu) return;

  var item = link.closest('.wp-block-navigation-item') || link.parentElement;
  item.classList.add('tiene-mega');
  var contenido = link.querySelector('.wp-block-navigation-item__label') || link;
  var flecha = document.createElement('span');
  flecha.className = 'flecha-mega';
  contenido.appendChild(flecha);
  link.setAttribute('aria-expanded', 'false');

  var cerrarTimer;
  var abrir = function () {
    clearTimeout(cerrarTimer);
    menu.classList.add('abierto');
    item.classList.add('abierto');
    link.setAttribute('aria-expanded', 'true');
  };
  var cerrar = function () {
    menu.classList.remove('abierto');
    item.classList.remove('abierto');
    link.setAttribute('aria-expanded', 'false');
  };
  var cerrarConRetraso = function () {
    clearTimeout(cerrarTimer);
    cerrarTimer = setTimeout(cerrar, 180);
  };

  item.addEventListener('mouseenter', abrir);
  item.addEventListener('mouseleave', cerrarConRetraso);
  menu.addEventListener('mouseenter', function () { clearTimeout(cerrarTimer); });
  menu.addEventListener('mouseleave', cerrarConRetraso);

  link.addEventListener('click', function (e) {
    e.preventDefault();
    if (menu.classList.contains('abierto')) cerrar(); else abrir();
  });
  document.addEventListener('click', function (e) {
    if (!menu.contains(e.target) && e.target !== link) cerrar();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') cerrar();
  });
})();
