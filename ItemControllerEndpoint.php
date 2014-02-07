<?php
//header('Content-Type: application/json');
/**
 * User: Ransom Roberson
 * Date: 1/30/14
 * Time: 11:36 PM
 */

//header('Content-Type: application/json');
require_once("bootstrap.php");
// Interfaces
require_once("interfaces/DomainModel.php");
require_once("interfaces/Hasher.php");

// Core libraries
require_once("lib/DI.php");
require_once("lib/Database.php");
require_once("lib/MD5IdentityHasher.php");
require_once("lib/DateMaker.php");
// Exceptions

// Models
require_once("models/ToDoItem.php");

// Controllers
require_once("controllers/ToDoItemController.php");

// Dependency Injection Mapping
DI::mapClassAsSingleton("database", "Database");
DI::mapClass("hasher", "MD5IdentityHasher");
DI::mapClass("datemaker","DateMaker");

$itemController = DI::getInstanceOf("ToDoItemController");

$data = $_POST;
if (isset($data['action'])) {
    switch ($data['action']) {
        case "add":
            if (isset($data['text'])) {
                $text = htmlentities($data['text']);
                $inProgress = isset($data['inProgress']) ? $data['inProgress'] : 0;
                if($r_id = $itemController->addItem($text, $inProgress)) {
                    print(json_encode(array("success"=>1,"response"=>"Item added","id"=>$r_id)));
                }
            } else {
                print(json_encode(array("success"=>0,"response"=>"No text specified")));
            }
            break;
        case "remove":
            print("Remove");
            break;
        case "setProgress":
            break;
        case "list":
            $items = $itemController->getAll();
            print(json_encode($items));
            break;
        default:
            break;
    }

} else {
    print(json_encode(array("success"=>0,"response"=>"No action specified")));
}
?>