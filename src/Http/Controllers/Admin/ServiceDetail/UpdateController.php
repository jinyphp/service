<?php

namespace Jiny\Service\Http\Controllers\Admin\ServiceDetail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\SiteService;
use Jiny\Service\Models\ServicePlanDetail;

class UpdateController extends Controller
{
    public function __invoke(Request $request, $serviceId, $detailId)
    {
        $service = SiteService::findOrFail($serviceId);

        $detail = ServicePlanDetail::where('service_id', $serviceId)
                     ->findOrFail($detailId);

        $validated = $request->validate([
            'detail_type' => 'required|string|in:feature,limitation,requirement,benefit,support,addon,specification,policy',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'value' => 'nullable|string',
            'value_type' => 'required|string|in:text,number,boolean,json,html',
            'unit' => 'nullable|string|max:50',
            'is_highlighted' => 'boolean',
            'show_in_comparison' => 'boolean',
            'show_in_summary' => 'boolean',
            'conditions' => 'nullable|array',
            'tooltip' => 'nullable|string',
            'link_url' => 'nullable|url',
            'link_text' => 'nullable|string|max:255',
            'category' => 'nullable|string|in:core,addon,support,limitation,specification,policy',
            'group_name' => 'nullable|string|max:255',
            'group_order' => 'required|integer|min:0',
            'pos' => 'required|integer|min:0',
            'enable' => 'boolean',
        ]);

        // 기본값 설정
        $validated['is_highlighted'] = $request->boolean('is_highlighted');
        $validated['show_in_comparison'] = $request->boolean('show_in_comparison', true);
        $validated['show_in_summary'] = $request->boolean('show_in_summary');
        $validated['enable'] = $request->boolean('enable', true);

        // JSON 값 처리
        if ($validated['value_type'] === 'json' && !empty($validated['value'])) {
            // JSON 형태인지 확인하고 파싱
            $jsonValue = json_decode($validated['value'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $validated['value'] = $validated['value']; // 이미 JSON 문자열
            } else {
                // 배열 형태로 입력된 경우 (예: 태그 입력)
                $validated['value'] = json_encode(explode(',', $validated['value']));
            }
        }

        // Boolean 값 처리
        if ($validated['value_type'] === 'boolean' && !empty($validated['value'])) {
            $validated['value'] = filter_var($validated['value'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ? 'true' : 'false';
        }

        $detail->update($validated);

        return redirect()
            ->route('admin.site.services.detail.index', $serviceId)
            ->with('success', '서비스 상세 정보가 성공적으로 수정되었습니다.');
    }
}