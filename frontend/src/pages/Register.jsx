import { useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import api from "../services/api";

function Register() {
  const navigate = useNavigate();

  const [form, setForm] = useState({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    role: "operador",
  });

  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  const handleChange = (e) => {
    setForm({
      ...form,
      [e.target.name]: e.target.value,
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    setError("");
    setLoading(true);

    try {
      await api.post("/register", form);

      navigate("/login");
    } catch (error) {
      if (error.response?.status === 422) {
        const errors = error.response.data.errors;

        if (errors.email) {
          setError(errors.email[0]);
        } else if (errors.password) {
          setError(errors.password[0]);
        } else if (errors.password_confirmation) {
          setError(errors.password_confirmation[0]);
        } else if (errors.name) {
          setError(errors.name[0]);
        } else {
          setError("Verifica los datos ingresados.");
        }
      } else if (error.code === "ERR_NETWORK") {
        setError(
          "No se pudo conectar con el servidor. Verifica que Laravel esté encendido."
        );
      } else {
        setError("Ocurrió un error inesperado.");
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <main className="auth-page">
      <section className="auth-card">
        <div className="logo">
          <span className="logo-icon">🌿</span>
          <span>AgroLogic</span>
        </div>

        <h1>Crear cuenta</h1>

        <p className="muted">
          Registra un usuario para acceder al sistema inteligente del
          invernadero.
        </p>

        <form onSubmit={handleSubmit} className="auth-form">
          <label>Nombre completo</label>
          <input
            name="name"
            type="text"
            placeholder="Karen Tentle"
            value={form.name}
            onChange={handleChange}
            required
          />

          <label>Correo electrónico</label>
          <input
            name="email"
            type="email"
            placeholder="correo@ejemplo.com"
            value={form.email}
            onChange={handleChange}
            required
          />

          <label>Contraseña</label>
          <input
            name="password"
            type="password"
            placeholder="••••••••"
            value={form.password}
            onChange={handleChange}
            required
          />

          <label>Confirmar contraseña</label>
          <input
            name="password_confirmation"
            type="password"
            placeholder="••••••••"
            value={form.password_confirmation}
            onChange={handleChange}
            required
          />

          {error && (
            <div className="form-error">
              {error}
            </div>
          )}

          <button
            className="btn-primary"
            type="submit"
            disabled={loading}
          >
            {loading ? "Creando cuenta..." : "Crear cuenta"}
          </button>
        </form>

        <p className="auth-link">
          ¿Ya tienes cuenta? <Link to="/login">Iniciar sesión</Link>
        </p>
      </section>
    </main>
  );
}

export default Register;