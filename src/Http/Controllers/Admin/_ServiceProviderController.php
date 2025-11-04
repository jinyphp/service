<?php

namespace Jiny\Service\Http\Controllers\Admin;

use Jiny\Service\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Http\Request;

class ServiceProviderController extends BaseAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = ServiceProvider::class;
        $this->viewPath = 'jiny-service::admin.service-providers';
        $this->routePrefix = 'service-providers';
        $this->title = '서비스 제공자';
    }

    protected function getValidationRules($item = null): array
    {
        $providerCodeRule = 'required|string|max:20';
        if ($item) {
            $providerCodeRule .= '|unique:service_providers,provider_code,' . $item->id;
        } else {
            $providerCodeRule .= '|unique:service_providers,provider_code';
        }

        return [
            'user_id' => 'required|exists:users,id',
            'provider_code' => $providerCodeRule,
            'status' => 'required|in:active,inactive,suspended',
            'specializations' => 'nullable|json',
            'service_areas' => 'nullable|json',
            'available_hours' => 'nullable|json',
            'vehicle_info' => 'nullable|json',
            'equipment_owned' => 'nullable|json',
            'emergency_contact' => 'nullable|json',
            'id_verification_status' => 'required|in:pending,verified,rejected',
            'background_check_status' => 'required|in:pending,passed,failed',
            'insurance_verified' => 'boolean'
        ];
    }

    protected function getSearchFields(): array
    {
        return ['provider_code'];
    }

    public function index(Request $request)
    {
        $query = $this->model::with('user');

        // 검색 기능
        if ($request->has('search') && $request->search) {
            $searchFields = $this->getSearchFields();
            $query->where(function($q) use ($request, $searchFields) {
                foreach ($searchFields as $field) {
                    $q->orWhere($field, 'like', '%' . $request->search . '%');
                }
                // 사용자 이름으로도 검색
                $q->orWhereHas('user', function($userQuery) use ($request) {
                    $userQuery->where('name', 'like', '%' . $request->search . '%')
                             ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            });
        }

        // 상태 필터
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // 검증 상태 필터
        if ($request->has('verification_status') && $request->verification_status) {
            switch ($request->verification_status) {
                case 'fully_verified':
                    $query->where('id_verification_status', 'verified')
                          ->where('background_check_status', 'passed')
                          ->where('insurance_verified', true);
                    break;
                case 'partially_verified':
                    $query->where(function($q) {
                        $q->where('id_verification_status', '!=', 'verified')
                          ->orWhere('background_check_status', '!=', 'passed')
                          ->orWhere('insurance_verified', false);
                    });
                    break;
                case 'unverified':
                    $query->where('id_verification_status', 'pending')
                          ->where('background_check_status', 'pending')
                          ->where('insurance_verified', false);
                    break;
            }
        }

        $items = $query->orderBy('id', 'desc')->paginate(20);

        return view("{$this->viewPath}.index", [
            'items' => $items,
            'title' => $this->title,
            'routePrefix' => $this->routePrefix,
            'searchValue' => $request->search,
            'selectedStatus' => $request->status,
            'selectedVerificationStatus' => $request->verification_status
        ]);
    }

    public function create()
    {
        // 서비스 제공자가 아닌 사용자만 선택 가능
        $users = User::whereDoesntHave('serviceProvider')->orderBy('name')->get();

        return view("{$this->viewPath}.create", [
            'users' => $users,
            'title' => $this->title,
            'routePrefix' => $this->routePrefix
        ]);
    }

    public function edit($id)
    {
        $item = $this->model::with('user')->findOrFail($id);
        $users = User::where('id', $item->user_id)
                    ->orWhereDoesntHave('serviceProvider')
                    ->orderBy('name')->get();

        return view("{$this->viewPath}.edit", [
            'item' => $item,
            'users' => $users,
            'title' => $this->title,
            'routePrefix' => $this->routePrefix
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->getValidationRules());

        // JSON 필드 처리
        $jsonFields = ['specializations', 'service_areas', 'available_hours', 'vehicle_info', 'equipment_owned', 'emergency_contact'];
        foreach ($jsonFields as $field) {
            if ($request->has($field) && is_string($request->$field)) {
                $validated[$field] = json_decode($request->$field, true);
            }
        }

        $item = $this->model::create($validated);

        return redirect()->route("admin.{$this->routePrefix}.show", $item->id)
            ->with('success', $this->title . ' 항목이 성공적으로 생성되었습니다.');
    }

    public function update(Request $request, $id)
    {
        $item = $this->model::findOrFail($id);
        $validated = $request->validate($this->getValidationRules($item));

        // JSON 필드 처리
        $jsonFields = ['specializations', 'service_areas', 'available_hours', 'vehicle_info', 'equipment_owned', 'emergency_contact'];
        foreach ($jsonFields as $field) {
            if ($request->has($field) && is_string($request->$field)) {
                $validated[$field] = json_decode($request->$field, true);
            }
        }

        $item->update($validated);

        return redirect()->route("admin.{$this->routePrefix}.show", $item->id)
            ->with('success', $this->title . ' 항목이 성공적으로 수정되었습니다.');
    }

    /**
     * 신원 확인 승인
     */
    public function verifyIdentity($id)
    {
        $provider = $this->model::findOrFail($id);
        $provider->update(['id_verification_status' => 'verified']);

        return response()->json([
            'success' => true,
            'message' => '신원 확인이 승인되었습니다.'
        ]);
    }

    /**
     * 신원 확인 거절
     */
    public function rejectIdentity(Request $request, $id)
    {
        $provider = $this->model::findOrFail($id);
        $provider->update(['id_verification_status' => 'rejected']);

        return response()->json([
            'success' => true,
            'message' => '신원 확인이 거절되었습니다.'
        ]);
    }

    /**
     * 백그라운드 체크 통과
     */
    public function passBackgroundCheck($id)
    {
        $provider = $this->model::findOrFail($id);
        $provider->update(['background_check_status' => 'passed']);

        return response()->json([
            'success' => true,
            'message' => '백그라운드 체크가 통과되었습니다.'
        ]);
    }

    /**
     * 백그라운드 체크 실패
     */
    public function failBackgroundCheck(Request $request, $id)
    {
        $provider = $this->model::findOrFail($id);
        $provider->update(['background_check_status' => 'failed']);

        return response()->json([
            'success' => true,
            'message' => '백그라운드 체크가 실패로 처리되었습니다.'
        ]);
    }

    /**
     * 보험 확인 승인
     */
    public function verifyInsurance($id)
    {
        $provider = $this->model::findOrFail($id);
        $provider->update(['insurance_verified' => true]);

        return response()->json([
            'success' => true,
            'message' => '보험이 확인되었습니다.'
        ]);
    }

    /**
     * 제공자 계정 정지
     */
    public function suspend(Request $request, $id)
    {
        $provider = $this->model::findOrFail($id);
        $provider->update(['status' => 'suspended']);

        return redirect()->route("admin.{$this->routePrefix}.show", $id)
            ->with('success', '서비스 제공자 계정이 정지되었습니다.');
    }

    /**
     * 제공자 계정 활성화
     */
    public function activate($id)
    {
        $provider = $this->model::findOrFail($id);

        // 완전히 검증된 제공자만 활성화 가능
        if (!$provider->is_verified) {
            return redirect()->back()->with('error', '검증이 완료되지 않은 제공자는 활성화할 수 없습니다.');
        }

        $provider->update(['status' => 'active']);

        return redirect()->route("admin.{$this->routePrefix}.show", $id)
            ->with('success', '서비스 제공자 계정이 활성화되었습니다.');
    }

    /**
     * 제공자 통계
     */
    public function statistics()
    {
        $stats = [
            'total' => $this->model::count(),
            'active' => $this->model::where('status', 'active')->count(),
            'inactive' => $this->model::where('status', 'inactive')->count(),
            'suspended' => $this->model::where('status', 'suspended')->count(),
            'fully_verified' => $this->model::verified()->count(),
            'pending_verification' => $this->model::where('id_verification_status', 'pending')->count(),
            'pending_background' => $this->model::where('background_check_status', 'pending')->count(),
            'uninsured' => $this->model::where('insurance_verified', false)->count()
        ];

        return view("{$this->viewPath}.statistics", [
            'stats' => $stats,
            'title' => '제공자 통계'
        ]);
    }

    /**
     * 제공자 코드 자동 생성
     */
    public function generateProviderCode()
    {
        do {
            $code = 'SP' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while ($this->model::where('provider_code', $code)->exists());

        return response()->json(['provider_code' => $code]);
    }
}