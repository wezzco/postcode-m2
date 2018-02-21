<?php

namespace Wezz\Postcode\Model\Api;

/**
 * Class ClientApi
 * @package Wezz\Postcode\Model\Api
 */
class ClientApi
{
    /**
     * API timeout const
     */
    const API_TIMEOUT = 3;
    const TEST_POSTCODE = '2012ES';
    const TEST_HOUSENUMBER = '30';

    /**
     * @var \Wezz\Postcode\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Wezz\Postcode\Helper\Config
     */
    protected $helperConfig;

    /**
     * @var
     */
    protected $httpResponseRaw;

    /**
     * @var
     */
    protected $httpResponseCode;

    /**
     * @var
     */
    protected $httpResponseCodeClass;

    /**
     * @var
     */
    protected $httpClientError;

    /**
     * ClientApi constructor.
     * @param \Wezz\Postcode\Helper\Data $helperData
     * @param \Wezz\Postcode\Helper\Config $helperConfig
     */
    public function __construct(
        \Wezz\Postcode\Helper\Data $helperData,
        \Wezz\Postcode\Helper\Config $helperConfig
    ) {
        $this->helperData = $helperData;
        $this->helperConfig = $helperConfig;
    }

    /**
     * Lookup information about a Dutch address by postcode, house number, and house number addition
     *
     * @param string $postcode
     * @param string $houseNumber
     * @param string $houseNumberAddition
     * @param boolean $testConnection
     *
     * @return string|array
     */
    public function lookupAddress($postcode, $houseNumber, $houseNumberAddition = '', $testConnection = false)
    {
        /**
         * Check for preconditions to API correct work
         */
        $response = $this->checkApiReady();

        if (!empty($response)) {
            return $response;
        }

        /**
         * Validate postcode value
         */
        $response = $this->validatePostcodeFormat($postcode);
        if (!empty($response)) {
            return $response;
        }

        $url = $this->helperConfig->getApiUrl() . '/rest/addresses/postcode/' . rawurlencode($postcode). '/'. rawurlencode($houseNumber) . '/'. rawurlencode($houseNumberAddition);
        $url = trim($url);

        $jsonResult = $this->callApiUrlGet($url);

        if ($this->httpResponseCode == 200 && !empty($jsonResult) && isset($jsonResult['postcode'])) {

            if ($this->helperConfig->getBlockPostAddresses() && isset($jsonResult['addressType']) && $jsonResult['addressType'] == 'PO box') {
                $this->httpResponseCode = 400;
                $response['message'] = __('Post office box not allowed.');
                $response['messageTarget'] = 'postcode';
            } else {
                $response = $jsonResult;
            }

        } else if (isset($jsonResult['exceptionId']) && ($this->httpResponseCode == 400 || $this->httpResponseCode == 404)) {
            switch ($jsonResult['exceptionId'])
            {
                case 'PostcodeNl_Controller_Address_PostcodeTooShortException':
                case 'PostcodeNl_Controller_Address_PostcodeTooLongException':
                case 'PostcodeNl_Controller_Address_NoPostcodeSpecifiedException':
                case 'PostcodeNl_Controller_Address_InvalidPostcodeException':
                    $response['message'] = __('Invalid postcode format, use `1234AB` format.');
                    $response['messageTarget'] = 'postcode';
                    break;
                case 'PostcodeNl_Service_PostcodeAddress_AddressNotFoundException':
                    $response['message'] = __('Unknown postcode + housenumber combination.');
                    $response['messageTarget'] = 'housenumber';
                    break;
                case 'PostcodeNl_Controller_Address_InvalidHouseNumberException':
                case 'PostcodeNl_Controller_Address_NoHouseNumberSpecifiedException':
                case 'PostcodeNl_Controller_Address_NegativeHouseNumberException':
                case 'PostcodeNl_Controller_Address_HouseNumberTooLargeException':
                case 'PostcodeNl_Controller_Address_HouseNumberIsNotAnIntegerException':
                    $response['message'] = __('Housenumber format is not valid.');
                    $response['messageTarget'] = 'housenumber';
                    break;
                default:
                    $response['message'] = __('Incorrect address.');
                    $response['messageTarget'] = 'housenumber';
                    break;
            }

            //$response = array_merge($response, $this->errorResponse());
        }

        if ($this->helperConfig->getIsDebug() || $testConnection) {
            $response['debugInfo'] = $this->getDebugInfo($url, $jsonResult);
        }

        if ($this->helperConfig->getApiShowcase()) {
            $response['showcaseResponse'] = $jsonResult;
        }

        /**
         * Check for block post addresses
         */
        return $response;
    }

