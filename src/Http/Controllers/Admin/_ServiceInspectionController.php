<?php

namespace Jiny\Service\Http\Controllers\Admin;

use Jiny\Service\Models\ServiceInspection;
use Jiny\Service\Models\Appointment;
use Jiny\Service\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Http\Request;

class ServiceInspectionController extends BaseAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = ServiceInspection::class;
        $this->viewPath = 'jiny-service::admin.service-inspections';
        $this->routePrefix = 'service-inspections';
        $this->title = '서비스 검수 관리';
    }

    protected function getValidationRules($item = null): array
    {
        return [
            'appointment_id' => 'required|exists:appointments,id',
            'customer_id' => 'required|exists:users,id',
            'provider_id' => 'required|exists:service_providers,id',
            'inspection_status' => 'required|in:pending,approved,rejected,conditional',
            'overall_rating' => 'nullable|numeric|min:0|max:5',
            'quality_ratings' => 'nullable|json',
            'feedback' => 'nullable|string',
            'rejection_reasons' => 'nullable|json',
            'customer_signature' => 'nullable|string',
            'photo_evidence' => 'nullable|json',
            'inspector_notes' => 'nullable|string',
            'deadline' => 'nullable|date',
            'completed_at' => 'nullable|date'
        ];
    }

    protected function getSearchFields(): array
    {
        return ['feedback', 'inspector_notes'];
    }

    public function index(Request $request)
    {
        $query = $this->model::with(['appointment.service', 'customer', 'provider.user']);

        // 검색 기능
        if ($request->has('search') && $request->search) {
            $searchFields = $this->getSearchFields();
            $query->where(function($q) use ($request, $searchFields) {
                foreach ($searchFields as $field) {
                    $q->orWhere($field, 'like', '%' . $request->search . '%');
                }
                // 고객명으로 검색
                $q->orWhereHas('customer', function($customerQuery) use ($request) {
                    $customerQuery->where('name', 'like', '%' . $request->search . '%')
                                 ->orWhere('email', 'like', '%' . $request->search . '%');
                });
                // 제공자명으로 검색
                $q->orWhereHas('provider.user', function($providerQuery) use ($request) {
                    $providerQuery->where('name', 'like', '%' . $request->search . '%');
                });
            });
        }

        // 상태 필터
        if ($request->has('inspection_status') && $request->inspection_status) {
            $query->where('inspection_status', $request->inspection_status);
        }

        // 마감일 필터
        if ($request->has('deadline_filter') && $request->deadline_filter) {
            switch ($request->deadline_filter) {
                case 'overdue':
                    $query->where('deadline', '<', now())->where('inspection_status', 'pending');
                    break;
                case 'today':
                    $query->whereDate('deadline', today())->where('inspection_status', 'pending');
                    break;
                case 'this_week':
                    $query->whereBetween('deadline', [now(), now()->addWeek()])->where('inspection_status', 'pending');
                    break;
            }
        }

        // 평점 필터
        if ($request->has('rating_filter') && $request->rating_filter) {
            switch ($request->rating_filter) {
                case 'high':
                    $query->where('overall_rating', '>=', 4.0);
                    break;
                case 'medium':
                    $query->whereBetween('overall_rating', [2.0, 3.9]);
                    break;
                case 'low':
                    $query->where('overall_rating', '<', 2.0);
                    break;
            }
        }

        $items = $query->orderBy('deadline', 'asc')->orderBy('created_at', 'desc')->paginate(20);

        return view("{$this->viewPath}.index", [
            'items' => $items,
            'title' => $this->title,
            'routePrefix' => $this->routePrefix,
            'searchValue' => $request->search,
            'selectedStatus' => $request->inspection_status,
            'selectedDeadlineFilter' => $request->deadline_filter,
            'selectedRatingFilter' => $request->rating_filter
        ]);
    }

    public function create()
    {
        $appointments = Appointment::with(['customer', 'service'])
            ->whereDoesntHave('serviceInspections')
            ->whereIn('status', ['completed', 'verified'])
            ->orderBy('actual_end_time', 'desc')
            ->get();

        $providers = ServiceProvider::with('user')->where('status', 'active')->get();

        return view("{$this->viewPath}.create", [
            'appointments' => $appointments,
            'providers' => $providers,
            'title' => $this->title,
            'routePrefix' => $this->routePrefix
        ]);
    }

    public function edit($id)
    {
        $item = $this->model::with(['appointment.service', 'customer', 'provider.user'])->findOrFail($id);
        $appointments = Appointment::with(['customer', 'service'])->whereIn('status', ['completed', 'verified'])->orderBy('actual_end_time', 'desc')->get();
        $providers = ServiceProvider::with('user')->where('status', 'active')->get();

        return view("{$this->viewPath}.edit", [
            'item' => $item,
            'appointments' => $appointments,
            'providers' => $providers,
            'title' => $this->title,
            'routePrefix' => $this->routePrefix
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->getValidationRules());

        // JSON 필드 처리
        $jsonFields = ['quality_ratings', 'rejection_reasons', 'photo_evidence'];
        foreach ($jsonFields as $field) {
            if ($request->has($field) && is_string($request->$field)) {
                $validated[$field] = json_decode($request->$field, true);
            }
        }

        // 자동으로 마감일 설정 (생성일로부터 3일)
        if (!$validated['deadline']) {
            $validated['deadline'] = now()->addDays(3);
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
        $jsonFields = ['quality_ratings', 'rejection_reasons', 'photo_evidence'];
        foreach ($jsonFields as $field) {
            if ($request->has($field) && is_string($request->$field)) {
                $validated[$field] = json_decode($request->$field, true);
            }
        }

        // 상태가 승인/거절로 변경되면 완료 시간 설정
        if (in_array($validated['inspection_status'], ['approved', 'rejected']) && !$item->completed_at) {
            $validated['completed_at'] = now();
        }

        $item->update($validated);

        return redirect()->route("admin.{$this->routePrefix}.show", $item->id)
            ->with('success', $this->title . ' 항목이 성공적으로 수정되었습니다.');
    }

    /**
     * 검수 승인
     */
    public function approve(Request $request, $id)
    {
        $inspection = $this->model::findOrFail($id);

        $request->validate([
            'overall_rating' => 'required|numeric|min:0|max:5',
            'feedback' => 'nullable|string',
            'inspector_notes' => 'nullable|string'
        ]);

        $inspection->approve(
            $request->overall_rating,
            $request->feedback,
            $request->inspector_notes
        );

        return response()->json([
            'success' => true,
            'message' => '검수가 승인되었습니다.'
        ]);
    }

    /**
     * 검수 거절
     */
    public function reject(Request $request, $id)
    {
        $inspection = $this->model::findOrFail($id);

        $request->validate([
            'rejection_reasons' => 'required|array',
            'inspector_notes' => 'nullable|string'
        ]);

        $inspection->reject(
            $request->rejection_reasons,
            $request->inspector_notes
        );

        return response()->json([
            'success' => true,
            'message' => '검수가 거절되었습니다.'
        ]);
    }

    /**
     * 사진 증빙 추가
     */
    public function addPhotoEvidence(Request $request, $id)
    {
        $inspection = $this->model::findOrFail($id);

        $request->validate([
            'photo' => 'required|image|max:5120', // 5MB 제한
            'description' => 'nullable|string|max:255'
        ]);

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('inspections/' . $inspection->id, $filename, 'public');

            $photoData = [
                'filename' => $filename,
                'path' => $path,
                'url' => asset('storage/' . $path),
                'description' => $request->description,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType()
            ];

            $inspection->addPhotoEvidence($photoData);

            return response()->json([
                'success' => true,
                'message' => '사진이 성공적으로 업로드되었습니다.',
                'photo' => $photoData
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => '사진 업로드에 실패했습니다.'
        ], 400);
    }

    /**
     * 마감 임박 검수 목록
     */
    public function overdueInspections()
    {
        $overdueInspections = $this->model::with(['appointment.service', 'customer', 'provider.user'])
            ->where('deadline', '<', now())
            ->where('inspection_status', 'pending')
            ->orderBy('deadline')
            ->get();

        return view("{$this->viewPath}.overdue", [
            'inspections' => $overdueInspections,
            'title' => '마감 임박 검수'
        ]);
    }

    /**
     * 검수 통계
     */
    public function statistics()
    {
        $stats = [
            'total' => $this->model::count(),
            'pending' => $this->model::where('inspection_status', 'pending')->count(),
            'approved' => $this->model::where('inspection_status', 'approved')->count(),
            'rejected' => $this->model::where('inspection_status', 'rejected')->count(),
            'overdue' => $this->model::where('deadline', '<', now())->where('inspection_status', 'pending')->count(),
            'avg_rating' => $this->model::whereNotNull('overall_rating')->avg('overall_rating'),
            'this_month' => $this->model::whereMonth('created_at', now()->month)->count()
        ];

        $stats['approval_rate'] = $stats['total'] > 0
            ? round(($stats['approved'] / ($stats['approved'] + $stats['rejected'])) * 100, 1)
            : 0;

        return view("{$this->viewPath}.statistics", [
            'stats' => $stats,
            'title' => '검수 통계'
        ]);
    }

    /**
     * 검수 보고서 생성
     */
    public function generateReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $inspections = $this->model::with(['appointment.service', 'customer', 'provider.user'])
            ->whereBetween('created_at', [$request->start_date, $request->end_date])
            ->get();

        return view("{$this->viewPath}.report", [
            'inspections' => $inspections,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'title' => '검수 보고서'
        ]);
    }
}