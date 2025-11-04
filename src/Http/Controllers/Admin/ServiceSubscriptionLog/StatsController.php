<?php

namespace Jiny\Service\Http\Controllers\Admin\ServiceSubscriptionLog;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\ServiceSubscriptionLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatsController extends Controller
{
    public function __invoke(Request $request)
    {
        $period = $request->input('period', '30days');
        $action = $request->input('action');

        // 기간 설정
        $startDate = $this->getStartDate($period);
        $endDate = now();

        $query = ServiceSubscriptionLog::whereBetween('service_subscription_logs.created_at', [$startDate, $endDate]);

        if ($action) {
            $query->where('action', $action);
        }

        // 일별 통계
        $dailyStats = $query->clone()
                           ->selectRaw('strftime("%Y-%m-%d", service_subscription_logs.created_at) as date, COUNT(*) as count')
                           ->groupBy('date')
                           ->orderBy('date')
                           ->get();

        // 액션별 통계
        $actionStats = $query->clone()
                            ->selectRaw('action, COUNT(*) as count')
                            ->groupBy('action')
                            ->orderBy('count', 'desc')
                            ->get();

        // 결과별 통계
        $resultStats = $query->clone()
                            ->selectRaw('result, COUNT(*) as count')
                            ->groupBy('result')
                            ->get();

        // 처리자별 통계
        $processedByStats = $query->clone()
                                 ->selectRaw('processed_by, COUNT(*) as count')
                                 ->groupBy('processed_by')
                                 ->get();

        // 서비스별 통계
        $serviceStats = $query->clone()
                             ->join('services', 'service_subscription_logs.service_id', '=', 'services.id')
                             ->selectRaw('services.title, COUNT(*) as count')
                             ->groupBy('services.id', 'services.title')
                             ->orderBy('count', 'desc')
                             ->limit(10)
                             ->get();

        // 시간대별 통계
        $hourlyStats = $query->clone()
                            ->selectRaw('strftime("%H", service_subscription_logs.created_at) as hour, COUNT(*) as count')
                            ->groupBy('hour')
                            ->orderBy('hour')
                            ->get();

        // 금액 통계 (결제 관련 액션만)
        $paymentStats = ServiceSubscriptionLog::whereBetween('service_subscription_logs.created_at', [$startDate, $endDate])
                                             ->whereIn('action', ['payment_success', 'payment_failed', 'refund'])
                                             ->selectRaw('
                                                 action,
                                                 COUNT(*) as count,
                                                 SUM(amount) as total_amount,
                                                 AVG(amount) as avg_amount
                                             ')
                                             ->groupBy('action')
                                             ->get();

        return view('jiny-service::admin.service_subscription_log.stats', compact(
            'dailyStats',
            'actionStats',
            'resultStats',
            'processedByStats',
            'serviceStats',
            'hourlyStats',
            'paymentStats',
            'period',
            'action',
            'startDate',
            'endDate'
        ));
    }

    private function getStartDate($period)
    {
        return match($period) {
            '7days' => now()->subDays(7),
            '30days' => now()->subDays(30),
            '3months' => now()->subMonths(3),
            '6months' => now()->subMonths(6),
            '1year' => now()->subYear(),
            default => now()->subDays(30)
        };
    }

    public function export(Request $request)
    {
        $period = $request->input('period', '30days');
        $format = $request->input('format', 'csv');
        $action = $request->input('action');

        $startDate = $this->getStartDate($period);
        $endDate = now();

        $query = ServiceSubscriptionLog::with(['serviceUser', 'service'])
                                       ->whereBetween('service_subscription_logs.created_at', [$startDate, $endDate]);

        if ($action) {
            $query->where('action', $action);
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        if ($format === 'csv') {
            return $this->exportToCsv($logs);
        }

        return $this->exportToJson($logs);
    }

    private function exportToCsv($logs)
    {
        $filename = 'subscription_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');

            // CSV 헤더
            fputcsv($file, [
                'ID',
                '사용자UUID',
                '사용자이름',
                '사용자이메일',
                '서비스',
                '액션',
                '액션제목',
                '액션설명',
                '이전상태',
                '이후상태',
                '금액',
                '처리자',
                '결과',
                '생성일시'
            ]);

            // 데이터 행
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user_uuid,
                    $log->serviceUser->user_name ?? '',
                    $log->serviceUser->user_email ?? '',
                    $log->service->title ?? '',
                    $log->action,
                    $log->action_title,
                    $log->action_description,
                    $log->status_before,
                    $log->status_after,
                    $log->amount,
                    $log->processed_by,
                    $log->result,
                    $log->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportToJson($logs)
    {
        $filename = 'subscription_logs_' . now()->format('Y-m-d_H-i-s') . '.json';

        return response()->json($logs, 200, [
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }
}