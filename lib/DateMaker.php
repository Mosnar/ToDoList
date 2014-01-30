<?php
/**
 * User: Ransom Roberson
 * Date: 1/30/14
 * Time: 3:28 PM
 */
class DateMaker {
    /**
     * Return the current datetime for inserting into a "datetime" MySQL column. You may also supply the time as
     * a parameter.
     * @return bool|string
     */
    public function getDate($time = null) {
        if ($time == null) {
            $time = time();
        }
        $mysqldate = date("m/d/y g:i A", $time);
        $phpdate = strtotime($mysqldate);
        return date('Y-m-d H:i:s', $phpdate);
    }
}