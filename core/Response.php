<?php
// /core/Response.php
namespace Core;

class Response
{
    /**
     * Send a JSON response
     *
     * @param mixed $data Data to encode as JSON
     * @param int $status HTTP status code
     * @return void
     */
    public static function json($data, $status = 200)
    {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    /**
     * Send a 404 Not Found response
     *
     * @return void
     */
    public static function notFound()
    {
        self::json(['error' => 'Not Found'], 404);
    }

    /**
     * Send an error response
     *
     * @param string $message Error message
     * @param int $status HTTP status code
     * @return void
     */
    public static function error($message, $status = 400)
    {
        self::json(['error' => $message], $status);
    }
}
