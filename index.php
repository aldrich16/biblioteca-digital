<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Biblioteca Digital</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f4f4f8;
      color: #333;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    header {
      background: linear-gradient(to right, #2d502c, #1ad849);
      color: white;
      text-align: center;
      padding: 1rem 2rem;
      position: relative;
    }

    .logo img {
      width: 60px;
      vertical-align: middle;
    }

    header h1 {
      font-size: 2rem;
      margin: 0.5rem 0;
    }

    header p {
      font-size: 1rem;
    }

    header form {
      position: absolute;
      top: 1rem;
      right: 2rem;
    }

    header button {
      background-color: #ff4d4d;
      color: white;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 20px;
      cursor: pointer;
      font-weight: bold;
      transition: background 0.3s;
    }

    header button:hover {
      background-color: #cc0000;
    }

    #toggleMenuBtn {
      display: none;
      position: absolute;
      left: 1rem;
      top: 1rem;
      background: none;
      border: none;
      color: white;
      font-size: 1.5rem;
      cursor: pointer;
    }

    main {
      display: flex;
      padding: 1rem;
      flex: 1;
    }

    #menu-materias {
      width: 300px;
      background-color: white;
      padding: 1rem;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      margin-right: 1rem;
      height: fit-content;
      transition: all 0.3s ease;
    }

    #menu-materias h2 {
      margin-bottom: 1rem;
      color: rgb(0, 102, 14);
    }

    #buscador {
      width: 100%;
      padding: 0.5rem;
      margin-bottom: 1rem;
      border-radius: 8px;
      border: 1px solid #ccc;
    }

    .materia {
      margin-bottom: 1rem;
    }

    .materia-btn {
      background: linear-gradient(to right, #2d502c, #1ad849);
      color: white;
      padding: 0.6rem 1rem;
      width: 100%;
      border: none;
      border-radius: 6px;
      text-align: left;
      cursor: pointer;
      font-weight: bold;
      transition: background 0.3s;
    }

    .materia-btn:hover,
    .materia-btn.activo {
      background: linear-gradient(to right, #2d502c, #1ad849);
    }

    .libros {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.4s ease;
      margin-top: 0.3rem;
      padding-left: 1rem;
    }

    .libros a {
      display: block;
      text-decoration: none;
      padding: 0.4rem 0;
      color: #333;
      border-left: 3px solid transparent;
      transition: all 0.2s;
    }

    .libros a:hover {
      color: #1ad849;
      border-left: 3px solid #1ad849;
      background-color: #f0f8ff;
    }

    #visor-contenedor {
      flex: 1;
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      overflow: hidden;
    }

    iframe {
      border: none;
      width: 100%;
      height: 600px;
    }

    footer {
      text-align: center;
      padding: 1rem;
      background: linear-gradient(to right, #2d502c, #1ad849);
      color: white;
    }

    @media (max-width: 768px) {
      main {
        flex-direction: column;
      }

      #menu-materias {
        display: none;
        width: 100%;
        margin-bottom: 1rem;
      }

      #menu-materias.activo {
        display: block;
      }

      #toggleMenuBtn {
        display: block;
      }

      header h1 {
        font-size: 1.5rem;
      }

      iframe {
        height: 400px;
      }
    }
  </style>
</head>
<body>

  <header>
    <button id="toggleMenuBtn"><i class="fas fa-bars"></i></button>
    <div class="logo">
      <img src="cuervo.png" alt="Logo Biblioteca" />
    </div>
    <h1><i class="fas fa-book-reader"></i> Biblioteca Digital</h1>
    <p>Explora libros por materia y visualízalos siempre que lo necesites</p>
    <form action="logout.php" method="post">
      <button type="submit">Cerrar sesión</button>
    </form>
  </header>

  <main>
    <aside id="menu-materias">
      <h2><i class="fas fa-list"></i> Categorías</h2>
      <input type="text" id="buscador" placeholder="Buscar libro..." />

      <?php
      $conn = new mysqli("localhost", "root", "", "prueba");
      $conn->set_charset("utf8");

      $sql = "SELECT * FROM libros ORDER BY materia, titulo";
      $result = $conn->query($sql);

      $materias = [];

      while ($row = $result->fetch_assoc()) {
          $materia = $row['materia'];
          $materias[$materia][] = $row;
      }

      foreach ($materias as $nombreMateria => $libros) {
          echo "<div class='materia'>";
          echo "<button class='materia-btn'><i class='fas fa-folder'></i> " . htmlspecialchars($nombreMateria) . "</button>";
          echo "<div class='libros'>";
          foreach ($libros as $libro) {
              $titulo = htmlspecialchars($libro['titulo']);
              $archivo = htmlspecialchars($libro['archivo']);
              echo "<a href='#' onclick=\"verLibro('libros/" . rawurlencode($archivo) . "')\"><i class='fas fa-book'></i> $titulo</a>";
          }
          echo "</div></div>";
      }

      $conn->close();
      ?>
    </aside>

    <section id="visor-contenedor">
      <iframe id="visor"></iframe>
    </section>
  </main>

  <footer>
    <p>&copy; 2025 Biblioteca Digital UTVT</p>
  </footer>

  <script>
    function verLibro(ruta) {
      const visor = document.getElementById('visor');
      if (visor) {
        const archivo = ruta.split('/').pop();
        let fullUrl;

        if (location.hostname === "localhost" || location.hostname === "127.0.0.1") {
          // Modo local: carga directa (con barra del navegador)
          fullUrl = ruta;
        } else {
          // Producción: carga con Google Docs Viewer sin descarga
          fullUrl = "https://docs.google.com/gview?embedded=true&url=" +
                    encodeURIComponent(location.origin + "/" + ruta);
        }

        visor.src = fullUrl;

        // Registrar vista
        fetch('registrar_evento.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `tipo=vista&valor=${encodeURIComponent(archivo)}`
        }).catch(error => console.error('Error al registrar el evento:', error));
      }
    }

    const botones = document.querySelectorAll('.materia-btn');
    botones.forEach(boton => {
      boton.addEventListener('click', () => {
        const panel = boton.nextElementSibling;
        boton.classList.toggle('activo');
        panel.style.maxHeight = panel.style.maxHeight ? null : panel.scrollHeight + "px";
      });
    });

    const buscador = document.getElementById('buscador');
    buscador.addEventListener('keyup', () => {
      const filtro = buscador.value.toLowerCase();
      const materias = document.querySelectorAll('.materia');
      materias.forEach(materia => {
        const libros = materia.querySelectorAll('.libros a');
        let visible = false;
        libros.forEach(libro => {
          const texto = libro.textContent.toLowerCase();
          const coincide = texto.includes(filtro);
          libro.style.display = coincide ? 'block' : 'none';
          if (coincide) visible = true;
        });
        materia.style.display = visible ? 'block' : 'none';
      });
    });

    const toggleBtn = document.getElementById('toggleMenuBtn');
    const menuMaterias = document.getElementById('menu-materias');
    toggleBtn.addEventListener('click', () => {
      menuMaterias.classList.toggle('activo');
    });
  </script>

</body>
</html>
