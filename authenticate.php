<?php

/*
Request status codes:
 * 0 = try the email data to connect
 * 1 = get the authentication url
 * 2 = get the token information
 */

require_once 'serverConstants.php';
session_start();

$data = $_REQUEST;
$msClientID = "";
$msTenantID = "";
$msSecretID = "";
$_SESSION['client'] = "";
if (isset($data['i'])) {
    $_SESSION['md_code'] = $data['i'];
}

if (isset($_GET['code'])) {
    if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
        echo "Invalid Session state";
        unset($_SESSION['oauth2state']);
        exit;
    } else {
        $idCode = $_GET['state'];
        $authCode = $_GET['code'];
        $userData = $_SESSION['user_data'];
        $msClientID = $_SESSION['client'];
        $msEntSecretID = "";
        $redirect_uri = "https://eu.arithon.com/mail/authenticate.php";

        $mysql = mysql_connect($mySqlHost, $mySqlUser, $mySqlPass);
        mysql_select_db($mySqlDB);
        mysql_query("UPDATE `mail_link`.`request` SET `status` = 2, `auth_code` = '$authCode' WHERE `code` = '$idCode' ", $mysql);


$postUrl = "/mycomp.onmicrosoft.com/oauth2/v2.0/token";

$hostname = "login.microsoftonline.com";
$fullurl = "https://login.microsoftonline.com/mycompany.onmicrosoft.com/token";

    $resultData = mysql_fetch_array($result, MYSQL_ASSOC);
    $userName = $resultData['username'];
    $database = $resultData['db_username'];
    $userID = $resultData['ID'];
    $oracleDB = oci_connect($oracleUser, $oraclePass, $oracleHost);
    $dataStm = oci_parse($oracleDB, "SELECT * FROM $database.email WHERE user_id = '$userName' ORDER BY id ASC");
    if (!oci_execute($dataStm)) {
        echo "Failed to find data in the email table in $database for $userName";
        exit;
    }
    if (!($oracleData = oci_fetch_array($dataStm, OCI_ASSOC+OCI_RETURN_NULLS+OCI_RETURN_LOBS))) {
        echo "Failed to retrieve data from the email table in $database for $userName";
        exit;
    }
    // Check connection using the email details
//    imap_timeout(IMAP_OPENTIMEOUT, 60);
//    if ($oracleData['INCOMING_MAIL_HOST'] == 'outlook.office365.com') {
//        $connectionString = "{outlook.office365.com:993/imap4rev1/ssl/novalidate-cert}";
//    } else {
//        $connectionString = "{" . str_replace('tls://', '', $oracleData['INCOMING_MAIL_HOST']) . ":" 
//                . $oracleData['MAIL_SERVER_PORT'] . '/imap';
//        if (!$tls) {
//            $connectionString .= ($oracleData['USE_SSL'] == 'off' ? '' : '/ssl') . "/notls";
//        }
//        // $connectionString .= $this->ssl . (stristr($this->incomingMailServer, "tls://") === false ? "/notls" : "");
//        $connectionString .= "/novalidate-cert}";
//    }
//    $imap = imap_open($connectionString, $oracleData['USERNAME'], $oracleData['PASSWORD'], OP_HALFOPEN);
//    if ($imap) {
//        echo "Success, connected using the entered details.";
//        exit;
//    }
    $mailHost = $oracleData['INCOMING_MAIL_HOST'];
    $_SESSION['user_data'] = array(
        'name' => $userName,
        'db' => $database,
        'host' => $mailHost
    );
    
    if ($mailHost == 'outlook.office365.com') {
        mysql_query("UPDATE `mail_link`.`request` SET `status` = 1, `incoming_server` = '$mailHost' WHERE `code` = '$idCode' ", $mysql);
        $_SESSION['oauth2state'] = $idCode;
        $msClientID = "";
        $msTenantID = "";
        $msEntSecretID = "";
        $url = "https://login.microsoftonline.com/common/oauth2/v2.0/authorize?"
                . "client_id=$msClientID"
                . "&response_type=code"
                . "&redirect_uri=". urlencode("https://eu.arithon.com/mail/authenticate.php") 
                . "&response_mode=query"
                . "&scope=offline_access%20openid%20mail.read"
                . "&state=$idCode";
//        $url = "https://arithonlink.b2clogin.com/arithonlink.onmicrosoft.com/b2c_1_sign_in/oauth2/v2.0/authorize";
//        $url = "https://login.microsoftonline.com//oauth2/v2.0/authorize";
        header('Location: ' . $url);
    } else {
        mysql_query("UPDATE `mail_link`.`request` SET `status` = 1, `incoming_server` = '$mailHost' WHERE `code` = '$idCode' ", $mysql);
        $requestURL = "http://devnew.arithon.com/mail/request.php?r=$idCode";
        echo "<br>".$requestURL."<br>";
        $curl = curl_init();
        $options = array(
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 2,
            CURLOPT_URL => $requestURL,
            CURLOPT_CUSTOMREQUEST => "GET"
        );
        curl_setopt_array($curl, $options);
        $response = json_decode(curl_exec($curl));
        echo 1;
        if ($response->code != 200) {
            echo $response->message;
            exit;
        }
        echo 2;
        $result = mysql_query("SELECT * FROM `mail_link`.`request` WHERE `code` = '$idCode'", $mysql);
        $resultData = mysql_fetch_array($result, OCI_ASSOC+OCI_RETURN_NULLS+OCI_RETURN_LOBS);
        $retState = $resultData['state'];
        $retUrl = $resultData['url'];
        $_SESSION['oauth2state'] = $retState;
        header('Location: ' . $retUrl);
    }
}

$post_params = array(
    "client_id" => $clientId,
    "scope" => "https%3A%2F%2Fgraph.microsoft.com%2F.default",
    "client_secret" => $clientSecret,
    "grant_type" => "client_credentials",
);
$curl = curl_init($fullurl);

curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_POSTFIELDS, $post_params);
curl_setopt($curl, CURLOPT_HTTPHEADER, array("application/x-www-form-urlencoded"));
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

$response = curl_exec($curl);
