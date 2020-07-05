<?php


namespace App\Tests\Controller\Utils;

use App\Controller\Utils\Utils;
use App\Tests\AbstractTestCase;
use Exception;


class UtilsTest extends AbstractTestCase
{

    /**
     * @throws Exception
     */
    public function testGetBoolRepresentationOfBoolString()
    {

        $bool_representation_of_false_string = Utils::getBoolRepresentationOfBoolString(Utils::FALSE_AS_STRING);
        $bool_representation_of_true_string  = Utils::getBoolRepresentationOfBoolString(Utils::TRUE_AS_STRING);

        $bool_representation_of_false_bool   = Utils::getBoolRepresentationOfBoolString(false);
        $bool_representation_of_true_bool    = Utils::getBoolRepresentationOfBoolString(true);


        $this->assertEquals(true, $bool_representation_of_true_string, "(string)`true` was not converted to (bool)`true`");
        $this->assertEquals(true, $bool_representation_of_true_bool, "(bool)`true` was not converted to (bool)`true`");

        $this->assertEquals(false, $bool_representation_of_false_string, "(string)`false` was not converted to (bool)`false`");
        $this->assertEquals(false, $bool_representation_of_false_bool, "(bool)`false` was not converted to (bool)`false`");
    }
}