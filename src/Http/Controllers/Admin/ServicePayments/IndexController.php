<?php

namespace Jiny\Service\Http\Controllers\Admin\ServicePayments;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\ServicePayment;
use Jiny\Service\Models\Service;

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        // 검색 및 필터 파라미터
        $search = $request->get('search');
        $status = $request->get('status');
        $payment_method = $request->get('payment_method');
        $payment_type = $request->get('payment_type');
        $service_id = $request->get('service_id');
        $date_from = $request->get('date_from');
        $date_to = $request->get('date_to');

        // 기본 쿼리
        $query = ServicePayment::with(['serviceUser', 'service'])
                              ->orderBy('created_at', 'desc');

        // 검색 조건
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('user_uuid', 'like', "%{$search}%")
                  ->orWhere('payment_uuid', 'like', "%{$search}%")
                  ->orWhere('transaction_id', 'like', "%{$search}%")
                  ->orWhere('order_id', 'like', "%{$search}%")
                  ->orWhereHas('serviceUser', function($subQ) use ($search) {
                      $subQ->where('user_email', 'like', "%{$search}%")
                           ->orWhere('user_name', 'like', "%{$search}%");
                  });
            });
        }

        // 상태 필터
        if ($status) {
            $query->where('status', $status);
        }

        // 결제 방법 필터
        if ($payment_method) {
            $query->where('payment_method', $payment_method);
        }

        // 결제 유형 필터
        if ($payment_type) {
            $query->where('payment_type', $payment_type);
        }

        // 서비스 필터
        if ($service_id) {
            $query->where('service_id', $service_id);
        }

        // 날짜 범위 필터
        if ($date_from) {
            $query->whereDate('created_at', '>=', $date_from);
        }
        if ($date_to) {
            $query->whereDate('created_at', '<=', $date_to);
        }

        // 페이지네이션
        $payments = $query->paginate(20);

        // 통계 데이터
        $stats = $this->getStatistics($request);

        // 필터용 데이터
        $services = Service::where('enable', true)->orderBy('title')->get();
        $statusOptions = [
            'pending' => '결제 대기',
            'processing' => '결제 진행 중',
            'completed' => '결제 완료',
            'failed' => '결제 실패',
            'cancelled' => '결제 취소',
            'refunded' => '환불 완료',
            'partially_refunded' => '부분 환불'
        ];
        $paymentMethodOptions = [
            'credit_card' => '신용카드',
            'debit_card' => '체크카드',
            'bank_transfer' => '계좌이체',
            'virtual_account' => '가상계좌',
            'mobile_payment' => '모바일결제',
            'crypto' => '암호화폐',
            'paypal' => '페이팔',
            'stripe' => '스트라이프',
            'other' => '기타'
        ];
        $paymentTypeOptions = [
            'subscription' => '정기 구독',
            'one_time' => '일회성 결제',
            'upgrade' => '업그레이드',
            'extension' => '연장',
            'late_fee' => '연체료',
            'setup_fee' => '설치비'
        ];

        return view('jiny-service::admin.service_payments.index', compact(
            'payments',
            'stats',
            'services',
            'statusOptions',
            'paymentMethodOptions',
            'paymentTypeOptions'
        ));
    }

    private function getStatistics(Request $request)
    {
        $baseQuery = ServicePayment::query();

        // 필터 적용
        if ($request->get('service_id')) {
            $baseQuery->where('service_id', $request->get('service_id'));
        }
        if ($request->get('date_from')) {
            $baseQuery->whereDate('created_at', '>=', $request->get('date_from'));
        }
        if ($request->get('date_to')) {
            $baseQuery->whereDate('created_at', '<=', $request->get('date_to'));
        }

        $total = $baseQuery->count();
        $completed = $baseQuery->clone()->where('status', 'completed')->count();
        $pending = $baseQuery->clone()->where('status', 'pending')->count();
        $failed = $baseQuery->clone()->where('status', 'failed')->count();
        $refunded = $baseQuery->clone()->whereIn('status', ['refunded', 'partially_refunded'])->count();

        $totalRevenue = $baseQuery->clone()->where('status', 'completed')->sum('final_amount');
        $refundedAmount = $baseQuery->clone()->whereIn('status', ['refunded', 'partially_refunded'])->sum('refunded_amount');
        $averagePayment = $baseQuery->clone()->where('status', 'completed')->avg('final_amount');

        $thisMonth = $baseQuery->clone()
                              ->whereMonth('created_at', now()->month)
                              ->whereYear('created_at', now()->year)
                              ->count();

        $lastMonth = $baseQuery->clone()
                              ->whereMonth('created_at', now()->subMonth()->month)
                              ->whereYear('created_at', now()->subMonth()->year)
                              ->count();

        $monthlyGrowth = $lastMonth > 0 ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1) : 0;

        return [
            'total' => $total,
            'completed' => $completed,
            'pending' => $pending,
            'failed' => $failed,
            'refunded' => $refunded,
            'total_revenue' => $totalRevenue,
            'refunded_amount' => $refundedAmount,
            'average_payment' => $averagePayment ?: 0,
            'this_month' => $thisMonth,
            'last_month' => $lastMonth,
            'monthly_growth' => $monthlyGrowth,
            'success_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
            'failure_rate' => $total > 0 ? round(($failed / $total) * 100, 1) : 0,
        ];
    }
}