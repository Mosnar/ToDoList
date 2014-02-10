<?php
/**
 * User: Ransom Roberson
 * Date: 2/7/14
 * Time: 3:32 PM
 * Description: 
 */

/**
 * Class DomainModel
 */
abstract class DomainModel {
    private $tableName;
    private $dbh;
    private $columns = array();

    /**
     * This should be implemented in the object class to run the DomainModel's abstract "setup" method.
     * This must be self-implemented when using dependency injection since DI does not become active in
     * the classes CONSTRUCT method.
     */
    protected abstract function initialize();

    /**
     * You should always implement some form of toString()
     * @return A meaningful string representation of the object
     */
    protected abstract function __toString();

    /**
     * Set the database PDO handle. This should be called from inside the model object extending this class.
     * This is important.
     * @param $databaseHandle
     */
    protected function setDatabaseHandle($databaseHandle) {
        $this->dbh = $databaseHandle;
    }

    /**
     * Set the name of the table that will be queried. This is important.
     * @param $tableName
     */
    protected function setTableName($tableName) {
        $this->tableName = $tableName;
    }

    /**
     * Set the fields and columns that will be used with queries. Just send an array like:
     * array('id','name','password');
     * @param $columns
     */
    protected function setColumns($columns) {
        $this->columns = $columns;
    }

    /**
     * Shortcut for setting up a domain model
     * @param $dbh database handle
     * @param $table table name
     */
    protected function setup($dbh, $table) {
        self::setDatabaseHandle($dbh);
        self::setTableName($table);
    }

    /**
     * Deletes the currently selected object from the database
     * @return bool success
     */
    public function delete()
    {
        if (self::verify()) {
            $delete = $this->dbh->prepare("DELETE FROM {$this->tableName} WHERE id = :id");
            if ($delete->execute(array(':id' => $this->id))) {
                return true;
            } else {
                return false;
            }
        } else {
            throw new Exception("No relational mapping from object to db");
        }
    }

    /**
     * Pushes up the changes to the object to the database
     */
    public abstract function synchronize();

    /**
     * Attempts to set the object fields to match those set in it's database-mapped counterpart
     * @return bool
     */
    public function pull() {
        if (!self::verify()) {
            return false;
        }
        //TODO: Store this prepared statement somewhere and use with verify()
        $select = $this->dbh->prepare("select * from {$this->tableName} where id = :id");
        $select->execute(array(':id' => $this->id));
        $select->setFetchMode(PDO::FETCH_ASSOC);

        while ($row = $select->fetch()) {
            print_r($row);
            /*

            $this->datetime = $row['datetime'];
            $this->text = $row['text'];
            $this->inProgress = $row['in_progress'];
            $this->uid = $row['uid'];
            */
        }
        return true;
    }


    /**
     * Creates a new database item based on the fields of this object
     * @return id
     * @throws Exception
     */
    public function create($force = false)
    {
        // Check if an id is set and it is in the database.
        if (self::verify()) {
            throw new Exception("Item already has mapping");
        }

        $outCols = array();
        $outVals = array();

        foreach ($this->columns as $name) {
            array_push($outCols, $name);
            array_push($outVals, ":$name");
        }
        $sOutVals = implode(', ', $outVals);
        $sOutCols = implode(', ', $outCols);

        $insert = $this->dbh->prepare("INSERT INTO {$this->tableName} ({$$sOutCols}) value ({$$sOutVals})");
        for($i = 0; $i < count($this->columns); $i++) {
            $insert->bindParam($outVals[$i], $this->columns[$i]);
        }

        $this->dbh->beginTransaction();
        $insert->execute();
        $id = $this->dbh->lastInsertId();
        $this->dbh->commit();
        return $id;
    }

    /**
     * Verifies the integrity of the object. If the object has a mapping in the db, return true. Else, false
     * This function will also create/recreate the database handle
     * @return boolean result
     */
    public function verify() {
        if ($this->id == null) {
            return false;
        }

        $select = $this->dbh->prepare("select id from {$this->tableName} where id = :id");
        $select->execute(array(':id' => $this->id));

        $count = $select->rowCount();
        return ($count == 1);
    }
} 