<?php

use Illuminate\Support\Facades\Route;

/**
 * Services 관리 라우트
 *
 * @description
 * 서비스 관리 시스템을 위한 라우트입니다.
 * 서비스 CRUD 기능을 제공합니다.
 */

/**
 * Service Dashboard 라우트
 *
 * @description
 * 서비스 관리 대시보드 페이지입니다.
 */
Route::prefix('admin/service')->middleware(['web', 'admin'])->name('admin.service.')->group(function () {
    Route::get('/', \Jiny\Service\Http\Controllers\Admin\Dashboard\IndexController::class)->name('dashboard');

    // Service Categories 관리
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', \Jiny\Service\Http\Controllers\Admin\Categories\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Service\Http\Controllers\Admin\Categories\CreateController::class)->name('create');
        Route::post('/', \Jiny\Service\Http\Controllers\Admin\Categories\StoreController::class)->name('store');
        Route::get('/{id}/edit', \Jiny\Service\Http\Controllers\Admin\Categories\EditController::class)->name('edit')->where(['id' => '[0-9]+']);
        Route::put('/{id}', \Jiny\Service\Http\Controllers\Admin\Categories\UpdateController::class)->name('update')->where(['id' => '[0-9]+']);
        Route::delete('/{id}', \Jiny\Service\Http\Controllers\Admin\Categories\DestroyController::class)->name('destroy')->where(['id' => '[0-9]+']);
    });

    // Service Plans 관리
    Route::prefix('plan')->name('plan.')->group(function () {
        Route::get('/', \Jiny\Service\Http\Controllers\Admin\Plan\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Service\Http\Controllers\Admin\Plan\CreateController::class)->name('create');
        Route::post('/', \Jiny\Service\Http\Controllers\Admin\Plan\StoreController::class)->name('store');
        Route::get('/{id}', \Jiny\Service\Http\Controllers\Admin\Plan\ShowController::class)->name('show')->where(['id' => '[0-9]+']);
        Route::get('/{id}/edit', \Jiny\Service\Http\Controllers\Admin\Plan\EditController::class)->name('edit')->where(['id' => '[0-9]+']);
        Route::put('/{id}', \Jiny\Service\Http\Controllers\Admin\Plan\UpdateController::class)->name('update')->where(['id' => '[0-9]+']);
        Route::delete('/{id}', \Jiny\Service\Http\Controllers\Admin\Plan\DestroyController::class)->name('destroy')->where(['id' => '[0-9]+']);

        // Plan Detail 관리 (nested)
        Route::prefix('{planId}/detail')->name('detail.')->where(['planId' => '[0-9]+'])->group(function () {
            Route::get('/', \Jiny\Service\Http\Controllers\Admin\PlanDetail\IndexController::class)->name('index');
            Route::get('/create', \Jiny\Service\Http\Controllers\Admin\PlanDetail\CreateController::class)->name('create');
            Route::post('/', \Jiny\Service\Http\Controllers\Admin\PlanDetail\StoreController::class)->name('store');
            Route::get('/{detailId}', \Jiny\Service\Http\Controllers\Admin\PlanDetail\ShowController::class)->name('show')->where(['detailId' => '[0-9]+']);
            Route::get('/{detailId}/edit', \Jiny\Service\Http\Controllers\Admin\PlanDetail\EditController::class)->name('edit')->where(['detailId' => '[0-9]+']);
            Route::put('/{detailId}', \Jiny\Service\Http\Controllers\Admin\PlanDetail\UpdateController::class)->name('update')->where(['detailId' => '[0-9]+']);
            Route::delete('/{detailId}', \Jiny\Service\Http\Controllers\Admin\PlanDetail\DestroyController::class)->name('destroy')->where(['detailId' => '[0-9]+']);
        });

        // Plan Price 관리 (nested)
        Route::prefix('{planId}/price')->name('price.')->where(['planId' => '[0-9]+'])->group(function () {
            Route::get('/', \Jiny\Service\Http\Controllers\Admin\PlanPrice\IndexController::class)->name('index');
            Route::get('/create', \Jiny\Service\Http\Controllers\Admin\PlanPrice\CreateController::class)->name('create');
            Route::post('/', \Jiny\Service\Http\Controllers\Admin\PlanPrice\StoreController::class)->name('store');
            Route::get('/{priceId}', \Jiny\Service\Http\Controllers\Admin\PlanPrice\ShowController::class)->name('show')->where(['priceId' => '[0-9]+']);
            Route::get('/{priceId}/edit', \Jiny\Service\Http\Controllers\Admin\PlanPrice\EditController::class)->name('edit')->where(['priceId' => '[0-9]+']);
            Route::put('/{priceId}', \Jiny\Service\Http\Controllers\Admin\PlanPrice\UpdateController::class)->name('update')->where(['priceId' => '[0-9]+']);
            Route::delete('/{priceId}', \Jiny\Service\Http\Controllers\Admin\PlanPrice\DestroyController::class)->name('destroy')->where(['priceId' => '[0-9]+']);
        });
    });

    // Service Users 관리 (구독 사용자)
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', \Jiny\Service\Http\Controllers\Admin\ServiceUsers\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Service\Http\Controllers\Admin\ServiceUsers\CreateController::class)->name('create');
        Route::post('/', \Jiny\Service\Http\Controllers\Admin\ServiceUsers\StoreController::class)->name('store');
        Route::get('/{id}', \Jiny\Service\Http\Controllers\Admin\ServiceUsers\ShowController::class)->name('show')->where(['id' => '[0-9]+']);
        Route::get('/{id}/edit', \Jiny\Service\Http\Controllers\Admin\ServiceUsers\EditController::class)->name('edit')->where(['id' => '[0-9]+']);
        Route::put('/{id}', \Jiny\Service\Http\Controllers\Admin\ServiceUsers\UpdateController::class)->name('update')->where(['id' => '[0-9]+']);
        Route::delete('/{id}', \Jiny\Service\Http\Controllers\Admin\ServiceUsers\DestroyController::class)->name('destroy')->where(['id' => '[0-9]+']);

        // 구독자 액션
        Route::post('/{id}/activate', \Jiny\Service\Http\Controllers\Admin\ServiceUsers\ActionController::class . '@activate')->name('activate')->where(['id' => '[0-9]+']);
        Route::post('/{id}/suspend', \Jiny\Service\Http\Controllers\Admin\ServiceUsers\ActionController::class . '@suspend')->name('suspend')->where(['id' => '[0-9]+']);
        Route::post('/{id}/cancel', \Jiny\Service\Http\Controllers\Admin\ServiceUsers\ActionController::class . '@cancel')->name('cancel')->where(['id' => '[0-9]+']);
        Route::post('/{id}/extend', \Jiny\Service\Http\Controllers\Admin\ServiceUsers\ActionController::class . '@extend')->name('extend')->where(['id' => '[0-9]+']);
        Route::post('/{id}/update-cache', \Jiny\Service\Http\Controllers\Admin\ServiceUsers\ActionController::class . '@updateUserCache')->name('update-cache')->where(['id' => '[0-9]+']);

        // 사용자 검색 API
        Route::post('/search/email', \Jiny\Service\Http\Controllers\Admin\ServiceUsers\SearchController::class . '@searchUserByEmail')->name('search.email');
        Route::get('/search/name', \Jiny\Service\Http\Controllers\Admin\ServiceUsers\SearchController::class . '@searchUsersByName')->name('search.name');
        Route::post('/search/uuid', \Jiny\Service\Http\Controllers\Admin\ServiceUsers\SearchController::class . '@searchUserByUuid')->name('search.uuid');

        // 서비스 계층구조 API (카테고리 > 서비스 > 플랜 > 가격)
        Route::prefix('hierarchy')->name('hierarchy.')->group(function () {
            Route::get('/categories', \Jiny\Service\Http\Controllers\Admin\ServiceUsers\HierarchyController::class . '@getCategories')->name('categories');
            Route::get('/services', \Jiny\Service\Http\Controllers\Admin\ServiceUsers\HierarchyController::class . '@getServicesByCategory')->name('services');
            Route::get('/plans', \Jiny\Service\Http\Controllers\Admin\ServiceUsers\HierarchyController::class . '@getPlansByService')->name('plans');
            Route::get('/prices', \Jiny\Service\Http\Controllers\Admin\ServiceUsers\HierarchyController::class . '@getPricesByPlan')->name('prices');
            Route::get('/full', \Jiny\Service\Http\Controllers\Admin\ServiceUsers\HierarchyController::class . '@getFullHierarchy')->name('full');
            Route::post('/calculate', \Jiny\Service\Http\Controllers\Admin\ServiceUsers\HierarchyController::class . '@calculatePrice')->name('calculate');
        });
    });

    // Service Subscription Logs 관리 (구독 로그)
    Route::prefix('subscription-logs')->name('subscription-logs.')->group(function () {
        Route::get('/', \Jiny\Service\Http\Controllers\Admin\ServiceSubscriptionLog\IndexController::class)->name('index');
        Route::get('/{id}', \Jiny\Service\Http\Controllers\Admin\ServiceSubscriptionLog\ShowController::class)->name('show')->where(['id' => '[0-9]+']);
        Route::get('/stats/overview', \Jiny\Service\Http\Controllers\Admin\ServiceSubscriptionLog\StatsController::class)->name('stats');
        Route::get('/export/data', [\Jiny\Service\Http\Controllers\Admin\ServiceSubscriptionLog\StatsController::class, 'export'])->name('export');
    });

    // Service Payments 관리 (결제 내역)
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', \Jiny\Service\Http\Controllers\Admin\ServicePayments\IndexController::class)->name('index');
        Route::get('/{payment}', \Jiny\Service\Http\Controllers\Admin\ServicePayments\ShowController::class)->name('show');
        Route::get('/stats/overview', \Jiny\Service\Http\Controllers\Admin\ServicePayments\StatsController::class)->name('stats');
        Route::get('/export/data', [\Jiny\Service\Http\Controllers\Admin\ServicePayments\StatsController::class, 'export'])->name('export');
    });

    // Subscription Process 관리 (구독 프로세스)
    Route::prefix('process')->name('process.')->group(function () {
        // 구독 생성
        Route::post('/subscribe', [\Jiny\Service\Http\Controllers\Admin\Process\SubscribeController::class, 'store'])->name('subscribe');

        // 구독 취소 및 재활성화
        Route::post('/cancel/{serviceUserId}', [\Jiny\Service\Http\Controllers\Admin\Process\CancelController::class, 'cancel'])->name('cancel');
        Route::post('/cancel/{serviceUserId}/reactivate', [\Jiny\Service\Http\Controllers\Admin\Process\CancelController::class, 'reactivate'])->name('reactivate');

        // 구독 연장 및 갱신
        Route::post('/extend/{serviceUserId}', [\Jiny\Service\Http\Controllers\Admin\Process\ExtendController::class, 'extend'])->name('extend');
        Route::post('/renew/{serviceUserId}', [\Jiny\Service\Http\Controllers\Admin\Process\ExtendController::class, 'renew'])->name('renew');

        // 플랜 업그레이드 및 다운그레이드
        Route::post('/upgrade/{serviceUserId}', [\Jiny\Service\Http\Controllers\Admin\Process\UpgradeController::class, 'upgrade'])->name('upgrade');
        Route::post('/downgrade/{serviceUserId}', [\Jiny\Service\Http\Controllers\Admin\Process\UpgradeController::class, 'downgrade'])->name('downgrade');

        // 환불 처리
        Route::post('/refund/{serviceUserId}', [\Jiny\Service\Http\Controllers\Admin\Process\RefundController::class, 'processRefund'])->name('refund.process');
        Route::get('/refund/{serviceUserId}/history', [\Jiny\Service\Http\Controllers\Admin\Process\RefundController::class, 'getRefundHistory'])->name('refund.history');
        Route::post('/refund/{serviceUserId}/payment/{paymentId}/cancel', [\Jiny\Service\Http\Controllers\Admin\Process\RefundController::class, 'cancelRefund'])->name('refund.cancel');
        Route::get('/refund/{serviceUserId}/amount', [\Jiny\Service\Http\Controllers\Admin\Process\RefundController::class, 'getRefundableAmount'])->name('refund.amount');
    });

    // Note: Engineer management has been moved to jiny/partner package
    // Routes are now available at admin/partner/tiers and admin/partner/engineers

    // Operations Management
    Route::prefix('operations')->name('operations.')->group(function () {
        // Appointments
        Route::prefix('appointments')->name('appointments.')->group(function () {
            Route::get('/', \Jiny\Service\Http\Controllers\Admin\AppointmentController::class . '@index')->name('index');
            Route::get('/create', \Jiny\Service\Http\Controllers\Admin\AppointmentController::class . '@create')->name('create');
            Route::post('/', \Jiny\Service\Http\Controllers\Admin\AppointmentController::class . '@store')->name('store');
            Route::get('/{id}', \Jiny\Service\Http\Controllers\Admin\AppointmentController::class . '@show')->name('show')->where(['id' => '[0-9]+']);
            Route::get('/{id}/edit', \Jiny\Service\Http\Controllers\Admin\AppointmentController::class . '@edit')->name('edit')->where(['id' => '[0-9]+']);
            Route::put('/{id}', \Jiny\Service\Http\Controllers\Admin\AppointmentController::class . '@update')->name('update')->where(['id' => '[0-9]+']);
            Route::delete('/{id}', \Jiny\Service\Http\Controllers\Admin\AppointmentController::class . '@destroy')->name('destroy')->where(['id' => '[0-9]+']);

            // AJAX routes
            Route::get('/customer-addresses', \Jiny\Service\Http\Controllers\Admin\AppointmentController::class . '@getCustomerAddresses')->name('customer-addresses');
        });
    });

    // Customer Management
    Route::prefix('customers')->name('customers.')->group(function () {
        // Customer Addresses
        Route::prefix('addresses')->name('addresses.')->group(function () {
            Route::get('/', \Jiny\Service\Http\Controllers\Admin\CustomerAddressController::class . '@index')->name('index');
            Route::get('/create', \Jiny\Service\Http\Controllers\Admin\CustomerAddressController::class . '@create')->name('create');
            Route::post('/', \Jiny\Service\Http\Controllers\Admin\CustomerAddressController::class . '@store')->name('store');
            Route::get('/{id}', \Jiny\Service\Http\Controllers\Admin\CustomerAddressController::class . '@show')->name('show')->where(['id' => '[0-9]+']);
            Route::get('/{id}/edit', \Jiny\Service\Http\Controllers\Admin\CustomerAddressController::class . '@edit')->name('edit')->where(['id' => '[0-9]+']);
            Route::put('/{id}', \Jiny\Service\Http\Controllers\Admin\CustomerAddressController::class . '@update')->name('update')->where(['id' => '[0-9]+']);
            Route::delete('/{id}', \Jiny\Service\Http\Controllers\Admin\CustomerAddressController::class . '@destroy')->name('destroy')->where(['id' => '[0-9]+']);
        });
    });


    // Quality Management (서비스 품질 관리)
    Route::prefix('quality')->name('quality.')->group(function () {
        // Service Checklists
        Route::prefix('checklists')->name('checklists.')->group(function () {
            Route::get('/', \Jiny\Service\Http\Controllers\Admin\ServiceChecklistController::class . '@index')->name('index');
            Route::get('/create', \Jiny\Service\Http\Controllers\Admin\ServiceChecklistController::class . '@create')->name('create');
            Route::post('/', \Jiny\Service\Http\Controllers\Admin\ServiceChecklistController::class . '@store')->name('store');
            Route::get('/{id}', \Jiny\Service\Http\Controllers\Admin\ServiceChecklistController::class . '@show')->name('show')->where(['id' => '[0-9]+']);
            Route::get('/{id}/edit', \Jiny\Service\Http\Controllers\Admin\ServiceChecklistController::class . '@edit')->name('edit')->where(['id' => '[0-9]+']);
            Route::put('/{id}', \Jiny\Service\Http\Controllers\Admin\ServiceChecklistController::class . '@update')->name('update')->where(['id' => '[0-9]+']);
            Route::delete('/{id}', \Jiny\Service\Http\Controllers\Admin\ServiceChecklistController::class . '@destroy')->name('destroy')->where(['id' => '[0-9]+']);

            // Special actions
            Route::post('/{id}/duplicate', \Jiny\Service\Http\Controllers\Admin\ServiceChecklistController::class . '@duplicate')->name('duplicate');
            Route::get('/template', \Jiny\Service\Http\Controllers\Admin\ServiceChecklistController::class . '@createTemplate')->name('template');
        });

        // Service Progress
        Route::prefix('progress')->name('progress.')->group(function () {
            Route::get('/', \Jiny\Service\Http\Controllers\Admin\ServiceProgressController::class . '@index')->name('index');
            Route::get('/create', \Jiny\Service\Http\Controllers\Admin\ServiceProgressController::class . '@create')->name('create');
            Route::post('/', \Jiny\Service\Http\Controllers\Admin\ServiceProgressController::class . '@store')->name('store');
            Route::get('/{id}', \Jiny\Service\Http\Controllers\Admin\ServiceProgressController::class . '@show')->name('show')->where(['id' => '[0-9]+']);
            Route::get('/{id}/edit', \Jiny\Service\Http\Controllers\Admin\ServiceProgressController::class . '@edit')->name('edit')->where(['id' => '[0-9]+']);
            Route::put('/{id}', \Jiny\Service\Http\Controllers\Admin\ServiceProgressController::class . '@update')->name('update')->where(['id' => '[0-9]+']);
            Route::delete('/{id}', \Jiny\Service\Http\Controllers\Admin\ServiceProgressController::class . '@destroy')->name('destroy')->where(['id' => '[0-9]+']);

            // Special actions
            Route::get('/appointment/{appointment_id}', \Jiny\Service\Http\Controllers\Admin\ServiceProgressController::class . '@appointmentProgress')->name('appointment');
            Route::get('/checklist-items', \Jiny\Service\Http\Controllers\Admin\ServiceProgressController::class . '@getChecklistItems')->name('checklist-items');
            Route::post('/bulk-update', \Jiny\Service\Http\Controllers\Admin\ServiceProgressController::class . '@bulkUpdate')->name('bulk-update');
        });

        // Service Inspections
        Route::prefix('inspections')->name('inspections.')->group(function () {
            Route::get('/', \Jiny\Service\Http\Controllers\Admin\ServiceInspectionController::class . '@index')->name('index');
            Route::get('/create', \Jiny\Service\Http\Controllers\Admin\ServiceInspectionController::class . '@create')->name('create');
            Route::post('/', \Jiny\Service\Http\Controllers\Admin\ServiceInspectionController::class . '@store')->name('store');
            Route::get('/{id}', \Jiny\Service\Http\Controllers\Admin\ServiceInspectionController::class . '@show')->name('show')->where(['id' => '[0-9]+']);
            Route::get('/{id}/edit', \Jiny\Service\Http\Controllers\Admin\ServiceInspectionController::class . '@edit')->name('edit')->where(['id' => '[0-9]+']);
            Route::put('/{id}', \Jiny\Service\Http\Controllers\Admin\ServiceInspectionController::class . '@update')->name('update')->where(['id' => '[0-9]+']);
            Route::delete('/{id}', \Jiny\Service\Http\Controllers\Admin\ServiceInspectionController::class . '@destroy')->name('destroy')->where(['id' => '[0-9]+']);

            // Special actions
            Route::post('/{id}/approve', \Jiny\Service\Http\Controllers\Admin\ServiceInspectionController::class . '@approve')->name('approve');
            Route::post('/{id}/reject', \Jiny\Service\Http\Controllers\Admin\ServiceInspectionController::class . '@reject')->name('reject');
            Route::post('/{id}/photo-evidence', \Jiny\Service\Http\Controllers\Admin\ServiceInspectionController::class . '@addPhotoEvidence')->name('photo-evidence');
            Route::get('/overdue', \Jiny\Service\Http\Controllers\Admin\ServiceInspectionController::class . '@overdueInspections')->name('overdue');
            Route::get('/statistics', \Jiny\Service\Http\Controllers\Admin\ServiceInspectionController::class . '@statistics')->name('statistics');
            Route::get('/report', \Jiny\Service\Http\Controllers\Admin\ServiceInspectionController::class . '@generateReport')->name('report');
        });
    });

    // Work Management (작업 관리)
    Route::prefix('work')->name('work.')->group(function () {
        // Task Assignments
        Route::prefix('assignments')->name('assignments.')->group(function () {
            Route::get('/', \Jiny\Service\Http\Controllers\Admin\TaskAssignmentController::class . '@index')->name('index');
            Route::get('/create', \Jiny\Service\Http\Controllers\Admin\TaskAssignmentController::class . '@create')->name('create');
            Route::post('/', \Jiny\Service\Http\Controllers\Admin\TaskAssignmentController::class . '@store')->name('store');
            Route::get('/{id}', \Jiny\Service\Http\Controllers\Admin\TaskAssignmentController::class . '@show')->name('show')->where(['id' => '[0-9]+']);
            Route::get('/{id}/edit', \Jiny\Service\Http\Controllers\Admin\TaskAssignmentController::class . '@edit')->name('edit')->where(['id' => '[0-9]+']);
            Route::put('/{id}', \Jiny\Service\Http\Controllers\Admin\TaskAssignmentController::class . '@update')->name('update')->where(['id' => '[0-9]+']);
            Route::delete('/{id}', \Jiny\Service\Http\Controllers\Admin\TaskAssignmentController::class . '@destroy')->name('destroy')->where(['id' => '[0-9]+']);

            // Assignment actions
            Route::post('/auto-assign', \Jiny\Service\Http\Controllers\Admin\TaskAssignmentController::class . '@autoAssign')->name('auto-assign');
            Route::post('/manual-assign', \Jiny\Service\Http\Controllers\Admin\TaskAssignmentController::class . '@manualAssign')->name('manual-assign');
            Route::post('/{id}/cancel', \Jiny\Service\Http\Controllers\Admin\TaskAssignmentController::class . '@cancelAssignment')->name('cancel');
            Route::get('/overdue', \Jiny\Service\Http\Controllers\Admin\TaskAssignmentController::class . '@overdueAssignments')->name('overdue');
        });

        // Service Providers
        Route::prefix('providers')->name('providers.')->group(function () {
            Route::get('/', \Jiny\Service\Http\Controllers\Admin\ServiceProviderController::class . '@index')->name('index');
            Route::get('/create', \Jiny\Service\Http\Controllers\Admin\ServiceProviderController::class . '@create')->name('create');
            Route::post('/', \Jiny\Service\Http\Controllers\Admin\ServiceProviderController::class . '@store')->name('store');
            Route::get('/{id}', \Jiny\Service\Http\Controllers\Admin\ServiceProviderController::class . '@show')->name('show')->where(['id' => '[0-9]+']);
            Route::get('/{id}/edit', \Jiny\Service\Http\Controllers\Admin\ServiceProviderController::class . '@edit')->name('edit')->where(['id' => '[0-9]+']);
            Route::put('/{id}', \Jiny\Service\Http\Controllers\Admin\ServiceProviderController::class . '@update')->name('update')->where(['id' => '[0-9]+']);
            Route::delete('/{id}', \Jiny\Service\Http\Controllers\Admin\ServiceProviderController::class . '@destroy')->name('destroy')->where(['id' => '[0-9]+']);

            // Provider verification actions
            Route::post('/{id}/verify-identity', \Jiny\Service\Http\Controllers\Admin\ServiceProviderController::class . '@verifyIdentity')->name('verify-identity');
            Route::post('/{id}/reject-identity', \Jiny\Service\Http\Controllers\Admin\ServiceProviderController::class . '@rejectIdentity')->name('reject-identity');
            Route::post('/{id}/pass-background', \Jiny\Service\Http\Controllers\Admin\ServiceProviderController::class . '@passBackgroundCheck')->name('pass-background');
            Route::post('/{id}/fail-background', \Jiny\Service\Http\Controllers\Admin\ServiceProviderController::class . '@failBackgroundCheck')->name('fail-background');
            Route::post('/{id}/verify-insurance', \Jiny\Service\Http\Controllers\Admin\ServiceProviderController::class . '@verifyInsurance')->name('verify-insurance');
            Route::post('/{id}/suspend', \Jiny\Service\Http\Controllers\Admin\ServiceProviderController::class . '@suspend')->name('suspend');
            Route::post('/{id}/activate', \Jiny\Service\Http\Controllers\Admin\ServiceProviderController::class . '@activate')->name('activate');

            Route::get('/statistics', \Jiny\Service\Http\Controllers\Admin\ServiceProviderController::class . '@statistics')->name('statistics');
            Route::get('/generate-code', \Jiny\Service\Http\Controllers\Admin\ServiceProviderController::class . '@generateProviderCode')->name('generate-code');
        });
    });

    // Operation History (운영 이력)
    Route::prefix('history')->name('history.')->group(function () {
        // Appointment Changes
        Route::prefix('appointment-changes')->name('appointment-changes.')->group(function () {
            Route::get('/', \Jiny\Service\Http\Controllers\Admin\AppointmentChangeController::class . '@index')->name('index');
            Route::get('/create', \Jiny\Service\Http\Controllers\Admin\AppointmentChangeController::class . '@create')->name('create');
            Route::post('/', \Jiny\Service\Http\Controllers\Admin\AppointmentChangeController::class . '@store')->name('store');
            Route::get('/{id}', \Jiny\Service\Http\Controllers\Admin\AppointmentChangeController::class . '@show')->name('show')->where(['id' => '[0-9]+']);
            Route::get('/{id}/edit', \Jiny\Service\Http\Controllers\Admin\AppointmentChangeController::class . '@edit')->name('edit')->where(['id' => '[0-9]+']);
            Route::put('/{id}', \Jiny\Service\Http\Controllers\Admin\AppointmentChangeController::class . '@update')->name('update')->where(['id' => '[0-9]+']);
            Route::delete('/{id}', \Jiny\Service\Http\Controllers\Admin\AppointmentChangeController::class . '@destroy')->name('destroy')->where(['id' => '[0-9]+']);

            // History views
            Route::get('/appointment/{appointment_id}', \Jiny\Service\Http\Controllers\Admin\AppointmentChangeController::class . '@appointmentHistory')->name('appointment');
            Route::get('/statistics', \Jiny\Service\Http\Controllers\Admin\AppointmentChangeController::class . '@statistics')->name('statistics');
        });
    });
});

