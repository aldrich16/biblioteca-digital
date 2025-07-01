<?php
require 'conexion.php';

$error = "";
$success = "";
$redirect = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $conn->real_escape_string($_POST["nombre"]);
    $correo = $conn->real_escape_string($_POST["correo"]);
    $carrera = $conn->real_escape_string($_POST["carrera"]);
    $rol = $conn->real_escape_string($_POST["rol"]);
    $password = $_POST["password"];

    if (empty($nombre) || empty($correo) || empty($carrera) || empty($rol) || empty($password)) {
        $error = "Todos los campos son obligatorios.";
    } elseif (!in_array($rol, ['docente', 'administrativo'])) {
        $error = "Rol no válido.";
    } else {
        // Verificar si el correo ya está registrado
        $stmt = $conn->prepare("SELECT id_docente FROM docentes WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Este correo ya está registrado.";
        } else {
            $stmt->close();

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO docentes (nombre, correo, carrera, password, rol) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nombre, $correo, $carrera, $passwordHash, $rol);

            if ($stmt->execute()) {
                header("Refresh:1; url=login.html");
                $success = "Registro exitoso. Redirigiendo al login...";
                $redirect = true;
            } else {
                $error = "Error al registrar docente.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro Docente</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to right, #2d502c, #1ad849);
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      color: white;
      text-align: center;
    }
    .login-contenedor {
      max-width: 400px;
      width: 100%;
      background-color: rgba(0, 0, 0, 0.7);
      padding: 40px;
      border-radius: 10px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
    }
    .login-contenedor h1 {
      font-size: 30px;
      margin-bottom: 30px;
    }
    input, select {
      width: 100%;
      padding: 12px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 5px;
      background-color: #f7f7f7;
      font-size: 16px;
    }
    input:focus, select:focus {
      border-color: #3498db;
      outline: none;
    }
    .boton-login {
      width: 100%;
      padding: 12px;
      background-color: #1abc9c;
      color: white;
      border: none;
      font-size: 16px;
      border-radius: 5px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    .boton-login:hover {
      background-color: #16a085;
    }
    .message {
      padding: 10px;
      margin-bottom: 15px;
      border-radius: 5px;
      font-weight: bold;
    }
    .success {
      background-color: #2ecc71;
      color: white;
    }
    .error {
      background-color: #e74c3c;
      color: white;
    }
  </style>
</head>
<body>
  <div class="login-contenedor">
    <h1>Registro Docente</h1>

    <?php if (!empty($error)) : ?>
      <div class="message error"><?php echo $error; ?></div>
    <?php elseif (!empty($success)) : ?>
      <div class="message success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="post" action="">
      <input type="text" name="nombre" placeholder="Nombre" required>
      <input type="email" name="correo" placeholder="Correo" required>
      <input type="text" name="carrera" placeholder="Carrera" required>
      <select name="rol" required>
        <option value="">Selecciona una función</option>
        <option value="docente">Docente</option>
        <option value="administrativo">Administrativo</option>
      </select>
      <input type="password" name="password" placeholder="Contraseña" required>
      <button type="submit" class="boton-login">Registrar</button>
    </form>
  </div>
</body>
</html>
