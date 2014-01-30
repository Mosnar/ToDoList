<?php
/**
 * User: Ransom Roberson
 * Date: 1/30/14
 * Time: 2:42 PM
 */

class MD5IdentityHasher implements Hasher {
    /**
     * Hashes a string using the md5 algorithm
     * @param $string string to hash
     * @return string hash
     */
    public function hash($string) {
        return md5($string);
    }

    /**
     * Compares a string and a hash to see if their hashes are equal
     * @param $string
     * @param $hash
     * @return bool
     */
    public function compare($string, $hash) {
        return (self::hash($string) == $hash);
    }
} 