    /**
     * Method for test connection
     *
     * @return array
     */
    public function testConnection()
    {
        // Default is not OK
        $status = 'error';
        $info = array();

        $addressData = $this->lookupAddress(
            SELF::TEST_POSTCODE,
            SELF::TEST_HOUSENUMBER,
            '',
            true
        );

        if (!isset($addressData['debugInfo']) && isset($addressData['message'])) {
            // Client-side error
            $message = $addressData['message'];
            if (isset($addressData['info'])) {
                $info = $addressData['info'];
            }
        } else if (isset($addressData['debugInfo']['httpClientError']) && $addressData['debugInfo']['httpClientError']) {
            // We have a HTTP connection error
            $message = __('Your server could not connect to the Postcode.nl server.');

            // Do some common SSL CA problem detection
            if (strpos($addressData['debugInfo']['httpClientError'], 'SSL certificate problem, verify that the CA cert is OK') !== false) {
                $info[] = __('Your servers\' \'cURL SSL CA bundle\' is missing or outdated. Further information:');
                $info[] = '- <a href="https://stackoverflow.com/questions/6400300/https-and-ssl3-get-server-certificatecertificate-verify-failed-ca-is-ok" target="_blank">'. __('How to update/fix your CA cert bundle') .'</a>';
                $info[] = '- <a href="https://curl.haxx.se/docs/sslcerts.html" target="_blank">'. __('About cURL SSL CA certificates') .'</a>';
                $info[] = '';
            } else if (strpos($addressData['debugInfo']['httpClientError'], 'unable to get local issuer certificate') !== false) {
                $info[] = __('cURL cannot read/access the CA cert file:');
                $info[] = '- <a href="https://curl.haxx.se/docs/sslcerts.html" target="_blank">'. __('About cURL SSL CA certificates') .'</a>';
                $info[] = '';
            } else {
                $info[] = __('Connection error.');
            }

            $info[] = __('Error message:') . ' "'. $addressData['debugInfo']['httpClientError'] .'"';
            $info[] = '- <a href="https://www.google.com/search?q='. urlencode($addressData['debugInfo']['httpClientError'])  .'" target="_blank">'. __('Google the error message') .'</a>';
            $info[] = '- '. __('Contact your hosting provider if problems persist.');
        } else if (!is_array($addressData['debugInfo']['parsedResponse'])) {
            // We have not received a valid JSON response

            $message = __('The response from the Postcode.nl service could not be understood.');
            $info[] = '- '. __('The service might be temporarily unavailable, if problems persist, please contact <a href=\'mailto:info@postcode.nl\'>info@postcode.nl</a>.');
            $info[] = '- '. __('Technical reason: No valid JSON was returned by the request.');
        } else if (is_array($addressData['debugInfo']['parsedResponse']) && isset($addressData['debugInfo']['parsedResponse']['exceptionId'])) {
            // We have an exception message from the service itself

            if ($addressData['debugInfo']['responseCode'] == 401) {
                if ($addressData['debugInfo']['parsedResponse']['exceptionId'] == 'PostcodeNl_Controller_Plugin_HttpBasicAuthentication_NotAuthorizedException')
                    $message = __('`API Key` specified is incorrect.');
                else if ($addressData['debugInfo']['parsedResponse']['exceptionId'] == 'PostcodeNl_Controller_Plugin_HttpBasicAuthentication_PasswordNotCorrectException')
                    $message = __('`API Secret` specified is incorrect.');
                else
                    $message = __('Authentication is incorrect.');
            } else if ($addressData['debugInfo']['responseCode'] == 403) {
                $message = __('Access is denied.');
            } else {
                $message = __('Service reported an error.');
            }

            $info[] = __('Postcode.nl service message:') .' "'. $addressData['debugInfo']['parsedResponse']['exception'] .'"';
        } else if (is_array($addressData['debugInfo']['parsedResponse']) && !isset($addressData['debugInfo']['parsedResponse']['postcode'])) {
            // This message is thrown when the JSON returned did not contain the data expected.

            $message = __('The response from the Postcode.nl service could not be understood.');
            $info[] = '- '. __('The service might be temporarily unavailable, if problems persist, please contact <a href=\'mailto:info@postcode.nl\'>info@postcode.nl</a>.');
            $info[] = '- '. __('Technical reason: Received JSON data did not contain expected data.');
        } else {
            $message = __('A test connection to the API was successfully completed.');
            $status = 'success';
        }

        return array(
            'message' => $message,
            'status' => $status,
            'info' => $info
        );
    }

