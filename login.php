<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $correo = trim($_POST["correo"]);
    $contrasena = $_POST["contrasena"];

    $conn = new mysqli("localhost", "root", "", "prueba");
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT * FROM alumno WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        if (password_verify($contrasena, $usuario["contrasena"])) {
            $_SESSION["usuario"] = $usuario["nombre"];
            $_SESSION["rol"] = $usuario["rol"];

            if ($usuario["rol"] === "admin") {
                header("Location: admin.php");
            } else {
                header("Location: index.php");
            }
            exit();
        }
    }

    $stmt = $conn->prepare("SELECT * FROM docentes WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $docente = $resultado->fetch_assoc();

        if (password_verify($contrasena, $docente["password"])) {
            $_SESSION["usuario"] = $docente["nombre"];
            $_SESSION["rol"] = "maestro";

            header("Location: index.php");
            exit();
        }
    }

    echo "<script>alert('❌ Usuario o contraseña incorrectos.'); window.location.href = 'login.html';</script>";

    $stmt->close();
    $conn->close();
}
?>
