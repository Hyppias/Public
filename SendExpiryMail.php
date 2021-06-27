<?php
// This script sends emails to users of licensed programs
// it checks to see if a license will expire at a date one month from the date the script runs.
// it runs as a PHP cron job each day.

// The license manager is the WordPress plug-in:
// Software License Manager
// from:
//  https://wordpress.org/plugins/software-license-manager/

// this script by: E.H. Terwiel,
// after an example found at: https://www.sitepoint.com/community/t/php-email-reminder-script/240604/9
// Date: 6/23/2021

// to create the database user LicenseManager with minimal privileges:
// DROP USER 'LicenseManager';
// CREATE USER 'LicenseManager' IDENTIFIED BY 'V89gGben6swqJoPYft';
// REVOKE ALL , GRANT OPTION FROM  'LicenseManager';
// GRANT SELECT ON deb136136_Terwiel.cc_lic_key_tbl TO 'deb_Terwiel_LicMan';
// GRANT SELECT ON terwiel.cc_lic_reg_domain_tbl TO 'LicenseManager';

$Database = "terwiel";
$User = "root";
$Password = "AUnpQhPtLKYz8oyUCw";
$Server = "localhost";

$db = mysqli_connect($Server , $User, $Password, $Database) or die("Check connection parameters!");


if (mysqli_connect_error()) {
    die ('Failed to connect to MySQL from SendExpiryMail.php');
}
else {
    /*SUCCESS MSG*/
    echo 'successful connection';
}

$log_file="C:/xampp/htdocs/SendExpiryMail/LicenseExpiryMessages.log";
// $log_file="/home/deb136136/domains/cablecalc.nl/public_html/LicenseExpiryMessages.log";

// WHERE date_expiry = '2021-08-08'
//WHERE 'date_expiry' BETWEEN CURDATE() AND CURDATE()+INTERVAL 1 MONTH
$sqlCommand = "SELECT c.*, count(p.lic_key_id) as domains " .
    "FROM cc_lic_key_tbl AS c LEFT OUTER JOIN cc_lic_reg_domain_tbl AS p " .
    "ON p.lic_key_id = c.id WHERE c.date_expiry = '2021-08-08'";

$query = mysqli_query($db, $sqlCommand) or die (mysqli_error($db));

while ($row = mysqli_fetch_object($query)) {

    //$to = $row->email;
    $to = "erik@terwiel.org";
    $headers = "From: webmaster@terwiel.org\r\n";
    $headers .= "Bcc: info@terwiel.com\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

    $subject = "Naderend einde licentie " . $row->product_ref;

    $r1 = "<tr><td>";
    $r2 = "</td><td>";
    $r3=  "</td></tr>";
    $message =
        "<html><body>" .
        "<p>Beste " . $row->first_name . " " . $row->last_name . ",</p>" .
        "<p>Uw licentie voor ". $row->product_ref ." zal op " . $row->date_expiry . "  aflopen.<br>" .
        "Om ononderbroken van " . $row->product_ref . " gebruik te kunnen blijven maken is het van belang " .
        "contact op te nemen met de leverancier.</p>" .

        "<table style=\"background-color:powderblue;vertical-align:middle;\">" .
        "<thead style=\"background-color:wheat;\"><tr><th colspan='2'><h4>Licentie-gegevens</h4></th></tr></thead>" .
        "$r1 Product naam $r2 " . $row->product_ref . $r3 .
        "$r1 Licentiesleutel $r2 ************* $r3" .
        "$r1 Naam licentiehouder $r2 " . $row->first_name . " " .$row->last_name . $r3 .
        "$r1 e-mail licentiehouder $r2 ".$row->email.  $r3 .
        "$r1 Licentiestatus $r2 ".$row->lic_status . $r3 .

        "$r1 Bedrijfsnaam $r2 ".$row->company_name . $r3 .
        "$r1 Begindatum $r2 ".$row->date_created . $r3 .
        "$r1 Vernieuwd op $r2 ".$row->date_renewed . $r3 .
        "$r1 Afloopdatum $r2 ".$row->date_expiry . $r3 .
        "$r1 Maximum aantal domeinen $r2 ".$row->max_allowed_domains . $r3 .
        "$r1 Aantal geregistreerde domeinen $r2 ".$row->domains . $r3 .  "</table>" ;

    $message .= "<hr>";

    $message .=
        "<p>Dear ".$row->first_name." ". $row->last_name.", </p>" .
        "<p>Your license for ".$row->product_ref."  will expire on ".$row->date_expiry." <br>" .
        "To guarantee uninterrupted use of ".$row->product_ref." it is important to " .
        "contact your supplier.</p>" .
        "<table style=\"background-color:powderblue;vertical-align:middle;\">" .
        "<thead  style=\"background-color:wheat;\"><tr><th colspan='2'><h4>License data</h4></th></tr></thead>" .
        "$r1 Product name $r2 ".$row->product_ref . $r3 .
        "$r1 License key $r2 ************* $r3" .
        "$r1 Name of license holder $r2 ".$row->first_name ." " . $row->last_name . $r3 .
        "$r1 License holder's e-mail $r2 ".$row->email  . $r3 .
        "$r1 License status $r2 ".$row->lic_status . $r3 .

        "$r1 Company name $r2 ".$row->company_name . $r3 .
        "$r1 Start date $r2 ".$row->date_created . $r3 .
        "$r1 Renewed on $r2 ".$row->date_renewed . $r3 .
        "$r1 Expiry date $r2 ".$row->date_expiry . $r3 .
        "$r1 Maximum number of domains $r2 ".$row->max_allowed_domains . $r3 .
        "$r1 Number of registered domains $r2 " . $row->domains . $r3 . "</table></body></html>" ;

    $sendmail = mail($to, $subject, $message, $headers);

    $date = date("Y-m-d h:i:s ", time());

    if ($sendmail) {
        echo $message;
        error_log("[$date] License expiry message sent to $to \r\n",3, $log_file);
    } else {
        error_log("[$date] Error in Sending License expiry message  $to \r\n",3, $log_file);
    }
}

// Free the results
mysqli_free_result($query);

//close the connection
mysqli_close($db);

?>