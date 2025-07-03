 <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Panel de Administración</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarText">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0" id="mainNav">
        <li class="nav-item">
          <a class="nav-link" aria-current="page" href="/inet/index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/inet/eventos.php" aria-current="page">Eventos</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/inet/competencias.php" aria-current="page" >Competencias</a>
        </li>
      </ul>

    </div>
  </div>
</nav>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const currentPage = window.location.pathname.split("/").pop(); 
    const navLinks = document.querySelectorAll("#mainNav .nav-link");

    navLinks.forEach(link => {
      const linkPage = link.getAttribute("href");
      if (linkPage === currentPage || (linkPage === 'index.html' && currentPage === '')) {
        link.classList.add("active");
      } else {
        link.classList.remove("active");
      }
    });
  });
</script>