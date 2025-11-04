<?php

namespace Jiny\Service\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

abstract class BaseAdminController extends Controller
{
    protected $model;
    protected $viewPath;
    protected $routePrefix;
    protected $title;

    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = $this->model::query();

        // 검색 기능
        if ($request->has('search') && $request->search) {
            $searchFields = $this->getSearchFields();
            $query->where(function($q) use ($request, $searchFields) {
                foreach ($searchFields as $field) {
                    $q->orWhere($field, 'like', '%' . $request->search . '%');
                }
            });
        }

        // 페이지네이션
        $items = $query->orderBy('id', 'desc')->paginate(20);

        return view("{$this->viewPath}.index", [
            'items' => $items,
            'title' => $this->title,
            'routePrefix' => $this->routePrefix,
            'searchValue' => $request->search
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view("{$this->viewPath}.create", [
            'title' => $this->title,
            'routePrefix' => $this->routePrefix
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->getValidationRules());

        $item = $this->model::create($validated);

        return redirect()->route("admin.{$this->routePrefix}.show", $item->id)
            ->with('success', $this->title . ' 항목이 성공적으로 생성되었습니다.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $item = $this->model::findOrFail($id);

        return view("{$this->viewPath}.show", [
            'item' => $item,
            'title' => $this->title,
            'routePrefix' => $this->routePrefix
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $item = $this->model::findOrFail($id);

        return view("{$this->viewPath}.edit", [
            'item' => $item,
            'title' => $this->title,
            'routePrefix' => $this->routePrefix
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $item = $this->model::findOrFail($id);
        $validated = $request->validate($this->getValidationRules($item));

        $item->update($validated);

        return redirect()->route("admin.{$this->routePrefix}.show", $item->id)
            ->with('success', $this->title . ' 항목이 성공적으로 수정되었습니다.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $item = $this->model::findOrFail($id);
        $item->delete();

        return redirect()->route("admin.{$this->routePrefix}.index")
            ->with('success', $this->title . ' 항목이 성공적으로 삭제되었습니다.');
    }

    /**
     * Get validation rules for the model
     */
    abstract protected function getValidationRules($item = null): array;

    /**
     * Get searchable fields for the model
     */
    protected function getSearchFields(): array
    {
        return ['id'];
    }
}