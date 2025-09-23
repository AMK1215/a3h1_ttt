<?php

namespace App\Http\Controllers\Api\PoneWine;

use App\Http\Controllers\Controller;
use App\Models\PoneWineTransaction;
use App\Models\User;
use App\Traits\HttpResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PoneWinePlayerReportController extends Controller
{
    use HttpResponses;

    /**
     * Get PoneWine transaction report for authenticated player
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPlayerReport(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Validate request parameters
            $request->validate([
                'date_from' => 'nullable|date_format:Y-m-d',
                'date_to' => 'nullable|date_format:Y-m-d',
                'room_id' => 'nullable|integer',
                'result' => 'nullable|in:Win,Lose,Draw',
                'limit' => 'nullable|integer|min:1|max:100',
                'page' => 'nullable|integer|min:1',
            ]);

            // Default to today's data if no date range provided
            $dateFrom = $request->input('date_from', now()->toDateString());
            $dateTo = $request->input('date_to', $dateFrom);
            $roomId = $request->input('room_id');
            $result = $request->input('result');
            $limit = $request->input('limit', 20);
            $page = $request->input('page', 1);

            Log::info('PoneWine Player Report Request', [
                'user_id' => $user->id,
                'user_name' => $user->user_name,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'room_id' => $roomId,
                'result' => $result,
                'limit' => $limit,
                'page' => $page,
            ]);

            // Build query for the authenticated player's transactions only
            $query = PoneWineTransaction::where('user_id', $user->id);

            // Apply date range filter
            if ($dateFrom && $dateTo) {
                $query->whereBetween('created_at', [
                    $dateFrom . ' 00:00:00',
                    $dateTo . ' 23:59:59',
                ]);
            }

            // Apply additional filters
            if ($roomId) {
                $query->where('room_id', $roomId);
            }

            if ($result) {
                $query->where('result', $result);
            }

            // Get paginated results
            $transactions = $query->orderByDesc('created_at')
                ->paginate($limit, ['*'], 'page', $page);

            // Calculate summary statistics
            $summaryQuery = PoneWineTransaction::where('user_id', $user->id);
            
            if ($dateFrom && $dateTo) {
                $summaryQuery->whereBetween('created_at', [
                    $dateFrom . ' 00:00:00',
                    $dateTo . ' 23:59:59',
                ]);
            }

            if ($roomId) {
                $summaryQuery->where('room_id', $roomId);
            }

            $summary = [
                'total_transactions' => $summaryQuery->count(),
                'total_bet_amount' => $summaryQuery->sum('bet_amount'),
                'total_win_lose_amount' => $summaryQuery->sum('win_lose_amount'),
                'wins' => $summaryQuery->where('result', 'Win')->count(),
                'losses' => $summaryQuery->where('result', 'Lose')->count(),
                'draws' => $summaryQuery->where('result', 'Draw')->count(),
                'win_rate' => 0,
            ];

            // Calculate win rate
            if ($summary['total_transactions'] > 0) {
                $summary['win_rate'] = round(($summary['wins'] / $summary['total_transactions']) * 100, 2);
            }

            // Format transaction data for response
            $formattedTransactions = $transactions->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'room_id' => $transaction->room_id,
                    'match_id' => $transaction->match_id,
                    'win_number' => $transaction->win_number,
                    'bet_number' => $transaction->bet_number,
                    'bet_amount' => number_format($transaction->bet_amount, 2),
                    'win_lose_amount' => number_format($transaction->win_lose_amount, 2),
                    'result' => $transaction->result,
                    'player_balance_before' => number_format($transaction->player_balance_before, 2),
                    'player_balance_after' => number_format($transaction->player_balance_after, 2),
                    'game_name' => $transaction->game_name ?? 'PoneWine',
                    'processed_at' => $transaction->processed_at?->format('Y-m-d H:i:s'),
                    'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
                ];
            });

            $response = [
                'summary' => $summary,
                'transactions' => $formattedTransactions,
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                    'from' => $transactions->firstItem(),
                    'to' => $transactions->lastItem(),
                ],
                'filters_applied' => [
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'room_id' => $roomId,
                    'result' => $result,
                ],
            ];

            Log::info('PoneWine Player Report Generated', [
                'user_id' => $user->id,
                'total_transactions' => $summary['total_transactions'],
                'current_page' => $transactions->currentPage(),
                'total_pages' => $transactions->lastPage(),
            ]);

            return $this->success($response, 'PoneWine player report retrieved successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('PoneWine Player Report Validation Error', [
                'user_id' => Auth::id(),
                'errors' => $e->errors(),
            ]);
            return $this->error('Validation Error', $e->getMessage(), 422, $e->errors());
        } catch (\Exception $e) {
            Log::error('PoneWine Player Report Error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->error('Internal Server Error', 'Failed to retrieve PoneWine report.', 500);
        }
    }

    /**
     * Get PoneWine transaction summary for authenticated player
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPlayerSummary(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Validate request parameters
            $request->validate([
                'date_from' => 'nullable|date_format:Y-m-d',
                'date_to' => 'nullable|date_format:Y-m-d',
                'period' => 'nullable|in:today,yesterday,this_week,last_week,this_month,last_month',
            ]);

            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');
            $period = $request->input('period', 'today');

            // Set date range based on period if not provided
            if (!$dateFrom || !$dateTo) {
                $dateRange = $this->getDateRangeByPeriod($period);
                $dateFrom = $dateRange['from'];
                $dateTo = $dateRange['to'];
            }

            Log::info('PoneWine Player Summary Request', [
                'user_id' => $user->id,
                'user_name' => $user->user_name,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'period' => $period,
            ]);

            // Get summary data
            $query = PoneWineTransaction::where('user_id', $user->id)
                ->whereBetween('created_at', [
                    $dateFrom . ' 00:00:00',
                    $dateTo . ' 23:59:59',
                ]);

            $summary = [
                'period' => $period,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'total_transactions' => $query->count(),
                'total_bet_amount' => $query->sum('bet_amount'),
                'total_win_lose_amount' => $query->sum('win_lose_amount'),
                'wins' => $query->where('result', 'Win')->count(),
                'losses' => $query->where('result', 'Lose')->count(),
                'draws' => $query->where('result', 'Draw')->count(),
                'win_rate' => 0,
                'profit_loss' => 0,
            ];

            // Calculate win rate and profit/loss
            if ($summary['total_transactions'] > 0) {
                $summary['win_rate'] = round(($summary['wins'] / $summary['total_transactions']) * 100, 2);
            }

            $summary['profit_loss'] = number_format($summary['total_win_lose_amount'], 2);

            // Get room statistics
            $roomStats = $query->selectRaw('room_id, COUNT(*) as transaction_count, SUM(bet_amount) as total_bet, SUM(win_lose_amount) as total_win_lose')
                ->groupBy('room_id')
                ->orderByDesc('transaction_count')
                ->get()
                ->map(function ($stat) {
                    return [
                        'room_id' => $stat->room_id,
                        'transaction_count' => $stat->transaction_count,
                        'total_bet' => number_format($stat->total_bet, 2),
                        'total_win_lose' => number_format($stat->total_win_lose, 2),
                    ];
                });

            $summary['room_statistics'] = $roomStats;

            Log::info('PoneWine Player Summary Generated', [
                'user_id' => $user->id,
                'total_transactions' => $summary['total_transactions'],
                'profit_loss' => $summary['profit_loss'],
            ]);

            return $this->success($summary, 'PoneWine player summary retrieved successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('PoneWine Player Summary Validation Error', [
                'user_id' => Auth::id(),
                'errors' => $e->errors(),
            ]);
            return $this->error('Validation Error', $e->getMessage(), 422, $e->errors());
        } catch (\Exception $e) {
            Log::error('PoneWine Player Summary Error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->error('Internal Server Error', 'Failed to retrieve PoneWine summary.', 500);
        }
    }

    /**
     * Get date range based on period
     * 
     * @param string $period
     * @return array
     */
    private function getDateRangeByPeriod(string $period): array
    {
        $today = Carbon::today();
        
        switch ($period) {
            case 'today':
                return ['from' => $today->toDateString(), 'to' => $today->toDateString()];
            case 'yesterday':
                $yesterday = $today->subDay();
                return ['from' => $yesterday->toDateString(), 'to' => $yesterday->toDateString()];
            case 'this_week':
                return ['from' => $today->startOfWeek()->toDateString(), 'to' => $today->endOfWeek()->toDateString()];
            case 'last_week':
                $lastWeek = $today->subWeek();
                return ['from' => $lastWeek->startOfWeek()->toDateString(), 'to' => $lastWeek->endOfWeek()->toDateString()];
            case 'this_month':
                return ['from' => $today->startOfMonth()->toDateString(), 'to' => $today->endOfMonth()->toDateString()];
            case 'last_month':
                $lastMonth = $today->subMonth();
                return ['from' => $lastMonth->startOfMonth()->toDateString(), 'to' => $lastMonth->endOfMonth()->toDateString()];
            default:
                return ['from' => $today->toDateString(), 'to' => $today->toDateString()];
        }
    }
}
