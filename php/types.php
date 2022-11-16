<?php
class Types {

    /**
     * // TODO: Add Description
     */
    private $types = [
        'REM' => '// {val}',
        
        'DELAY' => [
            'delay({val});',
            'regex' => '/[^0-9]/'
        ],

        'STRING' => [
            'type("{val}");'
        ],

        "WINDOWS|GUI" => [
            'press("LEFT_GUI{space}{val}");'
        ],

        'MENU|APP|SHIFT|ALT|CONTROL|CTRL' => [
            'press("{arg}{space}{val}");',
            'regex' => '/[^A-Za-z]/'
        ],

        'DOWNARROW|DOWN|DOWN_ARROW' => [
            'press("DOWN_ARROW");'
        ],

        'LEFTARROW|LEFT|LEFT_ARROW' => [
            'press("LEFT_ARROW");'
        ],

        'RIGHTARROW|RIGHT|RIGHT_ARROW' => [
            'press("RIGHT_ARROW");'
        ],

        'UPARROW|UP|UP_ARROW' => [
            'press("UP_ARROW");'
        ],

        'ENTER' => [
            'press("{val}");'
        ],
        
        'REPEAT' => [
            '',
            'function' => 'func_repeat',
            'regex' => '/[^0-9]/'
        ]
    ];

    /**
     * For the 'REPEAT' - command
     */
    public static function func_repeat($arg, $val, &$arr) {

        if(count($arr) === 0) {
            return 'No previous commands!';
        }

        $index = count($arr) - 1;
        $lastCommand = $arr[$index];
        
        unset($arr[$index]);
        array_push($arr, "for (var i = 0; i < $val; i++) {\n  $lastCommand\n}");

        return $arr;
    }

    public function getTypes() {
        return $this->types;
    }
}