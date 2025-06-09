<?php
// poblar_niveles_competencia.php

// Mostrar errores en pantalla
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'conexion.php';

try {
    // 1) Leer todas las competencias con sus JSON
    $stmt = $pdo->query("
        SELECT id, nombre_evento, modalidades, niveles 
          FROM competencias
    ");
    $competencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $insertados = 0;

    foreach ($competencias as $comp) {
        $id_comp      = $comp['id'];
        $nombre_comp  = $comp['nombre_evento'];
        $modalidades  = json_decode($comp['modalidades'], true);
        $niveles_json = json_decode($comp['niveles'],     true);

        echo "<h2>Competencia #{$id_comp} – {$nombre_comp}</h2>\n";
        echo "<pre>Modalidades: " . print_r($modalidades, true)
           . "\nNiveles (JSON): " . print_r($niveles_json, true)
           . "</pre>\n";

        if (!is_array($modalidades) || !is_array($niveles_json)) {
            echo "<p style='color:red;'>JSON inválido, saltando...</p>\n";
            continue;
        }

        foreach ($modalidades as $modalidad) {
            foreach ($niveles_json as $nivel) {
                // 2) Buscar IDs de nivel sin usar subniveles_definidos
                $stmtNivel = $pdo->prepare("
                    SELECT n.id
                      FROM niveles n
                      JOIN modalidades m ON n.modalidad_id = m.id
                     WHERE m.nombre       = :mod
                       AND n.nombre_nivel = :niv
                ");
                $stmtNivel->execute([
                    ':mod' => $modalidad,
                    ':niv' => $nivel,
                ]);
                $nivel_ids = $stmtNivel->fetchAll(PDO::FETCH_COLUMN);

                if (empty($nivel_ids)) {
                    echo "<p style='color:orange;'>⚠️ No se encontró nivel para modalidad «{$modalidad}» y nivel «{$nivel}»</p>\n";
                    continue;
                }

                echo "<p>Nivel «{$nivel}» en modalidad «{$modalidad}» → IDs: "
                   . implode(', ', $nivel_ids) . "</p>\n";

                foreach ($nivel_ids as $nivel_id) {
                    // 3) Evitar duplicados
                    $check = $pdo->prepare("
                        SELECT COUNT(*) 
                          FROM niveles_competencia
                         WHERE competencia_id = :cid
                           AND nivel_id        = :nid
                    ");
                    $check->execute([':cid'=>$id_comp, ':nid'=>$nivel_id]);

                    if ($check->fetchColumn() > 0) {
                        echo "<small>— Ya existe (comp {$id_comp}, nivel {$nivel_id})</small><br>\n";
                        continue;
                    }

                    // 4) Insertar
                    $ins = $pdo->prepare("
                        INSERT INTO niveles_competencia (competencia_id, nivel_id)
                        VALUES (:cid, :nid)
                    ");
                    $ins->execute([':cid'=>$id_comp, ':nid'=>$nivel_id]);
                    $insertados++;
                    echo "<small style='color:green;'>✔ Insertado nivel_id {$nivel_id}</small><br>\n";
                }
            }
        }
    }

    echo "<h3>Total insertados: {$insertados}</h3>\n";

} catch (PDOException $e) {
    echo "<p style='color:red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}