    /**
     * Get debug info
     *
     * @param $url
     * @param $jsonData
     * @return array
     */
    protected function getDebugInfo($url, $jsonData)
    {
        return array(
            'requestUrl' => $url,
            'rawResponse' => $this->httpResponseRaw,
            'responseCode' => $this->httpResponseCode,
            'responseCodeClass' => $this->httpResponseCodeClass,
            'parsedResponse' => $jsonData,
            'httpClientError' => $this->httpClientError,
            'configuration' => array(
                'url' => $this->helperConfig->getApiUrl(),
                'key' => $this->helperConfig->getApiKey(),
                'secret' => substr($this->helperConfig->getApiSecret(), 0, 6) . '[hidden]',
                'showcase' => $this->helperConfig->getApiShowcase(),
                'debug' => $this->helperConfig->getApiDebug()
            ),
            'magentoVersion' => $this->helperData->getMagentoVersion(),
            'extensionVersion' => $this->helperData->getExtensionVersion(),
            'modules' => $this->helperData->getMagentoModules(),
        );
    }

    /**
     * Method to add main exception
     *
     * @return array
     */
    protected function errorResponse()
    {
        return array(
            'message' => __('Validation error, please use manual input.'),
            'messageTarget' => 'housenumber',
            'useManual' => true
        );
    }

    /**
     * Method to check API ready state to correct work
     *
     * @return array
     */
    protected function checkApiReady()
    {
        if (!$this->helperConfig->getEnabled()) {
            return array(
                'message' => __('Postcode.nl API not enabled.')
            );
        }

        $result = $this->helperConfig->checkBasicApiSettings();
        if (!empty($result)) {
            return $result;
        }

        $result = $this->helperData->checkCapabilities();
        if (!empty($result)) {
            return $result;
        }

        return array();
    }

    /**
     * Method to validate postcode format
     * @param $postcode
     * @return mixed
     */
    protected function validatePostcodeFormat($postcode)
    {
        $result = array();

        // Some basic user data 'fixing', remove any not-letter, not-number characters
        $postcode = preg_replace('~[^a-z0-9]~i', '', $postcode);

        // Basic postcode format checking
        if (!preg_match('~^[1-9][0-9]{3}[a-z]{2}$~i', $postcode)) {
            $result['message'] = __('Invalid postcode format, use `1234AB` format.');
            $result['messageTarget'] = 'postcode';
            return $result;
        }
    }

    /**
     * CallApiUrlGet method
     *
     * @param $url
     * @return mixed
     */
    protected function callApiUrlGet($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::API_TIMEOUT);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->helperConfig->getApiPassword());
        curl_setopt($ch, CURLOPT_USERAGENT, $this->helperData->getUserAgent());

        $this->httpResponseRaw = curl_exec($ch);
        $this->httpResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->httpResponseCodeClass = (int)floor($this->httpResponseCode / 100) * 100;
        $curlErrno = curl_errno($ch);
        $this->httpClientError = $curlErrno ? sprintf('cURL error %s: %s', $curlErrno, curl_error($ch)) : null;

        curl_close($ch);

        return json_decode($this->httpResponseRaw, true);
    }
}
