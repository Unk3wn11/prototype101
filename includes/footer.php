<footer class="main-footer">
  <div class="footer-logo">⚡ Tantiado's E-bike Online Registration</div>
  <p>© <?= date('Y') ?> Tantiado's E-bike Online Registration</p>
  <p>📧 support@ebikereg.gov.ph</p>
</footer>

<script>
  const hamburger = document.getElementById('hamburger');
  const navMenu   = document.getElementById('navMenu');
  if (hamburger) {
    hamburger.addEventListener('click', () => {
      hamburger.classList.toggle('open');
      navMenu.classList.toggle('open');
    });
  }
  document.querySelectorAll('.nav-menu a').forEach(a => {
    a.addEventListener('click', () => {
      hamburger?.classList.remove('open');
      navMenu?.classList.remove('open');
    });
  });
</script>
</body>
</html>