Route::prefix('admin/site/services')->middleware(['web', 'admin'])->name('admin.site.services.')->group(function () {
    Route::get('/', \Jiny\Service\Http\Controllers\Admin\Services\IndexController::class)->name('index');
    Route::get('/create', \Jiny\Service\Http\Controllers\Admin\Services\CreateController::class)->name('create');
    Route::post('/', \Jiny\Service\Http\Controllers\Admin\Services\StoreController::class)->name('store');
    Route::get('/{id}', \Jiny\Service\Http\Controllers\Admin\Services\ShowController::class)->name('show')->where(['id' => '[0-9]+']);
    Route::get('/{id}/edit', \Jiny\Service\Http\Controllers\Admin\Services\EditController::class)->name('edit')->where(['id' => '[0-9]+']);
    Route::put('/{id}', \Jiny\Service\Http\Controllers\Admin\Services\UpdateController::class)->name('update')->where(['id' => '[0-9]+']);
    Route::delete('/{id}', \Jiny\Service\Http\Controllers\Admin\Services\DestroyController::class)->name('destroy')->where(['id' => '[0-9]+']);

    // Service Price 관리 (nested)
    Route::prefix('{serviceId}/price')->name('price.')->where(['serviceId' => '[0-9]+'])->group(function () {
        Route::get('/', \Jiny\Service\Http\Controllers\Admin\ServicePrice\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Service\Http\Controllers\Admin\ServicePrice\CreateController::class)->name('create');
        Route::post('/', \Jiny\Service\Http\Controllers\Admin\ServicePrice\StoreController::class)->name('store');
        Route::get('/{priceId}', \Jiny\Service\Http\Controllers\Admin\ServicePrice\ShowController::class)->name('show')->where(['priceId' => '[0-9]+']);
        Route::get('/{priceId}/edit', \Jiny\Service\Http\Controllers\Admin\ServicePrice\EditController::class)->name('edit')->where(['priceId' => '[0-9]+']);
        Route::put('/{priceId}', \Jiny\Service\Http\Controllers\Admin\ServicePrice\UpdateController::class)->name('update')->where(['priceId' => '[0-9]+']);
        Route::delete('/{priceId}', \Jiny\Service\Http\Controllers\Admin\ServicePrice\DestroyController::class)->name('destroy')->where(['priceId' => '[0-9]+']);
    });

    // Service Detail 관리 (nested)
    Route::prefix('{serviceId}/detail')->name('detail.')->where(['serviceId' => '[0-9]+'])->group(function () {
        Route::get('/', \Jiny\Service\Http\Controllers\Admin\ServiceDetail\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Service\Http\Controllers\Admin\ServiceDetail\CreateController::class)->name('create');
        Route::post('/', \Jiny\Service\Http\Controllers\Admin\ServiceDetail\StoreController::class)->name('store');
        Route::get('/{detailId}/edit', \Jiny\Service\Http\Controllers\Admin\ServiceDetail\EditController::class)->name('edit')->where(['detailId' => '[0-9]+']);
        Route::put('/{detailId}', \Jiny\Service\Http\Controllers\Admin\ServiceDetail\UpdateController::class)->name('update')->where(['detailId' => '[0-9]+']);
        Route::delete('/{detailId}', \Jiny\Service\Http\Controllers\Admin\ServiceDetail\DestroyController::class)->name('destroy')->where(['detailId' => '[0-9]+']);
    });
});