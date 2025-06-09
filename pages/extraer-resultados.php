<?php
/**
 * procesar_resultados_volcar_bd_html.php
 *
 * Descarga PDFs desde Google Drive, parsea con pdfparser,
 * extrae datos, guarda en JSON y en MySQL, y muestra una tabla HTML.
 */
require __DIR__ . '/../vendor/autoload.php';

use Google\Client;
use Google\Service\Drive;
use Smalot\PdfParser\Parser;

// — 1) Configuración Drive API y descarga de PDFs —
$client = new Client();
$client->setAuthConfig(__DIR__ . '/certifica.json');
$client->addScope(Drive::DRIVE_READONLY);
$drive    = new Drive($client);
$folderId = '1tAkCsZFmDLHULgAkaTMmU7JVXCHWWoW7';

$response = $drive->files->listFiles([
    'q'      => "'{$folderId}' in parents and mimeType='application/pdf'",
    'fields' => 'files(id,name)'
]);

foreach ($response->files as $file) {
    $out = "/tmp/{$file->name}";
    $content = $drive->files->get($file->id, ['alt' => 'media']);
    file_put_contents($out, $content->getBody()->getContents());
}

// — 2) Parser PDF + extracción dinámica de campos —
$parser     = new Parser();
$allResults = [];

foreach (glob('/tmp/*.pdf') as $rutaPdf) {
    $text     = $parser->parseFile($rutaPdf)->getText();
    $lines    = explode("\n", trim($text));
    $category = pathinfo($rutaPdf, PATHINFO_FILENAME);

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || !preg_match('/^\d+\s+/', $line)) {
            continue;
        }

        $tokens = preg_split('/\s+/', $line);
        if (count($tokens) < 8) {
            continue;
        }

        // jueces
        $j3 = (int) array_pop($tokens);
        $j2 = (int) array_pop($tokens);
        $j1 = (int) array_pop($tokens);

        // MV o código de figura
        $maybe = array_pop($tokens);
        if (preg_match('/^\d+\.\d+$/', $maybe)) {
            $mv      = (float) $maybe;
            $pattern = null;
            $points  = (float) array_pop($tokens);
        } else {
            $pattern = $maybe;
            $mv      = (float) array_pop($tokens);
            $points  = (float) array_pop($tokens);
        }

        $position = (int) array_shift($tokens);
        $club     = array_pop($tokens);
        $name     = implode(' ', $tokens);

        $allResults[] = [
            'category'=> $category,
            'position'=> $position,
            'name'    => $name,
            'club'    => $club,
            'points'  => $points,
            'mv'      => $mv,
            'pattern' => $pattern,
            'judge1'  => $j1,
            'judge2'  => $j2,
            'judge3'  => $j3,
        ];
    }
}

// — 4) Guardar en JSON —
file_put_contents(
    __DIR__ . '/resultados.json',
    json_encode($allResults, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);
var_dump(is_writable(__DIR__));       // debe ser true
var_dump(is_writable(__DIR__.'/resultados.json')); // idem

// — 4) (Opcional) Guardar en MySQL —
$dbHost = 'localhost';
$dbName = 'copa_figura';
$dbUser = 'usuario_bd';
$dbPass = 'password_bd';

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    // crear tabla si no existe
    $pdo->exec("CREATE TABLE IF NOT EXISTS resultados (
        id INT AUTO_INCREMENT PRIMARY KEY,
        categoria VARCHAR(255),
        posicion INT,
        nombre VARCHAR(255),
        club VARCHAR(255),
        puntos DECIMAL(6,2),
        mv DECIMAL(6,2),
        pattern VARCHAR(50) NULL,
        juez1 INT,
        juez2 INT,
        juez3 INT,
        UNIQUE KEY uni (categoria, posicion, nombre, club)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $stmt = $pdo->prepare("INSERT INTO resultados
        (categoria,posicion,nombre,club,puntos,mv,pattern,juez1,juez2,juez3)
        VALUES
        (:category,:position,:name,:club,:points,:mv,:pattern,:judge1,:judge2,:judge3)
        ON DUPLICATE KEY UPDATE
            puntos=VALUES(puntos), mv=VALUES(mv), pattern=VALUES(pattern),
            juez1=VALUES(juez1), juez2=VALUES(juez2), juez3=VALUES(juez3)");

    foreach ($allResults as $r) {
        $stmt->execute([
            ':category'=> $r['category'],
            ':position'=> $r['position'],
            ':name'    => $r['name'],
            ':club'    => $r['club'],
            ':points'  => $r['points'],
            ':mv'      => $r['mv'],
            ':pattern' => $r['pattern'],
            ':judge1'  => $r['judge1'],
            ':judge2'  => $r['judge2'],
            ':judge3'  => $r['judge3'],
        ]);
    }
} catch (PDOException $e) {
    error_log('DB Error: '.$e->getMessage());
}

// — 5) Mostrar resultados en HTML —
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Resultados Copa de Figuras</title>
  <style>
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #444; padding: 6px; }
    th { background: #eee; }
  </style>
</head>
<body>
  <h1>Resultados Copa de Figuras</h1>
  <table>
    <tr>
      <th>Categoría</th>
      <th>Pos.</th>
      <th>Nombre</th>
      <th>Club</th>
      <th>Puntos</th>
      <th>M.V.</th>
      <th>Código</th>
      <th>Juez 1</th>
      <th>Juez 2</th>
      <th>Juez 3</th>
    </tr>
    <?php foreach ($allResults as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['category'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= $r['position'] ?></td>
        <td><?= htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars($r['club'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= $r['points'] ?></td>
        <td><?= $r['mv'] ?></td>
        <td><?= htmlspecialchars($r['pattern'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= $r['judge1'] ?></td>
        <td><?= $r['judge2'] ?></td>
        <td><?= $r['judge3'] ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>
