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

        $boolRepresentationOfFalseString = Utils::getBoolRepresentationOfBoolString(Utils::FALSE_AS_STRING);
        $boolRepresentationOfTrueString  = Utils::getBoolRepresentationOfBoolString(Utils::TRUE_AS_STRING);

        $boolRepresentationOfFalseBool   = Utils::getBoolRepresentationOfBoolString(false);
        $boolRepresentationOfTrueBool    = Utils::getBoolRepresentationOfBoolString(true);


        $this->assertEquals(true, $boolRepresentationOfTrueString, "(string)`true` was not converted to (bool)`true`");
        $this->assertEquals(true, $boolRepresentationOfTrueBool, "(bool)`true` was not converted to (bool)`true`");

        $this->assertEquals(false, $boolRepresentationOfFalseString, "(string)`false` was not converted to (bool)`false`");
        $this->assertEquals(false, $boolRepresentationOfFalseBool, "(bool)`false` was not converted to (bool)`false`");
    }
}