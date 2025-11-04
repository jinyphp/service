<?php

namespace Jiny\Service\Http\Controllers\Admin;

use Jiny\Service\Models\ServiceProgress;
use Jiny\Service\Models\Appointment;
use Jiny\Service\Models\ServiceChecklist;
use Illuminate\Http\Request;

class ServiceProgressController extends BaseAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = ServiceProgress::class;
        $this->viewPath = 'jiny-service::admin.service-progress';
        $this->routePrefix = 'service-progress';
        $this->title = '서비스 진행상황';
    }

    protected function getValidationRules($item = null): array
    {
        return [
            'appointment_id' => 'required|exists:appointments,id',
            'checklist_id' => 'required|exists:service_checklists,id',
            'checklist_item_id' => 'required|string|max:255',
            'status' => 'required|in:pending,in_progress,completed,skipped,failed',
            'started_at' => 'nullable|date',
            'completed_at' => 'nullable|date',
            'quality_score' => 'nullable|numeric|min:0|max:5',
            'provider_notes' => 'nullable|string',
            'evidence_type' => 'nullable|in:photo,signature,note,measurement',
            'evidence_data' => 'nullable|json'
        ];
    }

    protected function getSearchFields(): array
    {
        return ['checklist_item_id', 'provider_notes'];
    }

    public function index(Request $request)
    {
        $query = $this->model::with(['appointment.customer', 'appointment.service', 'checklist']);

        // 검색 기능
        if ($request->has('search') && $request->search) {
            $searchFields = $this->getSearchFields();
            $query->where(function($q) use ($request, $searchFields) {
                foreach ($searchFields as $field) {
                    $q->orWhere($field, 'like', '%' . $request->search . '%');
                }
                // 고객명으로도 검색
                $q->orWhereHas('appointment.customer', function($customerQuery) use ($request) {
                    $customerQuery->where('name', 'like', '%' . $request->search . '%');
                });
            });
        }

        // 상태 필터
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // 예약 필터
        if ($request->has('appointment_id') && $request->appointment_id) {
            $query->where('appointment_id', $request->appointment_id);
        }

        // 체크리스트 필터
        if ($request->has('checklist_id') && $request->checklist_id) {
            $query->where('checklist_id', $request->checklist_id);
        }

        // 날짜 필터
        if ($request->has('date') && $request->date) {
            $query->whereHas('appointment', function($appointmentQuery) use ($request) {
                $appointmentQuery->whereDate('scheduled_date', $request->date);
            });
        }

        $items = $query->orderBy('id', 'desc')->paginate(20);
        $appointments = Appointment::with('customer', 'service')->orderBy('scheduled_date', 'desc')->limit(50)->get();
        $checklists = ServiceChecklist::where('is_active', true)->orderBy('name')->get();

        return view("{$this->viewPath}.index", [
            'items' => $items,
            'appointments' => $appointments,
            'checklists' => $checklists,
            'title' => $this->title,
            'routePrefix' => $this->routePrefix,
            'searchValue' => $request->search,
            'selectedStatus' => $request->status,
            'selectedAppointment' => $request->appointment_id,
            'selectedChecklist' => $request->checklist_id,
            'selectedDate' => $request->date
        ]);
    }

    public function create()
    {
        $appointments = Appointment::with('customer', 'service')->where('status', '!=', 'cancelled')->orderBy('scheduled_date', 'desc')->get();
        $checklists = ServiceChecklist::where('is_active', true)->orderBy('name')->get();

        return view("{$this->viewPath}.create", [
            'appointments' => $appointments,
            'checklists' => $checklists,
            'title' => $this->title,
            'routePrefix' => $this->routePrefix
        ]);
    }

    public function edit($id)
    {
        $item = $this->model::with(['appointment.customer', 'appointment.service', 'checklist'])->findOrFail($id);
        $appointments = Appointment::with('customer', 'service')->where('status', '!=', 'cancelled')->orderBy('scheduled_date', 'desc')->get();
        $checklists = ServiceChecklist::where('is_active', true)->orderBy('name')->get();

        return view("{$this->viewPath}.edit", [
            'item' => $item,
            'appointments' => $appointments,
            'checklists' => $checklists,
            'title' => $this->title,
            'routePrefix' => $this->routePrefix
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->getValidationRules());

        // JSON 필드 처리
        if ($request->has('evidence_data') && is_string($request->evidence_data)) {
            $validated['evidence_data'] = json_decode($request->evidence_data, true);
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
        if ($request->has('evidence_data') && is_string($request->evidence_data)) {
            $validated['evidence_data'] = json_decode($request->evidence_data, true);
        }

        $item->update($validated);

        return redirect()->route("admin.{$this->routePrefix}.show", $item->id)
            ->with('success', $this->title . ' 항목이 성공적으로 수정되었습니다.');
    }

    /**
     * 특정 예약의 진행상황 요약
     */
    public function appointmentProgress($appointmentId)
    {
        $appointment = Appointment::with(['customer', 'service'])->findOrFail($appointmentId);

        $progress = $this->model::where('appointment_id', $appointmentId)
            ->with('checklist')
            ->orderBy('checklist_id')
            ->orderBy('checklist_item_id')
            ->get();

        $summary = [
            'total_items' => $progress->count(),
            'completed' => $progress->where('status', 'completed')->count(),
            'in_progress' => $progress->where('status', 'in_progress')->count(),
            'pending' => $progress->where('status', 'pending')->count(),
            'failed' => $progress->where('status', 'failed')->count(),
            'skipped' => $progress->where('status', 'skipped')->count(),
        ];

        $summary['completion_rate'] = $summary['total_items'] > 0
            ? round(($summary['completed'] / $summary['total_items']) * 100, 1)
            : 0;

        return view("{$this->viewPath}.appointment-progress", [
            'appointment' => $appointment,
            'progress' => $progress,
            'summary' => $summary,
            'title' => $this->title
        ]);
    }

    /**
     * 체크리스트 항목 가져오기 (AJAX)
     */
    public function getChecklistItems(Request $request)
    {
        $checklistId = $request->get('checklist_id');
        $checklist = ServiceChecklist::find($checklistId);

        if (!$checklist) {
            return response()->json(['error' => '체크리스트를 찾을 수 없습니다.'], 404);
        }

        return response()->json([
            'items' => $checklist->checklist_items ?? [],
            'total_items' => $checklist->total_items_count
        ]);
    }

    /**
     * 진행상황 일괄 업데이트
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'updates' => 'required|array',
            'updates.*.id' => 'required|exists:service_progress,id',
            'updates.*.status' => 'required|in:pending,in_progress,completed,skipped,failed'
        ]);

        $updatedCount = 0;
        foreach ($request->updates as $update) {
            $progress = $this->model::find($update['id']);
            if ($progress && $progress->appointment_id == $request->appointment_id) {
                $progress->update([
                    'status' => $update['status'],
                    'completed_at' => $update['status'] === 'completed' ? now() : null
                ]);
                $updatedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$updatedCount}개의 항목이 업데이트되었습니다.",
            'updated_count' => $updatedCount
        ]);
    }
}