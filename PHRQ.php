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
            $ch = curl_init($url);

            if ($ch === false) {
                throw new Exception('Failed to initialize cURL');
            }

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));

            if (!empty($headers)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }

            if ($body !== null && !empty($body)) {
                if (is_array($body)) {
                    $body = json_encode($body);
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }

            foreach ($options as $key => $value) {
                if (defined($key)) {
                    curl_setopt($ch, constant($key), $value);
                }
            }

            $response = curl_exec($ch);
            if ($response === false) {
                throw new Exception(curl_error($ch));
            }

            curl_close($ch);

            $decoded_response = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded_response;
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
        $jsFunction = <<<JS
async function(method, url, headers, body) {
    try {
        var xhr = new XMLHttpRequest();
        xhr.open(method, url);

        if (headers) {
            for (var key in headers) {
                xhr.setRequestHeader(key, headers[key]);
            }
        }

        xhr.send(body);

        return new Promise((resolve, reject) => {
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    var responseText = xhr.responseText;
                    var contentType = xhr.getResponseHeader('Content-Type');
                    if (contentType && contentType.indexOf('application/json') !== -1) {
                        responseText = JSON.parse(responseText);
                    }
                    resolve(responseText);
                }
            };
            xhr.onerror = function() {
                reject(xhr.statusText);
            };
        });
    } catch (e) {
        console.error('XHR request failed:', e);
        throw e;
    }
}
JS;
        // Call the function and return its code with await keyword
        return 'async function() { return await (' . $jsFunction . ')("' . $method . '", "' . $url . '", ' . json_encode($headers) . ', ' . json_encode($body) . '); }';
    }


    /**
     * Set HTTP response headers for API responses.
     *
     * @param string $method HTTP method for the request (e.g., "GET", "POST").
     * @param string $origin CORS origin (default is "*").
     * @param string $contentType MIME type of the response content (default is 'application/json').
     * @param array $additionalHeaders Array of additional headers to set.
     */
    public static function header($method = 'GET', $origin = '*', $contentType = 'application/json', $additionalHeaders = []) {
        header('Content-Type: '.$contentType);
        header("Access-Control-Allow-Methods: $method");
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Headers: *");
        
        foreach ($additionalHeaders as $key => $value) {
            header("$key: $value");
        }
    }

    /**
     * Set HTTP response headers for API responses.
     *
     * @param int $code HTTP status code (default is 200).
     * @param string|null $msg Custom message for the response (default is null).
     * @return array HTTP response information containing code and message.
     */
    public static function status(int $code = 200, string $msg = null) {
        $statusMessages = [
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing (WebDAV)',
            103 => 'Early Hints',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status (WebDAV)',
            208 => 'Already Reported (WebDAV)',
            226 => 'IM Used',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => 'Switch Proxy',
            307 => 'Temporary Redirect',
            308 => 'Permanent Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Payload Too Large',
            414 => 'URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I’m a Teapot',
            421 => 'Misdirected Request',
            422 => 'Unprocessable Entity (WebDAV)',
            423 => 'Locked (WebDAV)',
            424 => 'Failed Dependency (WebDAV)',
            425 => 'Too Early',
            426 => 'Upgrade Required',
            428 => 'Precondition Required',
            429 => 'Too Many Requests',
            431 => 'Request Header Fields Too Large',
            451 => 'Unavailable For Legal Reasons',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage (WebDAV)',
            508 => 'Loop Detected (WebDAV)',
            510 => 'Not Extended',
            511 => 'Network Authentication Required',
            520 => 'Web Server Is Returning an Unknown Error',
            521 => 'Web Server Is Down',
            522 => 'Connection Timed Out',
            523 => 'Origin Is Unreachable',
            524 => 'A Timeout Occurred',
            525 => 'SSL Handshake Failed',
            526 => 'Invalid SSL Certificate',
            527 => 'Railgun Error',
            530 => 'Site is Frozen',
            600 => 'Custom Network Error',
            601 => 'Service Dependency Failure',
            602 => 'Request Rate Exceeded',
            603 => 'Database Unavailable',
            604 => 'Configuration Error',
            605 => 'Service Overloaded',
            606 => 'Timeout Waiting for Response',
            607 => 'Service Restarting',
            608 => 'Quota Limit Exceeded',
            609 => 'API Limit Reached',
            610 => 'Unprocessable Input',
            611 => 'Session Expired',
            612 => 'Request Blocked',
            613 => 'Security Violation',
            614 => 'License Expired',
            615 => 'Feature Not Supported',
            616 => 'User Action Required',
            617 => 'Invalid Credentials',
            618 => 'Token Expired',
            619 => 'Content Moderation Failed',
            620 => 'Blocked by Firewall',
            621 => 'Resource Locked',
            622 => 'Policy Violation',
            623 => 'Resource Not Allowed',
            1000 => 'DNS Query Timeout',
            1001 => 'DNS Record Not Found',
            1002 => 'SSL Certificate Mismatch',
            1003 => 'Rate Limiting Error',
            1004 => 'Blocked IP Address',
            1005 => 'Invalid Request Parameter',
            1006 => 'Unsupported Encoding',
            1007 => 'Content Security Policy Violation',
            1008 => 'Unrecognized Method',
            1009 => 'Malformed Request Body',
            1010 => 'Service Upgrade Required',
            1011 => 'Request Suspended',
            1012 => 'Server is Shutting Down',
            1013 => 'Server Restart Required',
            1014 => 'Read-Only Mode',
            1015 => 'Resource Quarantine',
            1016 => 'Blocked by Admin Policy',
            1017 => 'Service Dependency Unavailable',
            1018 => 'Service Undergoing Maintenance',
            1019 => 'Deprecated Version',
            1020 => 'API Version Deprecated',
            1021 => 'Data Conflict Detected',
            1022 => 'Service Not Available',
            1023 => 'Maintenance Mode Enabled',
            1024 => 'Data Integrity Violation',
            1025 => 'Operation Not Supported',
            1026 => 'Service Temporarily Unavailable',
            1027 => 'Invalid Session ID',
            1028 => 'Database Connection Failed',
            1029 => 'Database Lock Timeout',
            1030 => 'Duplicate Entry Detected',
            1031 => 'Invalid Data Format',
            1032 => 'Unsupported File Type',
            1033 => 'Storage Quota Exceeded',
            1034 => 'Data Migration Required',
            1035 => 'Resource Unavailable',
            1036 => 'Request Timeout Exceeded',
            1037 => 'Service Out of Scope',
            1038 => 'Operation Not Allowed in Current State',
            1039 => 'Feature Disabled',
            1040 => 'Invalid Access Level',
            1041 => 'Unsupported Authorization Method',
            1042 => 'Unapproved Application',
            1043 => 'Session Timeout',
            1044 => 'Authentication Timeout',
            1045 => 'Permission Denied',
            1046 => 'Malformed Authorization Header',
            1047 => 'Insufficient Permissions',
            1048 => 'Rate Limit Exceeded',
            1049 => 'Resource Already Exists',
            1050 => 'Invalid API Key',
            1051 => 'Data Type Mismatch',
            1052 => 'Invalid Parameter Value',
            1053 => 'Missing Required Parameter',
            1054 => 'Operation Aborted',
            1055 => 'Invalid Username or Password',
            1056 => 'Expired Token',
            1057 => 'Redirect Loop Detected',
            1058 => 'Invalid URL',
            1059 => 'Invalid Format',
            1060 => 'Service Temporarily Paused',
            1061 => 'Server Maintenance in Progress',
            1062 => 'Resource Locked for Editing',
            1063 => 'Session Already Active',
            1064 => 'Invalid Resource State',
            1065 => 'Unexpected Server Error',
            1066 => 'Internal Configuration Error',
            1067 => 'Feature Under Development',
            1068 => 'User Not Found',
            1069 => 'User Account Locked',
            1070 => 'User Account Suspended',
            1071 => 'Invalid User Input',
            1072 => 'Timeout Waiting for Data',
            1073 => 'Untrusted Source Detected',
            1074 => 'Data Expired',
            1075 => 'Unexpected Error Occurred',
            1076 => 'Insufficient Resources',
            1077 => 'Unexpected API Response',
            1078 => 'Invalid Request Format',
            1079 => 'Access Restricted',
            1080 => 'Network Error Occurred',
            1081 => 'Protocol Error',
            1082 => 'Connection Refused',
            1083 => 'Network Congestion Detected',
            1084 => 'SSL Configuration Error',
            1085 => 'Request Could Not Be Processed',
            1086 => 'Data Validation Failed',
            1087 => 'Unrecognized Token',
            1088 => 'Custom Error Detected',
            1089 => 'Inconsistent Data Detected',
            1090 => 'Service Out of Order',
            1091 => 'Service Will Be Back Soon',
            1092 => 'User Account Not Verified',
            1093 => 'Precondition Not Met',
            1094 => 'Authorization Required',
            1095 => 'Rate Limiting Exceeded',
            1096 => 'Invalid Operation',
            1097 => 'Request Not Found',
            1098 => 'Feature Disabled by Admin',
            1099 => 'Invalid Token',
            1100 => 'Security Token Required',
            1101 => 'Internal API Error',
            1102 => 'Connection Timeout',
            1103 => 'System Error Detected',
            1104 => 'Application Error Detected',
            1105 => 'Duplicate Request',
            1106 => 'Concurrent Modification Error',
            1107 => 'Service Not Configured',
            1108 => 'Unable to Load Resource',
            1109 => 'Custom Rate Limit Exceeded',
            1110 => 'Invalid Operation for Current State',
            1111 => 'Maintenance Break Scheduled',
            1112 => 'Insufficient Access Rights',
            1113 => 'Custom Invalid Error',
            1114 => 'Data Retrieval Error',
            1115 => 'Invalid Request Header',
            1116 => 'Service Configuration Error',
            1117 => 'Configuration Not Loaded',
            1118 => 'Invalid Authentication Token',
            1119 => 'Invalid Session',
            1120 => 'Invalid OAuth Token',
            1121 => 'Unexpected Behavior Detected',
            1122 => 'Custom Feature Unavailable',
            1123 => 'Configuration In Progress',
            1124 => 'Data Format Error',
            1125 => 'Data Processing Error',
            1126 => 'API Rate Limit Exceeded',
            1127 => 'User Action Required',
            1128 => 'User Input Required',
            1129 => 'Temporary Block Applied',
            1130 => 'Resource Expired',
            1131 => 'Feature Not Available',
            1132 => 'Invalid State Transition',
            1133 => 'Resource Usage Limit Reached',
            1134 => 'Service Not Available Temporarily',
            1135 => 'Service Maintenance in Progress',
            1136 => 'Service Will Be Back Shortly',
            1137 => 'Custom Server Error',
            1138 => 'Temporary Service Error',
            1139 => 'Data Modification Not Allowed',
            1140 => 'Invalid Callback',
            1141 => 'Not Supported',
            1142 => 'Invalid Webhook',
            1143 => 'Rate Limit Reached for User',
            1144 => 'Unexpected API Behavior',
            1145 => 'Resource Already Used',
            1146 => 'Concurrent Request Limit Reached',
            1147 => 'Request Failed',
            1148 => 'Service Blocked',
            1149 => 'Server Not Available',
            1150 => 'Invalid Payment Method',
            1151 => 'Payment Required',
            1152 => 'Outdated Client Version',
            1153 => 'User Needs to Reauthorize',
            1154 => 'Feature Deprecated',
            1155 => 'Service Overload',
            1156 => 'Service Requiring Approval',
            1157 => 'Policy Update Required',
            1158 => 'Resource Not Created',
            1159 => 'Action Not Allowed',
            1160 => 'Task Queued',
            1161 => 'Data Not Found',
            1162 => 'Too Many Active Sessions',
            1163 => 'Session Needs Renewal',
            1164 => 'Timeout Expired',
            1165 => 'Connection Lost',
            1166 => 'Data Transmission Error',
            1167 => 'Service Lag Detected',
            1168 => 'Slow Network Detected',
            1169 => 'Network Latency Issue',
            1170 => 'Service Restarting',
            1171 => 'Configuration Change Pending',
            1172 => 'Database Migration Required',
            1173 => 'Resource Unavailable Temporarily',
            1174 => 'In Maintenance Mode',
            1175 => 'Legacy Feature Disabled',
            1176 => 'User Registration Required',
            1177 => 'Custom Server Message',
            1178 => 'Invalid Request Timeout',
            1179 => 'Unsupported Resource Type',
            1180 => 'User Banned',
            1181 => 'Not Acceptable',
            1182 => 'Custom Rate Limit Reached',
            1183 => 'Service Disruption',
            1184 => 'Service Capacity Reached',
            1185 => 'Data Access Denied',
            1186 => 'Rate Limit Exceeded for Application',
            1187 => 'Service Request Denied',
            1188 => 'Not Allowed',
            1189 => 'Invalid Content Type',
            1190 => 'Invalid Access Token',
            1191 => 'Untrusted Application',
            1192 => 'Invalid State',
            1193 => 'Invalid IP Address',
            1194 => 'Unknown Error Occurred',
            1195 => 'Invalid Resource Access',
            1196 => 'Configuration Conflict',
            1197 => 'Unverified User',
            1198 => 'User Verification Required',
            1199 => 'Insufficient Funds',
            1200 => 'Invalid Request Structure',
            1201 => 'Service Queued',
            1202 => 'Service Interruption',
            1203 => 'Action Blocked',
            1204 => 'Unexpected API Status',
            1205 => 'Internal Service Error',
            1206 => 'Malformed URL',
            1207 => 'Custom Action Failed',
            1208 => 'Request Body Too Large',
            1209 => 'Resource Not Configured',
            1210 => 'Invalid User ID',
            1211 => 'User Needs Action',
            1212 => 'Invalid Transaction ID',
            1213 => 'Invalid Parameter',
            1214 => 'Transaction Error',
            1215 => 'Payment Declined',
            1216 => 'API Not Responding',
            1217 => 'Feature Lockout',
            1218 => 'Invalid Session Token',
            1219 => 'Feature Unavailable',
            1220 => 'Feature Not Configured',
            1221 => 'Feature Blocked',
            1222 => 'Invalid Account',
            1223 => 'Service Validation Failed',
            1224 => 'User Session Expired',
            1225 => 'Invalid Login Attempt',
            1226 => 'Rate Limit Resetting',
            1227 => 'Service Needs Restart',
            1228 => 'Network Error Detected',
            1229 => 'User Has No Permissions',
            1230 => 'Resource Not Initialized',
            1231 => 'Service Will Be Back Soon',
            1232 => 'Service Temporary Suspension',
            1233 => 'Rate Limit Will Reset Soon',
            1234 => 'API Not Available',
            1235 => 'Service Not Responding',
            1236 => 'Invalid Resource ID',
            1237 => 'Invalid Resource State',
            1238 => 'Invalid Data Entry',
            1239 => 'Service Dependencies Not Met',
            1240 => 'Resource Locked for Editing',
            1241 => 'Resource Already Exists',
            1242 => 'Resource Already Deleted',
            1243 => 'Resource Currently Unavailable',
            1244 => 'Data Type Conflict',
            1245 => 'Invalid Content Structure',
            1246 => 'User Registration Incomplete',
            1247 => 'User Needs Verification',
            1248 => 'User Suspended',
            1249 => 'User Needs to Re-Authenticate',
            1250 => 'Service Restart Required',
            1251 => 'Action Not Permitted',
            1252 => 'Service Downtime',
            1253 => 'Service Request Failed',
            1254 => 'API Downtime',
            1255 => 'System Error Detected',
            1256 => 'Resource Lock Failed',
            1257 => 'Service Temporarily Down',
            1258 => 'Resource Not Updated',
            1259 => 'Action Queued',
            1260 => 'Data Update Failed',
            1261 => 'Connection Refused',
            1262 => 'Invalid Configuration',
            1263 => 'Database Update Failed',
            1264 => 'Database Write Failed',
            1265 => 'Feature Needs Approval',
            1266 => 'Access Control Error',
            1267 => 'Service Under Review',
            1268 => 'Configuration Conflict Detected',
            1269 => 'Invalid Permissions',
            1270 => 'Operation Cancelled',
            1271 => 'Operation Not Allowed',
            1272 => 'Custom User Error',
            1273 => 'Server Too Busy',
            1274 => 'Service Unresponsive',
            1275 => 'Invalid Web Request',
            1276 => 'Request Not Permitted',
            1277 => 'Request Denied',
            1278 => 'Invalid Application Request',
            1279 => 'Rate Limit Applied',
            1280 => 'Service Setup Required',
            1281 => 'Invalid API Credentials',
            1282 => 'API Blocked',
            1283 => 'Unauthorized Access Attempt',
            1284 => 'Operation Timed Out',
            1285 => 'Request Rejected',
            1286 => 'Invalid Application ID',
            1287 => 'Server Error Detected',
            1288 => 'Feature Requires Reconfiguration',
            1289 => 'Insufficient System Resources',
            1290 => 'Feature Not Available',
            1291 => 'User Privileges Insufficient',
            1292 => 'User Registration Required',
            1293 => 'Action Not Allowed in Current Context',
            1294 => 'Service Configuration Error',
            1295 => 'Network Configuration Error',
            1296 => 'Unsupported Action',
            1297 => 'Invalid Session State',
            1298 => 'Invalid API Version',
            1299 => 'Rate Limit Reached for User',
            1300 => 'Unrecognized Response',
            1301 => 'Unknown Service Error',
            1302 => 'Invalid Content Encoding',
            1303 => 'Unrecognized Token',
            1304 => 'Invalid Credentials Provided',
            1305 => 'Data Not Found for User',
            1306 => 'Custom Rate Limit Exceeded',
            1307 => 'Custom Error Message',
            1308 => 'Server Processing Error',
            1309 => 'Invalid Query Parameter',
            1310 => 'Feature Version Conflict',
            1311 => 'Service Timeout',
            1312 => 'User Permission Change Required',
            1313 => 'API Key Required',
            1314 => 'Unexpected Error Occurred',
            1315 => 'Custom Timeout Error',
            1316 => 'Invalid Request Method',
            1317 => 'Service Unavailable',
            1318 => 'Error Fetching Data',
            1319 => 'Service Configuration Conflict',
            1320 => 'Invalid User Action',
            1321 => 'Custom Response Error',
            1322 => 'Service Needs Restart',
            1323 => 'Request Already Processed',
            1324 => 'Custom API Error',
            1325 => 'User Account Locked',
            1326 => 'Request Timed Out',
            1327 => 'Resource Access Denied',
            1328 => 'API Rate Limit Exceeded',
            1329 => 'Service Under Maintenance',
            1330 => 'Invalid Update Request',
            1331 => 'Data Source Error',
            1332 => 'Database Connection Failed',
            1333 => 'Service Response Error',
            1334 => 'Invalid File Upload',
            1335 => 'Database Query Failed',
            1336 => 'File Not Found',
            1337 => 'Custom Authentication Error',
            1338 => 'Connection Timeout',
            1339 => 'Invalid Request Format',
            1340 => 'Database Read Error',
            1341 => 'Resource Unavailable',
            1342 => 'Data Update Error',
            1343 => 'Data Validation Error',
            1344 => 'Connection Lost',
            1345 => 'Feature Not Implemented',
            1346 => 'Unsupported Operation',
            1347 => 'User Permission Required',
            1348 => 'Invalid Service Status',
            1349 => 'User Account Disabled',
            1350 => 'User Account Inactive',
            1351 => 'Invalid Subscription',
            1352 => 'Service Blocked',
            1353 => 'Request Already Completed',
            1354 => 'Rate Limit Already Exceeded',
            1355 => 'Unknown User Error',
            1356 => 'Session Management Error',
            1357 => 'Data Sync Error',
            1358 => 'Configuration Load Error',
            1359 => 'Unknown Resource Error',
            1360 => 'Invalid Resource Token',
            1361 => 'Service Maintenance Required',
            1362 => 'User Needs to Refresh Session',
            1363 => 'User Token Expired',
            1364 => 'Invalid API Endpoint',
            1365 => 'Action Not Supported',
            1366 => 'Invalid Subscription Status',
            1367 => 'Unexpected Error Occurred',
            1368 => 'User Needs to Retry',
            1369 => 'Server Restart Required',
            1370 => 'Unsupported Request Format',
            1371 => 'Feature Lockdown',
            1372 => 'Server Configuration Error',
            1373 => 'Unknown Error',
            1374 => 'Resource Unavailable for Editing',
            1375 => 'Request Denied for User',
            1376 => 'User Account Verification Required',
            1377 => 'Invalid Authorization Code',
            1378 => 'Service Requires Maintenance',
            1379 => 'Service Update Required',
            1380 => 'User Needs to Log In Again',
            1381 => 'Rate Limit Warning',
            1382 => 'User Account Expired',
            1383 => 'Unexpected Server Behavior',
            1384 => 'Invalid Session Timeout',
            1385 => 'Operation Not Found',
            1386 => 'Request Blocked',
            1387 => 'Action Not Allowed',
            1388 => 'Service Overload Detected',
            1389 => 'Server Load Limit Exceeded',
            1390 => 'Invalid Configuration Setting',
            1391 => 'API Response Timeout',
            1392 => 'Custom Service Error',
            1393 => 'User Needs to Log In',
            1394 => 'Connection Error',
            1395 => 'Operation Not Permitted',
            1396 => 'Action Not Supported for User',
            1397 => 'User Permission Level Insufficient',
            1398 => 'Feature Disabled',
            1399 => 'Configuration Validation Error',
            1400 => 'Feature Not Accessible',
            1401 => 'Service Error Detected',
            1402 => 'Session State Error',
            1403 => 'Resource Conflict Detected',
            1404 => 'Invalid Token Provided',
            1405 => 'Database Access Denied',
            1406 => 'Service Configuration Required',
            1407 => 'Service Not Available Temporarily',
            1408 => 'Resource Update Error',
            1409 => 'User Not Authorized',
            1410 => 'User Account Needs Review',
            1411 => 'Custom Service Response Error',
            1412 => 'Service Call Failed',
            1413 => 'User Action Required for Access',
            1414 => 'Invalid User Input',
            1415 => 'Service Configuration Update Required',
            1416 => 'Invalid Account Status',
            1417 => 'User Session Blocked',
            1418 => 'Invalid Parameter Value',
            1419 => 'Custom Operation Error',
            1420 => 'Resource Unavailable for Action',
            1421 => 'Invalid API Key',
            1422 => 'Invalid Service Token',
            1423 => 'Invalid Account ID',
            1424 => 'Resource Not Active',
            1425 => 'Service Setup Required',
            1426 => 'User Registration Needed',
            1427 => 'Request Failed with Error',
            1428 => 'Invalid Authentication',
            1429 => 'Invalid API Request',
            1430 => 'Data Lock Error',
            1431 => 'Connection Timeout Error',
            1432 => 'Action Failed',
            1433 => 'User Permission Error',
            1434 => 'Resource Unavailable for Update',
            1435 => 'Invalid HTTP Method',
            1436 => 'Service Restart Required',
            1437 => 'Feature Request Denied',
            1438 => 'Invalid Service Identifier',
            1439 => 'Feature Not Available for User',
            1440 => 'Service Limit Reached',
            1441 => 'Unknown Error Code',
            1442 => 'Service Request Not Allowed',
            1443 => 'User Token Invalid',
            1444 => 'Invalid User Request',
            1445 => 'Service Blocked Temporarily',
            1446 => 'Configuration Validation Required',
            1447 => 'Invalid Resource Type',
            1448 => 'Service Blocked for User',
            1449 => 'Service Will Be Unavailable',
            1450 => 'Resource Access Denied',
            1451 => 'User Needs to Validate Account',
            1452 => 'Invalid Resource Action',
            1453 => 'User Needs to Re-Authenticate',
            1454 => 'Action Blocked for User',
            1455 => 'Invalid Webhook Token',
            1456 => 'Service Requires Attention',
            1457 => 'User Account Verification Needed',
            1458 => 'Service Temporarily Unavailable',
            1459 => 'Invalid Account Action',
            1460 => 'User Permission Change Required',
            1461 => 'Invalid API Method',
            1462 => 'Service Call Denied',
            1463 => 'Invalid Service Action',
            1464 => 'Service Request Timeout',
            1465 => 'Invalid URL Request',
            1466 => 'User Needs to Re-Log In',
            1467 => 'Service Requires Update',
            1468 => 'Server Error Occurred',
            1469 => 'User Account Locked Out',
            1470 => 'Resource Update Failed',
            1471 => 'Invalid Data Response',
            1472 => 'Request Not Processed',
            1473 => 'Invalid Client ID',
            1474 => 'Service Not Available',
            1475 => 'Server Error in Processing Request',
            1476 => 'Service Handling Error',
            1477 => 'User Needs to Accept Terms',
            1478 => 'Invalid Payment Request',
            1479 => 'Invalid Request URL',
            1480 => 'Service Access Denied',
            1481 => 'Service Error Detected',
            1482 => 'Request Body Invalid',
            1483 => 'Service Function Not Available',
            1484 => 'User Action Required for Authentication',
            1485 => 'Invalid Session Key',
            1486 => 'Server Communication Error',
            1487 => 'Service Setup Needed',
            1488 => 'Invalid Client Credentials',
            1489 => 'Feature Needs Attention',
            1490 => 'Service Request Error',
            1491 => 'Invalid Service Endpoint',
            1492 => 'Resource Not Found',
            1493 => 'Invalid Transaction',
            1494 => 'Service Blocked for Account',
            1495 => 'Configuration Error',
            1496 => 'Service Unavailable',
            1497 => 'Invalid URL Parameter',
            1498 => 'Invalid Request Context',
            1499 => 'Service Shutdown Detected',
        ];

        if ($msg === null OR is_null($msg) OR $msg === '') {
            $msg = $statusMessages[200];
        } else {
            $msg = $statusMessages[$code];
        }

        http_response_code($code);
        header("HTTP/1.1 $code $msg");

        return [
            'code' => $code,
            'message' => $msg,
        ];
    }    

    /**
     * Set HTTP response headers for file downloads.
     *
     * @param string $name Filename of the file being downloaded.
     * @param int $length Length of the file being downloaded.
     */
    public static function file($name, $length) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.$name.'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: '.$length);
    }

    public static function livemap($url = '/livemap') {
        $liveData = PHRO::footprint();
        if (strpos($liveData['REQUEST_URI'], $url) === false) {
            $footprint = PHRO::footprint();
            $data = [
                'private' => $footprint['private'],
                'public' => $footprint['public'],
                'latitude' => $footprint['latitude'],
                'longitude' => $footprint['longitude'],
                'country' => $footprint['country'],
                'city' => $footprint['city'],
                'country' => $footprint['country'],
                'countryCode' => $footprint['countryCode'],
                'timezone' => $footprint['timezone'],
                'isp' => $footprint['isp'],
                'is_mobile' => $footprint['is_mobile'],
                'is_desktop' => $footprint['is_desktop'],
                'proxy' => $footprint['proxy'],
                'platform' => $footprint['platform'],
                'browser' => $footprint['browser'],
                'browser_version' => $footprint['browser_version'],
                'host' => $footprint['HTTP_HOST'],
                'requestURL' => $footprint['REQUEST_URI'],
                'requestMETHOD' => $footprint['REQUEST_METHOD'],
                'requestTIME' => $footprint['REQUEST_TIME'],
                'requestFLOAT' => $footprint['REQUEST_TIME_FLOAT'],
                'netkey' => $footprint['netkey'],
                'devicekey' => $footprint['devicekey']
            ];
            PHLS::limitizer('livemap', $data, 10, 60*24);
        }

        PHRO::get($url, function() {
            PHRQ::header("GET", "*", "text/html; charset=UTF-8", []);
                print <<<EOT
                <!DOCTYPE html>
                <html lang="en">

                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Tratraffic Map</title>
                    <meta name="icon" content="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABIAAAASCAMAAABhEH5lAAAB3VBMVEVHcEydiSEAAAChNxsbCQSQfR9HGwi+RCELBALApykQEAepOh2xPR6wmCVVMw7cTiWGdB14KhUAAABkWBd5aRu+QiDVSSPPtCqUMxrGRSG5QB+GfEaJMBg+NQZ/KhOtn02okiTWUi2YNhvAQiC+QyLAmildURVfEADWh3G9fG1pXBjEqihNRBOELxikORyKbR29pCcuKQ2QPxrrWCjxZirx0ij63R/jUSZ/jKv94B44PWPpaiqircLoVCbuZChgao7vYSnuzymUnbjqei/Iw4OPl5/glCOWp8bzthitv9nsxCn11yXE0t+KmbyOm7OHjaNzf6Lk6vLatyayvNLxbzrByNN3gqakuNjlxyy1rZJbZo+5qDjobjrpyizfwiyanLGWiJWGfFm9jDd6a2rddR/dvx2UbVL340Oqm3y2eCbtzSBue53akx311xZ+h56bizvx1i6/p3fzpS7qqhq5vszshi5OV4GhmaW0oYW2sr7Fj331glGbrc3xbDLKaENpc5jGmIzSn5bbhmXQ3OmqUkKur76Hb32zblc/RWx6em5UWn3jyivpyyvUwU1teJu/dV3JwHXCw7jcdU3rzxuQkorb0Hnx1B6wq3RgZ4DErC5tcoDgtCrLZTmAd0vjXybI0qtxAAAAM3RSTlMAtQJvD50l1wjYFYKX2j7+jVcBb3zc+PpY8KzqRjA188Twh73N11Ea59tk90p1r6zjM6B1d64/AAABMUlEQVQY02MQUhJlBwNVTjBg5WBg4DE3BgFrL1MQiGUECnGbm5g4O1uXBnl7ZVmaxbIyMTAwA4Wsc9w8it0yUy3NIgQYGBgk+B1CqotyvT1SktItzeJEgEIyYiEN5RWFkRlpya4uBUFaQCE5xUY/e/8S/zwLK3t3F3E1oBCbRqBNgFNoqE22VYCnjzwfUEhdM96iJswn0LPMyt3XT1oWKMSi0tIaXt8c7BvVEe6az8sGFOJQNq5rCwu26Yvq6ox25OUCCjFJ2dY2VSY42U+Z2GtmqsACFGLgsY2pau93mhE9dUK3I8g/IB/Z2dlNip8VOX2yWQ/IPyAfOZiYxCRYWCROg/gH6CNJfltjhzmJc2c6xmkLg4U4+HT1RMXmzRY3YBTR4WKAAg42QSN9YUOwbQwA5ztHGpIVCvUAAAAASUVORK5CYII=">
                    <meta name="description" content="PHKing Dev | PHP Framework">
                    <meta name="author" content="sakibweb">
                    <link rel="icon" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABIAAAASCAMAAABhEH5lAAAB3VBMVEVHcEydiSEAAAChNxsbCQSQfR9HGwi+RCELBALApykQEAepOh2xPR6wmCVVMw7cTiWGdB14KhUAAABkWBd5aRu+QiDVSSPPtCqUMxrGRSG5QB+GfEaJMBg+NQZ/KhOtn02okiTWUi2YNhvAQiC+QyLAmildURVfEADWh3G9fG1pXBjEqihNRBOELxikORyKbR29pCcuKQ2QPxrrWCjxZirx0ij63R/jUSZ/jKv94B44PWPpaiqircLoVCbuZChgao7vYSnuzymUnbjqei/Iw4OPl5/glCOWp8bzthitv9nsxCn11yXE0t+KmbyOm7OHjaNzf6Lk6vLatyayvNLxbzrByNN3gqakuNjlxyy1rZJbZo+5qDjobjrpyizfwiyanLGWiJWGfFm9jDd6a2rddR/dvx2UbVL340Oqm3y2eCbtzSBue53akx311xZ+h56bizvx1i6/p3fzpS7qqhq5vszshi5OV4GhmaW0oYW2sr7Fj331glGbrc3xbDLKaENpc5jGmIzSn5bbhmXQ3OmqUkKur76Hb32zblc/RWx6em5UWn3jyivpyyvUwU1teJu/dV3JwHXCw7jcdU3rzxuQkorb0Hnx1B6wq3RgZ4DErC5tcoDgtCrLZTmAd0vjXybI0qtxAAAAM3RSTlMAtQJvD50l1wjYFYKX2j7+jVcBb3zc+PpY8KzqRjA188Twh73N11Ea59tk90p1r6zjM6B1d64/AAABMUlEQVQY02MQUhJlBwNVTjBg5WBg4DE3BgFrL1MQiGUECnGbm5g4O1uXBnl7ZVmaxbIyMTAwA4Wsc9w8it0yUy3NIgQYGBgk+B1CqotyvT1SktItzeJEgEIyYiEN5RWFkRlpya4uBUFaQCE5xUY/e/8S/zwLK3t3F3E1oBCbRqBNgFNoqE22VYCnjzwfUEhdM96iJswn0LPMyt3XT1oWKMSi0tIaXt8c7BvVEe6az8sGFOJQNq5rCwu26Yvq6ox25OUCCjFJ2dY2VSY42U+Z2GtmqsACFGLgsY2pau93mhE9dUK3I8g/IB/Z2dlNip8VOX2yWQ/IPyAfOZiYxCRYWCROg/gH6CNJfltjhzmJc2c6xmkLg4U4+HT1RMXmzRY3YBTR4WKAAg42QSN9YUOwbQwA5ztHGpIVCvUAAAAASUVORK5CYII=">

                    <style>
                        body {
                        margin: 0;
                        overflow: hidden;
                        background-color: black;
                        display: flex;
                        flex-direction: column;
                        align-items: center; /* Center the contents */
                        height: 100vh; /* Use full viewport height */
                        }
                        #map {
                        position: relative;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        }
                        .header, .footer {
                        position: absolute;
                        width: calc(60% - 60px); /* Keep this at 60% */
                        background: rgba(255, 255, 255, 0.02);
                        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
                        backdrop-filter: blur(2px);
                        -webkit-backdrop-filter: blur(2px);
                        color: white;
                        text-align: center;
                        padding: 10px;
                        z-index: 2; /* Ensure the header is on top */
                        }
                        .header {
                        top: 0px; /* Set some space from the top */
                        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
                        }
                        .footer {
                        bottom: 0px; /* Set some space from the bottom */
                        display: flex;
                        justify-content: center;
                        border-top: 1px solid rgba(255, 255, 255, 0.2);
                        }
                        .color-palette {
                            width: auto;
                            display: flex;
                            justify-content: space-around;
                            align-content: center;
                            align-items: center;
                            flex-wrap: nowrap;
                            flex-direction: row;
                        }
                        .color-palette-item {
                            width: 20px;
                            height: 20px;
                            border-right: 1px solid rgba(255, 255, 255, 0.2);
                            border-radius: 5px;
                        }
                        .color-palette a {
                            padding-left: 5px;
                            padding-right: 8px;
                        }
                        .sidebar {
                        position: absolute;
                        left: 0;
                        /* top: 38px; Below the header */
                        width: 20%;
                        height: calc(100% - 100px); /* Full height minus header and footer */
                        background: rgba(255, 255, 255, 0.02);
                        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
                        backdrop-filter: blur(2px);
                        -webkit-backdrop-filter: blur(2px);
                        border-right: 1px solid rgba(255, 255, 255, 0.2);
                        color: white;
                        overflow-y: auto; /* Allow scrolling */
                        z-index: 2; /* Ensure the sidebar is on top */
                        padding: 10px;
                        margin-top: 40px;
                        margin-bottom: 40px;
                        }
                        .details {
                        position: absolute;
                        /* top: 50px; Below the header */
                        right: 0;
                        width: 20%;
                        height: calc(100% - 100px); /* Full height minus header and footer */
                        background: rgba(255, 255, 255, 0.02);
                        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
                        backdrop-filter: blur(2px);
                        -webkit-backdrop-filter: blur(2px);
                        border-left: 1px solid rgba(255, 255, 255, 0.2);
                        color: white;
                        overflow-y: auto; /* Allow scrolling */
                        z-index: 2; /* Ensure the details section is on top */
                        padding: 10px;
                        margin-top: 40px;
                        margin-bottom: 40px;
                        }
                        .tooltip {
                        position: absolute;
                        background: rgba(255, 255, 255, 0.02);
                        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
                        backdrop-filter: blur(2px);
                        -webkit-backdrop-filter: blur(2px);
                        border: 1px solid rgba(255, 255, 255, 0.2);
                        color: white;
                        padding: 5px;
                        border-radius: 5px;
                        display: none; /* Hidden by default */
                        z-index: 3; /* Above everything else */
                        }
                    
                        .attack-card {
                        background: rgba(255, 255, 255, 0.05);
                        border-radius: 8px;
                        padding: 10px;
                        margin-bottom: 10px;
                        color: white;
                        display: flex;
                        flex-direction: column;
                        border: 1px solid rgba(255, 255, 255, 0.2);
                        }
                        .attack-header, .attack-footer {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        }
                        .attack-country img {
                        width: 20px;
                        margin-right: 5px;
                        }
                        .attack-type {
                        background-color: #FF4500;
                        padding: 5px 10px;
                        border-radius: 12px;
                        color: white;
                        font-size: 12px;
                        text-transform: uppercase;
                        }
                        .attack-footer img {
                        width: 20px;
                        margin-right: 5px;
                        }
                        #attack-list {
                            padding-left: 0px;
                        }
                        .attack-body {
                            font-size: 14px;
                            margin-top: 8px;
                            margin-bottom: 8px;
                            display: flex;
                            flex-direction: row;
                            flex-wrap: nowrap;
                            align-items: center;
                            overflow-x: auto;
                            white-space: nowrap;
                            justify-content: space-between;
                            align-content: space-between;
                        }
                    </style>
                    <script src="//unpkg.com/globe.gl"></script>
                </head>

                <body>
                    <div class="header">Cyber Threat Live Map</div>
                    <div id="map"></div>
                    <div class="sidebar" id="live-attacks">
                        <h3>Live Attacks</h3>
                        <ul id="attack-list"></ul>
                    </div>
                    <div class="details" id="attack-details">
                        <h3>Attack Details</h3>
                        <p id="details-content">Select an attack to view details.</p>
                    </div>
                    <div class="footer">
                        <div class="color-palette" id="color-palette">
                            <div class="color-palette-item" style="background-color: #FF4500;"></div><a>Attack Type 1</a>
                            <div class="color-palette-item" style="background-color: #00FFFF;"></div><a>Attack Type 2</a>
                            <div class="color-palette-item" style="background-color: #FFD700;"></div><a>Attack Type 3</a>
                        </div>
                    </div>
                    <div class="tooltip" id="tooltip"></div>


                    <script>
                        // Neon color palette
                        const neonColors = ['#00FFFF', '#FF00FF', '#00FF00', '#FF4500', '#FFD700'];
                    
                    const markerSvg = `<svg viewBox="-4 0 36 36">
                        <path fill="currentColor" d="M14,0 C21.732,0 28,5.641 28,12.6 C28,23.963 14,36 14,36 C14,36 0,24.064 0,12.6 C0,5.641 6.268,0 14,0 Z"></path>
                        <circle fill="black" cx="14" cy="14" r="7"></circle>
                    </svg>`;
                    
                        // Gen random data
                        const N = 5;
                        const arcsData = [...Array(N).keys()].map(() => ({
                        startLat: (Math.random() - 0.5) * 180,
                        startLng: (Math.random() - 0.5) * 360,
                        endLat: (Math.random() - 0.5) * 180,
                        endLng: (Math.random() - 0.5) * 360,
                        color: [neonColors[Math.floor(Math.random() * neonColors.length)], neonColors[Math.floor(Math.random() * neonColors.length)]]
                        }));
                    
                        // Extract marker data from arcsData (use start points of arcs)
                        const markersData = arcsData.map(arc => ({
                        lat: arc.startLat,
                        lng: arc.startLng,
                        color: arc.color[0] // Use the color of the arc start
                        }));
                    
                    
                    
                    // Function to add generated data to the attack-list
                    function addToAttackList(data, listId) {
                    const attackList = document.getElementById(listId);
                    
                    data.forEach(item => {
                        const attackList = document.getElementById('attack-list');
                        const li = document.createElement('li');
                        li.className = 'attack-card';
                        li.innerHTML = `
                        <div class="attack-header">
                            <div class="attack-country">
                            <img src="https://flagcdn.com/w320/us.png" alt="US"> US
                            </div>
                            <div class="attack-time">\${new Date().toLocaleTimeString()}</div>
                            <div class="attack-type">GET</div>
                        </div>
                        <div class="attack-body">
                            <div class="attack-ip">192.168.1.1</div>
                            <div class="attack-time">»</div>
                            <div class="attack-ip">192.168.1.100</div>
                        </div>
                        <div class="attack-footer">
                            <div class="attack-device">
                            <img src="device-icon.png" alt="Device">12345
                            </div>
                            <div class="attack-network">Wi-Fi</div>
                        </div>
                        `;
                        attackList.prepend(li);
                    });
                    }
                    // Function to add click event listener to attack-list items
                    function setAttackDetailsOnClick(listId, detailsId) {
                    const attackList = document.getElementById(listId);
                    const detailsContent = document.getElementById(detailsId);
                    
                    // Delegate click event to the attack-list
                    attackList.addEventListener('click', (event) => {
                        const target = event.target.closest('li.attack-card'); // Select the closest attack-card li
                        if (target) {
                        const attackCountry = target.querySelector('.attack-country').textContent.trim();
                        const attackTime = target.querySelector('.attack-time').textContent.trim();
                        const attackType = target.querySelector('.attack-type').textContent.trim();
                        const attackIpFrom = target.querySelector('.attack-body .attack-ip').textContent.trim();
                        const attackIpTo = target.querySelector('.attack-body .attack-ip:nth-child(3)').textContent.trim();
                        const attackDevice = target.querySelector('.attack-device').textContent.trim();
                        const attackNetwork = target.querySelector('.attack-network').textContent.trim();
                    
                        // Construct the details content
                        detailsContent.innerHTML = `
                            <strong>Attack Details</strong><br>
                            <strong>Country:</strong> \${attackCountry}<br>
                            <strong>Time:</strong> \${attackTime}<br>
                            <strong>Type:</strong> \${attackType}<br>
                            <strong>Source IP:</strong> \${attackIpFrom}<br>
                            <strong>Destination IP:</strong> \${attackIpTo}<br>
                            <strong>Device ID:</strong> \${attackDevice}<br>
                            <strong>Network Type:</strong> \${attackNetwork}
                        `;
                        }
                    });
                    }
                    
                    // Adding arcsData to attack-list
                    addToAttackList(arcsData, 'attack-list');
                    
                    // Call the function after attack-list is populated
                    setAttackDetailsOnClick('attack-list', 'details-content');
                    
                    
                    
                    
                    
                        const map = Globe()
                            .globeImageUrl('//unpkg.com/three-globe@2.31.1/example/img/earth-night.jpg')
                            .bumpImageUrl('//unpkg.com/three-globe/example/img/earth-topology.png')
                            .backgroundImageUrl('//unpkg.com/three-globe/example/img/night-sky.png')
                            .arcsData(arcsData)
                            .arcColor('color')
                            .arcStroke(0.8)
                            .arcDashLength(0.6)
                            .arcDashGap(0.05)
                            .arcDashAnimateTime(() => Math.random() * 4000 + 200)
                    
                            // Add markers
                            .htmlElementsData(markersData)
                            .htmlElement(marker => {
                                const el = document.createElement('div');
                                el.innerHTML = markerSvg;
                                el.style.color = marker.color;
                                el.style.width = '16px';  // Adjust marker size
                                el.className = 'marker';
                    
                                el.style['pointer-events'] = 'auto';
                                el.style.cursor = 'pointer';
                                el.onclick = () => {
                                    // console.info('Clicked marker at', marker.lat, marker.lng);
                                    document.getElementById('attack-info').textContent = `Clicked marker at Lat: \${marker.lat}, Lng: \${marker.lng}`;
                                    document.getElementById('attack-list').innerHTML += `<li>Attack at Lat: \${marker.lat}, Lng: \${marker.lng}</li>`;
                    
                                };
                                // Tooltip functionality
                                el.onmouseover = () => {
                                showTooltip(marker.lat, marker.lng, el);
                                };
                                el.onmouseout = () => {
                                hideTooltip();
                                };
                                return el;
                            })
                        (document.getElementById('map'));
                    
                    
                        // Polygons
                        fetch('https://globe.gl/example/datasets/ne_110m_admin_0_countries.geojson')
                        .then(res => res.json())
                        .then(countries => {
                            map
                            .polygonsData(countries.features)
                            .polygonCapColor(() => '#191d1b')
                            .polygonSideColor(() => '#010f23')
                            .polygonStrokeColor(() => '#010f23')
                            .polygonAltitude(0.02);
                        });
                        
                    
                        // Label
                        fetch('https://globe.gl/example/datasets/ne_110m_populated_places_simple.geojson')
                        .then(res => res.json())
                        .then(places => {
                            map
                            .labelsData(places.features)
                            .labelLat(d => d.properties.latitude)
                            .labelLng(d => d.properties.longitude)
                            .labelText(d => d.properties.name)
                            .labelSize(1)
                            .labelColor(() => 'rgba(255, 165, 0, 0.75)')
                            .labelResolution(1)
                            .labelAltitude(0.02);
                        });
                    
                        // Control the zoom (min and max distance)
                        const controls = map.controls();
                        controls.minDistance = 250;
                        controls.maxDistance = 450;
                    
                        // Animate the globe background
                        //   map.controls().autoRotate = true;
                        //   map.controls().autoRotateSpeed = 1;
                    
                    
                        map.renderer().setPixelRatio(window.devicePixelRatio / 1); // Reduce resolution
                        // Optionally disable auto-rotate if enabled
                        map.controls().autoRotate = false;
                    
                    
                    
                        // Tooltip for marker info
                        const tooltip = document.createElement('div');
                        tooltip.className = 'tooltip';
                        document.body.appendChild(tooltip);
                    
                        function showTooltip(lat, lng, marker) {
                        tooltip.textContent = `Lat: \${lat.toFixed(2)}, Lng: \${lng.toFixed(2)}`;
                        tooltip.style.display = 'block';
                        tooltip.style.left = `\${marker.getBoundingClientRect().left + window.scrollX + 20}px`;
                        tooltip.style.top = `\${marker.getBoundingClientRect().top + window.scrollY - 30}px`;
                        }
                    
                        function hideTooltip() {
                        tooltip.style.display = 'none';
                        }
                    
                    
                        // Define pause and resume animation functions
                        function pauseAnimation() {
                            map.controls().autoRotate = false;  // Disable auto-rotate if enabled
                            // console.log("Animation paused");
                        }
                    
                        function resumeAnimation() {
                            map.controls().autoRotate = true;  // Enable auto-rotate when the page is visible again
                            map.controls().autoRotateSpeed = 1;  // Set your desired rotation speed
                            // console.log("Animation resumed");
                        }
                    
                        // Listen for visibility change events
                        document.addEventListener('visibilitychange', () => {
                            if (document.hidden) {
                                pauseAnimation();  // Pause animation when the page is hidden
                            } else {
                                resumeAnimation();  // Resume animation when the page is visible again
                            }
                        });
                    
                        // Handle page unload/close events
                        window.addEventListener('beforeunload', pauseAnimation);
                        window.addEventListener('unload', pauseAnimation);
                    
                        // Pause animation when the tab is minimized or the window loses focus
                        window.addEventListener('blur', pauseAnimation);
                        // Resume animation when the tab is focused again
                        window.addEventListener('focus', resumeAnimation);
                    </script>
                </body>

                </html>
                EOT;
        });

        PHRO::post($url, function() {
            header('Content-Type: application/json');

            $data = PHLS::get('livemap');

            if (is_array($data) && !empty($data)) {
                echo json_encode($data);
            }
        });
    }

    /**
     * Stream data to the client.
     *
     * @param int $sleepSec The number of seconds to sleep between data updates (min: 1, max: 300).
     * @param string $type The content type for the response (default: "text").
     * @param callable $callback The callback function to execute for generating data.
     */
    public static function stream($sleep = 1, $type = "text", callable $callback) {
        if ($sleep < 1 || $sleep > 300) {
            throw new InvalidArgumentException("Sleep duration must be between 1 and 300 seconds.");
        }

        header('Content-Type: ' . $type . '/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');

        ob_implicit_flush(true);
        ob_end_flush();

        while (true) {
            if (connection_aborted()) {
                exit("Client disconnected, terminating stream.\n");
            }

            $data = call_user_func($callback);

            echo "data: " . json_encode($data) . "\n\n";

            ob_flush();
            flush();

            sleep($sleep);
        }
    }
}

?>
