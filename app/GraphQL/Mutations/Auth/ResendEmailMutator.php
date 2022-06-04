<?php

namespace App\GraphQL\Mutations\Auth;

use App\Exceptions\GraphQL\GraphqlException;
use App\Models\User;
use Illuminate\Http\Response;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ResendEmailMutator
{
    private const RESEND_STATUS = 'RESEND';

    public function resolve($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user = User::where(['email' => $args['email']])->first();
        if ($user === null) {
            throw new GraphqlException(sprintf('User with email %s was not found', $args['email']), [
                'type' => 'Resend confirmation email',
                'category' => 'resend_email',
                'reason' => 'Not found',
                'code' => Response::HTTP_NOT_FOUND,
            ]);
        }
        //-- Resend registration confirmation message
        $this->sendEmail($user);
        return [
            'status' => self::RESEND_STATUS,
            'message' => 'Successfully resent',
        ];
    }
}
