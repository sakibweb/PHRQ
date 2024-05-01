<?php
/**
 * PHRQ is PHP Request Library
 * @author Sakibur Rahman (@sakibweb)
 * 
 * A PHP library for sending HTTP requests using cURL and generating JavaScript code for XHR requests.
 */

class PHRQ {
    /**
     * Send an HTTP request from PHP using cURL.
     *
     * @param string $method HTTP method (GET/POST/PUT/PATCH/DELETE/HEAD/OPTIONS/CUSTOM).
     * @param string $url URL to send the request to.
     * @param array $headers Associative array of request headers.
     * @param mixed $body Request body data.
     * @param array $options cURL options.
     * @return mixed Response data.
     */
    public static function php($method, $url, $headers = [], $body = null, $options = []) {
        try {
            $ch = curl_init();

            if ($ch === false) {
                throw new Exception('Failed to initialize cURL');
            }

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));

            if ($headers) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }

            if ($body !== null) {
                if (is_array($body)) {
                    $body = json_encode($body);
                    $headers[] = 'Content-Type: application/json';
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }

            if ($options) {
                curl_setopt_array($ch, $options);
            }

            $response = curl_exec($ch);
            if ($response === false) {
                throw new Exception(curl_error($ch));
            }

            curl_close($ch);

            // Automatically decode JSON if Content-Type is application/json
            $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            if ($content_type && strpos($content_type, 'application/json') !== false) {
                $response = json_decode($response, true);
            }

            return $response;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Generate JavaScript code for sending an HTTP request using XHR.
     *
     * @param string $method HTTP method (GET/POST/PUT/PATCH/DELETE/HEAD/OPTIONS/CUSTOM).
     * @param string $url URL to send the request to.
     * @param array $headers Associative array of request headers.
     * @param mixed $body Request body data.
     * @param array $options XHR options.
     * @return string JavaScript code.
     */
    public static function js($method, $url, $headers = [], $body = null, $options = []) {
        $js = "try {";
        $js .= "var xhr = new XMLHttpRequest();";
        $js .= "xhr.open('" . strtoupper($method) . "', '" . $url . "');";

        if ($headers) {
            foreach ($headers as $key => $value) {
                $js .= "xhr.setRequestHeader('" . $key . "', '" . $value . "');";
            }
        }

        $js .= "xhr.onreadystatechange = function() {";
        $js .= "if (xhr.readyState === XMLHttpRequest.DONE) {";
        $js .= "console.log(xhr.responseText);";
        $js .= "}";
        $js .= "};";

        $js .= "xhr.send(";
        if ($body !== null) {
            if (is_array($body)) {
                $js .= "'" . urlencode(json_encode($body)) . "'";
            } else {
                $js .= "'" . urlencode($body) . "'";
            }
        }
        $js .= ");";

        $js .= "} catch (e) {";
        $js .= "console.error('XHR request failed:', e);";
        $js .= "}";
        return $js;
    }
}
?>
