<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Comision Tecnica - Patinaje Artistico – Herramientas</title>
  <link rel="stylesheet" href="css/style.css">
  <script src="js/main.js"></script>
</head>
<?php
// error-404.php
http_response_code(404);
// Incluir header desde templates/

include __DIR__ . '/templates/header.php';
?>

<main>
  <section class="hero error-hero">
    <div class="container">
      <h1>Error 404<br><small>Página No Encontrada</small></h1>
      <p>Lo sentimos, la página que buscas no existe o fue movida. Por favor verifica la URL o regresa al inicio.</p>
      <a href="/index.php" class="btn">Ir al Inicio</a>
    </div>
  </section>
</main>

<?php



