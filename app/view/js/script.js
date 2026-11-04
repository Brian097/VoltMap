function ir(id) {
  document.querySelectorAll(".sc").forEach(s => s.classList.remove("on"));
  document.getElementById(id).classList.add("on");
  window.scrollTo(0, 0);
}
function selChip(el) {
  el.closest(".filtros-bar").querySelectorAll(".chip").forEach(c => c.classList.remove("sel"));
  el.classList.add("sel");
}

function selTipo(btn, tipo) {
  // 1. Actualizar la clase 'on' en los botones
  document.querySelectorAll('.tipo-btn').forEach(b => b.classList.remove('on'));
  btn.classList.add('on');

  // 2. Actualizar el valor del input oculto
  const inputTipo = document.getElementById('tipo_usuario');
  if (inputTipo) {
    inputTipo.value = tipo;
  }

  // 3. Mostrar/ocultar campos según el tipo seleccionado
  const campoCi = document.getElementById('r-ci');
  const campoRut = document.getElementById('r-rut');
  const inputCi = document.getElementById('cedula');
  const inputRut = document.getElementById('rut');

  if (tipo === 'part') {
    if (campoCi) campoCi.style.display = 'block';
    if (campoRut) campoRut.style.display = 'none';
    if (inputCi) inputCi.required = true;
    if (inputRut) inputRut.required = false;
    if (inputRut) inputRut.value = '';
  } else {
    if (campoCi) campoCi.style.display = 'none';
    if (campoRut) campoRut.style.display = 'block';
    if (inputCi) inputCi.required = false;
    if (inputRut) inputRut.required = true;
    if (inputCi) inputCi.value = '';
  }
}

function alternarIdioma() {
  const btn = document.getElementById("btnIdioma");

  if (btn.innerText === "ES") {
    btn.innerText = "EN";
  } else {
    btn.innerText = "ES";
  }
}

