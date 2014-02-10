<?php
/**
 * User: Ransom Roberson
 * Date: 1/29/14
 * Time: 4:38 PM
 */

/**
 * @Inject database
 */
class ToDoItem extends DatabaseModel
{
    public $id = null;
    public $text;
    public $datetime;
    public $inProgress;
    public $uid;

    private $dbh;

    public function __CONSTRUCT($id = null)
    {
        if ($id != null) {
            $this->id = $id;
        }
    }


    /**
     * Sends updated fields upstream and alters item with ID
     */
    public function synchronize()
    {
        if (self::verify()) {
            $sql = "UPDATE tasks SET title=?, author=? WHERE id=?";
            $update = $this->dbh->prepare("UPDATE tasks SET uid=:uid, text=:text, datetime=:datetime, in_progress=:in_progress where id = :id");
            $this->dbh->beginTransaction();
            $update->execute(array(
                    ':id' => $this->id,
                    ':uid' => $this->uid,
                    ':datetime' => $this->datetime,
                    ':in_progress' => $this->inProgress,
                    ':text' => $this->text
                )
            );
            if ($update->rowCount() <= 1) {
                $this->dbh->commit();
                return true;
            } else {
                $this->dbh->rollBack();
                return false;
            }
        }
    }

    /**
     * Prints out a human readable string regardless of synchronization state
     * @return string
     */
    public function __toString()
    {
        return $this->text . " on " . $this->datetime;
    }
} 