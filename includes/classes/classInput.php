<?php
// TODO: Update copyright on this code
/**
 * Input Cleaner
 *
 * @package Xinsto Desk
 * @version 2.0.0
 * @link 
 * @license GPLv2
 */

 /**
  * @class Input_Cleaner
  */
class Input_Cleaner {
	/**
	 * @var array|string
	 */
    public function sanitize($input) {
        if (is_array($input)) {
            foreach ($input as &$value) {
                $value = $this->sanitize($value);
            }
            unset($value); // unset reference to the last element
        } else {
            $input = $this->array_stripslashes($input);
            $input = trim($input);
            $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        }
        return $input;
    }

    private function array_stripslashes(&$input) {
        if (is_array($input)) {
            foreach ($input as &$value) {
                $this->array_stripslashes($value);
            }
            unset($value); // unset reference to the last element
        } else {
            $input = stripslashes($input);
        }
        return $input;
    }
}

?>