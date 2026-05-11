# 📚 Wiki — API Mundial FIFA 2026

> Documento de referencia y estudio del proyecto. Aquí se recogen todos los conceptos implementados, explicados en profundidad con ejemplos del propio código.

---

## Índice

1. [Descripción del proyecto](#descripción-del-proyecto)
2. [Tecnologías utilizadas](#tecnologías-utilizadas)
3. [Instalación y puesta en marcha](#instalación-y-puesta-en-marcha)
4. [Base de datos](#base-de-datos)
5. [Endpoints implementados](#endpoints-implementados)
6. [Conceptos explicados en profundidad](#conceptos-explicados-en-profundidad)
   - [Trait](#trait)
   - [Sanctum y autenticación por tokens](#sanctum-y-autenticación-por-tokens)
   - [Abilities de Sanctum](#abilities-de-sanctum)
   - [Middleware](#middleware)
   - [Eloquent: Modelos y Relaciones](#eloquent-modelos-y-relaciones)
   - [Migraciones](#migraciones)
   - [Seeders](#seeders)
   - [Services](#services)
   - [Mailables y Mailpit](#mailables-y-mailpit)
   - [Importación de CSV](#importación-de-csv)
   - [Query Builder y filtros opcionales](#query-builder-y-filtros-opcionales)
   - [Carbon: manejo de fechas](#carbon-manejo-de-fechas)
   - [Constructor del Controller](#constructor-del-controller)
   - [Seguridad en endpoints](#seguridad-en-endpoints)

---

## Descripción del proyecto

API REST desarrollada en **Laravel 11** para gestionar predicciones del Mundial FIFA 2026. Permite a los usuarios registrarse, predecir resultados de partidos, crear comunidades competitivas y obtener puntos según la precisión de sus predicciones.

---

## Tecnologías utilizadas

| Tecnología | Uso |
|---|---|
| Laravel 11 | Framework principal |
| Laravel Sanctum | Autenticación por tokens |
| MySQL | Base de datos |
| Docker + Nginx | Entorno de desarrollo |
| Mailpit | Servidor de email local para desarrollo |

---

## Instalación y puesta en marcha

```bash
# Levantar los contenedores Docker
docker-compose up -d

# Instalar dependencias
composer install

# Copiar el fichero de entorno
cp .env.example .env

# Generar la clave de la aplicación
php artisan key:generate

# Ejecutar las migraciones
php artisan migrate

# Crear el usuario admin
php artisan db:seed

# Instalar Sanctum y crear api.php
php artisan install:api
```

Variables de entorno necesarias en `.env`:
```env
# Base de datos
DB_CONNECTION=mysql
DB_HOST=mundial_db
DB_PORT=3306
DB_DATABASE=mundial_2026_db

# Email (Mailpit local)
MAIL_MAILER=smtp
MAIL_HOST=mailer
MAIL_PORT=1025
MAIL_FROM_ADDRESS="noreply@mundial2026.com"
MAIL_FROM_NAME="Mundial 2026"

# Usuario admin (usado por el Seeder)
ADMIN_EMAIL=admin@gmail.com
ADMIN_PASSWORD=tupassword123
```

---

## Base de datos

### Esquema de tablas

**`users`**
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | autoincremental |
| name | string | |
| email | string | unique |
| password | string | hasheada automáticamente |
| rol | enum | `admin` o `usuario` |
| created_at / updated_at | timestamp | |

**`partidos`**
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | autoincremental |
| id_event | integer | unique, viene del CSV externo |
| equipo_A | string | |
| equipo_B | string | |
| fase | string | Round 1, Round 2... |
| fecha_hora_partido | datetime | |
| goles_eqipo_A | integer | nullable (partido no jugado) |
| goles_eqipo_B | integer | nullable (partido no jugado) |

**`comunidades`**
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| nombre | string | |
| codigo | string(6) | unique |
| creador_id | FK → users | |

**`comunidad_user`** *(tabla pivote)*
| Campo | Tipo | Notas |
|---|---|---|
| user_id | FK → users | |
| comunidad_id | FK → comunidades | |
| estado_solicitud | enum | `aceptado` o `pendiente` |

**`predicciones`**
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| user_id | FK → users | |
| partido_id | FK → partidos | |
| goles_eqipo_A | integer | predicción del usuario |
| goles_eqipo_B | integer | predicción del usuario |
| puntos_ganados | integer | nullable, se calcula después |

---

## Endpoints implementados

### Autenticación

| Método | Ruta | Acceso | Descripción |
|---|---|---|---|
| POST | `/api/register` | Público | Registro de usuario |
| POST | `/api/login` | Público | Login, devuelve token |
| POST | `/api/logout` | Auth | Cierra sesión, elimina tokens |

### Partidos

| Método | Ruta | Acceso | Descripción |
|---|---|---|---|
| GET | `/api/partidos` | Auth | Listar partidos (filtros: `?fase=` y `?equipo=`) |
| GET | `/api/partidos/fase-actual` | Auth | Ver partidos de la fase actual |
| POST | `/api/importar` | Admin | Importa/actualiza partidos desde CSV |

### Predicciones

| Método | Ruta | Acceso | Descripción |
|---|---|---|---|
| GET | `/api/predicciones` | Auth | Ver todas mis predicciones |
| POST | `/api/predicciones` | Auth | Crear una predicción |
| PUT | `/api/predicciones/{id}` | Auth | Editar una predicción |

### Comunidades

| Método | Ruta | Acceso | Descripción |
|---|---|---|---|
| POST | `/api/comunidades` | Auth | Crear una comunidad |
| GET | `/api/comunidades/{id}` | Auth (miembro) | Ver miembros de la comunidad |
| POST | `/api/comunidades/solicitar` | Auth | Solicitar unirse con código |
| PUT | `/api/comunidades/{id}/aceptar/{user_id}` | Auth (creador) | Aceptar solicitud |
| DELETE | `/api/comunidades/{id}/miembros/{user_id}` | Auth (creador) | Eliminar miembro |

### Estructura de respuesta uniforme

Todos los endpoints devuelven siempre esta estructura:

```json
// Éxito
{
    "success": true,
    "message": "Descripción de la operación",
    "data": { ... }
}

// Error
{
    "success": false,
    "message": "Descripción del error",
    "data": null
}
```

---

## Conceptos explicados en profundidad

---

### Trait

Un **Trait** es un bloque de métodos reutilizables que puedes "inyectar" en cualquier clase sin usar herencia. Es una forma de compartir código entre clases que no tienen relación entre sí.

**¿Por qué usarlo?**
En nuestro proyecto, todos los controllers tienen que devolver JSON con la misma estructura. Sin un Trait, repetiríamos el mismo código en cada controller. Con el Trait lo escribimos una vez y lo reutilizamos en todos.

**Cómo se crea:**
```php
// app/Traits/TraitApiResponse.php

namespace App\Traits;

trait TraitApiResponse
{
    public function successResponse($data, $message, $code)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    public function errorResponse($message, $code)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
        ], $code);
    }
}
```

**Cómo se usa en un Controller:**
```php
// Paso 1: importar el Trait (fuera de la clase)
use App\Traits\TraitApiResponse;

class AuthController extends Controller
{
    // Paso 2: activarlo (dentro de la clase)
    use TraitApiResponse;

    public function login()
    {
        // Ahora puedes usar sus métodos con $this->
        return $this->successResponse($user, 'Login correcto', 200);
        return $this->errorResponse('Credenciales inválidas', 401);
    }
}
```

> ⚠️ El `use` fuera de la clase **importa** el fichero. El `use` dentro de la clase **activa** el Trait. Son dos usos diferentes de la misma palabra clave.

---

### Sanctum y autenticación por tokens

**Laravel Sanctum** es el sistema de autenticación de Laravel para APIs. Funciona con **tokens**: cadenas de texto únicas que identifican a cada usuario en cada petición.

**¿Por qué tokens y no sesiones?**
Las APIs son *stateless* (sin estado) — el servidor no recuerda quién eres entre peticiones. En vez de una sesión guardada en el servidor, el cliente guarda un token y lo manda en cada petición.

**Flujo completo:**
```
1. POST /login  →  el servidor verifica las credenciales
                →  genera un token único para ese usuario
                →  devuelve el token al cliente

2. GET /partidos  →  el cliente manda el token en la cabecera:
                     Authorization: Bearer 1|Na12s0wYytbQ3...
                  →  Sanctum verifica el token
                  →  si es válido, deja pasar la petición
```

**Instalación en Laravel 11:**
```bash
php artisan install:api
# Esto instala Sanctum, crea routes/api.php y la tabla personal_access_tokens
```

**El modelo User necesita el trait `HasApiTokens`:**
```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
}
```

**Crear un token:**
```php
// Parámetros: nombre, abilities, expiración
$token = $user->createToken('api-token', ['full_access'], now()->addWeek())->plainTextToken;
```

**Eliminar todos los tokens (logout):**
```php
$user->tokens()->delete();
```

**Proteger una ruta con Sanctum:**
```php
// Solo usuarios con token válido pueden acceder
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
```

**En Postman**, el token se manda en:
- Pestaña **Authorization** → tipo **Bearer Token**
- O en la cabecera: `Authorization: Bearer tu_token_aqui`

---

### Abilities de Sanctum

Las **abilities** son etiquetas/permisos que se asignan a un token al crearlo. Permiten controlar qué puede hacer cada token.

**¿Para qué las usamos?**
En nuestro proyecto, el usuario `admin` necesita acceder a rutas que los usuarios normales no pueden. En vez de crear un middleware personalizado, usamos abilities.

**Asignar abilities según el rol:**
```php
// En AuthController@login
$abilities = $user->rol === 'admin'
    ? ['full_access', 'admin']   // admin tiene ambas
    : ['full_access'];            // usuario normal solo tiene esta

$token = $user->createToken('api-token', $abilities, now()->addWeek())->plainTextToken;
```

**Registrar los alias en `bootstrap/app.php`:**
```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        // 'ability' comprueba si el token tiene AL MENOS UNA de las abilities indicadas
        'ability'    => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
        // 'abilities' comprueba si el token tiene TODAS las abilities indicadas
        'abilities'  => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
    ]);
})
```

**Proteger rutas solo para admin:**
```php
Route::post('/importar', [PartidoController::class, 'importar'])
    ->middleware(['auth:sanctum', 'ability:admin']);
//               ↑ primero verifica que hay token, luego verifica la ability
```

> ⚠️ Siempre necesitas `auth:sanctum` antes de `ability:admin`. Sin el primero, Laravel ni siquiera sabe quién eres.

---

### Middleware

Un **Middleware** es una capa intermedia que intercepta las peticiones HTTP antes de que lleguen al Controller. Es como un portero que decide si una petición puede pasar o no.

**Flujo de una petición con middleware:**
```
Request → Middleware 1 → Middleware 2 → Controller → Response
              ↓
         (si no pasa, devuelve error directamente)
```

**En Laravel 11, los middlewares se registran en `bootstrap/app.php`:**
```php
$middleware->alias([
    'ability' => CheckForAnyAbility::class,
]);
```

**Aplicar middleware a una ruta:**
```php
// Una sola ruta
Route::post('/logout', ...)->middleware('auth:sanctum');

// Varias rutas agrupadas
Route::middleware(['auth:sanctum', 'ability:admin'])->group(function () {
    Route::post('/importar', ...);
    Route::put('/partidos/{id}', ...);
});
```

**Códigos HTTP habituales en middleware:**
- `401 Unauthorized` — no hay token o es inválido
- `403 Forbidden` — hay token pero no tiene permisos suficientes

---

### Eloquent: Modelos y Relaciones

**Eloquent** es el ORM (Object-Relational Mapper) de Laravel. Convierte las tablas de la base de datos en clases PHP, y las filas en objetos. Así trabajas con objetos en vez de escribir SQL directamente.

**Conceptos clave del modelo:**

```php
class Partido extends Model
{
    // $fillable: campos que se pueden rellenar con create() o update()
    // Sin esto, Laravel bloquea la creación masiva por seguridad (Mass Assignment Protection)
    protected $fillable = ['id_event', 'equipo_A', 'equipo_B', 'fase', 'fecha_hora_partido', 'goles_eqipo_A', 'goles_eqipo_B'];

    // $hidden: campos que NO aparecen en las respuestas JSON
    protected $hidden = ['password', 'remember_token'];

    // $table: nombre explícito de la tabla (cuando Laravel no lo infiere bien)
    protected $table = 'predicciones';
    // Sin esto, Laravel convierte "Prediccion" → "prediccions" (inglés), que no existe
}
```

**Tipos de relaciones:**

```php
// hasMany: "un Partido tiene muchas Predicciones"
// Se usa cuando la foreign key está en la otra tabla
public function predicciones(): HasMany
{
    return $this->hasMany(Prediccion::class);
}

// belongsTo: "una Prediccion pertenece a un Partido"
// Se usa cuando la foreign key está en ESTA tabla (partido_id)
public function partido(): BelongsTo
{
    return $this->belongsTo(Partido::class);
}

// belongsToMany: relación N:M a través de una tabla pivote
// Un User pertenece a muchas Comunidades y viceversa
public function comunidades(): BelongsToMany
{
    return $this->belongsToMany(Comunidad::class)
        ->withPivot('estado_solicitud'); // incluir campos extra de la tabla pivote
}
```

**Operaciones CRUD con Eloquent:**
```php
// Crear
$user = User::create(['name' => 'Juan', 'email' => 'juan@test.com']);

// Buscar por ID
$partido = Partido::find(1);
$partido = Partido::findOrFail(1); // lanza 404 si no existe

// Buscar con condición
$user = User::where('email', $request->email)->first();

// Actualizar o crear (útil para importaciones)
Partido::updateOrCreate(
    ['id_event' => $fila[0]],   // condición de búsqueda
    ['equipo_A' => $fila[3], ...]  // datos a guardar
);

// Eliminar
$user->delete();
```

---

### Migraciones

Las **migraciones** son ficheros PHP que definen la estructura de la base de datos. Son como un sistema de control de versiones para la BD — puedes crear, modificar y revertir tablas de forma controlada.

**Crear una migración:**
```bash
php artisan make:migration create_partidos_table
php artisan make:migration add_id_event_to_partidos_table  # añadir columna
```

**Estructura de una migración:**
```php
return new class extends Migration
{
    // up(): lo que se ejecuta al migrar
    public function up(): void
    {
        Schema::create('partidos', function (Blueprint $table) {
            $table->id();                              // bigint autoincremental PK
            $table->integer('id_event')->unique();     // unique: no puede repetirse
            $table->string('equipo_A');
            $table->string('fase');
            $table->dateTime('fecha_hora_partido');
            $table->integer('goles_eqipo_A')->nullable(); // nullable: puede ser null
            $table->foreignId('user_id')               // foreign key
                  ->constrained('users')               // apunta a tabla users
                  ->onDelete('cascade');               // si se borra el user, se borran sus registros
            $table->timestamps();                      // created_at y updated_at automáticos
        });
    }

    // down(): lo que se ejecuta al revertir (rollback)
    public function down(): void
    {
        Schema::dropIfExists('partidos');
    }
};
```

**Comandos útiles:**
```bash
php artisan migrate              # ejecutar migraciones pendientes
php artisan migrate:status       # ver estado de las migraciones
php artisan migrate:fresh        # ⚠️ borra TODO y recrea desde cero (solo en desarrollo)
php artisan migrate:rollback     # revertir la última migración
```

> ⚠️ `migrate:fresh` borra todos los datos. Úsalo solo en desarrollo.

---

### Seeders

Los **Seeders** son clases que insertan datos iniciales en la base de datos. Son útiles para crear datos de prueba o datos necesarios para que la app funcione (como el usuario admin).

**Ventaja sobre insertar datos manualmente:**
- Son reproducibles — cualquiera que clone el proyecto puede ejecutarlos
- Están versionados en git
- No hay que tocar SQL directamente

**Ejemplo del seeder del admin:**
```php
// database/seeders/DatabaseSeeder.php

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'     => 'admin',
            'email'    => env('ADMIN_EMAIL'),      // ← variables de entorno
            'password' => Hash::make(env('ADMIN_PASSWORD')), // nunca hardcodear passwords
            'rol'      => 'admin'
        ]);
    }
}
```

**Variables de entorno en `.env`:**
```env
ADMIN_EMAIL=admin@gmail.com
ADMIN_PASSWORD=mipasswordseguro123
```

Las variables de entorno se usan para no tener credenciales directamente en el código. Si alguien accede al repositorio, no ve las contraseñas reales.

**Ejecutar el seeder:**
```bash
php artisan db:seed
```

---

### Services

Un **Service** es una clase PHP normal que contiene la lógica de negocio compleja. No tiene nada especial de Laravel — es simplemente una forma de organizar el código.

**¿Por qué usarlos?**
Los Controllers deben ser ligeros: recibir la request, validar, llamar al service y devolver la respuesta. Si metemos toda la lógica en el controller, se vuelve enorme y difícil de mantener.

```
Sin Services (mal):               Con Services (bien):
AuthController (500 líneas)       AuthController (50 líneas)
  └── validar                       └── validar
  └── leer CSV                      └── llamar a PartidoService
  └── parsear CSV                   └── devolver respuesta
  └── crear partidos
  └── calcular puntos               PartidoService (lógica de negocio)
  └── enviar emails                   └── leer CSV
  └── devolver respuesta              └── parsear CSV
                                      └── crear/actualizar partidos
```

**Ejemplo de nuestro PartidoService:**
```php
// app/Services/PartidoService.php

namespace App\Services;

use App\Models\Partido;

class PartidoService
{
    public function importarCSV($fichero): array
    {
        $handle = fopen($fichero->getRealPath(), 'r');
        fgetcsv($handle); // saltar cabecera

        $partidos = [];

        while (($fila = fgetcsv($handle)) !== false) {
            $partidos[] = Partido::updateOrCreate(
                ['id_event' => $fila[0]],
                [
                    'fecha_hora_partido' => $fila[1],
                    'fase'               => $fila[2],
                    'equipo_A'           => $fila[3],
                    'goles_eqipo_A'      => $fila[4] !== '' ? $fila[4] : null,
                    'equipo_B'           => $fila[5],
                    'goles_eqipo_B'      => $fila[6] !== '' ? $fila[6] : null,
                ]
            );
        }

        fclose($handle);
        return $partidos;
    }
}
```

**Cómo se llama desde el Controller:**
```php
public function importar(Request $request)
{
    $request->validate(['fichero' => ['required', 'file', 'mimes:csv,txt']]);

    $service  = new PartidoService();
    $partidos = $service->importarCSV($request->file('fichero'));

    return $this->successResponse($partidos, 'Partidos importados', 201);
}
```

---

### Mailables y Mailpit

**Mailables** son clases de Laravel que representan un email. Separan la lógica del email (a quién va, el asunto) de la vista (el HTML del cuerpo).

**Crear un Mailable:**
```bash
php artisan make:mail WelcomeMail
```

**Estructura de un Mailable:**
```php
// app/Mail/WelcomeMail.php

class WelcomeMail extends Mailable
{
    // Constructor: recibe los datos que necesita el email
    // "public" hace que $user esté disponible automáticamente en la vista Blade
    public function __construct(public User $user) {}

    // envelope(): define el asunto y metadatos del email
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Bienvenido al Mundial 2026');
    }

    // content(): define qué vista Blade se usa como cuerpo del email
    public function content(): Content
    {
        return new Content(view: 'emails.welcomeMail');
    }
}
```

**Vista Blade del email** (`resources/views/emails/welcomeMail.blade.php`):
```html
<!DOCTYPE html>
<html>
<body>
    <h1>¡Bienvenido al Mundial 2026, {{ $user->name }}!</h1>
    <p>Tu cuenta ha sido creada con las siguientes credenciales:</p>
    <ul>
        <li><strong>Nombre:</strong> {{ $user->name }}</li>
        <li><strong>Email:</strong> {{ $user->email }}</li>
    </ul>
    <p>¡Buena suerte con tus predicciones!</p>
</body>
</html>
```

**Enviar el email desde el Controller:**
```php
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;

// En AuthController@register, después de crear el usuario:
Mail::to($user->email)->send(new WelcomeMail($user));
```

**Mailpit** es un servidor de email local para desarrollo. Los emails no se envían de verdad — se pueden ver en `http://localhost:8025`. Se configura en Docker y en el `.env`:

```yaml
# docker-compose.yml
mailer:
  image: 'axllent/mailpit:latest'
  ports:
    - "1025:1025"  # puerto SMTP
    - "8025:8025"  # interfaz web
```

```env
MAIL_MAILER=smtp
MAIL_HOST=mailer       # nombre del servicio en docker-compose, no "localhost"
MAIL_PORT=1025
```

> ⚠️ `MAIL_HOST` usa el nombre del **servicio** en docker-compose (`mailer`), no `localhost`. Los contenedores Docker se comunican entre sí por el nombre del servicio.

---

### Importación de CSV

PHP tiene funciones nativas para leer ficheros CSV sin necesidad de librerías externas.

**Funciones utilizadas:**

```php
// fopen(): abre el fichero y devuelve un "puntero" (cursor)
// 'r' = solo lectura (read)
$handle = fopen($fichero->getRealPath(), 'r');

// fgetcsv(): lee la línea actual y mueve el cursor a la siguiente
// Devuelve un array con cada valor separado por coma
// Devuelve false cuando no hay más líneas
$fila = fgetcsv($handle); // ['idEvent', 'strTimestamp', 'Round', ...]

// fclose(): cierra el fichero y libera memoria
fclose($handle);
```

**Patrón completo de lectura:**
```php
$handle = fopen($fichero->getRealPath(), 'r');

fgetcsv($handle); // primera llamada: lee y descarta la cabecera (títulos de columnas)

while (($fila = fgetcsv($handle)) !== false) {
    // $fila es un array indexado:
    // $fila[0] → idEvent
    // $fila[1] → strTimestamp (fecha)
    // $fila[2] → Round (fase)
    // $fila[3] → Home Team (equipo_A)
    // $fila[4] → Home Score (goles_A, puede estar vacío)
    // $fila[5] → Away Team (equipo_B)
    // $fila[6] → Away Score (goles_B, puede estar vacío)

    // Tratar valores vacíos (partidos no jugados):
    $goles_A = $fila[4] !== '' ? $fila[4] : null;
}

fclose($handle);
```

**`updateOrCreate()`** — crea o actualiza según si el registro ya existe:
```php
Partido::updateOrCreate(
    ['id_event' => $fila[0]],  // busca por este campo
    [                           // si lo encuentra: actualiza estos campos
        'equipo_A' => $fila[3], // si no lo encuentra: los crea
        'goles_eqipo_A' => $fila[4] !== '' ? $fila[4] : null,
    ]
);
```

Esto permite usar el **mismo endpoint** tanto para importar partidos nuevos como para actualizar resultados — simplemente importando el CSV actualizado de thesportsdb.

---

*Documento generado durante el desarrollo del proyecto — Mayo 2026*

---

### Query Builder y filtros opcionales

El **Query Builder** de Laravel permite construir consultas SQL de forma progresiva sin ejecutarlas hasta el final. Es útil cuando necesitas aplicar filtros opcionales según los parámetros que lleguen en la request.

**`Modelo::query()`** — inicia un constructor de consulta sin ejecutarla:
```php
$query = Partido::query(); // todavía no va a la BD
```

**Añadir filtros opcionales:**
```php
if ($request->has('fase')) {
    $query->where('fase', $request->fase);
}

// orWhere: busca en dos columnas a la vez
if ($request->has('equipo')) {
    $query->where('equipo_A', $request->equipo)
          ->orWhere('equipo_B', $request->equipo);
}

$partidos = $query->get(); // AHORA ejecuta la query con todos los filtros
```

**Uso en la URL:**
```
GET /partidos                    → todos los partidos
GET /partidos?fase=Round 1       → solo partidos de Round 1
GET /partidos?equipo=Spain       → partidos donde Spain es equipo_A o equipo_B
```

**`value('campo')`** — devuelve directamente el valor de un campo del primer resultado:
```php
// En una sola línea, sin devolver el modelo completo
$fase = Partido::whereNull('goles_equipo_A')
               ->orderBy('fecha_hora_partido', 'asc')
               ->value('fase');
```

---

### Carbon: manejo de fechas

**Carbon** es la librería de fechas de Laravel. Viene incluida y permite comparar, sumar y restar fechas de forma muy sencilla.

```php
use Carbon\Carbon;

$hoy = Carbon::now();                                        // fecha y hora actual
$fechaPartido = Carbon::parse($partido->fecha_hora_partido); // parsear string a Carbon
$diaAntes = $fechaPartido->subDay();                         // restar un día

// Comparar fechas
$hoy->greaterThanOrEqualTo($diaAntes); // ¿hoy es mayor o igual que el día anterior?
$hoy->lessThan($fechaPartido);          // ¿hoy es antes del partido?
```

**Uso en nuestro proyecto** — bloquear predicciones el día del partido:
```php
$hoy = Carbon::now();
$fechaPartido = Carbon::parse($partido->fecha_hora_partido);

if ($hoy->greaterThanOrEqualTo($fechaPartido->subDay())) {
    return ['error' => true, 'message' => 'Ya no puedes predecir este partido', 'code' => 422];
}
```

> ⚠️ `subDay()` modifica la fecha original. Si necesitas usarla después, usa `copy()->subDay()`.

---

### Constructor del Controller

En vez de instanciar un Service en cada método, puedes instanciarlo una sola vez en el constructor:

```php
class PrediccionController extends Controller
{
    use TraitApiResponse;

    private PrediccionService $service;

    public function __construct()
    {
        $this->service = new PrediccionService(); // una sola vez
    }

    public function store(Request $request)
    {
        $this->service->crearPrediccion(...); // reutilizamos
    }

    public function update(Request $request, $id)
    {
        $this->service->editarPrediccion(...); // reutilizamos
    }
}
```

---

### Seguridad en endpoints

**1. Nunca confíes en el `user_id` del body** — obtenerlo siempre del token:
```php
// Inseguro ❌ — el usuario podría mandar user_id de otro usuario
Prediccion::create(['user_id' => $request->user_id, ...]);

// Seguro ✅ — viene del token de Sanctum, no se puede falsificar
Prediccion::create(['user_id' => $request->user()->id, ...]);
```

**2. Verifica que el recurso pertenece al usuario** antes de editar o eliminar:
```php
$prediccion = Prediccion::findOrFail($prediccion_id);

if ($prediccion->user_id !== $user_id) {
    return ['error' => true, 'message' => 'No tienes permiso', 'code' => 403];
}
```

**Códigos HTTP de seguridad:**
- `401 Unauthorized` — no hay token o es inválido
- `403 Forbidden` — hay token pero no tienes permiso para ese recurso
- `404 Not Found` — el recurso no existe


---

### Relaciones N:M y tabla pivote

Las relaciones **muchos a muchos** (N:M) se gestionan en Laravel con `belongsToMany` y una tabla pivote intermedia.

En nuestro proyecto, un usuario puede pertenecer a muchas comunidades y una comunidad puede tener muchos usuarios — relación N:M a través de `comunidad_user`.

**Métodos de Eloquent para relaciones N:M:**

```php
// attach() — añade un registro en la tabla pivote
$comunidad->users()->attach($user_id, ['estado_solicitud' => 'pendiente']);
// Inserta: comunidad_id=X, user_id=Y, estado_solicitud=pendiente

// detach() — elimina un registro de la tabla pivote
$comunidad->users()->detach($user_id);
// Elimina la fila donde comunidad_id=X y user_id=Y

// updateExistingPivot() — actualiza campos extra de la tabla pivote
$comunidad->users()->updateExistingPivot($user_id, ['estado_solicitud' => 'aceptado']);
// Actualiza estado_solicitud donde comunidad_id=X y user_id=Y

// wherePivot() — filtra por campos de la tabla pivote
$comunidad->users()->wherePivot('estado_solicitud', 'pendiente')->exists();
// Busca usuarios con estado_solicitud=pendiente en esa comunidad
```

**`withPivot()`** — necesario en la relación del modelo para que Eloquent incluya los campos extra de la tabla pivote:
```php
public function users(): BelongsToMany
{
    return $this->belongsToMany(User::class)
        ->withPivot('estado_solicitud'); // sin esto, estado_solicitud no aparece
}
```

---

### Generación de códigos únicos

Para generar el código único de cada comunidad usamos un bucle `do...while` que garantiza que el código no existe antes de usarlo:

```php
use Illuminate\Support\Str;

do {
    $codigo = strtoupper(Str::random(6)); // genera string aleatorio de 6 chars en mayúsculas
} while (Comunidad::where('codigo', $codigo)->exists()); // repite si ya existe
```

**`do...while` vs `while`:**
- `while` — comprueba la condición ANTES de ejecutar el bloque (puede no ejecutarse nunca)
- `do...while` — ejecuta el bloque PRIMERO y luego comprueba (siempre se ejecuta al menos una vez)

Para generar un código siempre necesitas ejecutar el bloque al menos una vez, por eso usamos `do...while`.

**`Str::random(6)`** — genera una cadena aleatoria de 6 caracteres alfanuméricos.
**`strtoupper()`** — convierte la cadena a mayúsculas.
**`exists()`** — ejecuta la query y devuelve `true` o `false`.

---

### Carga de relaciones con `with()`

Para cargar una relación junto con el modelo principal en una sola query se usa `with()`:

```php
// Sin with() — hace 2 queries (N+1 problem)
$comunidad = Comunidad::findOrFail($id);
$comunidad->users; // segunda query aquí

// Con with() — hace 1 sola query optimizada
$comunidad = Comunidad::with('users')->findOrFail($id);
// users ya viene cargado, no hace segunda query
```

El **problema N+1** ocurre cuando cargas una colección y accedes a una relación en un bucle — hace una query por cada elemento. `with()` lo soluciona cargando todo de una vez.