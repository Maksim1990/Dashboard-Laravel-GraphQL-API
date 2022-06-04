<?php

use App\Models\User;
use Behat\Testwork\Hook\Scope\AfterSuiteScope;

/**
 * Defines application features from the specific context.
 */
class AuthFeatureContext extends BaseFeatureContext
{
    /**
     * @Given /^i get confirmation token by "([^"]*)" email$/
     */
    public function iGetConfirmationTokenByEmail(string $email)
    {
        self::$user = User::where('email', $email)->first();
        return self::$user->confirmation_token;
    }

    /**
     * @AfterSuite
     */
    public static function deleteTestUser(AfterSuiteScope $scope)
    {
        if (self::$user === null) {
            return;
        }
        User::where('email', self::$user->email)->delete();
    }
}
