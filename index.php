<?php
/**
 * User: Ransom Roberson
 * Date: 1/29/14
 * Time: 10:33 PM
 */
// Environment setup
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

$tdi = DI::getInstanceOf("ToDoItem");

// Static libraries
$hasher = new MD5IdentityHasher();
$date = new DateMaker();

$tdi->datetime = $date->getDate();
$tdi->text = "Hi, this is a test!";
$tdi->uid = $hasher->hash($ip);
$tdi->inProgress = 0;
//print($tdi->create());