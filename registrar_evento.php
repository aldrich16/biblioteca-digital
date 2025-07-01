<?php
$conn = new mysqli("localhost", "root", "", "prueba");
$conn->set_charset("utf8");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';
    $valor = isset($_POST['valor']) ? $conn->real_escape_string($_POST['valor']) : '';

    if ($tipo === "vista") {
        $sql = "UPDATE libros SET visitas = visitas + 1 WHERE archivo = '$valor'";
        $conn->query($sql);
    } elseif ($tipo === "busqueda") {
        $sql = "SELECT id_materias FROM materias WHERE nombre LIKE '%$valor%' LIMIT 1";
        $result = $conn->query($sql);
        if ($row = $result->fetch_assoc()) {
            $id_materia = $row['id_materias'];
            $sqlUpdate = "UPDATE materias SET busquedas = busquedas + 1 WHERE id_materias = $id_materia";
            $conn->query($sqlUpdate);
        }
    }
}

$conn->close();
?>
