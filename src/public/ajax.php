<?php 
require '../../vendor/autoload.php';
use \ForceUTF8\Encoding;

$action = $_REQUEST['action'];

switch($action) {
    case "upload" :
        uploadCsv();
        break;
    case "loadAddresses" :
        loadAddresses();
        break;
    case "loadAddress" :
        $id = (int) $_GET['id'];
        loadAddress($id);
        break;
    case "delete" :
       $id = (int) $_GET['id'];
       deleteAddress($id);
       break;
    case "save" :
        saveAddress();
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

function loadAddresses()
{
    $pdo = dbConnect();
    $sql = "SELECT *
            FROM coords 
            WHERE 1";
    $req = $pdo->query($sql);
    $result = $req->fetchAll();
    
    foreach($result as &$line) {
        $id = $line[0];
        $line[] = '<a href="#" data-id="' . $id . '" data-action="edit"><i class="glyphicon glyphicon-pencil"></i></a>' .
                  ' <a href="#" data-id="' . $id . '" data-action="delete"><i class="glyphicon glyphicon-remove-circle"></i></a>';
        array_shift($line);
    }
    
    $response = array('data' => $result);
    echo json_encode($response);
}

function loadAddress($id)
{
    $pdo = dbConnect();
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $sql = "SELECT *
            FROM coords
            WHERE coords_id = :id";
    $stm = $pdo->prepare($sql);
    $stm->bindParam(':id', $id);
    $req = $stm->execute();
    $result = $stm->fetch();
    echo json_encode($result);
}

function deleteAddress($id)
{
    $pdo = dbConnect();
    $sql = "DELETE FROM coords WHERE coords_id = ?";
    $stm = $pdo->prepare($sql);
    echo $stm->execute(array($id));
}

function saveAddress()
{
    $data['id'] = (int) $_POST['id'];
    $data['nom'] = (string) $_POST['nom'];
    $data['desc'] = (string) $_POST['description'];
    $data['adresse'] = (string) $_POST['adresse'];
    $data['url'] = (string) $_POST['url'];
     
    $pdo = dbConnect();
    
    if ($data['id'] === 0) {
        $sql = "INSERT INTO coords 
               (coords_nom, coords_desc, coords_adresse, coords_url)
               VALUES (:nom, :desc, :adresse, :url)";
        $stm = $pdo->prepare($sql);
        
        unset($data['id']);
        try {
            $stm->execute($data);
            echo 'saved';
        } catch(Exception $e) {
            echo $e->getMessage();
            exit;
        }
    } else {
        $sql = "UPDATE coords 
               SET coords_nom = :nom, 
                   coords_desc = :desc, 
                   coords_adresse = :adresse,
                   coords_url = :url
               WHERE coords_id = :id";
        $stm = $pdo->prepare($sql);
        $stm->execute($data);
    }
    
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
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_NUM);
    return $pdo;
}