<?php 
require '../../vendor/autoload.php';
use \ForceUTF8\Encoding;

$action = $_POST['action'];

switch($action) {
    case "upload" :
        uploadCsv();
        break;
}

/**
 * structure CSV :
 *  [0] => Nom
    [1] => Description
    [2] => Adresse
    [3] => URL
 */
function uploadCsv()
{
    $pdo = dbConnect();
    $data = $_POST['data'];
    // Transformation des fins de ligne au format LF
    $data = str_replace("\r\n", PHP_EOL, $data);
    // détection du jeu de caractères
    $data = Encoding::fixUTF8($data);

    
    $data = explode(PHP_EOL, $data);
    $sql = "INSERT INTO coords 
           (coords_nom, coords_desc, coords_adresse, coords_url)
           VALUES (:nom, :desc, :adresse, :url)";
    $stm = $pdo->prepare($sql);
    $i = 0;
    foreach($data as $line) {
        $entry = str_getcsv($line, ";");
        if(count($entry) != 4) {
            continue;
        }
        $stm->bindParam(':nom', $entry[0]);
        $stm->bindParam(':desc', $entry[1]);
        $stm->bindParam(':adresse', $entry[2]);
        $stm->bindParam(':url', $entry[3]);
        try {
            $stm->execute();
            $i++;
        } catch(Exception $e) {
            continue;
        }
    }
    echo $i;
}

function dbConnect()
{
    $dsn = "mysql:dbname=project;host=localhost";
    $username = "project";
    $password = "0000";
    $pdo = new PDO(
        $dsn, 
        $username, 
        $password, 
        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8")
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}