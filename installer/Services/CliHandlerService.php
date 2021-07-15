<?php

namespace Installer\Controller\Utils;

/**
 * This is non object based class
 * It should not be extended and dependent on any vendor package as it's used directly in cli from composer
 * Class CliHandler
 * @package App\Controller\Utils
 */
class CliHandlerService {

    static $foregroundColors = array();

    static $backgroundColors = array();

    static $textYellow = "yellow";

    static $textRed = "red";

    static $textBlue = "blue";

    static $failureMark = "✗";

    static $successMark = "✓";

    public static function initialize(){
        self::defineShellColors();
    }

    private static function defineShellColors(){
        // Set up shell colors
        self::$foregroundColors['black'] = '0;30';
        self::$foregroundColors['dark_gray'] = '1;30';
        self::$foregroundColors['blue'] = '0;34';
        self::$foregroundColors['light_blue'] = '1;34';
        self::$foregroundColors['green'] = '0;32';
        self::$foregroundColors['light_green'] = '1;32';
        self::$foregroundColors['cyan'] = '0;36';
        self::$foregroundColors['light_cyan'] = '1;36';
        self::$foregroundColors['red'] = '0;31';
        self::$foregroundColors['light_red'] = '1;31';
        self::$foregroundColors['purple'] = '0;35';
        self::$foregroundColors['light_purple'] = '1;35';
        self::$foregroundColors['brown'] = '0;33';
        self::$foregroundColors['yellow'] = '1;33';
        self::$foregroundColors['light_gray'] = '0;37';
        self::$foregroundColors['white'] = '1;37';

        self::$backgroundColors['black'] = '40';
        self::$backgroundColors['red'] = '41';
        self::$backgroundColors['green'] = '42';
        self::$backgroundColors['yellow'] = '43';
        self::$backgroundColors['blue'] = '44';
        self::$backgroundColors['magenta'] = '45';
        self::$backgroundColors['cyan'] = '46';
        self::$backgroundColors['light_gray'] = '47';
    }

    /**
     * Handles coloring string in cli
     * @param string $string
     * @param string|null $foregroundColor
     * @param string|null $backgroundColor
     * @return string
     */
    public static function getColoredString(string $string, ?string $foregroundColor = null, ?string $backgroundColor = null) {
        $coloredString = "";

        // Check if given foreground color found
        if (isset(self::$foregroundColors[$foregroundColor])) {
            $coloredString .= "\033[" . self::$foregroundColors[$foregroundColor] . "m";
        }
        // Check if given background color found
        if (isset(self::$backgroundColors[$backgroundColor])) {
            $coloredString .= "\033[" . self::$backgroundColors[$backgroundColor] . "m";
        }

        // Add string and end coloring
        $coloredString .=  $string . "\033[0m";

        return $coloredString;
    }

    /**
     * Returns colored string for cli
     * @param string $string
     * @param bool $newLine
     */
    public static function errorText(string $string, bool $newLine = true){

        if($newLine){
            echo PHP_EOL;
        }

        echo self::getColoredString($string, self::$textRed);

        if($newLine){
            echo PHP_EOL;
        }
    }

    /**
     * Returns colored string for cli
     * @param string $string
     * @param bool $newLine
     */
    public static function infoText(string $string, bool $newLine = true){

        if($newLine){
            echo PHP_EOL;
        }

        echo self::getColoredString($string, self::$textYellow);

        if($newLine){
            echo PHP_EOL;
        }
    }

    /**
     * Displays standard string
     * @param string $string
     * @param bool $newLine
     */
    public static function text(string $string, bool $newLine = true){

        echo $string;

        if($newLine){
            echo PHP_EOL;
        }
    }

    public static function newLine(){
        echo PHP_EOL;
    }

    /**
     * This function returns line separator with either default settings or customized
     * @param int $lineSize
     * @param string $symbol
     * @param string|null $color
     */
    public static function lineSeparator(int $lineSize = 30, string $symbol = '-', ?string $color = null)
    {
        $line = '';

        for($x = 0; $x <= $lineSize; $x++){
            $line .= $symbol;
        }

        echo  PHP_EOL . self::getColoredString($line, $color) . PHP_EOL;
    }

    /**
     * This function returns '✓' character
     * @return string
     */
    public static function successMark(){
        return self::getColoredString(self::$successMark);
    }

    /**
     * This function returns '✗' character
     * @return string
     */
    public static function failureMark(){
        return self::getColoredString(self::$failureMark, self::$textRed);
    }

    /**
     * This method will get a single line user input
     * @param $message
     * @return string
     */
    public static function getUserInput($message){
        $line = readline("{$message}: ");
        return $line;
    }

    /**
     * Will display list of choices to select from, if user will select incorrect one then he will be asked again
     * until existing option will be provided
     * @param array $choices
     * @param string $displayedString
     * @return string
     */
    public static function choices(array $choices, string $displayedString): string {

        $selectedOptionIndex = null;

        echo PHP_EOL;
            echo $displayedString;
        echo PHP_EOL;

        foreach($choices as $index => $choice ){
            self::infoText("[{$index}] ", false);
            echo $choice;
            echo PHP_EOL;
        }

        echo PHP_EOL;
            while( !array_key_exists($selectedOptionIndex, $choices) ){
                $selectedOptionIndex = readline("Select correct option: ");
            }

        $selectedOption = $choices[$selectedOptionIndex];

        echo PHP_EOL;

        return $selectedOption;
    }
}
