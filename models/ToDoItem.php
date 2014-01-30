<?php
/**
 * User: Ransom Roberson
 * Date: 1/29/14
 * Time: 4:38 PM
 */

/**
 * @Inject database
 */
class ToDoItem implements DatabaseModel {
    public $id = null;
    public $text;
    public $datetime;
    public $inProgress;
    public $uid;

    private $dbh;

    public function __CONSTRUCT($id = null) {
        if ($id != null) {
            $this->id = $id;
        }
    }

    public function delete() {
        if (self::verify()) {
            $delete = $this->dbh->prepare("DELETE FROM `tasks` WHERE `id` = ?");
            if ($delete->execute(array($this->id))) {
                return true;
            } else {
                return false;
            }
        } else {
            throw new Exception("No relational mapping from object to db");
        }
    }

    public function create() {
        if (self::verify()) {
            throw new Exception("ToDoItem already has mapping");
        }
        $insert = $this->dbh->prepare("INSERT INTO `tasks` (uid, datetime, text, in_progress) value (:uid, :datetime, :text, :in_progress)");

        $insert->bindParam(':uid', $this->uid);
        $insert->bindParam(':datetime', $this->datetime);
        $insert->bindParam(':text', $this->text);
        $insert->bindParam(':in_progress', $this->inProgress);

        $this->dbh->beginTransaction();
        $insert->execute();
        $id = $this->dbh->lastInsertId();
        $this->dbh->commit();
        return $id;
    }

    /**
     * Sends updated fields upstream and alters item with ID
     */
    public function synchronize() {

    }

    public function pull() {

    }

    public function verify() {
        $this->dbh = $this->database->getHandle();
        if ($this->id == null) {
            return false;
        }
        $select = $this->dbh->prepare('SELECT FROM `tasks` WEHRE `id` = `'.intval($this->id).'`');
        $select->execute();
        $count = $select->rowCount();
        return ($count == 1);
    }

    public function __toString() {
        return $this->text ." on ".$this->datetime;
    }
} 