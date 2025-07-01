<?php
$conn = new mysqli("localhost", "root", "", "prueba");
$conn->set_charset("utf8");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"]);
    $matricula = trim($_POST["matricula"]);
    $carrera = trim($_POST["carrera"]);
    $correo = trim($_POST["correo"]);
    $contrasena = $_POST["contrasena"];

    if (
        empty($nombre) || empty($matricula) ||
        empty($carrera) || empty($correo) || empty($contrasena)
    ) {
        echo "<script>alert('❌ Todos los campos son obligatorios.'); window.history.back();</script>";
        exit();
    }

    $stmt = $conn->prepare("SELECT id_alumno FROM alumno WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<script>alert('❌ Este correo ya está registrado.'); window.history.back();</script>";
        $stmt->close();
        exit();
    }

    $stmt->close();

    $hash = password_hash($contrasena, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO alumno (nombre, matricula, carrera, correo, contrasena) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nombre, $matricula, $carrera, $correo, $hash);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Registro exitoso. Serás redirigido al login.'); window.location.href = 'login.html';</script>";
    } else {
        echo "<script>alert('❌ Error al registrar el usuario.'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
