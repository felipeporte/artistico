<?php
// poblar_nivel_categoria.php
ini_set('display_errors',1);
error_reporting(E_ALL);

include 'conexion.php';

// 1) Vaciar la tabla para partir de cero
$pdo->exec("TRUNCATE TABLE nivel_categoria");

// 2) Este es tu mapeo “modalidad => [ 'Nivel Completo' => [categorías] ]”
$mapping = [
  'freeskating' => [
    'Formativo Inicio'         => ['Pre-novato','Novato','Tots','Minis','Espoir'],
    'Formativo Intermedio'     => ['Pre-novato','Novato','Tots','Minis','Espoir'],
    'Escuela D'                => ['Tots','Minis','Espoir','Cadet','Youth','Senior'],
    'Escuela C'                => ['Espoir','Cadet','Youth','Senior'],
    'Escuela B'                => ['Cadet','Youth','Junior','Senior'],
    'Promotional Basic'        => ['Minis','Espoir','Cadet','Youth','Junior','Senior'],
    'Promotional Intermediate' => ['Tots','Minis','Espoir','Cadet','Youth','Junior','Senior'],
    'International'            => ['Tots','Minis','Espoir','Cadet','Youth','Junior','Senior'],
  ],
  'solo_dance' => [
    'Escuela D'                => ['Tots','Minis','Espoir','Cadet','Youth','Junior','Senior'],
    'Escuela C'                => ['Espoir','Cadet','Youth','Junior','Senior'],
    'Promotional Basic'        => ['Minis','Espoir','Cadet','Youth','Junior','Senior'],
    'Promotional Intermediate' => ['Tots','Minis','Espoir','Cadet','Youth','Junior','Senior'],
    'International'            => ['Tots','Minis','Espoir','Cadet','Youth','Junior','Senior'],
  ],
  'figuras' => [
    'Escuela D'           => ['Tots','Minis','Espoir','Cadet','Youth','Junior','Senior'],
    'Escuela C'           => ['Espoir','Cadet','Youth','Junior','Senior'],
    'Eficiencia Básica'   => ['Tots','Minis','Espoir','Cadet'],
    'Eficiencia Intermedia'=> ['Tots','Minis','Espoir','Cadet'],
    'Eficiencia Avanzada' => ['Tots','Minis','Espoir','Cadet','Youth','Junior','Senior'],
    'International'       => ['Tots','Minis','Espoir','Cadet','Youth','Junior','Senior'],
  ],
  'inline' => [
    'Escuela'      => ['Tots','Minis','Espoir','Cadet','Youth','Junior','Senior'],
    'Promotional'  => ['Tots','Minis','Espoir','Cadet','Youth','Junior','Senior'],
    'International'=> ['Tots','Minis','Espoir','Cadet','Youth','Junior','Senior'],
  ],
  'pairs'       => [ 'Escuela'=>['Minis','Espoir','Cadet'], 'Promotional Basic'=>['Minis','Espoir','Cadet','Youth','Junior','Senior'], 'Promotional Intermediate'=>['Tots','Minis','Espoir','Cadet','Youth','Junior','Senior'], 'International'=>['Tots','Minis','Espoir','Cadet','Youth','Junior','Senior'] ],
  'couple_dance'=> [ 'Escuela'=>['Minis','Espoir','Cadet'], 'Promotional Basic'=>['Minis','Espoir','Cadet','Youth','Junior','Senior'], 'Promotional Intermediate'=>['Tots','Minis','Espoir','Cadet','Youth','Junior','Senior'], 'International'=>['Tots','Minis','Espoir','Cadet','Youth','Junior','Senior'] ],
  'show'     => [
    'Promotional junior' => ['tots','minis','espoir','cadet','youth','junior','senior'],
    'Promotional small'  => ['tots','minis','espoir','cadet','youth','junior','senior'],
    'Promotional large'  => ['tots','minis','espoir','cadet','youth','junior','senior'],
    // International junior / small / large
    'International junior' => ['tots','minis','espoir','cadet','youth','junior','senior'],
    'International small'  => ['tots','minis','espoir','cadet','youth','junior','senior'],
    'International large'  => ['tots','minis','espoir','cadet','youth','junior','senior'],
  ],

  
  'quartets' => ['Promotional'=>['Espoir','Cadet','Junior','Senior'], 'International'=>['Espoir','Cadet','Junior','Senior']],
  'precision'=> ['Promotional'=>['Espoir','Junior','Senior'], 'International'=>['Espoir','Junior','Senior']],
  'adaptados'=> ['Inicio'=>['Novato','Minis','Cadet','Junior'], 'Intermedio'=>['Novato','Minis','Cadet','Junior'], 'Avanzado'=>['Novato','Minis','Cadet','Junior']],
];

$inserted = 0;

foreach ($mapping as $modName => $levels) {
    // 3) Modalidad
    $modStmt = $pdo->prepare("SELECT id FROM modalidades WHERE nombre = ?");
    $modStmt->execute([$modName]);
    $modId = $modStmt->fetchColumn();
    if (!$modId) {
        echo "⚠ No existe modalidad «{$modName}»\n";
        continue;
    }

    foreach ($levels as $fullName => $cats) {
        // 4) Separar nombre_nivel y subnivel (si viene con espacio)
        if (strpos($fullName, ' ') !== false) {
            $pos = strrpos($fullName, ' ');
            $nivelName = substr($fullName, 0, $pos);
            $subnivel  = substr($fullName, $pos + 1);
        } else {
            $nivelName = $fullName;
            $subnivel  = '';
        }

        // 5) Buscar todos los nivel_id que coincidan
        $nivStmt = $pdo->prepare("
            SELECT id FROM niveles 
            WHERE modalidad_id = :mid
              AND nombre_nivel  = :niv
              AND COALESCE(subnivel,'') = :sub
        ");
        $nivStmt->execute([
            ':mid' => $modId,
            ':niv' => $nivelName,
            ':sub' => $subnivel
        ]);
        $nivelIds = $nivStmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($nivelIds)) {
            echo "⚠ No se encontró nivel «{$fullName}» en modalidad «{$modName}»\n";
            continue;
        }

        foreach ($nivelIds as $nid) {
            foreach ($cats as $catName) {
                // 6) Categoría
                $catStmt = $pdo->prepare("SELECT id FROM categorias_edad WHERE nombre_categoria = ?");
                $catStmt->execute([$catName]);
                $catId = $catStmt->fetchColumn();
                if (!$catId) {
                    echo "  ⚠ No existe categoría «{$catName}»\n";
                    continue;
                }

                // 7) Insertar si no existe
                $ins = $pdo->prepare("
                    INSERT IGNORE INTO nivel_categoria (nivel_id, categoria_id)
                    VALUES (:nid, :cid)
                ");
                $ins->execute([':nid'=>$nid, ':cid'=>$catId]);
                $inserted += $ins->rowCount();
            }
        }
    }
}

echo "\nTotal insertados en nivel_categoria: {$inserted}\n";
