<?php

include_once (dirname(dirname(__FILE__)) . '/config.php');

//Initial response is NULL
$response = null;

//Initialize appropriate action and return as HTML response
if (isset($_POST["action"])) {
    $action = $_POST["action"];

    switch ($action) {
        case "SendMessage": {
                $name    = isset($_POST["name"]) ? trim($_POST["name"]) : '';
                $emailIn = isset($_POST["email"]) ? trim($_POST["email"]) : '';
                $subject = isset($_POST["subject"]) ? trim($_POST["subject"]) : '';
                $message = isset($_POST["message"]) ? trim($_POST["message"]) : '';

                if (!empty($emailIn)) {
                    $response = (SendEmail($message, $subject, $name, $emailIn, $email)) ? 'Message Sent' : "Sending Message Failed";
                } else {
                    $response = "Sending Message Failed";
                }
            }
            break;
        default: {
                $response = "Invalid action is set! Action is: " . $action;
            }
    }
}


if (isset($response) && !empty($response) && !is_null($response)) {
    echo '{"ResponseData":' . json_encode($response) . '}';
}

function SendEmail($message, $subject, $name, $from, $to) {
    $isSent = false;
    $cleanName    = filter_var($name, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    $cleanFrom    = filter_var($from, FILTER_VALIDATE_EMAIL);
    $cleanSubject = $subject ?: 'Website contact';

    // Use a domain-owned from address to avoid SPF/DKIM spam issues and put the visitor email in Reply-To
    $domainHost   = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'efren-cavazos.com';
    $fromAddress  = 'no-reply@' . preg_replace('/^www\\./', '', $domainHost);

    $bodyLines = array(
        "New contact form submission",
        "Name: " . ($cleanName ?: 'Unknown'),
        "Email: " . ($cleanFrom ?: 'Not provided'),
        "Subject: " . $cleanSubject,
        "",
        "Message:",
        $message
    );
    $body = implode("\n", $bodyLines);

    $headers = array();
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/plain; charset=UTF-8';
    $headers[] = 'From: Efren Cavazos Contact <' . $fromAddress . '>';
    if ($cleanFrom) {
        $headers[] = 'Reply-To: ' . $cleanFrom;
    }
    $headers[] = 'X-Feedback-ID: contact-form';
    $headers[] = 'X-Entity-Ref-ID: contact-form';

    if (@mail($to, $cleanSubject, $body, implode("\r\n", $headers))) {
        $isSent = true;
    }
    return $isSent;
}

?>
