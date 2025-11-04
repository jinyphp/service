<?php

namespace Jiny\Service\Http\Controllers\Admin\ServiceUsers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Jiny\Auth\Models\ShardedUser;
use Jiny\Auth\Services\ShardingService;

class SearchController extends Controller
{
    protected $shardingService;

    public function __construct(ShardingService $shardingService)
    {
        $this->shardingService = $shardingService;
    }

    /**
     * 이메일로 샤드 회원 검색
     */
    public function searchUserByEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $email = $request->input('email');

        try {
            // 기존 auth 시스템의 ShardedUser 모델 사용
            $user = ShardedUser::findByEmail($email);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => '해당 이메일로 등록된 사용자를 찾을 수 없습니다.'
                ], 404);
            }

            // 샤드 정보 가져오기
            $shardNumber = $user->getShardNumber();
            $shardName = 'user_' . str_pad($shardNumber, 3, '0', STR_PAD_LEFT);

            return response()->json([
                'success' => true,
                'user' => [
                    'user_uuid' => $user->uuid,
                    'user_shard' => $shardName,
                    'user_id' => $user->id ?? $shardNumber, // auto-increment id가 없을 수 있으므로 샤드 번호 사용
                    'user_email' => $user->email,
                    'user_name' => $user->name ?? $user->username ?? '미설정',
                    'phone' => $user->phone ?? null,
                    'created_at' => $user->created_at ?? null,
                    'shard_number' => $shardNumber,
                    'shard_table' => $user->getShardTableName(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('User search error: ' . $e->getMessage(), [
                'email' => $email,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => '사용자 검색 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 사용자 이름으로 검색 (자동완성용)
     */
    public function searchUsersByName(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2'
        ]);

        $query = $request->input('query');

        try {
            $users = collect();

            // 이메일로 먼저 검색
            if (filter_var($query, FILTER_VALIDATE_EMAIL)) {
                $user = ShardedUser::findByEmail($query);
                if ($user) {
                    $users->push($user);
                }
            } else {
                // 사용자명으로 검색
                $users = $this->searchByNameInAllShards($query);
            }

            return response()->json([
                'success' => true,
                'users' => $users->take(10)->map(function($user) {
                    $shardNumber = $user->getShardNumber();
                    $shardName = 'user_' . str_pad($shardNumber, 3, '0', STR_PAD_LEFT);

                    return [
                        'user_uuid' => $user->uuid,
                        'user_shard' => $shardName,
                        'user_id' => $user->id ?? $shardNumber,
                        'user_email' => $user->email,
                        'user_name' => $user->name ?? $user->username ?? '미설정',
                        'display_text' => ($user->name ?? $user->username ?? '미설정') . ' (' . $user->email . ')'
                    ];
                })->values()
            ]);

        } catch (\Exception $e) {
            Log::error('Users search error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => '사용자 검색 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    /**
     * UUID로 사용자 검색
     */
    public function searchUserByUuid(Request $request): JsonResponse
    {
        $request->validate([
            'uuid' => 'required|string'
        ]);

        $uuid = $request->input('uuid');

        try {
            $user = ShardedUser::findByUuid($uuid);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => '해당 UUID로 등록된 사용자를 찾을 수 없습니다.'
                ], 404);
            }

            $shardNumber = $user->getShardNumber();
            $shardName = 'user_' . str_pad($shardNumber, 3, '0', STR_PAD_LEFT);

            return response()->json([
                'success' => true,
                'user' => [
                    'user_uuid' => $user->uuid,
                    'user_shard' => $shardName,
                    'user_id' => $user->id ?? $shardNumber,
                    'user_email' => $user->email,
                    'user_name' => $user->name ?? $user->username ?? '미설정',
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('UUID search error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'UUID 검색 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    /**
     * 모든 샤드에서 이름으로 검색
     */
    private function searchByNameInAllShards($query)
    {
        $users = collect();

        try {
            if (!$this->shardingService->isEnabled()) {
                // 샤딩이 비활성화된 경우 기본 users 테이블에서 검색
                $userData = DB::table('users')
                    ->where(function($q) use ($query) {
                        $q->where('name', 'LIKE', "%{$query}%")
                          ->orWhere('username', 'LIKE', "%{$query}%");
                    })
                    ->limit(10)
                    ->get();

                foreach ($userData as $data) {
                    $users->push(ShardedUser::hydrate([$data])->first());
                }

                return $users;
            }

            // 모든 샤드 테이블에서 검색
            $shardTables = $this->shardingService->getAllShardTables();

            foreach ($shardTables as $tableName) {
                if (Schema::hasTable($tableName)) {
                    $userData = DB::table($tableName)
                        ->where(function($q) use ($query) {
                            $q->where('name', 'LIKE', "%{$query}%")
                              ->orWhere('username', 'LIKE', "%{$query}%");
                        })
                        ->limit(5)
                        ->get();

                    foreach ($userData as $data) {
                        $users->push(ShardedUser::hydrate([$data])->first());
                    }

                    // 10개 이상 찾으면 중단
                    if ($users->count() >= 10) {
                        break;
                    }
                }
            }

        } catch (\Exception $e) {
            Log::warning("Failed to search users by name: " . $e->getMessage());
        }

        return $users;
    }
}