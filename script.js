$(document).ready(function() {
  $('body').on('click', '.btnBuscar', function() {
    event.preventDefault(); // Evita el envío del formulario interno

    var $this = $(this);
    var cursoId = $this.closest('form').find('#cursoId').val();
    var fechai = $this.closest('form').find('#fechai').val();
    var fechaf = $this.closest('form').find('#fechaf').val();

    // Verificar si los campos obligatorios están llenos
    if (cursoId && fechai) {
      $.ajax({
        type: 'POST',
        url: 'pcl_conn.php',
        data: {
          cursoId: cursoId,
          fechai: fechai,
          fechaf: fechaf
        },
        success: function(response) {
          // Manejar la respuesta del servidor
          console.log(response);

          // Encontrar el elemento #resultado correspondiente al botón clickeado
          var $resultadoDiv = $this.closest('td').find('#resultado');

          // Actualizar el contenido del elemento #resultado
          $resultadoDiv.html(response);

          // Obtener el ID del curso
          var cursoId = $this.closest('form').find('#cursoId').val();

          // Obtener el idmaster (supongamos que se obtuvo del formulario general)
          var idmaster = $('#idmaster').val();

          // Variable global para almacenar las actividades seleccionadas
		var actividadesSeleccionadas = [];

		// Función para imprimir las actividades seleccionadas en la consola
		function imprimirActividadesSeleccionadas() {
		  $resultadoDiv.find('input[type="checkbox"]:checked').each(function() {
			var actividad_id = $(this).val();
			var fecha = $(this).data('fecha');
			var hora = $(this).data('hora');
			var actividadSeleccionada = {
			  idmaster: idmaster,
			  curso_id: $this.data('curso'),
			  actividad_id: actividad_id,
			  fecha: fecha,
			  hora: hora
			};

			// Verificar si la actividad ya existe en el arreglo
			var actividadExistente = actividadesSeleccionadas.find(function(actividad) {
			  return actividad.actividad_id === actividad_id;
			});

			// Agregar la actividad solo si no existe previamente
			if (!actividadExistente) {
			  actividadesSeleccionadas.push(actividadSeleccionada);
			  console.log('Actividad seleccionada:', actividadSeleccionada);
			}
		  });
		  $('#actividadesSeleccionadas').val(JSON.stringify(actividadesSeleccionadas));
		}

          // Llamar a la función para imprimir las actividades seleccionadas inicialmente
          imprimirActividadesSeleccionadas();

          // Desvincular todos los manejadores de eventos change antes de agregar uno nuevo
          $resultadoDiv.find('input[type="checkbox"]').off('change');

          // Agregar evento change a los checkboxes para actualizar las actividades seleccionadas en tiempo real
          $resultadoDiv.find('input[type="checkbox"]').on('change', imprimirActividadesSeleccionadas);
        },
        error: function() {
          alert('Error al procesar la solicitud');
        }
      });
    } else {
      alert('Por favor, complete todos los campos obligatorios');
    }
  });

  var actividadesManuales = {};

  function agregarActividadManual(event) {
    var boton = event.currentTarget;
    var row = boton.closest('tr');
    var curso = boton.closest('table').getAttribute('data-curso');
    var tipoActividadSelect = row.querySelector('.tipo-actividad');
    var fechaUnicoInput = row.querySelector('.fecha-unico');
    var fechaInicioInput = row.querySelector('.fecha-inicio-container input');
    var fechaFinInput = row.querySelector('.fecha-fin-container input');
    var horariosSelect = row.querySelector('select[name^="Horario_"]');

    if (!actividadesManuales[curso]) {
      actividadesManuales[curso] = [];
    }
    actividadesManuales[curso].push({
      curso_id: curso,
      tipoActividad: tipoActividadSelect.value,
      fechaUnico: fechaUnicoInput.value,
      fechaInicio: fechaInicioInput.value,
      fechaFin: fechaFinInput.value,
      horario: horariosSelect.value
    });

    // Actualizar el campo oculto con el arreglo actividadesManuales en formato JSON
    $('#actividadesManualesInput').val(JSON.stringify(actividadesManuales));

    // Deshabilitar los campos y marcarlos como de solo lectura
    tipoActividadSelect.disabled = true;
    fechaUnicoInput.readOnly = true;
    fechaInicioInput.readOnly = true;
    fechaFinInput.readOnly = true;
    horariosSelect.disabled = true;

    // Ocultar el botón "Agregar"
    boton.style.display = 'none';
  }

  $('#formularioExterno').submit(function(event) {
    var cursoId = $('#cursoId').val();
    // Verificar si el formulario interno está enviándose
    if ($(event.target).find('#consultaForm-' + curso).length > 0) {
      return; // Si es así, no hacer nada (no enviar el formulario externo)
    }

    // Aquí puedes agregar cualquier validación adicional para el formulario externo
    // Por ejemplo, verificar el campo 1
    var campo1Value = $('#catina').val();
    if (campo1Value === '') {
      alert('Por favor, complete el Campo 1');
      event.preventDefault(); // Evita el envío del formulario externo
      return;
    }

    var actividadesmanualesInput = $('<input>').attr({
      type: 'hidden',
      name: 'actividadesManuales',
      value: JSON.stringify(actividadesManuales)
    });
    $(this).append(actividadesmanualesInput);

    // Si todas las validaciones son exitosas, puedes enviar el formulario externo
    enviarFormularioExterno();
  });

  function enviarFormularioExterno() {
    // Aquí puedes enviar el formulario externo mediante AJAX o realizar cualquier otra acción necesaria
    alert('Formulario externo enviado');
  }

  var agregarActividadBotones = document.querySelectorAll('.agregar-actividad');
  agregarActividadBotones.forEach(function(boton) {
    boton.addEventListener('click', function() {
      var curso = this.getAttribute('data-curso');
      var tablaActividades = document.getElementById('tabla-' + curso);
      var newRow = tablaActividades.insertRow();

      // Crear celdas
      var tipoActividadCell = newRow.insertCell();
      var fechaCell = newRow.insertCell();
      var HorarioCell = newRow.insertCell();
      var borrarCell = newRow.insertCell();

      // Crear elementos HTML
      var tipoActividadSelect = document.createElement('select');
      tipoActividadSelect.classList.add('form-control', 'tipo-actividad');
      tipoActividadSelect.name = 'tipo_actividad_' + curso + '[]';

      var opcionDefault = document.createElement('option');
      opcionDefault.value = '';
      opcionDefault.disabled = true;
      opcionDefault.selected = true;
      opcionDefault.textContent = 'Seleccione Tipo de Actividad';
      tipoActividadSelect.appendChild(opcionDefault);

      tipoActividadSelect.innerHTML += tipoActividadOptions;

      var fechaUnicoInput = document.createElement('input');
      fechaUnicoInput.type = 'date';
      fechaUnicoInput.classList.add('form-control', 'fecha-unico');
      fechaUnicoInput.name = 'fecha_actividad_' + curso + '[]';

      var fechaRangoDiv = document.createElement('div');
      fechaRangoDiv.classList.add('fecha-rango');
      fechaRangoDiv.style.display = 'none';

      var fechaInicioContainer = document.createElement('div');
      fechaInicioContainer.classList.add('fecha-inicio-container');

      var fechaInicioLabel = document.createElement('label');
      fechaInicioLabel.textContent = 'Inicio:';

      var fechaInicioInput = document.createElement('input');
      fechaInicioInput.type = 'date';
      fechaInicioInput.classList.add('form-control');
      fechaInicioInput.name = 'fecha_inicio_actividad_' + curso + '[]';

      fechaInicioContainer.appendChild(fechaInicioLabel);
      fechaInicioContainer.appendChild(fechaInicioInput);

      var fechaFinContainer = document.createElement('div');
      fechaFinContainer.classList.add('fecha-fin-container');

      var fechaFinLabel = document.createElement('label');
      fechaFinLabel.textContent = 'Fin:';
      fechaFinLabel.style.paddingRight = '20px'; // Agrega un espacio adicional a la derecha

      var fechaFinInput = document.createElement('input');
      fechaFinInput.type = 'date';
      fechaFinInput.classList.add('form-control');
      fechaFinInput.name = 'fecha_fin_actividad_' + curso + '[]';

      fechaFinContainer.appendChild(fechaFinLabel);
      fechaFinContainer.appendChild(fechaFinInput);

      fechaRangoDiv.appendChild(fechaInicioContainer);
      fechaRangoDiv.appendChild(fechaFinContainer);

      var HorariosSelect = document.createElement('select');
      HorariosSelect.classList.add('form-control');
      HorariosSelect.name = 'Horario_' + curso + '[]';

      var optionDefault = document.createElement('option');
      optionDefault.hidden = true;
      optionDefault.text = "Seleccione";
      HorariosSelect.appendChild(optionDefault);

      HorariosSelect.innerHTML += horariosOptions;

      optionDefault.disabled = true;
      optionDefault.selected = true;

      var agregarButton = document.createElement('button');
      agregarButton.type = 'button';
      agregarButton.classList.add('btn', 'btn-primary', 'agregar-actividad-manual');
      agregarButton.textContent = 'Agregar';
      borrarCell.appendChild(agregarButton);

      var borrarButton = document.createElement('button');
      borrarButton.type = 'button';
      borrarButton.classList.add('btn', 'btn-danger', 'borrar-actividad');
      borrarButton.textContent = 'Borrar';

      // Agregar elementos al DOM
      tipoActividadCell.appendChild(tipoActividadSelect);
      fechaCell.appendChild(fechaUnicoInput);
      fechaCell.appendChild(fechaRangoDiv);
      HorarioCell.appendChild(HorariosSelect);
      borrarCell.appendChild(borrarButton);

      // Agregar eventos
      tipoActividadSelect.addEventListener('change', function() {
        var selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value == 26) {
          fechaRangoDiv.style.display = 'block';
          fechaUnicoInput.style.display = 'none';
        } else {
          fechaRangoDiv.style.display = 'none';
          fechaUnicoInput.style.display = 'block';
        }
      });

      borrarButton.addEventListener('click', function() {
        var row = this.closest('tr');
        row.remove();
      });

      var agregarActividadManualBotones = document.querySelectorAll('.agregar-actividad-manual');
      agregarActividadManualBotones.forEach(function(boton) {
        boton.addEventListener('click', agregarActividadManual);
      });
    });
  });

  var cursoCheckboxes = document.querySelectorAll('.curso-checkbox');
  cursoCheckboxes.forEach(function(checkbox) {
    checkbox.addEventListener('change', function() {
      var curso = this.value;
      var acciones = document.getElementById('acciones-' + curso);
      var buscar = document.getElementById('buscarf-' + curso);
      if (this.checked) {
        acciones.style.display = 'block';
        buscar.style.display = 'block';
      } else {
        acciones.style.display = 'none';
        buscar.style.display = 'none';
        var tablaActividades = document.getElementById('tabla-' + curso);
        tablaActividades.innerHTML = ''; // Eliminar contenido de la tabla
      }
    });
  });
});