async function registrarUsuario(username, password) {
  try {
    const respuesta = await fetch('register.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        username: username,
        password: password
      })
    });

    const resultado = await respuesta.json();

    if (respuesta.ok && resultado.status === 'success') {
      alert(`¡Usuario ${resultado.user.username} creado con éxito!`);
    } else {
      alert(resultado.message || 'Error en el registro');
    }
  } catch (error) {
    console.error('Error de conexión:', error);
  }
}