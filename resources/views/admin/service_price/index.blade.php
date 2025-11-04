@extends('jiny-service::layouts.admin.sidebar')

@section('content')
<div class="container-fluid">
    <!-- 헤더 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">{{ $service->title }} - 가격 옵션 관리</h2>
                    <p class="text-muted mb-0">서비스의 다양한 가격 옵션을 관리합니다.</p>
                </div>
                <div>
                    <a href="{{ route('admin.site.services.show', $service->id) }}" class="btn btn-outline-secondary me-2">
                        <i class="fe fe-arrow-left me-2"></i>서비스로
                    </a>
                    <a href="{{ route('admin.site.services.price.create', $service->id) }}" class="btn btn-primary">
                        <i class="fe fe-plus me-2"></i>가격 옵션 추가
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 서비스 정보 카드 -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h5 class="mb-1">{{ $service->title }}</h5>
                    <p class="text-muted mb-2">{{ $service->slug }}</p>
                    <p class="mb-0">{{ $service->description }}</p>
                </div>
                <div class="col-md-4 text-end">
                    <span class="badge bg-{{ $service->enable ? 'success' : 'secondary' }} mb-2">
                        {{ $service->enable ? '활성' : '비활성' }}
                    </span>
                    @if($service->featured)
                        <span class="badge bg-warning text-dark mb-2">추천</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 통계 카드 -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-gradient rounded-circle p-3">
                                <i class="fe fe-dollar-sign text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">전체 가격 옵션</h6>
                            <h4 class="mb-0">{{ $stats['total'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-gradient rounded-circle p-3">
                                <i class="fe fe-check-circle text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">활성 옵션</h6>
                            <h4 class="mb-0">{{ $stats['active'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-gradient rounded-circle p-3">
                                <i class="fe fe-star text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">인기 옵션</h6>
                            <h4 class="mb-0">{{ $stats['popular'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-gradient rounded-circle p-3">
                                <i class="fe fe-gift text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">무료체험 제공</h6>
                            <h4 class="mb-0">{{ $stats['with_trial'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 필터 -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.site.services.price.index', $service->id) }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">검색</label>
                            <input type="text"
                                   id="search"
                                   name="search"
                                   class="form-control"
                                   placeholder="가격명, 코드 검색..."
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="status">상태</label>
                            <select id="status" name="status" class="form-control">
                                <option value="">전체</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>활성</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>비활성</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="type">타입</label>
                            <select id="type" name="type" class="form-control">
                                <option value="">전체</option>
                                <option value="popular" {{ request('type') === 'popular' ? 'selected' : '' }}>인기</option>
                                <option value="recommended" {{ request('type') === 'recommended' ? 'selected' : '' }}>추천</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-5 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-primary me-2">
                            <i class="fe fe-search me-1"></i>검색
                        </button>
                        <a href="{{ route('admin.site.services.price.index', $service->id) }}" class="btn btn-outline-secondary">
                            <i class="fe fe-refresh-cw me-1"></i>초기화
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 가격 옵션 목록 -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">가격 옵션 목록</h5>
        </div>
        <div class="card-body p-0">
            @if($prices->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>옵션명</th>
                                <th>가격</th>
                                <th>할인</th>
                                <th>무료체험</th>
                                <th>상태</th>
                                <th>정렬순서</th>
                                <th width="120">관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($prices as $price)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $price->name }}</strong>
                                        @if($price->is_popular)
                                            <span class="badge bg-warning text-dark ms-1">인기</span>
                                        @endif
                                        @if($price->is_recommended)
                                            <span class="badge bg-success ms-1">추천</span>
                                        @endif
                                        <br>
                                        <small class="text-muted">{{ $price->code ?? '-' }}</small>
                                    </div>
                                </td>
                                <td>
                                    @if($price->has_discount)
                                        <div>
                                            <span class="text-decoration-line-through text-muted">{{ $price->formatted_price }}</span>
                                            <br>
                                            <strong class="text-success">{{ $price->formatted_sale_price }}</strong>
                                        </div>
                                    @else
                                        <strong>{{ $price->formatted_price }}</strong>
                                    @endif
                                    <br>
                                    <small class="text-muted">{{ $price->currency_display }}</small>
                                </td>
                                <td>
                                    @if($price->has_discount)
                                        <span class="badge bg-success">{{ $price->actual_discount_percentage }}% 할인</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($price->has_trial)
                                        <span class="badge bg-info">{{ $price->trial_days }}일</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($price->enable)
                                        <span class="badge bg-success">활성</span>
                                    @else
                                        <span class="badge bg-secondary">비활성</span>
                                    @endif
                                    @if(!$price->is_currently_valid)
                                        <br><small class="text-warning">유효기간 만료</small>
                                    @endif
                                </td>
                                <td>{{ $price->pos }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.site.services.price.show', [$service->id, $price->id]) }}"
                                           class="btn btn-outline-info"
                                           title="상세보기">
                                            <i class="fe fe-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.site.services.price.edit', [$service->id, $price->id]) }}"
                                           class="btn btn-outline-primary"
                                           title="수정">
                                            <i class="fe fe-edit"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-outline-danger"
                                                title="삭제"
                                                onclick="deletePrice({{ $price->id }})">
                                            <i class="fe fe-trash-2"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- 페이지네이션 -->
                <div class="card-footer">
                    {{ $prices->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fe fe-dollar-sign fe-3x text-muted mb-3"></i>
                    <h5 class="text-muted">등록된 가격 옵션이 없습니다</h5>
                    <p class="text-muted">새로운 가격 옵션을 추가해보세요.</p>
                    <a href="{{ route('admin.site.services.price.create', $service->id) }}" class="btn btn-primary">
                        <i class="fe fe-plus me-2"></i>첫 번째 가격 옵션 추가
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- 삭제 확인 모달 -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">가격 옵션 삭제</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>이 가격 옵션을 삭제하시겠습니까?</p>
                <p class="text-danger small">
                    <i class="fe fe-alert-triangle me-1"></i>
                    삭제된 데이터는 복구할 수 없습니다.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">삭제</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function deletePrice(id) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const form = document.getElementById('deleteForm');
    form.action = `/admin/site/services/{{ $service->id }}/price/${id}`;
    modal.show();
}
</script>
@endpush