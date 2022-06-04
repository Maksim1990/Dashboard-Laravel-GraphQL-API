<?php

use App\Exceptions\BehatRuntimeException;
use App\Models\User;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Imbo\BehatApiExtension\Context\ApiContext;
use GuzzleHttp\Psr7;

/**
 * Defines application features from the specific context.
 */
abstract class BaseFeatureContext extends ApiContext implements Context
{

    /**
     * @var User
     */
    protected static ?User $user = null;

    /**
     * @param resource|string|PyStringNode $string
     * @return self
     *
     * @Given the graphql request body is:
     * @throws InvalidArgumentException
     */
    public function setGraphqlRequestBody($string)
    {
        if (!empty($this->requestOptions['multipart']) || !empty($this->requestOptions['form_params'])) {
            throw new InvalidArgumentException(
                'It\'s not allowed to set a request body when using multipart/form-data or form parameters.'
            );
        }

        $requestBody = [
            'query' => implode('', $string->getStrings()),
            'variables' => new stdClass,
        ];


        $this->request = $this->request->withBody(Psr7\stream_for(json_encode($requestBody)));

        return $this;
    }

    /**
     * @Then /^I get auth token from the response$/
     * @throws BehatRuntimeException
     */
    public function iGetAuthTokenFromTheResponse()
    {
        return $this->getResponseBodyContent()->data->login->access_token;
    }

    protected function getResponseBodyContent()
    {
        $this->requireResponse();
        $response = json_decode($this->response->getBody()->getContents());
        if (isset($response->errors)) {
            throw new BehatRuntimeException($response->errors->message, $response->code);
        }

        return $response;
    }

    /**
     * @Then /^I get user ID and auth token from the response$/
     */
    public function iGetUserIDAndAuthTokenFromTheResponse()
    {
        $data = $this->getResponseBodyContent()->data->login;
        return [
            $data->access_token,
            $data->user->_id,
        ];
    }
}
