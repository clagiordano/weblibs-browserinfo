<?php

namespace clagiordano\weblibs\browserinfo;

/**
 * Class to get information from user agent string and return them
 * in an object.
 *
 * @version  2.1.3
 * @author   Claudio Giordano <claudio.giordano@autistici.org>
 * @since    2014-04-04
 * @category Information
 * @package  weblibs
 */
class BrowserInfo
{
    /** @var string $Platform System platform name */
    public $Platform = "Unknown";
    /** @var string $Architecture System platform architecture (32/64bit) */
    public $Architecture = "Unknown";
    /** @var string $Browser Browser name */
    public $Browser = "Unknown";
    /** @var int $Version Browser version */
    public $Version = "Unknown";
    /** @var string $RemoteAddress System remote ip address */
    public $RemoteAddress = "0.0.0.0";
    /** @var string $userAgentString Server HTTP_USER_AGENT */
    private $userAgentString = "";
    /** @var string $BrowserMatched Browser name matched form user agent */
    private $BrowserMatched = "";

    /**
     * @constructor
     * @param  boolean $useBrowscap
     */
    public function __construct($useBrowscap = false)
    {
        $this->userAgentString = str_replace(
            '\n',
            '',
            filter_input(INPUT_SERVER, 'HTTP_USER_AGENT')
        );
        $this->getBrowserInfo($useBrowscap);

        return $this;
    }

    /**
     * Retrive information from native method get_browser with php_browscap.ini
     * or detect them with this class functions from $SERVER['HTTP_USER_AGENT']
     * and return an object BrowserInfo, with al properties setted.
     *
     * @param  boolean $useBrowscap
     * @return \clagiordano\weblibs\browserinfo\BrowserInfo
     */
    public function getBrowserInfo($useBrowscap = false)
    {
        if ($useBrowscap && ini_get('browscap')) {
            /**
             * In this case use get_browser with php_browscap.ini method:
             */
            $dataInfo = get_browser(null, true);
            $this->Platform = $dataInfo['platform'];
            $this->Browser = $dataInfo['browser'];
            $this->Version = $dataInfo['version'];
        } else {
            /**
             * Otherwise start detection by regular expressions
             */
            $this->Platform = $this->detectPlatform();
            $this->Browser = $this->detectBrowser();
            $this->Version = $this->detectVersion();
        }

        /**
         * Common methods for both cases
         */
        $this->Architecture = $this->detectArchitecture();
        $this->RemoteAddress = (filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR')
            ? filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR')
            : filter_input(INPUT_SERVER, 'REMOTE_ADDR')
        );

        return $this;
    }

    /**
     * Detect and returns user agent platform
     *
     * @return string
     */
    private function detectPlatform()
    {
        $platform = "Unknown";

        if (preg_match('/linux/i', $this->userAgentString)) {
            $platform = 'Linux';
        } elseif (preg_match('/ipad/i', $this->userAgentString)) {
            $platform = 'iPad';
        } elseif (preg_match('/iphone/i', $this->userAgentString)) {
            $platform = 'iPhone';
        } elseif (preg_match('/macintosh|mac os x/i', $this->userAgentString)) {
            $platform = 'Mac';
        } elseif (preg_match('/windows|win32/i', $this->userAgentString)) {
            $platform = 'Windows';
        } elseif (preg_match('/android/i', $this->userAgentString)) {
            $platform = 'Android';
        } elseif (preg_match('/series40|series\ 60/i', $this->userAgentString)) {
            $platform = 'SymbianOS';
        }

        return $platform;
    }

    /**
     * @return string
     */
    private function detectBrowser()
    {
        $browser = "Unknown";

        if (preg_match('/MSIE/i', $this->userAgentString) && !preg_match(
            '/Opera/i',
            $this->userAgentString
        )
        ) {
            $browser = 'Internet Explorer';
            $this->BrowserMatched = "MSIE";
        } elseif (preg_match('/Firefox/i', $this->userAgentString)) {
            $browser = 'Mozilla Firefox';
            $this->BrowserMatched = "Firefox";
        } elseif (preg_match('/Chrome/i', $this->userAgentString)) {
            $browser = 'Google Chrome';
            $this->BrowserMatched = "Chrome";
        } elseif (preg_match('/Safari/i', $this->userAgentString)) {
            $browser = 'Apple Safari';
            $this->BrowserMatched = "Safari";
        } elseif (preg_match('/Opera/i', $this->userAgentString)) {
            $browser = 'Opera';
            $this->BrowserMatched = "Opera";
        } elseif (preg_match('/Netscape/i', $this->userAgentString)) {
            $browser = 'Netscape';
            $this->BrowserMatched = "Netscape";
        } elseif (preg_match('/S40OviBrowser/i', $this->userAgentString)) {
            $browser = 'Nokia proxy browser';
            $this->BrowserMatched = "S40OviBrowser";
        }

        return $browser;
    }

    /**
     * @return string
     */
    private function detectVersion()
    {
        $version = "Unknown";
        $matches = [];

        // finally get the correct version number
        $known = ['Version', $this->BrowserMatched, 'other'];
        $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $this->userAgentString, $matches)) {
            // we have no matching number just continue
        }

        // see how many we have
        if (count($matches['browser']) != 1) {
            // we will have two since we are not using 'other' argument yet
            // see if version is before or after the name
            if (strripos($this->userAgentString, "Version") < strripos(
                $this->userAgentString,
                $this->BrowserMatched
            )
            ) {
                $version = isset($matches['version'][0]) ? $matches['version'][0] : "Unknown";
            } elseif (isset($matches['version'][1])) {
                $version = $matches['version'][1];
            }
        } else {
            $version = $matches['version'][0];
        }

        /**
         * check if we have a number
         */
        if (is_null($version) || $version === "") {
            $version = "Unknown";
        }

        return $version;
    }

    /**
     * @return string
     */
    private function detectArchitecture()
    {
        $architecture = "Unknown";

        if (preg_match('/i386|i486|i686/i', $this->userAgentString)) {
            $architecture = '32bit';
        } elseif (preg_match('/X86_64|ia64/i', $this->userAgentString)) {
            $architecture = '64bit';
        }

        return $architecture;
    }

    /**
     * @return bool
     */
    public function identificationStatus()
    {
        $identification = true;

        if ($this->Platform == "Unknown") {
            $identification = false;
        } elseif ($this->Browser == "Unknown") {
            $identification = false;
        } elseif ($this->Version == "Unknown") {
            $identification = false;
        } elseif ($this->RemoteAddress == "0.0.0.0") {
            $identification = false;
        }

        return $identification;
    }

    /**
     * Print internal properties info
     */
    public function printR()
    {
        echo "<pre>";
        echo "userAgentString: " . $this->userAgentString . "\n";
        echo "       Platform: " . $this->Platform . "\n";
        echo "   Architecture: " . $this->Architecture . "\n";
        echo "        Browser: " . $this->Browser . "\n";
        echo "        Version: " . $this->Version . "\n";
        echo "  RemoteAddress: " . $this->RemoteAddress . "\n";
        echo "</pre>";
    }

    /**
     * Returns current user agent string
     * @return string
     */
    public function getUserAgentString()
    {
        return $this->userAgentString;
    }

    /**
     * Sets user agent string to identify
     * @param string $userAgentString
     * @return \clagiordano\weblibs\browserinfo\BrowserInfo
     */
    public function setUserAgentString($userAgentString)
    {
        $this->userAgentString = $userAgentString;

        return $this;
    }
}
