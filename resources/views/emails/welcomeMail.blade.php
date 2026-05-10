<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <h1>¡Bienvenido al Mundial 2026, {{ $user->name }}!</h1>
    <p>Tu cuenta ha sido creada correctamente con las siguientes credenciales:</p>
    <ul>
        <li><strong>Nombre:</strong> {{ $user->name }}</li>
        <li><strong>Email:</strong> {{ $user->email }}</li>
    </ul>
    <p>¡Buena suerte con tus predicciones!</p>
</body>
</html>