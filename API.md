# uCARibe API

El propósito de este documento es definir los endpoints a desarrollar para el proyecto uCARibe.

El proyecto se divide en dos ámbitos: cliente y servidor, los cuales se comunican mediante el protocolo HTTP.

## Estructura principal para las comunicaciones

Para todas las comunicaciones, se asume complacencia con el estándar RFC 2616 (HTTP/1.1).

A partir de la misma, el proyecto utiliza para el intercambio de mensajes el formato JSON (http://www.json.org). Esto significa que todas las peticiones y respuestas llevarán el encabezado `Content-Type: application/json` *a menos que se especifique lo contrario*.

El cliente puede establecer comunicación con el servidor en cualquier momento aprovechando los *endpoints* definidos en este documento.

### Peticiones

Para las peticiones, se define para cada *endpoint* la lista de parámetros, encabezados y métodos permitidos y/o requeridos.

La sección **Consulta** lista parámetros que se pueden agregar al URI del *endpoint* como un query.

La sección **Recibe** lista parámetros que se deben enviar en formato JSON, o el formato que se especifique en esta sección.

La sección **Encabezados** lista encabezados que se deben enviar con la petición.

### Respuestas

Siempre se define la lista de posibles respuestas para cada *endpoint*.
El éxito de una operación se comunicará mediante el código de estatus HTTP.
En caso de un fracaso en la operación (código de estatus `>=` 400), la respuesta siempre llevará el siguiente formato:

| Parámetro	| Tipo de dato	| Descripción	|
|-----------|---------------|---------------|
| `code`	| `string`		| Una cadena de texto corta y fácil de identificar para los desarrolladores. Por ejemplo: `"missing-parameters"`.|
| `message`	| `string`		| Mensaje de información que puede ser util para el desarrollador y para el usuario final.|
| `data`	| `*`			| Puede representar información extra sobre la falla para el desarrollador.|

El campo `data` es descrito para cada **endpoint**.

#### Respuestas comunes

Aquí se especifican respuestas que se reutilizan en los endpoints que lo especifican.
La sección de respuestas de dichos endpoints referencía estas mismas con el mismo nombre y el sufijo "(común)".

- <span style="color:darkred">**[400]: Formulario inválido**</span>

La petición fracasó en especificar los parámetros adecuados y/o correctos.

**Código de error:** `"invalid-form"`  
**Datos del error:** `array<error>` - La llave `data` puede contener un arreglo de sub-errores que den un mayor detalle sobre la invalidez de la petición.

- <span style="color:darkred">**[401]: Sin permisos**</span>

La petición fracasó en proporcionar la información necesaria de autenticación.
Esto generalmente ocurre cuando hace falta un token de autenticación (*auth-token*), o la misma es inválida
(véase introducción de sección *Endpoints de viaje*).

**Código de error:** `"unauthorized"`

## Endpoints de usuario

Los *endpoints* en esta sección se relacionan a la administración de usuarios.

### `[POST] /registration`

Registra un estudiante sin verificación.

Envía un mensaje de solicitud de verificación al correo institucional ligado a la matrícula especificada,
con un enlace funcional al endpoint `[GET] /verification`.

#### Recibe

| Requisito	| Parámetro		| Tipo de dato	| Descripción	|
|-----------|---------------|---------------|---------------|
| Si		| `id`			| `string`		| Matrícula de estudiante. |
| Si		| `password`	| `string`		| Contraseña para esta cuenta en uCARibe. |
| Si		| `name`		| `string`		| Nombre de estudiante. |
| Si		| `phone`		| `string`		| Número de teléfono del estudiante. |

Ejemplo
```json
{
	"id"		: "160300122",
	"password"	: "ucaribe",
	"name"		: "Cesar Cruz Muñoz",
	"phone"		: "9982003302"
}
```

#### Respuestas

- <span style="color:green">**[200]: Éxito**</span>

El registro fué exitoso. Esta respuesta no lleva ningún contenido.

- <span style="color:darkred">**[400]: Formulario inválido** (común)</span>

- <span style="color:darkred">**[401]: Ya registrado**</span>

La matrícula que se intenta registrar ya está en el sistema.

**Código de error:** `"already-registered"`

### `[GET] /verification`

Cambia un registro de estudiante a verificado, si los parámetros de la petición son válidos.

#### Consulta

| Requisito	| Parámetro				| Tipo de dato	| Descripción	|
|-----------|-----------------------|---------------|---------------|
| Si		| `student_id`			| `string`		| Matrícula de estudiante. |
| Si		| `verification_token`	| `string`		| Token de verificación de correo electrónico. |

#### Respuestas

Este endpoint responde con un documento HTML visualizable en un navegador web. Por lo mismo, el encabezado `Content-Type` de esta respuesta es `text/html`.

- <span style="color:green">**[200]: Éxito**</span>

Se verificó la cuenta, y ya puede utilizarse para ingresar al sistema.

**Contenido:** Un mensaje de éxito.

- <span style="color:darkred">**[404]: No encontrado**</span>

No se encontró ninguna cuenta que se pueda verificar con los datos proporcionados.

### `[POST] /login`

Valida credencial de estudiante y provee un token de autenticación para otras solicitudes.

#### Recibe

| Requisito	| Parámetro		| Tipo de dato	| Descripción	|
|-----------|---------------|---------------|---------------|
| Si		| `id`			| `string`		| Matrícula de estudiante. |
| Si		| `password`	| `string`		| Contraseña de esta cuenta en uCARibe. |

Ejemplo
```json
{
	"id"		: "160300116",
	"password"	: "ucaribe"
}
```

#### Respuestas

- <span style="color:green">**[200]: Éxito**</span>

La credencial es válida.

**Contenido:** `string`  
Un token de autenticación (*auth-token*).

- <span style="color:darkred">**[400]: Credencial inválida**</span>

La credencial es inválida. Puede ser que el estudiante no esté registrado, o que su contraseña se haya proporcionado incorrectamente.

**Código de error**: `"wrong-credentials"`

- <span style="color:darkred">**[401]: Esperando verificación**</span>

La credencial es válida, y está en espera de verificación por correo electrónico institucional.

**Código de error**: `"pending-email-verification"`

## Endpoints de viaje

Manejan la administración de viajes.

**Importante:** Todos los endpoints de esta sección deben cumplir con los siguientes encabezados.

| Requisito	| Encabezado		| Valor						| Descripción	|
|-----------|-------------------|---------------------------|---------------|
| Si		| `Authorization`	| `"Token {$auth_token}"`	| Autenticación del usuario\*. |

\*Sustituir `{$auth_token}` con un token de autenticación (*auth-token*).

Igualmente, todos los endpoints incluyen la siguiente posible respuesta común:

- <span style="color:darkred">**[401]: Sin permisos** (común)</span>

#### Tipos de datos comunes

En esta sección, se utilizan los siguientes tipos de datos, y representan diccionarios (`dict`)
con las especificaciones descritas.

- `marker-dict`

| Requisito	| Parámetro		| Tipo de dato	| Descripción	|
|-----------|---------------|---------------|---------------|
| Si		| `latitude`	| `float`		| Coordenada de latitud. |
| Si		| `longitude`	| `float`		| Coordenada de longitud. |

- `contact-dict`

| Requisito	| Parámetro	| Tipo de dato	| Descripción	|
|-----------|-----------|---------------|---------------|
| Si		| `name`	| `string`		| Nombre. |
| Si		| `email`	| `string`		| Correo electrónico. |
| Si		| `phone`	| `string`		| Número de teléfono. |

- `proposal-dict`

| Requisito		| Parámetro		| Tipo de dato			| Descripción	|
|---------------|---------------|-----------------------|---------------|
| Si			| `id`			| `int`					| Identificador de esta propuesta. |
| Si			| `status`		| `string`				| Estatus de esta propuesta.\* |
| No (`null`)	| `contact`		| `contact-dict`		| Información de contacto con el otro participante. |
| No (`null`)	| `markers`		| `array<marker-dict>`	| Conjunto de marcadores para esta propuesta. |

La presencia de `contact` y `markers` dependerá del valor de `status`.

**\*El valor de `status` puede ser uno de los siguientes:**

| Valor					| `contact`	| `markers`	| Descripción	|
|-----------------------|-----------|-----------|---------------|
| `"pending"`			| No		| Si		| El usuario actual puede aceptar esta propuesta. |
| `"accepted-pending"`	| No		| Si		| El usuario actual ha aceptado esta propuesta, y está en espera de otro usuario. |
| `"accepted"`			| Si		| Si		| Todos los usuarios relacionados han aceptado la propuesta. |
| `"rejected"`			| No		| No		| Al menos un usuario ha rechazado esta propuesta. |

### `[POST] /trip/start`

Crea un viaje con los datos especificados y su(s) marcador(es) relacionados, igualmente especificados.

#### Recibe

| Requisito	| Parámetro		| Tipo de dato			| Descripción	|
|-----------|---------------|-----------------------|---------------|
| Si		| `datetime`	| `datetime-string`		| Fecha de salida. |
| Si		| `role`		| `string`				| Rol de usuario. |
| Si		| `to_uni`		| `boolean`				| Especifica la dirección del viaje (`true` es "*hacia la uni*"). |
| Si		| `markers`		| `array<marker-dict>`	| Conjunto de marcadores para este viaje. |

Ejemplo
```json
{
	"datetime"	: "2019-05-10 20:00:00",
	"role"		: "driver",
	"to_uni"	: true,
	"markers"	:
	[
		{
			"latitude"	: 10.52,
			"longitude"	: 14.98
		},
		{
			"latitude"	: 7.982,
			"longitude"	: 15.04
		},
		{
			"latitude"	: 8.10,
			"longitude"	: 19
		}
	]
}
```

#### Respuestas

- <span style="color:green">**[201]: Éxito**</span>

Se creó el viaje y los marcadores del mismo.
El contenido puede, o no, incluir una propuesta con `status=="pending"`.

| Requisito	| Parámetro		| Tipo de dato		| Descripción	|
|-----------|---------------|-------------------|---------------|
| Si		| `trip_id`		| `int`				| `id` del viaje creado. |
| No		| `proposal`	| `proposal-dict`	| Coordenada de longitud. |

**Contenido (con propuesta): `dict`**
```json
{
	"trip_id"	: 2,
	"proposal"	:
	{
		"id"		: 45,
		"status"	: "pending",
		"markers"	:
		[
			{
				"latitude"	: 10.17,
				"longitude"	: 13.55
			},
		]
	}
}
```

**Contenido (sin propuesta): `dict`**
```json
{
	"trip_id"	: 2,
	"proposal"	: null,
}
```

- <span style="color:darkred">**[400]: Formulario inválido** (común)</span>

### `[GET] /trip/proposal`

Consulta de una propuesta para un viaje en específico. Puede o no especificarse una propuesta en específico a consultar.

Cuando no se especifica una propuesta, el sistema responde con la **actual**.

#### Consulta

| Requisito	| Parámetro		| Tipo de dato	| Descripción	|
|-----------|---------------|---------------|---------------|
| Si		| `trip_id`		| `int`			| `id` del viaje a consultar. |
| No		| `proposal_id`	| `int`			| `id` de la propuesta de este viaje a consultar. |

Ejemplos  
`/trip/proposal?trip_id=2`
`/trip/proposal?trip_id=2&proposal_id=45`

#### Respuestas

- <span style="color:green">**[200]: Encontrada**</span>

Se ha encontrado la propuesta especificada o actual para este viaje.  

**Contenido: `proposal-dict`**
```json
{
	"id"		: 45,
	"status"	: "pending",
	"markers"	:
	[
		{
			"latitude"	: 10.17,
			"longitude"	: 13.55
		}
	],
	"contact"	: null
}
```

- <span style="color:darkred">**[404]: No encontrada**</span>

La propuesta o viaje especificado no existe.

Si faltó especificar una propuesta, no hay una propuesta actual para el viaje.
Esto significa que el viaje aún no tiene propuestas o todas han sido rechazadas.

**Código de error:** `"not-found"`

### `[POST] /trip/accept`

Acepta la propuesta especificada para un viaje especificado.

#### Recibe

| Requisito	| Parámetro		| Tipo de dato	| Descripción	|
|-----------|---------------|---------------|---------------|
| Si		| `trip_id`		| `int`			| `id` del viaje a confirmar. |
| Si		| `proposal_id`	| `int`			| `id` de la propuesta a confirmar. |

Ejemplo  
```json
{
	"trip_id"		: 2,
	"proposal_id"	: 45
}
```

#### Respuestas

- <span style="color:green">**[200]: Aceptada**</span>

Se confirmó la propuesta para el usuario actual. La respuesta refleja la propuesta resultante.  
Si la misma ha sido aceptada por todos los participantes, incluirá información de contacto.

**Contenido: `proposal-dict`**
```json
{
	"id"		: 45,
	"status"	: "accepted",
	"markers"	:
	[
		{
			"latitude"	: 10.17,
			"longitude"	: 13.55
		}
	],
	"contact"	: {
		"name"	: "Axel Villalobos",
		"email"	: "170300002@ucaribe.edu.mx",
		"phone"	: "9981053651"
	}
}
```

- <span style="color:darkred">**[404]: No encontrada**</span>

La propuesta o viaje especificado no existe.

**Código de error:** `"not-found"`

- <span style="color:darkred">**[409]: Previamente rechazada**</span>

La propuesta especificada fué rechazada por algún participante.

**Código de error:** `"previously-rejected"`  
**Contenido de error: `proposal-dict`**

Ejemplo
```json
{
	"code"		: "cannot-update",
	"message"	: "La propuesta especificada fue rechazada por algún participante.",
	"data"		:
	{
		"id"		: 44,
		"status"	: "rejected",
	}
}
```

### `[POST] /trip/reject`

Rechaza la propuesta especificada para un viaje especificado.

#### Recibe

| Requisito	| Parámetro		| Tipo de dato	| Descripción	|
|-----------|---------------|---------------|---------------|
| Si		| `trip_id`		| `int`			| `id` del viaje a rechazar. |
| Si		| `proposal_id`	| `int`			| `id` de la propuesta a rechazar. |

Ejemplo  
```json
{
	"trip_id"		: 2,
	"proposal_id"	: 45
}
```

#### Respuestas

- <span style="color:green">**[200]: Rechazada**</span>

Se rechazó la propuesta para el usuario actual.

Si el servidor encuentra una nueva propuesta, la misma se incluye en la respuesta.
De lo contrario, la respuesta es `null`.

**Contenido: `proposal-dict`|null**
```json
{
	"id"		: 46,
	"status"	: "pending",
	"markers"	:
	[
		{
			"latitude"	: 10.17,
			"longitude"	: 13.55
		}
	],
	"contact"	: null
}
```

- <span style="color:darkred">**[404]: No encontrada**</span>

La propuesta o viaje especificado no existe.

**Código de error:** `"not-found"`

- <span style="color:darkred">**[409]: Previamente aceptada**</span>

La propuesta especificada ya había sido aceptada por todos los participantes.

**Código de error:** `"previously-accepted"`  
**Contenido de error: `proposal-dict`**

Ejemplo
```json
{
	"code"		: "cannot-update",
	"message"	: "La propuesta especificada ya había sido aceptada por todos los participantes.",
	"data"		:
	{
		"id"		: 46,
		"status"	: "accepted",
		"markers"	:
		[
			{
				"latitude"	: 10.17,
				"longitude"	: 13.55
			}
		],
		"contact"	: {
			"name"	: "Marcos Alejandro Perez",
			"email"	: "160300154@ucaribe.edu.mx",
			"phone"	: "9981053651"
		}
	}
}
```