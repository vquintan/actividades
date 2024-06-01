<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Justificación de Inasistencias</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
  <div class="container">
    <h2>Justificación de Inasistencias</h2>
    <form id="formularioExterno" action="insert.php" method="post" enctype="multipart/form-data" onsubmit="return validarFormulario();">
      <input type="hidden" id="actividadesSeleccionadas" name="actividadesSeleccionadas" value="">
      <input type="hidden" id="actividadesManualesInput" name="actividadesManuales" value="">
      <input type="hidden" name="rut_estudiante" value="016784781k">
      <input type="hidden" id="idmaster" name="idmaster" value="99">

      <div class="card">
        <div class="card-header">Justificación de Inasistencias</div>
        <div class="card-body">
          <table id="tabla-actividades" class="table table-striped">
            <thead>
              <tr>
                <th>Curso</th>
                <th>Declarar Actividades</th>
              </tr>
            </thead>
            <tbody>
              <?php
			  $mysqli = new mysqli('dpi.med.uchile.cl', 'dpimeduchile', 'Zo)g[lH-MqFhBoMa~n', 'dpimeduc_planificacion');
              $rut_api = '24949120';
              $periodoX = '2024.1';
              $url = 'https://3da5f7dc59b7f086569838076e7d7df5:698c0edbf95ddbde@ucampus.uchile.cl/api/0/medicina_mufasa/cursos_inscritos?rut=24949120&periodo=2024.1';

              $ch = curl_init();
              curl_setopt($ch, CURLOPT_URL, $url);
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
              curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
              curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
              $resultado = curl_exec($ch);
              $characters = json_decode($resultado, true);

              // Función para ordenar alfabéticamente por código
              function sortByCodigo($a, $b)
              {
                return strcmp($a['codigo'], $b['codigo']);
              }

              // Ordenar el array por código ascendente
              usort($characters, 'sortByCodigo');

              foreach ($characters as $character) :
                if ($character['codigo'] !== 'FG00000503410') {
                  $value = $character['codigo'] . '*' . $character['seccion'] . '*' . $character['nombre'] . '*' . $character['id_periodo'];
                  $result = $character['codigo'] . '-' . $character['seccion'] . ' - ' . $character['nombre'];
                  $cursillox = $character['codigo'];
                  $periodex = $character['id_periodo'];
                  $seccionx = $character['seccion'];

                  $queryidc = "SELECT `idCurso` FROM `spre_cursos` WHERE `CodigoCurso` = '$cursillox' AND `idperiodo` = '$periodex' AND `Seccion` = '$seccionx'";
                  $cursete = mysqli_query($mysqli, $queryidc);
                  $rowcursete = mysqli_fetch_assoc($cursete);
                  $idcursete = $rowcursete['idCurso'];
              ?>
                <tr>
                  <td>
                    <div class="form-check form-switch">
                      <input type="checkbox" data-toggle="toggle" data-size="sm" data-onstyle="outline-success" data-offstyle="outline-danger" data-onlabel="Si" data-offlabel="No" class="curso-checkbox" role="switch" id="curso-<?php echo $character['codigo']; ?>" data-curso="<?php echo $idcursete; ?>" name="cursos_seleccionados[]" value="<?php echo $character['codigo']; ?>">
                      <label for="curso-<?php echo $character['codigo']; ?>"><?php echo $character['codigo'] . '-' . $character['seccion'] . ' - ' . $character['nombre']; ?></label>
                    </div>
                  </td>
                  <td>
                    <div id="buscarf-<?php echo $character['codigo']; ?>" style="display: none;">
                      <form id="consultaForm-<?php echo $character['codigo']; ?>">
                        <input type="hidden" id="idmaster" name="idmaster" value="99">
                        <input type="hidden" name="cursoId" id="cursoId" value="<?php echo $idcursete; ?>">
                        <div class="form-group">
                          <label for="fecha"><b>Fecha Inicio:&nbsp</b><small>Obligatorio</small></label>
                          <input type="date" class="form-control" id="fechai" name="fechai">
                        </div>
                        <div class="form-group">
                          <label for="fecha"><b>Fecha Fin:&nbsp</b><small>Opcional</small></label>
                          <input type="date" class="form-control" id="fechaf" name="fechaf">
                        </div>
                        <br>
                        <div class="text-end">
                          <button type="button" id="btnBuscar" class="btn btn-primary btnBuscar" data-curso="<?php echo $idcursete; ?>">Consultar</button>
                        </div>
                      </form>
                    </div>
                    <div id="resultado"></div>
                    <input type="hidden" id="idmaster" name="idmaster" value="99">
                    <hr>
                    <div id="acciones-<?php echo $character['codigo']; ?>" style="display: none;">
                      <table class="table tabla-actividades table-striped">
                        <thead>
                          <tr>
                            <th>Actividad</th>
                            <th>Fecha</th>
                            <th>Horario</th>
                            <th>Accion</th>
                          </tr>
                        </thead>
                        <tbody id="tabla-<?php echo $character['codigo']; ?>">
                        </tbody>
                      </table>
                      <div class="text-end">
                        <button type="button" class="btn btn-info agregar-actividad" data-curso="<?php echo $character['codigo']; ?>">Agregar Actividad Manual</button>
                      </div>
                    </div>
                  </td>
                </tr>
              <?php
                }
              endforeach;
              ?>
            </tbody>
          </table>
        </div>

        <div class="form-group row">
          <label class="col-sm-2 control-label" style="text-align:left;">Tipo de Inasistencia:</label>
          <div class="col-sm-6">
            <span class="input-group">
              <select id="catina" name="catina" onchange="habilitarInputs()" class="form-control">
                <?php
                $querygh = mysqli_query($mysqli, "SELECT `id`, `categoria`, `asistente_social`, `num_doc`, `tipo_doc` FROM `pe_justif_categoria` WHERE 1");
                ?>
                <option value="" disabled selected>Seleccione Tipo de Inasistencia</option>
                <?php while ($generoh = mysqli_fetch_array($querygh)) { ?>
                  <option value="<?php echo $generoh['id'] . '*' . $generoh['asistente_social'] . '*' . $generoh['num_doc'] . '*' . $generoh['tipo_doc']; ?>"><?php echo $generoh['categoria']; ?></option>
                <?php } ?>
              </select>
              <span><small>&nbsp;&nbsp;<i class="fas fa-asterisk"></i></small></span>
            </span>
          </div>
        </div>
        <br>

        <div class="form-group row">
          <label class="col-sm-2 control-label" style="text-align:left;">
            <input class="form-check-input" type="checkbox" data-toggle="toggle" data-onstyle="success" data-offstyle="secondary" data-on="Si" data-off="No" name="covid" value="1"> <span><small>&nbsp;&nbsp;<i class="fas fa-asterisk"></i></small></span>
          </label>
          <div class="col-sm-10">
            <span class="input-group">
              <label class="form-check-label">¿El motivo de la inasistencia corresponde a enfermedad por COVID-19, propia o de un familiar cercano (bajo el mismo techo), o duelo por deceso de un familiar cercano?</label>
              <small>La Facultad ha diseñado un protocolo especial para entregar apoyo a estudiantes que hayan contraído COVID-19, cuyas familias se hayan visto afectadas o estén viviendo un duelo. Por favor infórmanos si tu inasistencia responde a esos motivos.</small>
            </span>
          </div>
        </div>
        <br>

        <div id="inputContainer" class="mb-3">
          <!-- Los campos de carga de archivos se agregarán aquí dinámicamente -->
        </div>

        <div class="form-group row">
          <label class="col-sm-2 control-label"></label>
          <div class="alert alert-secondary col-sm-8">
            <span class="input-group">
              <center><strong>Los certificados deben tener <u>NOMBRE</u>, <u>FIRMA</u> y <u>TIMBRE</u> de la entidad que los emite.</strong></center>
            </span>
          </div>
        </div>
      </div>

      <div class="card-footer text-muted">
        <center>
          <input type="submit" name="guardar" class="btn btn-primary" role="button" aria-pressed="true" value="Guardar">
        </center>
      </div>
    </form>
  </div>

  <?php
  $tipoActividadOptions = "";
  $queryg = mysqli_query($mysqli, "SELECT `idTipoSolicitud`, `TipoSolicitud` FROM `pe_TipoSolicitud` WHERE idModulosEstudiante='6' and idTipoSolicitud<>24");
  while ($genero = mysqli_fetch_array($queryg)) {
      $tipoActividadOptions .= '<option value="' . $genero['idTipoSolicitud'] . '">' . $genero['TipoSolicitud'] . '</option>';
  }

  $horariosOptions = "";
  $queryg = mysqli_query($mysqli, "SELECT `id`, `entra`, `sale` FROM `pe_justif_bloques` WHERE 1 ORDER BY `pe_justif_bloques`.`id` ASC");
  while ($genero = mysqli_fetch_array($queryg)) {
      if (empty($genero['sale'])) {
          $sale = "";
      } else {
          $sale = " - " . $genero['sale'];
      }
      $horariosOptions .= '<option value="' . $genero['id'] . '">' . $genero['entra'] . $sale . '</option>';
  }
  ?>

  <script>
    var tipoActividadOptions = '<?php echo $tipoActividadOptions; ?>';
    var horariosOptions = '<?php echo $horariosOptions; ?>';
  </script>

  <script src="script.js"></script>
</body>
</html>