<?php
    class CheckType {

        function checkBool($myString) {
            if( $myString == '0' || $myString == '1' || $myString == 'true' || $myString == 'false'){
                return true;
            }
            return false;
        }

        function checkInt($myString){
            $regex = preg_match('/^[0-9]*$/', $myString);
            if( $regex ){
                return true;
            }
            return false;
        }

        function checkFloat($myString){
            $regex = preg_match('/^-?(?:\d+|\d*\.\d+)$/', $myString);
            if( $regex ){
                return true;
            }
            return false;
        }

        function checkDecimal($myString){
            $regex = preg_match('/^[0-9]+(\.[0-9]{1,2})?$/', $myString);
            if ($regex){
                return "true";
            }
            return "false";
        }

        function checkDate($myString){
            // var_dump(validateDate('28/02/12', 'd/m/y')); // "yyyy-mm-dd" "dd-mm-yyyy" / "yyyy/mm/dd" "dd/mm/yyyy"
            $regex = preg_match('/^[0-3][0-9]-[0-1][0-9]-[0-9]{4}$/', $myString);
            if( $regex ){
                return true;
            }else{
                $regex_usaformat = preg_match('/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/', $myString);
                if( $regex_usaformat ){
                    return true;
                }else{
                    $regexslash = preg_match('/^[0-3][0-9]\/[0-1][0-9]\/[0-9]{4}$/', $myString);
                    if( $regexslash ){
                        return true;
                    }else{
                        $regexslash_usaformat = preg_match('/^[0-9]{4}\/[0-1][0-9]\/[0-3][0-9]$/', $myString);
                        if( $regexslash_usaformat ){
                            return true;
                        }else{
                            return false;
                        }
                    }
                }
            }
        }
    }
?>