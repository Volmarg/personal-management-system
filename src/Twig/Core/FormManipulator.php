<?php


namespace App\Twig\Core;


use App\Controller\Core\Application;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FormManipulator extends AbstractExtension {

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function getFunctions() {
        return [
            new TwigFunction('setSelectedOption', [$this, 'setSelectedOption']),
        ];
    }

    /**
     * This function takes raw html coming from `form_widget` (SELECT)
     * and sets the `selected` attribute for given default value
     * if this element is not a SELECT then nothing is being applied
     *
     * This solution is required as sometimes default select value need to be set in twig based on some variable
     * however setting `selected/default/value` is not working at all in case of SELECT tag
     *
     * This can be especially useful to avoid extensive js logic just to select something from list
     *
     * @param string $html
     * @param string $defaultValue
     * @return string
     */
    public function setSelectedOption(string $html, string $defaultValue): string
    {
        if( !strstr($html, "select"))
        {
            $this->app->logger->warning("This element is not an SELECT element");
            return $html;
        }

        $valueString              = 'value="' . $defaultValue . '"';
        $selectWithSelectedOption = str_replace($valueString, $valueString . " selected", $html);

        return $selectWithSelectedOption;
    }
}