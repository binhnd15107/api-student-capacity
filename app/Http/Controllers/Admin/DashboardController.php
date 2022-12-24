<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contest;
use App\Models\Result;
use App\Models\ResultCapacity;
use App\Models\Round;
use App\Services\Modules\MContest\MContestInterface;
use App\Services\Modules\MTeam\MTeamInterface;
use App\Services\Modules\MUser\MUserInterface;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class DashboardController extends Controller
{
    public function __construct(
        private MContestInterface $contest,
        private Carbon $carbon,
        private CarbonPeriod $carbonPeriod,
        private MUserInterface $user,
        private MTeamInterface $team,
    ) {
    }

    public function index(Request $request)
    {
        $totalContestGoingOn = $this->contest->getCountContestGoingOn();

        $totalTeamActive = $this->team->getTotalTeamActive();

        $listRankCapacity = $this->getRankCapacity($request);

        $listRankContest = $this->getRankContest($request);

        $totalStudentAccount = $this->user->getTotalStudentAcount();

        $dt = $this->carbon::now('Asia/Ho_Chi_Minh');
        $dt2 = $this->carbon::now('Asia/Ho_Chi_Minh');
        $dt3 = $this->carbon::now('Asia/Ho_Chi_Minh');
        $timeNow   = $this->carbon::now('Asia/Ho_Chi_Minh')->toDateTimeString();
        $contests = $this->contest->getContestMapSubDays($dt->subDays(2)->toDateTimeString());
        $contestsDealineNow = $this->contest->getContestByDateNow($this->carbon::now('Asia/Ho_Chi_Minh'));
        $period = $this->carbonPeriod::create($dt3->subDays(2)->toDateTimeString(), $dt2->addDays(7)->toDateTimeString());
        $dataContest = Contest::with(['rounds:id,name,contest_id'])
                        ->where('type', config('util.TYPE_CONTEST'))
                        ->orderByDesc('created_at')
                        ->get(['id','name']);
        return view('dashboard.index', compact(
            'listRankCapacity',
            'listRankContest',
            'totalContestGoingOn',
            'totalTeamActive',
            'totalStudentAccount',
            'contests',
            'period',
            'timeNow',
            'contestsDealineNow',
            'dataContest'
        ));
    }

    public function chartCompetity(Request $request)
    {
        $start = date($request->startDate);
        $end = date($request->endDate);
        $lstContest = Contest::with('teams')
            ->where('status', config('util.CONTEST_STATUS_GOING_ON'))
            ->whereBetween('register_deadline', [$start, $end])
            ->orderByDesc('id')
            ->get();
        return response()->json([
            'status' => true,
            'data' => $lstContest
        ]);
    }

    public function getRankCapacity($params){
        $limit = $params->get('limit',10);
        return ResultCapacity::with(['user:id,name,avatar,email'])
                ->selectRaw('sum(scores) as total_scores,id,user_id')
                ->groupBy('user_id')
                ->orderByDesc('total_scores')
                ->paginate($limit);
    }

    public function getRankContest(Request $request){

        $contestID = $request->get('contest_id',
            Contest::where('type', config('util.TYPE_CONTEST'))
            ->where('register_deadline','<',now())
            ->orderByDesc('register_deadline')
            ->first()
            ->id
        );
       $results = Round::with(['results'=>function($query){
            $query->with('team:id,name,image')
            ->orderByDesc('point')
            ->orderByDesc('updated_at');
        }])
            ->where('contest_id',$contestID)
            ->orderByDesc('created_at')
            ->get(['id','name'])
            ->map(function ($item) {
              $result = $item->results->take(10);
              unset($item->results);
              $item['results'] =  $result ;
                return $item;
            });
        if($request->has('contest_id')){
            return response()->json([
                'status' => true,
                'data' => $results
            ]);
        }
        $request['old_contest'] = $contestID ;
        return  $results ? $results : false;
     }
}
