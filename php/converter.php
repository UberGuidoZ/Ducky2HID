<?php

class Converter {

    /**
     * Convert DuckyScript to P4wnP1 / JavaScript
     * 
     * @param $input            DuckyScript input
     * @param $newLine          Should a new line be inserted if there is an empty line in DuckyScript?
     * @param $cancelUnknown    Should it be aborted if an unknown type was specified?
     */
    public function convert($input, $newLine = true, $types, $cancelUnknown = false) {

        // Monitor execution time
        $executionStartTime = microtime(true);

        // Default result
        $result = [
            'error' => [
                'err' => 0,
                'msg' => ''
            ],
            'output' => [

            ],
            'time' => 0
        ];

        $currentLine = 0;
        foreach(explode("\n", $input) as $line) {

            // Count lines
            $currentLine++;
    
            $line = trim($line);

            // Insert newline
            if(strlen($line) === 0) {
                if($newLine === true) {
                    array_push($result['output'], '');
                }
                continue;
            }
    
            $arg = $line;
            $val = "";
    
            // Val
            if(\strpos($line, ' ') !== false) {
                $arg = explode(' ', $line)[0];
                $val = ltrim(substr($line, strpos($line, ' ')));
            }

            $foundType = false;
            $foundIndex = '';

            // Check types
            foreach($types as $typeKey => $typeVal) {
                $args = [];

                // Multiple types
                foreach(explode('|', $typeKey) as $splitted) {
                    array_push($args, $splitted);
                }

                if(in_array($arg, $args)) {
                    $foundIndex = $typeKey;
                    $foundType = true;
                    break;
                }
            }

            if($foundType === false) {

                if($cancelUnknown === true) {
                    $result['error']['err'] = 1;
                    $result['error']['msg'] = '[L: ' . $currentLine . ']: Type "' . $arg . '" not found!';

                    break; 
                }

                continue;
            }

            $settings = $types[$foundIndex];
            
            // Normal type
            $str = $settings;

            // Apply regex?
            if(is_array($settings)) {
                $str = $settings[0];

                // Check regex ?
                if(isset($settings['regex'])) {
                    $regex = $settings['regex'];

                    if(preg_match($regex, $val)) {
                        $result['error']['err'] = 1;
                        $result['error']['msg'] = '[L: ' . $currentLine . ']: Regex "' . $regex . '" failed for value "' . $val . '"!';

                        break;
                    }
                }

                // Execute function
                if(isset($settings['function'])) {
                    $funct_name = $settings['function'];

                    if(!is_callable(['Types', $funct_name])) {
                        $result['error']['err'] = 1;
                        $result['error']['msg'] = '[L: ' . $currentLine . ']: Function "' . $funct_name . '" not found! (Backend-Bug)';

                        break;
                    }

                    $funct_res = call_user_func(['Types', $funct_name], $arg, $val, $result['output']);

                    // String => ERROR / Continue
                    if(is_string($funct_res)) {
                        if($funct_res == 'OK') {
                            continue;
                        }

                        $result['error']['err'] = 1;
                        $result['error']['msg'] = '[L: ' . $currentLine . ']: ["' . $funct_name . '"]: ' . $funct_res;

                        break;
                    }

                    // Array => New result
                    if(is_array($funct_res)) {
                        $result['output'] = $funct_res;
                        continue;
                    }
                }
            }

            // Replace types
            $str = str_replace('{arg}', $arg, $str);
            $str = str_replace('{val}', $val, $str);

            $i = 0;
            foreach(explode('|', $foundIndex) as $splitted) {
                $i++;
                $str = str_replace('{arg|'.$i.'}', $splitted, $str);
            }

            if(strlen($val) > 0) {
                $str = str_replace('{space}', ' ', $str);
            } else {
                $str = str_replace('{space}', '', $str);
            }

            array_push($result['output'], $str);
        }

        // Execution time
        $result['time'] = (microtime(true) - $executionStartTime);

        return $result;
    }

}