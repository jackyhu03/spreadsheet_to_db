<?php
    require './changetype.php';

    $example = '[
        [
            {
                "value": "COLONNA 1",
                "type": "stringValue"
            },
            {
                "value": "COLONNA 2",
                "type": "stringValue"
            },
            {
                "value": "COLONNA 3",
                "type": "stringValue"
            }
        ],
        [
            {
                "value": true,
                "type": "boolValue"
            },
            {
                "value": "22\/05\/2020",
                "type": "DATE"
            },
            {
                "value": 14433.294444444444,
                "type": "numberValue"
            }
        ],
        [
            {
                "value": true,
                "type": "boolValue"
            },
            {
                "value": "22\/05\/2020",
                "type": "DATE"
            },
            {
                "value": "a3",
                "type": "stringValue"
            }
        ],
        [
            {
                "value": true,
                "type": "boolValue"
            },
            {
                "value": "22\/05\/2020",
                "type": "DATE"
            },
            {
                "value": "c5",
                "type": "stringValue"
            }
        ],
        [
            {
                "value": true,
                "type": "boolValue"
            },
            {
                "value": "22\/05\/2020",
                "type": "DATE"
            },
            {
                "value": "fewqf",
                "type": "stringValue"
            }
        ],
        [
            {
                "value": true,
                "type": "boolValue"
            },
            {
                "value": "22\/05\/2020",
                "type": "DATE"
            },
            {
                "value": "NULL",
                "type": "NULL"
            }
        ],
        [
            {
                "value": true,
                "type": "boolValue"
            },
            {
                "value": "22\/05\/2020",
                "type": "DATE"
            },
            {
                "value": "r32r",
                "type": "stringValue"
            }
        ],
        [
            {
                "value": true,
                "type": "boolValue"
            },
            {
                "value": "22\/05\/2020",
                "type": "DATE"
            },
            {
                "value": "3r",
                "type": "stringValue"
            }
        ],
        [
            {
                "value": "1",
                "type": "stringValue"
            },
            {
                "value": "22\/05\/2020",
                "type": "DATE"
            },
            {
                "value": "2432fewfc",
                "type": "stringValue"
            }
        ]
    ]';

    $myArray = json_decode($example, true);

    var_dump(get_arrayOfType($myArray));

    function get_arrayOfType($myArray){ // FUNCTION THAT RETURN AN ARRAY OF TYPE
        $arrayOfType = array();
        for($n = 0; $n < count($myArray[0]); $n++){
            $type = '';
            for($i = 1; $i < count($myArray); $i++){
                if($myArray[$i][$n]['type'] != "NULL"){
                    if($type == ''){
                        if($myArray[$i][$n]['type'] != 'stringValue'){
                            if($myArray[$i][$n]['type'] == 'numberValue'){
                                $type = get_numberValueType($myArray[$i][$n]['value']);
                            }else{
                                $type = $myArray[$i][$n]['type'];
                            }
                        }else{
                            $type = get_type($myArray[$i][$n]['value']);
                        }
                    }else{
                        if($type != $myArray[$i][$n]['type']){
                            if($type != get_type($myArray[$i][$n]['value'])){
                                if($myArray[$i][$n]['type'] == 'numberValue'){
                                    if($type != get_numberValueType($myArray[$i][$n]['type'])){
                                        if($type == 'intValue' && get_numberValueType($myArray[$i][$n]['type']) == 'floatValue'){
                                            $type = 'floatValue';
                                        }else if($type == 'floatValue' && get_numberValueType($myArray[$i][$n]['type']) == 'intValue'){
                                            $type = 'floatValue';
                                        }else{
                                            $type = 'stringValue';
                                            break;
                                        }
                                    }
                                }else{
                                    $type = 'stringValue';
                                    break;
                                }
                            }
                        }
                    }
                }
                // echo "<br> $i $type </br>";
            }
            $arrayOfType[] = $type;
        }
        return $arrayOfType;
    }

    function get_type($myString){
        if(CheckType::checkFloat($myString)){
            if(CheckType::checkInt($myString)){
                return "intValue";
            }else{
                return "floatValue";
            }
        }else{
            if(CheckType::checkDate($myString)){
                return "DATE";
            }else{
                if(CheckType::checkBool($myString)){
                    return "boolValue";
                }else{
                    return "stringValue";
                }
            }
        }
    }

    function get_numberValueType($myNumber){
        if(is_float($myNumber)){
            return "floatValue";
        }else{
            return "intValue";
        }
    }
?>