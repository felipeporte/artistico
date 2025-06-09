// Espera a que el DOM cargue
document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('.tab-button');
    const contents = document.querySelectorAll('.tab-content');
  
    buttons.forEach(btn => {
      btn.addEventListener('click', () => {
        // Quitar activa/mostrar de todas
        buttons.forEach(b => b.classList.remove('active'));
        contents.forEach(c => c.classList.add('hidden'));
  
        // Activar la que se clickeó
        btn.classList.add('active');
        const dayId = btn.getAttribute('data-day');
        document.getElementById(dayId).classList.remove('hidden');
      });
    });
  });
  document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('.menu-toggle');
    const nav    = document.querySelector('.nav');
  
    toggle.addEventListener('click', () => {
      nav.classList.toggle('active');
    });
  
    // Opcional: cerrar menú al hacer clic en un link
    nav.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        nav.classList.remove('active');
      });
    });
  });
  