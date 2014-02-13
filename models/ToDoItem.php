<?php
/**
 * User: Ransom Roberson
 * Date: 1/29/14
 * Time: 4:38 PM
 */

/**
 * @Inject database
 */
class ToDoItem extends DomainModel
{
    public $cols = array();
    private $dbh;

    public function __CONSTRUCT($id = null)
    {
        $this->cols['id'] = null;
        if ($id != null) {
            $this->cols['id'] = $id;
        }
        $this->cols['text'] = null;
        $this->cols['datetime'] = null;
        $this->cols['in_progress'] = null;
        $this->cols['uid'] = null;
    }

    public function initialize() {
        $this->dbh = $this->database->getHandle();
        self::setup($this->dbh, 'tasks');
        self::setColumns($this->cols);
    }
    /**
     * Prints out a human readable string regardless of synchronization state
     * @return string
     */
    public function __toString()
    {
        $text = is_null($this->cols['text']) ? "null" : $this->cols['text'];
        $datetime = is_null($this->cols['datetime']) ? "null" : $this->cols['datetime'];
        return "$text on $datetime";
    }
} 