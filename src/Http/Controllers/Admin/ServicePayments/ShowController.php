<?php

namespace Jiny\Service\Http\Controllers\Admin\ServicePayments;

use App\Http\Controllers\Controller;
use Jiny\Service\Models\ServicePayment;

class ShowController extends Controller
{
    public function __invoke(ServicePayment $payment)
    {
        // 관련 데이터 로드
        $payment->load(['serviceUser', 'service']);

        // 동일 사용자의 최근 결제 내역 (최대 10개)
        $relatedPayments = ServicePayment::where('user_uuid', $payment->user_uuid)
                                        ->where('id', '!=', $payment->id)
                                        ->with(['service'])
                                        ->orderBy('created_at', 'desc')
                                        ->limit(10)
                                        ->get();

        // 동일 서비스의 최근 결제 내역 (최대 10개)
        $servicePayments = ServicePayment::where('service_id', $payment->service_id)
                                        ->where('id', '!=', $payment->id)
                                        ->with(['serviceUser'])
                                        ->orderBy('created_at', 'desc')
                                        ->limit(10)
                                        ->get();

        // 환불 가능 여부 체크
        $canRefund = $payment->status === 'completed' && $payment->refundable_amount > 0;

        // 재시도 가능 여부 체크
        $canRetry = $payment->status === 'failed' && $payment->retry_count < 3;

        return view('jiny-service::admin.service_payments.show', compact(
            'payment',
            'relatedPayments',
            'servicePayments',
            'canRefund',
            'canRetry'
        ));
    }
}