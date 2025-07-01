<?php
$conn = new mysqli("localhost", "root", "", "prueba");
$conn->set_charset("utf8");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["archivo"])) {
    $titulo = trim($_POST["titulo"]);
    $materia = trim($_POST["materia"]);

    $archivoNombre = $_FILES["archivo"]["name"];
    $archivoTmp = $_FILES["archivo"]["tmp_name"];
    $destino = "libros/" . basename($archivoNombre);

    if (move_uploaded_file($archivoTmp, $destino)) {
        $stmt = $conn->prepare("INSERT INTO libros (titulo, materia, archivo) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $titulo, $materia, $destino);

        if ($stmt->execute()) {
            echo "✅ Libro subido correctamente.";
            // Puedes redirigir al panel de admin si quieres:
            // header("Location: admin.php");
        } else {
            echo "❌ Error al guardar en la base de datos.";
        }

        $stmt->close();
    } else {
        echo "❌ Error al mover el archivo.";
    }
}

$conn->close();
?>
