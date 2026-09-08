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
  document.querySelectorAll(".tipo-btn").forEach(b => b.classList.remove("on"));
  btn.classList.add("on");
  document.getElementById("r-ci").style.display  = tipo === "part" ? "block" : "none";
  document.getElementById("r-rut").style.display = tipo === "emp"  ? "block" : "none";
}

function alternarIdioma() {
  const btn = document.getElementById("btnIdioma");
  
  if (btn.innerText === "ES") {
    btn.innerText = "EN";
  } else {
    btn.innerText = "ES";
  }
}

