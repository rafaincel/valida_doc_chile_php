<?php

/**
 * Consulta el estado de vigencia de un documento de identidad.
 *
 * @param string $run Identificador de la persona (ej. "98765432-K")
 * @param string $doc_num Número del documento (ej. "12345678")
 * @param string $doc_type Tipo de documento: CEDULA o PASAPORTE
 * @param string|null $api_key Token de acceso enviado como X-Token
 * @return string Estado del documento: VALID, NOT_VALID, NO_MATCH
 * @throws RuntimeException Si la API retorna un error
 */
function valida_doc_chile_query($run, $doc_num, $doc_type, $api_key = null)
{
    $data = [
        'run' => $run,
        'serial_number' => $doc_num,
        'doc_type' => $doc_type,
    ];
    $request_body = json_encode($data);
    if ($request_body === false) {
        throw new RuntimeException('JSON request encoding failed: ' . json_last_error_msg());
    }

    $opts = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json",
            'content' => $request_body,
            'timeout' => 10,
            'ignore_errors' => true,
        ]
    ];

    if ($api_key !== null) {
        $opts['http']['header'] .= "\r\nX-Token: " . $api_key;
    }

    $context = stream_context_create($opts);

    $res = @file_get_contents('https://regcivil.impish.top/v1/query', false, $context);

    if ($res === false) {
        // Try to get error response
        $error = isset($http_response_header) ? implode("\n", $http_response_header) : 'Unknown error';
        throw new RuntimeException("HTTP request failed: $error");
    }

    $json_data = json_decode($res, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException("JSON error: " . json_last_error_msg());
    }

    if (!is_array($json_data)) {
        throw new RuntimeException('API returned an invalid response');
    }

    $status_code = 200;
    if (isset($http_response_header[0]) &&
        preg_match('/\s([0-9]{3})\s/', $http_response_header[0], $matches)) {
        $status_code = (int) $matches[1];
    }

    if ($status_code < 200 || $status_code >= 300 || !empty($json_data['error'])) {
        $message = isset($json_data['message']) ? $json_data['message'] : null;
        if ($message === null && isset($json_data['detail'])) {
            $message = is_string($json_data['detail'])
                ? $json_data['detail']
                : json_encode($json_data['detail']);
        }
        if ($message === null) {
            $message = 'Unknown API error';
        }
        throw new RuntimeException(
            'API request failed (HTTP ' . $status_code . '): ' . $message
        );
    }

    $status = isset($json_data['status']) ? $json_data['status'] : null;
    if (!in_array($status, ['VALID', 'NOT_VALID', 'NO_MATCH'], true)) {
        throw new RuntimeException('API returned an unknown document status');
    }

    return $status;
}