<?php
if (!isset($_GET['archivo'])) {
    http_response_code(400);
    exit('Archivo no especificado.');
}

$archivo = basename($_GET['archivo']); // Evita rutas maliciosas
$ruta = __DIR__ . "/libros/$archivo";

if (!file_exists($ruta)) {
    http_response_code(404);
    exit('Archivo no encontrado.');
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $archivo . '"');
readfile($ruta);
?>
