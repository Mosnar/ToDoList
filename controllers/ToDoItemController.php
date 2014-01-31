<?php
/**
 * User: Ransom Roberson
 * Date: 1/30/14
 * Time: 2:37 PM
 */

/**
 * @Inject database
 * @Inject hasher
 */
class ToDoItemController
{
    private $dbh;
    private $uid;
    public function __CONSTRUCT() {

    }

    /**
     * Gets a ToDoItem object from the database based on id
     * @param $id
     */
    public function get($id)
    {
        $tdi = DI::getInstanceOf("ToDoItem");
        $tdi->id = intval($id);
        if ($tdi->pull()) {
            return $tdi;
        } else {
            return null;
        }
    }

    /**
     * Gets all ToDoItems for a particular user. If uid is null, get all
     */
    public function getAll($uid = null)
    {
        $this->dbh = $this->database->getHandle();
        if ($uid == null)
        {
            $select = $this->dbh->prepare("select * from tasks");
        } else {
            $select = $this->dbh->prepare("select * from tasks where uid = :uid");
        }
        $select->execute(array(':uid' => $uid));
        $select->setFetchMode(PDO::FETCH_ASSOC);

        $results = array();

        while ($row = $select->fetch()) {
            $tdi = DI::getInstanceOf("ToDoItem");

            $tdi->id = $row['id'];
            $tdi->datetime = $row['datetime'];
            $tdi->text = $row['text'];
            $tdi->inProgress = $row['in_progress'];
            $tdi->uid = $row['uid'];
            $results[] = $tdi;
        }
        return $results;
    }

    /**
     * Adds a ToDoItem to the database
     * @param $text
     * @return Item object added or null if failure
     */
    public function addItem($text) {
        $this->uid = $this->hasher->hash($_SERVER['REMOTE_ADDR']);
        $tdi = DI::getInstanceOf("ToDoItem");
        $tdi->datetime = $this->datemaker->getDate();
        $tdi->text = $text;
        $tdi->inProgress = 0;
        $tdi->uid = $this->uid;
        if($id = $tdi->create()) {
            $tdi->id = $id;
            return $tdi;
        } else {
            return null;
        }
    }

    /**
     * Modified the in_progress field for a task
     * @param $id Item ID
     * @param $uuid user ID, used for auth
     * @param $state 0 or 1 for progress state
     * @return boolean success
     */
    public function setProgressState($id, $uid, $state) {
        $tdi = DI::getInstanceOf("ToDoItem");
        $tdi->id = intval($id);
        if($tdi->pull()) {
            if($tdi->uid == $uid) {
                $tdi->inProgress = intval($state);
                return $tdi->synchronize();
            } else {
                // Bad auth
                return false;
            }
        } else {
            // No such item
            return false;
        }
    }
} 