<?php

/*
 * Sorry to the one who has to read this code massacre.
 * I didn't want to use a real template system like Twig 
 * because I wanted to keep the converter as lightweight as possible
 * and I'm still quite new in the web development area.
 * 
 * A cat as an excuse:
 * 
 *    /\     /\
 *  {  `---'  }
 *  {  O   O  }  
 * ~|~   V   ~|~  
 *   \  \|/  /   
 *    `-----'__
 *    /     \  `^\_
 *   {       }\ |\_\_   W
 *   |  \_/  |/ /  \_\_( )
 *    \__/  /(_E     \__/
 *      (  /
 *       MM
 */

require 'php/converter.php';
require 'php/types.php';

$converter = new Converter();
$types = new Types();

$input = isset($_POST['input']) ? $_POST['input'] : false;
$result = [
    'error' => [
        'err' => 0,
        'msg' => ''
    ],
    'time' => 0
];

$output = '';

if($input !== false) {
    $result = $converter->convert($input, false, $types->getTypes(), false);
    
    if(isset($_POST['layout'])) {
        $output .= '// Layout: ' . $_POST['layout']. "\n" . 'layout("'.$_POST['layout'].'");' . "\n\n";
    }

    foreach($result['output'] as $o) {
        $output .= "$o\n";
    }
}

$page_content = file_get_contents('page/index.phtml');


$vars = [
    'input' => $input,
    'output' => $output,
    'input' => isset($_POST['input']) ? $_POST['input'] : '',
    'error_msg' => $result['error']['err'] === 1 ? $result['error']['msg'] : '',
    'execution_time' => $result['time'],
    'layout' => isset($_POST['layout']) ? $_POST['layout'] : ''
];

$page_lines = explode("\n", $page_content);
$ignore = false;
foreach($page_lines as $line) {
    foreach($vars as $key => $val) {
        $line = str_replace("{{ $key }}", $val, $line);
    }

    $t = trim($line);
    if($t == '{{ if_output_convert }}') {
        if($input === false) {
            $ignore = true;
        }
        continue;
    }

    if($t == '{{ end_if_output_convert }}') {
        $ignore = false;
        continue;
    }

    if($t == '{{ if_error }}') {
        if($result['error']['err'] === 0) {
            $ignore = true;
        }
        continue;
    }

    if($t == '{{ end_if_error }}') {
        $ignore = false;
        continue;
    }

    if($ignore) {
        continue;
    }

    echo "$line\n";
}