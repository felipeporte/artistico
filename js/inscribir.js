document.addEventListener('DOMContentLoaded', function () {
  const competencia = document.getElementById('competencia_id');
  const modalidad    = document.getElementById('modalidad');
  const nivel        = document.getElementById('nivel');
  const subnivel     = document.getElementById('subnivel');
  const deportista   = document.getElementById('deportista_id');
  const categoria    = document.getElementById('categoria');

  let nivelesFull = [];

  competencia.addEventListener('change', function () {
    fetch('get_competencia_info.php?id=' + this.value)
      .then(r => r.json())
      .then(data => {
        // Poblamos modalidades
        modalidad.innerHTML = '<option value="" disabled selected hidden>Seleccionar modalidad</option>';
        data.modalidades.forEach(m => {
          const opt = document.createElement('option');
          opt.value = m;
          opt.text  = m.charAt(0).toUpperCase() + m.slice(1).replace('_',' ');
          modalidad.appendChild(opt);
        });
        // Guardamos todos los niveles con su ID
        nivelesFull = data.niveles;
        modalidad.dispatchEvent(new Event('change'));
      });
  });

  modalidad.addEventListener('change', function () {
    nivel.innerHTML    = '<option value="" disabled selected hidden>Seleccionar nivel</option>';
    subnivel.innerHTML = '<option value="">Sin subnivel</option>';

    // Filtrar niveles de la modalidad elegida
    nivelesFull
      .filter(n => n.modalidad === this.value)
      .forEach(nobj => {
        const opt = document.createElement('option');
        opt.value = nobj.id;                                   // <-- Aquí el ID numérico
        opt.text  = nobj.nivel.charAt(0).toUpperCase() + nobj.nivel.slice(1);
        nivel.appendChild(opt);
      });

    nivel.dispatchEvent(new Event('change'));
  });

  nivel.addEventListener('change', function () {
    subnivel.innerHTML = '<option value="">Sin subnivel</option>';

    const selId = parseInt(this.value, 10);
    const info  = nivelesFull.find(n => n.id === selId);

    if (info && info.subnivel) {
      const opt = document.createElement('option');
      opt.value = info.subnivel;
      opt.text  = info.subnivel.charAt(0).toUpperCase() + info.subnivel.slice(1);
      subnivel.appendChild(opt);
    }

    actualizarCategoria();
  });

  deportista.addEventListener('change', actualizarCategoria);

  function actualizarCategoria() {
    if (!deportista.value || !nivel.value) return;
    fetch('obtener_categoria.php?id=' + deportista.value)
      .then(res => res.text())
      .then(c => categoria.value = c);
  }

  // Carga inicial
  competencia.dispatchEvent(new Event('change'));
  // No disparamos deportista aquí para no calcular antes de nivel
});
