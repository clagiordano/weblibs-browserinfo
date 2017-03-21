<?php

namespace clagiordano\weblibs\browserinfo\tests;

use clagiordano\weblibs\browserinfo\BrowserInfo;

/**
 * Class BrowserInfoTest
 * @package clagiordano\weblibs\browserinfo\tests
 */
class BrowserInfoTest extends \PHPUnit_Framework_TestCase
{
    const LIST_PATH = "testsdata/browserInfo_sample_agents.txt";

    /** @var BrowserInfo $browserInfo */
    private $browserInfo = null;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->browserInfo = new BrowserInfo();

        $this->assertInstanceOf(
            'clagiordano\weblibs\browserinfo\BrowserInfo',
            $this->browserInfo
        );
    }

    public function testgetBrowserInfo()
    {
        $this->assertFalse(
            $this->browserInfo->identificationStatus()
        );

        $this->assertInstanceOf(
            'clagiordano\weblibs\browserinfo\BrowserInfo',
            $this->browserInfo->getBrowserInfo()
        );

        ob_start();
        $this->browserInfo->printR();
        ob_end_clean();
    }

    public function testgetBrowserInfoForceUserAgent()
    {
        $this->browserInfo->setUserAgentString(
            "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1) ; .NET CLR 3.5.30729)"
        );

        $this->assertInstanceOf(
            'clagiordano\weblibs\browserinfo\BrowserInfo',
            $this->browserInfo->getBrowserInfo()
        );
        $this->assertTrue($this->browserInfo->identificationStatus());

    }

    public function testgetBrowserInfoCycleUserAgents()
    {
        $this->assertFileExists(self::LIST_PATH);

        $userAgents = file(self::LIST_PATH);
        $this->assertInternalType('array', $userAgents);
        $this->assertTrue(count($userAgents) > 0);

        $tested = 0;
        $failed = 0;
        $notMatched = [];

        foreach ($userAgents as $agent) {
            $this->browserInfo->setUserAgentString($agent);
            $this->assertInstanceOf(
                'clagiordano\weblibs\browserinfo\BrowserInfo',
                $this->browserInfo->getBrowserInfo()
            );

            $tested++;
            $status = $this->browserInfo->identificationStatus();

            if ($status === false) {
                $failed++;
                $notMatched[] = $agent;
            }
        }

//        var_dump($notMatched);
//
//        $this->assertEquals(
//            0,
//            $failed,
//            "Failed identifications {$failed} / {$tested}"
//        );
    }

    public function testIdentifyTarget()
    {
        $targetString = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.79 Safari/537.36";

        $this->browserInfo->setUserAgentString($targetString);
        $bInfo = $this->browserInfo->getBrowserInfo();

        $this->assertInstanceOf(
            'clagiordano\weblibs\browserinfo\BrowserInfo',
            $bInfo
        );

        $this->assertTrue(
            $this->browserInfo->identificationStatus()
        );

        $this->assertEquals(
            $targetString,
            $this->browserInfo->getUserAgentString()
        );

        $this->assertEquals(
            'Linux',
            $this->browserInfo->Platform
        );

        $this->assertEquals(
            '64bit',
            $this->browserInfo->Architecture
        );

        $this->assertEquals(
            'Google Chrome',
            $this->browserInfo->Browser
        );

        $this->assertEquals(
            '51.0.2704.79',
            $this->browserInfo->Version
        );
    }

    /**
     * @group custom
     */
    public function testCustomDetection()
    {
        $this->browserInfo->setUserAgentString(
            'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_1 like Mac OS X; en-us) AppleWebKit/532.9 (KHTML, like Gecko) Version/4.0.5 Mobile/8B117 Safari/6531.22.7 (compatible; Googlebot-Mobile/2.1;'
        );
        $this->browserInfo->getBrowserInfo();
        $this->browserInfo->printR();
//        $this->assertEquals(
//            'iPhone',
//            $this->browserInfo->Platform
//        );
    }
}
