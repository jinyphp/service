<?php

namespace Jiny\Service\Http\Controllers\Admin\ServiceSubscriptionLog;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\ServiceSubscriptionLog;

class ShowController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $log = ServiceSubscriptionLog::with([
            'serviceUser.service',
            'service'
        ])->findOrFail($id);

        // 동일 사용자의 관련 로그들 (최근 10개)
        $relatedLogs = ServiceSubscriptionLog::where('user_uuid', $log->user_uuid)
                                            ->where('id', '!=', $log->id)
                                            ->orderBy('created_at', 'desc')
                                            ->limit(10)
                                            ->get();

        // 같은 액션의 최근 로그들 (최근 5개)
        $similarLogs = ServiceSubscriptionLog::where('action', $log->action)
                                            ->where('id', '!=', $log->id)
                                            ->orderBy('created_at', 'desc')
                                            ->limit(5)
                                            ->get();

        return view('jiny-service::admin.service_subscription_log.show', compact(
            'log',
            'relatedLogs',
            'similarLogs'
        ));
    }
}