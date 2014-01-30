<?php
/**
 * User: Ransom Roberson
 * Date: 1/29/14
 * Time: 10:26 PM
 */

interface DatabaseModel {
    /**
     * Deletes the currently selected object from the database
     * @return bool success
     */
    public function delete();

    /**
     * Creates a new database entry from the object.
     * @returns id of created row
     */
    public function create();

    /**
     * Pushes up the changes to the object to the database
     */
    public function synchronize();

    /**
     * Pulls the object data from the database based on currently set ID
     */
    public function pull();

    /**
     * Verifies the integrity of the object. If the object has a mapping in the db, return true. Else, false
     * This function will also create/recreate the database handle
     * @return boolean result
     */
    public function verify();
} 