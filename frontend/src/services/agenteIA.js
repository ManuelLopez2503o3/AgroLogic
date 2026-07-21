import api from "./api";

export const obtenerEstadoAgente = () =>
  api.get("/ia/estado");

export const cambiarModoAutonomo = (activo) =>
  api.post("/ia/modo-autonomo", { activo });

export const evaluarAgenteManual = () =>
  api.post("/ia/evaluar");