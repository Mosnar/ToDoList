<?php
/**
 * User: Ransom Roberson
 * Date: 2/7/14
 * Time: 3:32 PM
 */

/**
 * Class DomainModel
 * This purpose of this class is to be extended and used as a framework for interacting with the database and easily
 * creating new models. This abstract class will manage all database transactions that you might need to perform on an
 * individual model entity.
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
    protected function setup($dbh, $table, &$columns) {
        self::setDatabaseHandle($dbh);
        self::setTableName($table);
        self::setColumns($columns);
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
     * Pushes the changes to the object to the database
     */

    public function synchronize() {
        // If we exist in the database, continue. Otherwise, return false
        if (self::verify()) {

            // Holds the names of the columns. Formatted for PDO prepared statement
            $outCols = array();
            // Holds the values of the columns. Formatted for PDO prepared statement (:val, :val2)
            $outVals = array();
            // Holds the prepared inputs that are formatted (val=:name)
            $outPrepared = array();
            // Formatted prepared statement string (val1=:val1,val2=:val2,etc)
            $outPrepString = "";

            // Format the arrays
            foreach ($this->columns as $name => $value) {
                array_push($outCols, $name);
                array_push($outVals, ":".$name);
            }

            // Create the string of values and pdo IDs
            for($i = 0; $i < count($outCols); $i++) {
                $outPrepared[$i] = "{$outCols[$i]}={$outVals[$i]}";
            }
            $outPrepString = implode(",",$outPrepared);

            $query = "UPDATE {$this->tableName} SET {$outPrepString} where id = :id";

            // Prepare the query
            $update = $this->dbh->prepare($query);

            // Bind proper values to PDO vars
            for($i = 0; $i < count($this->columns); $i++) {
                //print("Binding ".$outVals[$i]." to ". $outCols[$i]." ({$this->columns[$outCols[$i]]}) <br />");
                $update->bindParam($outVals[$i], $this->columns[$outCols[$i]]);
            }

            $this->dbh->beginTransaction();
            $update->execute();

            // If everything went smoothly, commit. Else, rollback and fail
            if ($update->rowCount() == 1) {
                $this->dbh->commit();
                return true;
            } else {
                $this->dbh->rollBack();
                return false;
            }
        } else {
            return false;
        }
    }


    /**
     * Returns the columns and values as array
     * @return columns + vals
     */
    public function toArray() {
        return $this->columns;
    }

    /**
     * Attempts to set the object fields to match those set in it's database-mapped counterpart
     * @return bool
     */
    public function pull() {
        //var_dump(get_object_vars($this));
        if (!self::verify()) {
            return false;
        }
        $select = $this->dbh->prepare("select * from {$this->tableName} where id = :id");
        $select->execute(array(':id' => $this->columns['id']));
        $select->setFetchMode(PDO::FETCH_ASSOC);

        while ($row = $select->fetch()) {
            $this->columns = array_replace($this->columns, $row);
        }
        return true;
    }



    /**
     * Creates a new database item based on the fields of this object. Sets the current object ID to the created ID
     * @return id
     * @throws Exception if the item already has an ID
     */
    public function create()
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
        if (!isset($this->columns['id']) || $this->columns['id'] == null) {
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
        $this->columns[$key] = $value;
    }

    /**
     * Gets a key value from the object.
     * @param $key
     * @return value or null if key doesn't exist
     */
    public function getVal($key) {
        if(isset($this->columns[$key])) {
            return $this->columns[$key];
        } else {
            return null;
        }
    }
} 