# ⚽ FIFA World Cup 2026 - Prediction API

API REST desarrollada en **Laravel** para la gestión de predicciones (porras) del Mundial 2026. Este sistema permite a los usuarios competir en comunidades privadas y obtener puntos basados en la precisión de sus pronósticos.

## 📋 Requisitos del Sistema
* **Registro de Usuarios:** Sistema de autenticación con envío de email de bienvenida.
* **Comunidades:** Creación de ligas competitivas con códigos únicos de acceso. Solo el creador gestiona miembros.
* **Predicciones:** Sistema dinámico que permite apostar solo en la fase actual del torneo hasta un día antes del partido.
* **Gestión de Partidos:** Importación masiva de datos mediante ficheros y actualización de resultados en tiempo real por un Administrador.

## 🏆 Sistema de Puntuación
El cálculo de puntos se realiza de forma automática tras la actualización de resultados:
- **3 Puntos:** Acierto exacto del resultado (ej. Predicción 2-1, Resultado 2-1).
- **1 Punto:** Acierto del ganador o empate, pero no del resultado exacto.
- **0 Puntos:** No se acierta el ganador ni el resultado.

## 🛠️ Tecnologías y Estructura
- **Framework:** Laravel 11.
- **Seguridad:** Middleware de protección de datos (un usuario solo ve lo suyo).
- **Consistencia:** Todas las respuestas JSON (éxito o error) mantienen una estructura uniforme.
- **Clean Code:** Arquitectura basada en controladores, servicios y políticas de acceso.
