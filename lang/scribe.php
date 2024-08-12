<?php

return [
    "labels" => [
        "search" => "Buscar",
        "base_url" => "Base URL",
    ],

    "auth" => [
        "none" => "Esta API no está autenticada.",
        "instruction" => [
            "query" => <<<TEXT
                Para autenticar solicitudes, incluya un parámetro de consulta **`:parameterName`** en la solicitud.
                TEXT,
            "body" => <<<TEXT
                Para autenticar solicitudes, incluya un parámetro **`:parameterName`** en el cuerpo de la solicitud.
                TEXT,
            "query_or_body" => <<<TEXT
                Para autenticar solicitudes, incluya un parámetro **`:parameterName`** ya sea en la cadena de consulta o en el cuerpo de la solicitud.
                TEXT,
            "bearer" => <<<TEXT
                Para autenticar solicitudes, incluya un encabezado **`Authorization`** con el valor **`"Bearer :placeholder"`**.
                TEXT,
            "basic" => <<<TEXT
                Para autenticar solicitudes, incluya un encabezado **`Authorization`** en el formulario **`"Basic {credentials}"`**.
                El valor de `{credentials}` debe ser su nombre de usuario/id y su contraseña, unidos con dos puntos (:),
                y luego codificado en base64.
                TEXT,
            "header" => <<<TEXT
                Para autenticar solicitudes, incluya un encabezado **`:parameterName`** con el valor **`":placeholder"`**.
                TEXT,
        ],
        "details" => <<<TEXT
            Todos los endpoints que necesitan autenticación están marcados con un badge "requires authentication" en la documentación siguiente.
            TEXT,
    ],

    "headings" => [
        "introduction" => "Introducción",
        "auth" => "Autenticando petición",
    ],

    "endpoint" => [
        "request" => "Request",
        "headers" => "Headers",
        "url_parameters" => "URL Parameters",
        "body_parameters" => "Body Parameters",
        "query_parameters" => "Query Parameters",
        "response" => "Response",
        "response_fields" => "Response Fields",
        "example_request" => "Ejemplo de petición",
        "example_response" => "Ejemplo de respuesta",
        "responses" => [
            "binary" => "Binary data",
            "empty" => "Respuesta vacía",
        ],
    ],

    "try_it_out" => [
        "open" => "Pruébalo ⚡",
        "cancel" => "Cancelar 🛑",
        "send" => "Enviar petición 💥",
        "loading" => "⏱ Enviando...",
        "received_response" => "Respuesta recibida",
        "request_failed" => "La solicitud falló con error",
        "error_help" => <<<TEXT
            Consejo: comprueba que estés conectado correctamente a la red.
            Si es desarrollador de la API, verifique que su API se esté ejecutando y haya habilitado CORS.
            Puede consultar la consola de Dev Tools para obtener información de depuración..
            TEXT,
    ],

    "links" => [
        "postman" => "Ver colección de Postman",
        "openapi" => "Ver especificaciones de OpenAPI",
    ],
];
