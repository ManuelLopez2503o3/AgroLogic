function Users() {
  return (
    <main className="dashboard-content">
      <p className="badge">Administrador</p>

      <h1>Gestión de usuarios</h1>

      <p className="muted">
        Esta sección solo puede ser visualizada por usuarios con rol de administrador.
      </p>

      <div className="card">
        <p className="card-label">Módulo protegido</p>
        <h2>Usuarios del sistema</h2>
        <p className="muted">
          Aquí posteriormente se mostrará la lista de usuarios registrados,
          asignación de roles y administración de permisos.
        </p>
      </div>
    </main>
  );
}

export default Users;