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
    public abstract function __toString();

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
    protected function setColumns(&$columns) {
        // Set the columns field to the location of the columns in the extending class. This allows for updates to be
        // instantly reflected.
        $this->columns =& $columns;
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
            if ($delete->execute(array(':id' => $this->columns['id']))) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Pushes up the changes to the object to the database
     */
    /*
    public function synchronize() {
        if (self::verify()) {


            $update = $this->dbh->prepare("UPDATE {$this->tableName} SET uid=:uid, text=:text, datetime=:datetime, in_progress=:in_progress where id = :id");
            $this->dbh->beginTransaction();
            $update->execute(array(
                    ':id' => $this->columns['id'],
                    ':uid' => $this->columns['uid'],
                    ':datetime' => $this->columns['datetime'],
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
    */

    /**
     * Attempts to set the object fields to match those set in it's database-mapped counterpart
     * @return bool
     */
    /*
    public function pull() {
        if (!self::verify()) {
            return false;
        }
        //TODO: Store this prepared statement somewhere and use with verify()
        $select = $this->dbh->prepare("select * from {$this->tableName} where id = :id");
        $select->execute(array(':id' => $this->columns['id']));
        $select->setFetchMode(PDO::FETCH_ASSOC);

        while ($row = $select->fetch()) {
            print_r($row);


            $this->datetime = $row['datetime'];
            $this->text = $row['text'];
            $this->inProgress = $row['in_progress'];
            $this->uid = $row['uid'];

        }
        return true;
    }
    */


    /**
     * Creates a new database item based on the fields of this object. Sets the current object ID to the created ID
     * @return id
     * @throws Exception if the item already has an ID
     */
    public function create($force = false)
    {
        // Check if an id is set and it is in the database.
        if (self::verify()) {
            throw new Exception("Item already has mapping");
        }

        // Holds the names of the columns. Formatted for PDO prepared statement
        $outCols = array();
        // Holds the values of the columns. Formatted for PDO prepared statement (:val, :val2)
        $outVals = array();

        // Format the arrays
        foreach ($this->columns as $name => $value) {
            //print("Working on $name - $value <br />");
            array_push($outCols, $name);
            array_push($outVals, ":".$name);
        }

        $sOutVals = implode(', ', $outVals);
        $sOutCols = implode(', ', $outCols);
        $query = "INSERT INTO {$this->tableName} ({$sOutCols}) value ({$sOutVals})";

        // Prepare the query
        $insert = $this->dbh->prepare($query);

        // Bind proper values to PDO vars
        for($i = 0; $i < count($this->columns); $i++) {
            //print("Binding ".$outVals[$i]." to ". $outCols[$i]." ({$this->columns[$outCols[$i]]})");
            $insert->bindParam($outVals[$i], $this->columns[$outCols[$i]]);
        }

        // Perform the transaction and return the id generated.
        $this->dbh->beginTransaction();
        $insert->execute();
        $id = $this->dbh->lastInsertId();
        $this->columns['id'] = $id;
        $this->dbh->commit();
        return $id;
    }

    /**
     * Verifies the integrity of the object. If the object has a mapping in the db, return true. Else, false
     * This function will also create/recreate the database handle
     * @return boolean result
     */
    public function verify() {
        if ($this->columns['id'] == null) {
            return false;
        }

        $select = $this->dbh->prepare("select id from {$this->tableName} where id = :id");
        $select->execute(array(':id' => $this->columns['id']));

        $count = $select->rowCount();
        return ($count == 1);
    }

    /**
     * Sets a column key value. This is just a cleaner way of $domain->cols[]
     * @param $key
     * @param $value
     */
    public function setVal($key, $value) {

    }

    /**
     * Gets a key value from the object.
     * @param $key
     */
    public function getVal($key) {

    }
} 