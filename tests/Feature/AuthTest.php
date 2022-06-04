<?php
namespace Tests\Feature;

use App\GraphQL\Mutations\Auth\RegisterMutator;
use App\Models\User;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use MakesGraphQLRequests;

    private static $arrUser = [
        'name' => "test",
        'email' => "test@gmail.com",
        'password' => "testtest",
        'password_confirmation' => "testtest",
    ];

    /**
     * @return void
     */
    public function testRegisterCase()
    {

        $response = $this->graphQL(/** @lang GraphQL */ '
        mutation RegisterApp($name: String! $email: String! $password: String! $password_confirmation: String!) {
            register(name: $name, email: $email, password: $password, password_confirmation: $password_confirmation) {
                status
                message
            }
        }
    ', self::$arrUser);

        $response->assertStatus(200)->assertJsonFragment(['status' => RegisterMutator::REGISTER_STATUS]);
    }

    /**
     * @return void
     */
    public function testEnableUserCase()
    {
        $user = User::where('email', self::$arrUser['email'])->first();
        if (!is_null($user)) {
            $response = $this->graphQL(/** @lang GraphQL */ '
        mutation ConfirmRegistration($token: String!) {
            confirm_registration(token: $token) {
                confirmed
            }
        }
    ', [
                'token' => $user->confirmation_token
            ]);

            $response->assertStatus(200)->assertJsonFragment(['confirmed' => true]);
        }


    }

    /**
     * @return void
     */
    public function testLoginCase()
    {
//        $user = factory(User::class)->create([
//            'name' => 'test',
//            'email'=>"test@gmail.com",
//            'enabled'=>true,
//            'password'=>'testtest',
//        ]);

        $response = $this->graphQL(/** @lang GraphQL */ '
        mutation LoginApp($email: String! $password: String!) {
            login(email: $email, password: $password) {
                access_token
                token_type
                user{
                  _id
                  name
                }
            }
        }
    ', [
            'email' => self::$arrUser['email'],
            'password' => self::$arrUser['password'],
        ]);

        $response->assertStatus(200)->assertJsonFragment(['token_type' => 'Bearer']);
    }


    /**
     * Remove registered user after all tests
     */
    public static function tearDownAfterClass(): void
    {
        User::where('email', self::$arrUser['email'])->delete();
    }
}
