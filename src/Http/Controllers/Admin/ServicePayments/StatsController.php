<?php

namespace Jiny\Service\Http\Controllers\Admin\ServicePayments;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\ServicePayment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatsController extends Controller
{
    public function __invoke(Request $request)
    {
        $period = $request->input('period', '30days');
        $status = $request->input('status');
        $payment_method = $request->input('payment_method');

        // 기간 설정
        $startDate = $this->getStartDate($period);
        $endDate = now();

        $query = ServicePayment::whereBetween('service_payments.created_at', [$startDate, $endDate]);

        if ($status) {
            $query->where('status', $status);
        }

        if ($payment_method) {
            $query->where('payment_method', $payment_method);
        }

        // 일별 결제 통계
        $dailyStats = $query->clone()
                           ->selectRaw('strftime("%Y-%m-%d", service_payments.created_at) as date,
                                       COUNT(*) as count,
                                       SUM(final_amount) as total_amount,
                                       AVG(final_amount) as avg_amount')
                           ->groupBy('date')
                           ->orderBy('date')
                           ->get();

        // 상태별 통계
        $statusStats = $query->clone()
                            ->selectRaw('status, COUNT(*) as count, SUM(final_amount) as total_amount')
                            ->groupBy('status')
                            ->orderBy('count', 'desc')
                            ->get();

        // 결제 방법별 통계
        $methodStats = $query->clone()
                            ->selectRaw('payment_method, COUNT(*) as count, SUM(final_amount) as total_amount')
                            ->groupBy('payment_method')
                            ->orderBy('count', 'desc')
                            ->get();

        // 결제 유형별 통계
        $typeStats = $query->clone()
                          ->selectRaw('payment_type, COUNT(*) as count, SUM(final_amount) as total_amount')
                          ->groupBy('payment_type')
                          ->orderBy('count', 'desc')
                          ->get();

        // 서비스별 통계 (상위 10개)
        $serviceStats = $query->clone()
                             ->join('services', 'service_payments.service_id', '=', 'services.id')
                             ->selectRaw('services.title, COUNT(*) as count, SUM(service_payments.final_amount) as total_amount')
                             ->groupBy('services.id', 'services.title')
                             ->orderBy('total_amount', 'desc')
                             ->limit(10)
                             ->get();

        // 시간대별 통계
        $hourlyStats = $query->clone()
                            ->selectRaw('strftime("%H", service_payments.created_at) as hour, COUNT(*) as count')
                            ->groupBy('hour')
                            ->orderBy('hour')
                            ->get();

        // 월별 수익 통계 (최근 12개월)
        $monthlyRevenue = ServicePayment::where('status', 'completed')
                                       ->where('created_at', '>=', now()->subMonths(12))
                                       ->selectRaw('strftime("%Y-%m", created_at) as month,
                                                   SUM(final_amount) as revenue,
                                                   COUNT(*) as count')
                                       ->groupBy('month')
                                       ->orderBy('month')
                                       ->get();

        // 환불 통계
        $refundStats = ServicePayment::whereBetween('service_payments.created_at', [$startDate, $endDate])
                                    ->whereIn('status', ['refunded', 'partially_refunded'])
                                    ->selectRaw('
                                        status,
                                        COUNT(*) as count,
                                        SUM(refunded_amount) as total_refunded,
                                        AVG(refunded_amount) as avg_refunded
                                    ')
                                    ->groupBy('status')
                                    ->get();

        // 실패 원인별 통계
        $failureStats = ServicePayment::whereBetween('service_payments.created_at', [$startDate, $endDate])
                                     ->where('status', 'failed')
                                     ->selectRaw('failure_code, COUNT(*) as count')
                                     ->whereNotNull('failure_code')
                                     ->groupBy('failure_code')
                                     ->orderBy('count', 'desc')
                                     ->limit(10)
                                     ->get();

        return view('jiny-service::admin.service_payments.stats', compact(
            'dailyStats',
            'statusStats',
            'methodStats',
            'typeStats',
            'serviceStats',
            'hourlyStats',
            'monthlyRevenue',
            'refundStats',
            'failureStats',
            'period',
            'status',
            'payment_method',
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
        $status = $request->input('status');

        $startDate = $this->getStartDate($period);
        $endDate = now();

        $query = ServicePayment::with(['serviceUser', 'service'])
                              ->whereBetween('service_payments.created_at', [$startDate, $endDate]);

        if ($status) {
            $query->where('status', $status);
        }

        $payments = $query->orderBy('created_at', 'desc')->get();

        if ($format === 'csv') {
            return $this->exportToCsv($payments);
        }

        return $this->exportToJson($payments);
    }

    private function exportToCsv($payments)
    {
        $filename = 'payment_stats_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($payments) {
            $file = fopen('php://output', 'w');

            // CSV 헤더
            fputcsv($file, [
                'ID',
                '결제UUID',
                '거래ID',
                '사용자UUID',
                '사용자이름',
                '사용자이메일',
                '서비스',
                '결제금액',
                '최종금액',
                '통화',
                '결제방법',
                '결제유형',
                '상태',
                '청구주기',
                '환불금액',
                '결제일시',
                '생성일시'
            ]);

            // 데이터 행
            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->id,
                    $payment->payment_uuid,
                    $payment->transaction_id,
                    $payment->user_uuid,
                    $payment->serviceUser->user_name ?? '',
                    $payment->serviceUser->user_email ?? '',
                    $payment->service->title ?? '',
                    $payment->amount,
                    $payment->final_amount,
                    $payment->currency,
                    $payment->payment_method,
                    $payment->payment_type,
                    $payment->status,
                    $payment->billing_cycle,
                    $payment->refunded_amount,
                    $payment->paid_at ? $payment->paid_at->format('Y-m-d H:i:s') : '',
                    $payment->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportToJson($payments)
    {
        $filename = 'payment_stats_' . now()->format('Y-m-d_H-i-s') . '.json';

        return response()->json($payments, 200, [
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }
}