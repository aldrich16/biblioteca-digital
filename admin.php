<?php
session_start();

if (!isset($_SESSION["usuario"]) || $_SESSION["rol"] !== "admin") {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "prueba");
$conn->set_charset("utf8");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["eliminar_usuario"])) {
    $id = intval($_POST["eliminar_usuario"]);
    $conn->query("DELETE FROM alumno WHERE id_alumno = $id");
    $conn->query("DELETE FROM docentes WHERE id_docente = $id");
}

$sql1 = "SELECT carrera, COUNT(*) AS total FROM alumno WHERE rol != 'admin' GROUP BY carrera";
$res1 = $conn->query($sql1);

$sql2 = "SELECT nombre, busquedas FROM materias ORDER BY busquedas DESC LIMIT 5";
$res2 = $conn->query($sql2);

$sql3 = "SELECT titulo, visitas FROM libros ORDER BY visitas DESC LIMIT 5";
$res3 = $conn->query($sql3);

$sqlUsuarios = "
    SELECT id_alumno AS id, nombre, correo, carrera, rol FROM alumno WHERE rol != 'admin'
    UNION ALL
    SELECT id_docente AS id, nombre, correo, carrera, rol FROM docentes
";

$resUsuarios = $conn->query($sqlUsuarios);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Panel Administrador - Biblioteca Digital</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f0f0f0;
      margin: 0;
      padding: 0;
    }
    header {
      background: linear-gradient(to right, #2d502c, #1ad849);
      color: white;
      padding: 10px;
      text-align: center;
    }
    header img {
      height: 40px;
      vertical-align: middle;
    }
    .tabs {
      display: flex;
      justify-content: center;
      background: #eee;
      flex-wrap: wrap;
    }
    .tab-button {
      padding: 10px 20px;
      background: #ddd;
      border: none;
      cursor: pointer;
      margin: 5px;
      font-size: 16px;
    }
    .tab-button.active {
      background: #1ab849;
      color: white;
    }
    .tab-content {
      display: none;
      padding: 20px;
      background: white;
      margin: 10px;
      border-radius: 10px;
    }
    .tab-content.active {
      display: block;
    }
    ul {
      list-style: none;
      padding: 0;
    }
    li {
      padding: 8px 0;
      border-bottom: 1px solid #ccc;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    table, th, td {
      border: 1px solid #ccc;
    }
    th, td {
      padding: 8px;
      text-align: center;
    }
    button {
      background: #1ab849;
      color: white;
      padding: 8px 16px;
      border: none;
      margin-top: 10px;
      cursor: pointer;
      border-radius: 5px;
    }
    .delete-btn {
      background: #d62828;
    }
    .card-container {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
      justify-content: center;
      margin-bottom: 15px;
    }
    .card {
      background: #fff;
      padding: 15px;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      width: 350px;
      text-align: center;
    }
    canvas {
      max-width: 100%;
      height: 250px !important;
    }
  </style>
</head>
<body>

<header>
  <img src="cuervo.png" alt="Logo Biblioteca" />
  <h1><i class="fas fa-book-reader"></i> Panel del Administrador</h1>
  <p>Bienvenido, <?php echo htmlspecialchars($_SESSION["usuario"]); ?></p>
  <form action="logout.php" method="post"><button type="submit">Cerrar sesión</button></form>
</header>

<div class="tabs">
  <button class="tab-button active" data-tab="alumnos">👨‍🎓 Alumnos por carrera</button>
  <button class="tab-button" data-tab="materias">📚 Materias más buscadas</button>
  <button class="tab-button" data-tab="libros">📖 Libros más vistos</button>
  <button class="tab-button" data-tab="graficos">📊 Reportes en gráficos</button>
  <button class="tab-button" data-tab="usuarios">👥 Usuarios registrados</button>
  <button class="tab-button" data-tab="subir">📤 Subir libro</button>
</div>

<div class="tab-content active" id="alumnos">
  <h3>Alumnos por carrera</h3>
  <ul>
    <?php while ($row = $res1->fetch_assoc()) { ?>
      <li><?php echo htmlspecialchars($row["carrera"]); ?>: <?php echo $row["total"]; ?> alumnos</li>
    <?php } ?>
  </ul>
</div>

<div class="tab-content" id="materias">
  <h3>Materias más buscadas</h3>
  <ul>
    <?php while ($row = $res2->fetch_assoc()) { ?>
      <li><?php echo htmlspecialchars($row["nombre"]); ?> (<?php echo $row["busquedas"]; ?> búsquedas)</li>
    <?php } ?>
  </ul>
</div>

<div class="tab-content" id="libros">
  <h3>Libros más vistos</h3>
  <ul>
    <?php while ($row = $res3->fetch_assoc()) { ?>
      <li><?php echo htmlspecialchars($row["titulo"]); ?> (<?php echo $row["visitas"]; ?> vistas)</li>
    <?php } ?>
  </ul>
</div>

<div class="tab-content" id="graficos">
  <h3>Reportes en gráficos</h3>
  <div class="card-container">
    <div class="card">
      <h4>Alumnos por carrera</h4>
      <canvas id="chartCarreras"></canvas>
    </div>
    <div class="card">
      <h4>Usuarios por rol</h4>
      <canvas id="chartRoles"></canvas>
    </div>
  </div>
  <button onclick="descargarPDF()">📄 Descargar PDF</button>
</div>

<div class="tab-content" id="usuarios">
  <h3>Usuarios registrados</h3>
  <table>
    <thead>
      <tr>
        <th>Nombre</th>
        <th>Correo</th>
        <th>Carrera</th>
        <th>Rol</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($user = $resUsuarios->fetch_assoc()) { ?>
        <tr>
          <td><?php echo htmlspecialchars($user["nombre"]); ?></td>
          <td><?php echo htmlspecialchars($user["correo"]); ?></td>
          <td><?php echo htmlspecialchars($user["carrera"]); ?></td>
          <td><?php echo htmlspecialchars($user["rol"]); ?></td>
          <td>
            <form method="post" onsubmit="return confirm('¿Seguro que quieres eliminar este usuario?');">
              <input type="hidden" name="eliminar_usuario" value="<?php echo $user["id"]; ?>">
              <button type="submit" class="delete-btn">Eliminar</button>
            </form>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<div class="tab-content" id="subir">
  <h3>Subir nuevo libro</h3>
  <form action="subir_libro.php" method="POST" enctype="multipart/form-data">
    <label for="titulo">Título del libro:</label><br>
    <input type="text" id="titulo" name="titulo" required><br><br>
    <label for="materia">Materia:</label><br>
    <input type="text" id="materia" name="materia" required><br><br>
    <label for="archivo">Archivo PDF:</label><br>
    <input type="file" id="archivo" name="archivo" accept="application/pdf" required><br><br>
    <button type="submit">Subir libro</button>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
  const tabButtons = document.querySelectorAll(".tab-button");
  const tabContents = document.querySelectorAll(".tab-content");

  tabButtons.forEach(btn => {
    btn.addEventListener("click", () => {
      tabButtons.forEach(b => b.classList.remove("active"));
      tabContents.forEach(c => c.classList.remove("active"));
      btn.classList.add("active");
      const currentTab = document.getElementById(btn.dataset.tab);
      currentTab.classList.add("active");

      // Dibuja los gráficos al mostrar la pestaña correspondiente
      if (btn.dataset.tab === "graficos") {
        dibujarGraficos();
      }
    });
  });

  const datosCarreras = <?php
    $res = $conn->query("SELECT carrera, COUNT(*) AS total FROM alumno WHERE rol != 'admin' GROUP BY carrera");
    $carreras = [];
    $totales = [];
    while ($row = $res->fetch_assoc()) {
      $carreras[] = $row['carrera'];
      $totales[] = $row['total'];
    }
    echo json_encode(['labels' => $carreras, 'data' => $totales]);
  ?>;

  const datosRoles = {
    labels: ['alumno', 'docente', 'administrativo'],
    data: [
      <?php
        $res = $conn->query("SELECT COUNT(*) AS total FROM alumno WHERE rol = 'alumno'");
        echo $res->fetch_assoc()['total'] . ',';

        $res = $conn->query("SELECT COUNT(*) AS total FROM docentes WHERE rol = 'docente'");
        echo $res->fetch_assoc()['total'] . ',';

        $res = $conn->query("SELECT COUNT(*) AS total FROM docentes WHERE rol = 'administrativo'");
        echo $res->fetch_assoc()['total'];
      ?>
    ]
  };

  const coloresPorRol = {
    alumno: 'rgba(126, 217, 234, 0.9)',
    docente: 'rgba(255, 0, 255, 0.9)',
    administrativo: 'rgb(236, 8, 8)'
  };

  const backgroundColors = datosRoles.labels.map(label => coloresPorRol[label]);

  let graficosCargados = false;
  function dibujarGraficos() {
    if (graficosCargados) return;
    graficosCargados = true;

    new Chart(document.getElementById('chartCarreras'), {
      type: 'bar',
      data: {
        labels: datosCarreras.labels,
        datasets: [{
          label: 'Cantidad de alumnos',
          data: datosCarreras.data,
          backgroundColor: 'rgba(9, 250, 1, 0.94)',
          borderColor: 'rgba(9, 250, 1, 1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            ticks: { stepSize: 1 }
          }
        }
      }
    });

    new Chart(document.getElementById('chartRoles'), {
      type: 'pie',
      data: {
        labels: datosRoles.labels,
        datasets: [{
          label: 'Cantidad de usuarios por rol',
          data: datosRoles.data,
          backgroundColor: backgroundColors,
          borderColor: '#fff',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: true,
            position: 'top'
          }
        }
      }
    });
  }

  function descargarPDF() {
    html2canvas(document.querySelector("#graficos")).then(canvas => {
      const imgData = canvas.toDataURL('image/png');
      const { jsPDF } = window.jspdf;
      const pdf = new jsPDF({
        orientation: "landscape",
        unit: "px",
        format: [canvas.width, canvas.height]
      });
      pdf.addImage(imgData, 'PNG', 0, 0, canvas.width, canvas.height);
      pdf.save("reportes_graficos.pdf");
    });
  }
</script>


</body>
</html>
<?php $conn->close(); ?>
