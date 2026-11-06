// Konfirmasi hapus tanpa handler inline (CSP). Pakai atribut data-konfirmasi="pesan" pada <form> atau <button>.
document.addEventListener('submit', function (e) {
  var pesan = e.target.getAttribute && e.target.getAttribute('data-konfirmasi');
  if (pesan && !window.confirm(pesan)) e.preventDefault();
});
document.addEventListener('click', function (e) {
  var el = e.target.closest && e.target.closest('button[data-konfirmasi]');
  if (el && !window.confirm(el.getAttribute('data-konfirmasi'))) e.preventDefault();
});
