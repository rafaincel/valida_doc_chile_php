# valida_doc_chile

Librería en PHP para validar la vigencia de documentos de identidad chilenos (cédula y pasaporte) usando la API pública de [impishapi.com](https://impishapi.com/vigencia-documento-identidad/).

## Instalación

### Con composer
```bash
composer install impish/valida_doc_chile
```

luego, tu archivo debería incluír el autoloader de composer. Éste cargará la función `valida_doc_chile_query`.

### Sin administrador de paquetes
baja el archivo principal [consulta.php](src/consulta.php) e incluyelo en tu proyecto.

```php
require_once 'consulta.php';
```

## Requisitos

- PHP >= 5.5.0

## Uso

```php
$estado = valida_doc_chile_query("16273463-6", "106879378", "CEDULA");
// $estado puede ser: "VALID", "NOT_VALID" o "NO_MATCH"
echo $estado;

if ($estado === "VALID") {
    // ...
}
```

### Parámetros

- `run`: string, RUT con guión (ej. "16273463-6").
- `doc_num`: string, número de documento o número de serie (al menos 9 caracteres).
- `doc_type`: string, `CEDULA` o `PASAPORTE`.
- `api_key` (opcional): token enviado en el encabezado `X-Token`.

La consulta se envía como JSON a `POST /v1/query`.

### Retorno

La función `valida_doc_chile_query(...)` retorna un string con uno de los siguientes valores:

- `VALID`: el documento está vigente.
- `NOT_VALID`: el documento existe, pero no está vigente (vencido, bloqueado, perdido, etc.).
- `NO_MATCH`: no se encuentra coincidencia o el documento no existe.

## Más información

Esta librería usa la API gratuita de consulta de documentos proporcionada por [impishapi.com](https://impishapi.com/vigencia-documento-identidad/).

## Licencia

Esta librería se distribuye bajo la [licencia MIT](LICENSE).