<?php

namespace App\GraphQL\Queries;

use App\Models\Message;
use App\Models\User;
use DateTime;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Statistics
{
    /**
     * Return a value for the field.
     *
     * @param null $rootValue Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param mixed[] $args The arguments that were passed into the field.
     * @param \Nuwave\Lighthouse\Support\Contracts\GraphQLContext $context Arbitrary data that is shared between all fields of a single query.
     * @param \GraphQL\Type\Definition\ResolveInfo $resolveInfo Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @return mixed
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): array
    {

        $arrStatistics = $this->getUsersStatisticData();

        return [
            'users_statistics' => [
                'asixX' => 'Month',
                'asixY' => 'Number',
                'columns' => array_keys($arrStatistics),
                'values' => array_values($arrStatistics)
            ],
            'messages_statistics' => [
                'user'=>Auth::user(),
                'messages_sent'=>[
                    'y'=>Message::where('from_user_id',Auth::id())->count(),
                    'name'=>'Messages sent',
                    'color'=>'#3E9D5D',
                ],
                'messages_received'=>[
                    'y'=>Message::where('user_id',Auth::id())->count(),
                    'name'=>'Messages received',
                    'color'=>'#CE5916',
                ],
            ],
        ];
    }

    private function getUsersStatisticData()
    {
        $arrMonths = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December',
        ];
        $arrStatistics = array_fill_keys(array_keys(array_flip($arrMonths)), 0);
        User::where(
            'created_at', '>=', new DateTime('-1 year')
        )->groupBy('created_at')->pluck('created_at')->each(function ($date) use (&$arrStatistics) {
            $year=Carbon::createFromFormat('Y-m-d H:i:s', $date)->year;
            $yearCurrent=Carbon::now()->year;
            if($year===$yearCurrent){
                $month=Carbon::parse($date)->format('F');
                if(isset($arrStatistics[$month])){
                    $arrStatistics[$month]+=1;
                }
            }
        });

        array_walk($arrStatistics, function ($value, $key) use (&$arrStatistics) {
            $arrStatistics[$key] = [
                'y' => $value,
                'key' => $key,
                'name' => $key,
            ];
        });

        return $arrStatistics;
    }

}
