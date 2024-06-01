<?php
// Conexión a la base de datos
$conn = new mysqli('dpi.med.uchile.cl', 'dpimeduchile', 'Zo)g[lH-MqFhBoMa~n', 'dpimeduc_planificacion');
mysqli_set_charset($conn, "utf8");

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener los datos del formulario
$rut_estudiante = $_POST['rut_estudiante'];
$catina = $_POST['catina'];
$covid = isset($_POST['covid']) ? 1 : 0;

// Obtener las actividades seleccionadas
$actividadesSeleccionadas = [];
if (isset($_POST['actividadesSeleccionadas'])) {
    foreach ($_POST['actividadesSeleccionadas'] as $actividadSeleccionada) {
        $actividadesSeleccionadas[] = json_decode($actividadSeleccionada, true);
    }
}
echo "<pre>";
print_r($actividadesSeleccionadas);
echo "</pre>";

// Obtener las actividades manuales
$actividadesManuales = array();
if (isset($_POST['actividadesManuales'])) {
    $actividadesManuales = json_decode($_POST['actividadesManuales'], true);
}
echo "<pre>";
print_r($actividadesManuales);
echo "</pre>";

// Insertar los datos generales en la tabla correspondiente
$sql = "INSERT INTO tabla_general (rut_estudiante, categoria, covid) VALUES ('$rut_estudiante', '$catina', '$covid')";
if ($conn->query($sql) === true) {
    $idGeneralInsertado = $conn->insert_id;

    // Insertar las actividades seleccionadas en la tabla correspondiente
    foreach ($actividadesSeleccionadas as $actividad) {
        $curso_id = $actividad['curso_id'];
        $actividad_id = $actividad['actividad_id'];
        $fecha = $actividad['fecha'];
        $hora = $actividad['hora'];

        $sql = "INSERT INTO tabla_actividades (id_general, curso_id, actividad_id, fecha, hora) VALUES ('$idGeneralInsertado', '$curso_id', '$actividad_id', '$fecha', '$hora')";
        $conn->query($sql);
    }

    // Insertar las actividades manuales en la tabla correspondiente
    if (!empty($actividadesManuales)) {
        foreach ($actividadesManuales as $curso => $actividades) {
            foreach ($actividades as $actividad) {
                $tipoActividad = $actividad['tipoActividad'];
                $fechaUnico = $actividad['fechaUnico'];
                $fechaInicio = $actividad['fechaInicio'];
                $fechaFin = $actividad['fechaFin'];
                $horario = $actividad['horario'];

                $sql = "INSERT INTO tabla_actividades_manuales (id_general, curso_id, tipo_actividad, fecha_unico, fecha_inicio, fecha_fin, horario) VALUES ('$idGeneralInsertado', '$curso', '$tipoActividad', '$fechaUnico', '$fechaInicio', '$fechaFin', '$horario')";
                $conn->query($sql);
            }
        }
    }

    // Manejar los documentos adjuntos
    $files = $_FILES['doc'];
    foreach ($files['tmp_name'] as $key => $tmp_name) {
        $file_name = $files['name'][$key];
        $file_tmp = $files['tmp_name'][$key];

        // Mover el archivo a una ubicación deseada
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($file_name);

        if (move_uploaded_file($file_tmp, $target_file)) {
            // Insertar el nombre del archivo en la tabla correspondiente
            $sql = "INSERT INTO tabla_documentos (id_general, nombre_archivo) VALUES ('$idGeneralInsertado', '$file_name')";
            $conn->query($sql);
        }
    }

    echo "Datos insertados correctamente";
} else {
    echo "Error al insertar datos: " . $conn->error;
}

$conn->close();