<?php
/**
 * File Core\Password.php
 *
 * @category Core
 * @package  Netoverconsulting
 * @author   Loïc Dandoy <ldandoy@overconsulting.net>
 * @license  GNU
 * @link     http://overconsulting.net
 */

namespace Core;

define('VALID_PASSWORD_LENGTH', 0);
define('VALID_PASSWORD_LOWERCASE', 1);
define('VALID_PASSWORD_UPPERCASE', 2);
define('VALID_PASSWORD_DIGIT', 3);
define('VALID_PASSWORD_SPECIAL', 4);

/**
 * Class to crypt password
 *
 * @category System
 * @package  Netoverconsulting
 * @author   Loïc Dandoy <ldandoy@overconsulting.net>
 * @license  GNU 
 * @link     http://overconsulting.net
 */
class Password
{
    /**
    * Generate a password
    *
    * @return string
    */
    public static function generatePassword()
    {
        $minuscules = 'abcdefghijklmopqrstuvwxyz';
        $majuscules = 'ABCDEFGHIJKLMOPQRSTUVWXYZ';
        $chiffres = '0123456789';
        $specialChars = '!"#$%&\'()*+,-./:;<=>?@[\]^_`{|}~';

        $allChars = $minuscules.$majuscules.$chiffres;//.$specialChars;
        $len = strlen($allChars);

        $hasMinuscule = false;
        $hasMajuscule = false;
        $hasChiffre = false;
        $hasSpecialChar = false;

        $password = '';
        for ($i = 0; $i < 8; $i++) {
            $c = $allChars[rand(0, $len - 1)];
            $password .= $c;

            if ($hasMinuscule === false && strpos($minuscules, $c) !== false) {
                $hasMinuscule = true;
            }

            if ($hasMajuscule === false && strpos($majuscules, $c) !== false) {
                $hasMajuscule = true;
            }

            if ($hasChiffre === false && strpos($chiffres, $c) !== false) {
                $hasChiffre = true;
            }

            if ($hasSpecialChar === false && strpos($specialChars, $c) !== false) {
                $hasSpecialChar = true;
            }
        }

        if ($hasMinuscule === false) {
            $password .= $minuscules[rand(0, strlen($minuscules) - 1)];
        }

        if ($hasMajuscule === false) {
            $password .= $majuscules[rand(0, strlen($majuscules) - 1)];
        }

        if ($hasChiffre === false) {
            $password .= $chiffres[rand(0, strlen($chiffres) - 1)];
        }

        // if ($hasSpecialChar === false) {
        //  $password .= $specialChars[rand(0, strlen($specialChars) - 1)];
        // }

        return $password;
    }

    /**
    * Test if password is valid
    *
    * @param string $password
    * @param mixed $validTypes
    *
    * @return bool
    */
    public static function validPassword($password, $validTypes = array(VALID_PASSWORD_LENGTH, VALID_PASSWORD_LOWERCASE/*, VALID_PASSWORD_UPPERCASE*/, VALID_PASSWORD_DIGIT/*, VALID_PASSWORD_SPECIAL*/))
    {
        $minuscules = 'abcdefghijklmopqrstuvwxyz';
        $majuscules = 'ABCDEFGHIJKLMOPQRSTUVWXYZ';
        $chiffres = '0123456789';
        $specialChars = '!"#$%&\'()*+,-./:;<=>?@[\]^_`{|}~';

        $lengthOk = in_array(VALID_PASSWORD_LENGTH, $validTypes) && strlen($password) >= 8 && strlen($password) <= 32;

        $hasMinuscule = false;
        if (in_array(VALID_PASSWORD_LOWERCASE, $validTypes)) {
            for ($i = 0; $i < strlen($password); $i++) {
                if (strpos($minuscules, $password[$i]) !== false) {
                    $hasMinuscule = true;
                    break;
                }
            }
        }

        $hasMajuscule = false;
        if (in_array(VALID_PASSWORD_UPPERCASE, $validTypes)) {
            for ($i = 0; $i < strlen($password); $i++) {
                if (strpos($majuscules, $password[$i]) !== false) {
                    $hasMajuscule = true;
                    break;
                }
            }
        }

        $hasChiffre = false;
        if (in_array(VALID_PASSWORD_DIGIT, $validTypes)) {
            for ($i = 0; $i < strlen($password); $i++) {
                if (strpos($chiffres, $password[$i]) !== false) {
                    $hasChiffre = true;
                    break;
                }
            }
        }

        $hasSpecialChar = false;
        if (in_array(VALID_PASSWORD_SPECIAL, $validTypes)) {
            for ($i = 0; $i < strlen($password); $i++) {
                if (strpos($specialChars, $password[$i]) !== false) {
                    $hasSpecialChar = true;
                    break;
                }
            }
        }

        return
            ($lengthOk || in_array(VALID_PASSWORD_LENGTH, $validTypes) === false) &&
            ($hasMinuscule || in_array(VALID_PASSWORD_LOWERCASE, $validTypes) === false) &&
            ($hasMajuscule || in_array(VALID_PASSWORD_UPPERCASE, $validTypes) === false) &&
            ($hasChiffre || in_array(VALID_PASSWORD_DIGIT, $validTypes) === false) &&
            ($hasSpecialChar || in_array(VALID_PASSWORD_SPECIAL, $validTypes) === false);
    }

    /**
    * Generate token
    *
    * @return string
    */
    public static function generateToken($length = 30)
    {
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charsLen = strlen($chars);

        $token = '';
        for ($i = 0; $i < $length; $i++) {
            $token .= $chars[mt_rand(0, $charsLen - 1)];
        }
        
        return $token;
    }

    /**
     * Crypt a string
     *
     * @param string $str The string to crypt
     *
     * @return string
     */
    public static function crypt($str)
    {    	
        // $salt = defined('SALT') ? SALT : '';
        // return crypt($str, $salt);
        return password_hash($str, PASSWORD_DEFAULT);
    }

    /**
     * Check if an uncrypted string is equal to a crypted one
     *
     * @param string $str The string to check
     * @param string $cryptedStr The crypted string
     *
     * @return bool
     */
    public static function check($str, $cryptedStr)
    {
        // $salt = defined('SALT') ? SALT : '';
        // return crypt($str, $salt) == $cryptedStr;
        return password_verify($str, $cryptedStr);
    }
}
