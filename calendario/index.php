<?php
// calendario/index.php
session_start();

?>
<?php include __DIR__ . '/../templates/header.php';
?>
<style>
<?php include __DIR__ . '/../css/calendario.css'; ?>
<?php include __DIR__ . '/../css/style.css'; ?>
</style>
<main>
  
  <div class="header-content">
    
    <h1>Calendario General 2025</h1>
  </div>

  <div class="calendar-container">
    <?php
    $eventos = [
      ["fecha" => "02/05 – 04/05", "start" => "2025-05-02", "titulo" => "1º Copa de Figuras", "detalles" => ["Nivel: Todos (Clasificatoria Panamericano)", "Zona: Todas", "Organizador: Federación"]],
      ["fecha" => "12/05 – 18/05", "start" => "2025-05-12", "titulo" => "AIS Trieste", "detalles" => ["Nivel: Internacional (ITA)", "Organizador: WorldSkate"]],
      ["fecha" => "22/05 – 25/05", "start" => "2025-05-22", "titulo" => "Clasificatorio Panamericano", "detalles" => ["Nivel: Específico Internacional", "Zona: Stgo", "Organizador: Federación"]],
      ["fecha" => "20/05 – 01/06", "start" => "2025-05-20", "titulo" => "1º Ranking Formativo, Intermedio y Escuelas", "detalles" => ["Zona: Norcosta", "Organizador: Zona"]],
      ["fecha" => "09/06 – 15/06", "start" => "2025-06-09", "titulo" => "Final WorldSkate Clasificados", "detalles" => ["Nivel: EUR", "Organizador: WorldSkate"]],
      ["fecha" => "14/06 – 15/06", "start" => "2025-06-14", "titulo" => "Clasificatorio JJPP Junior", "detalles" => ["Nivel: Todo Competidor Sub23 (Sistema Excepcional para damas Freeskating)", "Zona: Centro", "Organizador: Federación"]],
      ["fecha" => "26/06 – 04/07", "start" => "2025-06-26", "titulo" => "Panamericano Naciones – Clubes", "detalles" => ["Nivel: BBAA", "Organizador: WorldSkate"]],
      ["fecha" => "17/07 – 20/07", "start" => "2025-07-17", "titulo" => "1º Ranking Formativo, Intermedio y Escuelas", "detalles" => ["Zona: Sur", "Organizador: Zona"]],
      ["fecha" => "24/07 – 27/07", "start" => "2025-07-24", "titulo" => "1º Ranking Escuelas", "detalles" => ["Zona: Centro", "Organizador: Zona"]],
      ["fecha" => "31/07 – 03/08", "start" => "2025-07-31", "titulo" => "1º Ranking Promoción", "detalles" => ["Organizador: Federación"]],
      ["fecha" => "07/08 – 10/08", "start" => "2025-08-07", "titulo" => "Clasificatorio Mundial – Juegos Bolivarianos", "detalles" => ["Nivel: Internacional", "Zona: Stgo", "Organizador: Federación"]],
      ["fecha" => "21/08 – 22/08", "start" => "2025-08-21", "titulo" => "JJPP Junior Internacional", "detalles" => ["Evento: ASU Panam"]],
      ["fecha" => "28/08 – 31/08", "start" => "2025-08-28", "titulo" => "1º Ranking Formativo e Intermedio", "detalles" => ["Zona: Centro", "Organizador: Zona"]],
      ["fecha" => "05/09 – 07/09", "start" => "2025-09-05", "titulo" => "2ª Copa de Figuras", "detalles" => ["Nivel: Todos (Clasificatoria Sudamericano y Mundial)", "Zona: Todas", "Organizador: Federación"]],
      ["fecha" => "11/09 – 14/09", "start" => "2025-09-11", "titulo" => "Campeonato Nacional y Clasificatorio Sudamericano", "detalles" => ["Nivel: Internacional", "Organizador: Federación"]],
      ["fecha" => "25/09 – 28/09", "start" => "2025-09-25", "titulo" => "2º Ranking Formativo, Intermedio y Escuelas", "detalles" => ["Zona: Norcosta", "Organizador: Zona"]],
      ["fecha" => "02/10 – 05/10", "start" => "2025-10-02", "titulo" => "2º Ranking Promoción", "detalles" => ["Zona: Sur", "Organizador: Zona"]],
      ["fecha" => "09/10 – 12/10", "start" => "2025-10-09", "titulo" => "2º Ranking Formativo, Intermedio y Escuelas", "detalles" => ["Zona: Sur", "Organizador: Zona"]],
      ["fecha" => "29/10 – 02/11", "start" => "2025-10-29", "titulo" => "2º Ranking Formativo, Intermedio y Escuelas", "detalles" => ["Zona: Centro", "Organizador: Zona"]],
      ["fecha" => "17/11 – 23/11", "start" => "2025-11-17", "titulo" => "Campeonato Nacional Formativo, Intermedio, Escuelas y Promoción", "detalles" => ["Zona: Todas", "Organizador: Federación"]],
    ];

    foreach ($eventos as $e) {
      echo "<div class='calendar-card' data-start='{$e['start']}'>";
      echo "  <div class='calendar-date'>{$e['fecha']}</div>";
      echo "  <h2 class='calendar-event'>{$e['titulo']}</h2>";
      echo "  <div class='calendar-details'>";
      foreach ($e['detalles'] as $d) {
        echo "    <p><strong>{$d}</strong></p>";
      }
      echo "  </div>";
      echo "</div>";
    }
    ?>
  </div>

  <div id="modal" class="modal">
    <div class="modal-content">
      <button class="close">&times;</button>
      <div id="modal-body"></div>
    </div>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.calendar-card').forEach(card => {
    card.addEventListener('click', () => {
      const modal = document.getElementById('modal');
      const body = document.getElementById('modal-body');
      const title = card.querySelector('.calendar-event').innerText;
      const details = card.querySelector('.calendar-details').innerHTML;
      body.innerHTML = `<h2>${title}</h2>${details}`;
      modal.classList.add('show');
    });
  });

  document.querySelector('.modal .close').addEventListener('click', () => {
    document.getElementById('modal').classList.remove('show');
  });
  document.getElementById('modal').addEventListener('click', e => {
    if (e.target === e.currentTarget) {
      e.currentTarget.classList.remove('show');
    }
  });

  // Resaltar próximo evento
  (function(){
    const cards = Array.from(document.querySelectorAll('.calendar-card'));
    const today = new Date();
    const next = cards.map(c => ({c, d:new Date(c.dataset.start)})).filter(o=>o.d>=today).sort((a,b)=>a.d-b.d)[0];
    if(next) next.c.classList.add('upcoming');
  })();
});
</script>

<?php include __DIR__ . '/../templates/footer.php'; ?>

