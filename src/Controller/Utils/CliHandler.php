<?php

namespace App\Controller\Utils;

/**
 * This is non object based class
 * It should not be extended and dependent on any vendor package as it's used directly in cli from composer
 * Class CliHandler
 * @package App\Controller\Utils
 */
class CliHandler{

    static $foreground_colors = array();

    static $background_colors = array();

    static $text_yellow = "yellow";

    static $text_red = "red";

    static $text_blue = "blue";

    static $failure_mark = "✗";

    static $success_mark = "✓";

    public function initialize(){
        self::defineShellColors();
    }
    
    private static function defineShellColors(){
        // Set up shell colors
        self::$foreground_colors['black'] = '0;30';
        self::$foreground_colors['dark_gray'] = '1;30';
        self::$foreground_colors['blue'] = '0;34';
        self::$foreground_colors['light_blue'] = '1;34';
        self::$foreground_colors['green'] = '0;32';
        self::$foreground_colors['light_green'] = '1;32';
        self::$foreground_colors['cyan'] = '0;36';
        self::$foreground_colors['light_cyan'] = '1;36';
        self::$foreground_colors['red'] = '0;31';
        self::$foreground_colors['light_red'] = '1;31';
        self::$foreground_colors['purple'] = '0;35';
        self::$foreground_colors['light_purple'] = '1;35';
        self::$foreground_colors['brown'] = '0;33';
        self::$foreground_colors['yellow'] = '1;33';
        self::$foreground_colors['light_gray'] = '0;37';
        self::$foreground_colors['white'] = '1;37';

        self::$background_colors['black'] = '40';
        self::$background_colors['red'] = '41';
        self::$background_colors['green'] = '42';
        self::$background_colors['yellow'] = '43';
        self::$background_colors['blue'] = '44';
        self::$background_colors['magenta'] = '45';
        self::$background_colors['cyan'] = '46';
        self::$background_colors['light_gray'] = '47';
    }

    /**
     * Handles coloring string in cli
     * @param string $string
     * @param string|null $foreground_color
     * @param string|null $background_color
     * @return string
     */
    public static function getColoredString(string $string, ?string $foreground_color = null, ?string $background_color = null) {
        $colored_string = "";

        // Check if given foreground color found
        if (isset(self::$foreground_colors[$foreground_color])) {
            $colored_string .= "\033[" . self::$foreground_colors[$foreground_color] . "m";
        }
        // Check if given background color found
        if (isset(self::$background_colors[$background_color])) {
            $colored_string .= "\033[" . self::$background_colors[$background_color] . "m";
        }

        // Add string and end coloring
        $colored_string .=  $string . "\033[0m";

        return $colored_string;
    }

    /**
     * Returns colored string for cli
     * @param string $string
     * @param bool $new_line
     */
    public static function errorText(string $string, bool $new_line = true){

        if($new_line){
            echo PHP_EOL;
        }

        echo self::getColoredString($string, self::$text_red);

        if($new_line){
            echo PHP_EOL;
        }
    }

    /**
     * Returns colored string for cli
     * @param string $string
     * @param bool $new_line
     */
    public static function infoText(string $string, bool $new_line = true){

        if($new_line){
            echo PHP_EOL;
        }

        echo self::getColoredString($string, self::$text_yellow);

        if($new_line){
            echo PHP_EOL;
        }
    }

    /**
     * Displays standard string
     * @param string $string
     * @param bool $new_line
     */
    public static function text(string $string, bool $new_line = true){

        echo $string;

        if($new_line){
            echo PHP_EOL;
        }
    }

    public static function newLine(){
        echo PHP_EOL;
    }

    /**
     * This function returns line separator with either default settings or customized
     * @param int $line_size
     * @param string $symbol
     * @param string|null $color
     */
    public static function lineSeparator(int $line_size = 30, string $symbol = '-', ?string $color = null)
    {
        $line = '';

        for($x = 0; $x <= $line_size; $x++){
            $line .= $symbol;
        }

        echo  PHP_EOL . self::getColoredString($line, $color) . PHP_EOL;
    }

    /**
     * This function returns '✓' character
     * @return string
     */
    public static function successMark(){
        return self::getColoredString(self::$success_mark);
    }

    /**
     * This function returns '✗' character
     * @return string
     */
    public static function failureMark(){
        return self::getColoredString(self::$failure_mark, self::$text_red);
    }

    /**
     * This method will get a single line user input
     * @param $message
     * @return string
     */
    public static function getUserInput($message){
        echo "{$message}: ";
        $line = readline();
        return $line;
    }

    /**
     * Will display list of choices to select from, if user will select incorrect one then he will be asked again
     * until existing option will be provided
     * @param array $choices
     * @param string $displayed_string
     * @return string
     */
    public static function choices(array $choices, string $displayed_string): string {

        $selected_option_index = null;

        echo PHP_EOL;
            echo $displayed_string;
        echo PHP_EOL;

        foreach($choices as $index => $choice ){
            self::infoText("[{$index}] ", false);
            echo $choice;
            echo PHP_EOL;
        }

        echo PHP_EOL;
            while( !array_key_exists($selected_option_index, $choices) ){
                echo "Select correct option: ";
                $selected_option_index = readline();
            }

        $selected_option = $choices[$selected_option_index];

        echo PHP_EOL;

        return $selected_option;
    }
}
