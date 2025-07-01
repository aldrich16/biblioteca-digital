<?php
$conn = new mysqli("localhost", "root", "", "prueba");
$conn->set_charset("utf8");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $conn->real_escape_string($_POST["titulo"]);
    $materia = $conn->real_escape_string($_POST["materia"]);

    if (isset($_FILES["archivo"]) && $_FILES["archivo"]["error"] == 0) {
        $archivo_nombre = basename($_FILES["archivo"]["name"]);
        $archivo_tmp = $_FILES["archivo"]["tmp_name"];
        $ruta_destino = "libros/" . $archivo_nombre;

        if (move_uploaded_file($archivo_tmp, $ruta_destino)) {
            $stmt = $conn->prepare("INSERT INTO libros (titulo, materia, archivo) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $titulo, $materia, $archivo_nombre);
            if ($stmt->execute()) {
                header("Location: admin.php?mensaje=libro_subido");
            } else {
                echo "Error al guardar en la base de datos.";
            }
        } else {
            echo "Error al mover el archivo.";
        }
    } else {
        echo "Error al subir archivo.";
    }
}
?>
