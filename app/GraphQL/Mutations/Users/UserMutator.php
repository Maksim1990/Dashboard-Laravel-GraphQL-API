<?php

namespace App\GraphQL\Mutations\Users;

use App\Exceptions\GraphQL\GraphqlException;
use App\GraphQL\Mutations\BaseMutator;
use App\Jobs\User\DeleteUser;
use App\Models\Message;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserMutator extends BaseMutator
{
    public function create($rootValue, array $args): User
    {
        $validator = Validator::make($args, [
            'email' => 'required|email|unique:users',
            'name' => 'required|unique:users|string|max:50',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            throw new GraphqlException(
                implode(',', $validator->messages()->all()),
                [
                    'type' => 'user',
                    'category' => 'user_create',
                    'reason' => 'validation_error',
                ]
            );
        }

        $user = new User();
        $user->name = $args['name'];
        $user->email = $args['email'];
        $user->enabled = false;
        $user->role = $args['role'] ?? "user";
        $user->lastname = $args['lastname'] ?? "";
        $user->country = $args['country'] ?? "";
        $user->country_code = $args['country_code'] ?? "";
        $user->city = $args['city'] ?? "";
        $user->zip = $args['zip'] ?? "";
        $user->address = $args['address'] ?? "";
        $user->birthdate = $args['birthdate'] ?? "";
        $user->bio = $args['bio'] ?? "";
        $user->phone = $args['phone'] ?? "";
        $user->confirmation_token = $this->getTokenGenerator()->generate();
        $user->password = Hash::make($args['password']);

        //-- Update Settings relation if relation request is not empty
        if (isset($args['settings']) && !empty($args['settings'])) {
            $user->settings()->create(
                $args['settings']);
        }

        $user->save();

        Cache::tags(['users'])->flush();

        return $user;
    }

    public function update($rootValue, array $args): User
    {
        $validator = Validator::make($args, [
            '_id' => 'required',
            'email' => "unique:users,email," . $args['_id'] . ",_id",
        ]);
        if ($validator->fails()) {
            throw new GraphqlException(
                implode(',', $validator->messages()->all()),
                [
                    'type' => 'user',
                    'category' => 'user_create',
                    'reason' => 'validation_error',
                ]
            );
        }

        if (($user = User::find($args['_id'])) === null) {
            throw new GraphqlException('User not found', [
                'type' => 'user',
                'category' => 'user_update',
                'reason' => 'Not found',
            ]);
        }

        if (isset($args['name'])) {
            $user->name = $args['name'];
        }
        if (isset($args['email']) && !empty($args['email'])) {
            $user->email = $args['email'];
        }
        if (isset($args['password']) && !empty($args['password'])) {
            $user->password = Hash::make($args['password']);
        }

        if (isset($args['enabled'])) {
            $user->enabled = (bool)$args['enabled'];
        }
        if (isset($args['role']) && !empty($args['role'])) {
            $user->role = $args['role'];
        }
        if (isset($args['lastname']) && !empty($args['lastname'])) {
            $user->lastname = $args['lastname'];
        }
        if (isset($args['country']) && !empty($args['country'])) {
            $user->country = $args['country'];
        }
        if (isset($args['country_code']) && !empty($args['country_code'])) {
            $user->country_code = $args['country_code'];
        }
        if (isset($args['city']) && !empty($args['city'])) {
            $user->city = $args['city'];
        }
        if (isset($args['zip']) && !empty($args['zip'])) {
            $user->zip = $args['zip'];
        }
        if (isset($args['address']) && !empty($args['address'])) {
            $user->address = $args['address'];
        }

        if (isset($args['birthdate']) && !empty($args['birthdate'])) {
            $user->birthdate = Carbon::parse($args['birthdate'])->format('Y-m-d H:i:s');
        }
        if (isset($args['bio']) && !empty($args['bio'])) {
            $user->bio = $args['bio'];
        }
        if (isset($args['phone']) && !empty($args['phone'])) {
            $user->phone = $args['phone'];
        }

        //-- Update Settings relation if relation request is not empty
        if (isset($args['settings']) && !empty($args['settings'])) {
            $user->settings()->updateOrCreate(['user_id' => $user->_id], $args['settings']);
        }

        $user->update();

        Cache::tags(['users', sprintf('user_%s', $user->_id)])->flush();
        return $user;
    }

    public function delete($rootValue, array $args): User
    {
        $validator = Validator::make($args, [
            '_id' => 'required',
        ]);
        if ($validator->fails()) {
            throw new GraphqlException(
                implode(',', $validator->messages()->all()),
                [
                    'type' => 'user',
                    'category' => 'user_delete',
                    'reason' => 'validation_error',
                ]
            );
        }

        $user = User::find($args['_id']);
        if ($user === null) {
            throw new GraphqlException('User not found', [
                'type' => 'user',
                'category' => 'user_delete',
                'reason' => 'Not found',
            ]);
        }

        //-- Delete user related items
        //@TODO Temporary disable delete related items vie event subscriber
        //@TODO Should add later on support of delete user event subscriber
        //DeleteUser::dispatch($user);


        //-- Delete linked posts
       Post::where('user_id', $user->_id)->delete();

        //-- Delete linked messages
        Message::where('user_id', $user->_id)->get();
        $user->delete();

        Cache::tags(['users', sprintf('user_%s', $user->_id)])->flush();
        return $user;
    }
}
