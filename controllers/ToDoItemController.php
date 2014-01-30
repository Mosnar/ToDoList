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
    /**
     * Gets a ToDoItem object from the database based on id
     * @param $id
     */
    public function get($id)
    {

    }

    /**
     * Gets all ToDoItems for a particular user. If uid is null, get all
     */
    public function getAll($uid = null)
    {

    }

    /**
     * Modified the in_progress field for a task
     * @param $id Item ID
     * @param $uuid user ID, used for auth
     * @param $state 0 or 1 for progress state
     * @return boolean success
     */
    public function setProgressState($id, $uid, $state) {

    }
} 