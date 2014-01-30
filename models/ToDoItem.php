<?php
/**
 * User: Ransom Roberson
 * Date: 1/29/14
 * Time: 4:38 PM
 */

/**
 * @Inject database
 */
class ToDoItem implements DatabaseModel
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
     * Deletes the current item if it exists in the database
     * @return bool
     * @throws Exception if no relational mapping exists to database
     */
    public function delete()
    {
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

    /**
     * Creates a new database item based on the fields of this object
     * @return id
     * @throws Exception
     */
    public function create()
    {
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
     * Attempts to set the object fields to match those set in it's database-mapped counterpart
     * @return bool
     */
    public function pull()
    {
        if (!self::verify()) {
            return false;
        }
        //TODO: Store this prepared statement somewhere and use with verify()
        $select = $this->dbh->prepare("select * from tasks where id = :id");
        $select->execute(array(':id' => $this->id));
        $select->setFetchMode(PDO::FETCH_ASSOC);

        while ($row = $select->fetch()) {
            $this->datetime = $row['datetime'];
            $this->text = $row['text'];
            $this->inProgress = $row['in_progress'];
            $this->uid = $row['uid'];
        }
        return true;
    }

    /**
     * Returns true of an item matching the id of the object exists in the database
     * @return bool
     */
    public function verify()
    {
        $this->dbh = $this->database->getHandle();
        if ($this->id == null) {
            return false;
        }
        $select = $this->dbh->prepare("select id from tasks where id = :id");
        $select->execute(array(':id' => $this->id));

        $count = $select->rowCount();
        return ($count == 1);
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