<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\RendezVous;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * GET /api/admin/dashboard
     * Renvoie le résumé global : users, rdv, paiements
     */
    public function summary(Request $req)
    {
        return response()->json([
            'users'      => $this->usersStats($req)->original,
            'rendezVous' => $this->rdvStats($req)->original,
            'paiements'  => $this->paiementsStats($req)->original,
        ]);
    }

    /**
     * GET /api/admin/dashboard/users
     * Statistiques utilisateurs
     */
    public function usersStats(Request $req)
    {
        $total     = User::count();
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        $countThis = User::whereBetween('created_at', [$thisMonth, now()])->count();
        $countLast = User::whereBetween('created_at', [$lastMonth, $thisMonth])->count();
        $changePct = $countLast
            ? round((($countThis - $countLast) / $countLast) * 100, 1)
            : null;

        $byRole = User::select('role_id', DB::raw('count(*) as cnt'))
            ->groupBy('role_id')
            ->get()
            ->pluck('cnt', 'role_id');

        // Trend 6 derniers mois
        $trend = collect();
        for ($i = 5; $i >= 0; $i--) {
            $m     = now()->subMonths($i);
            $start = $m->copy()->startOfMonth();
            $end   = $m->copy()->endOfMonth();
            $trend->push([
                'month' => $m->format('Y-m'),
                'count' => User::whereBetween('created_at', [$start, $end])->count(),
            ]);
        }

        return response()->json(compact('total', 'changePct', 'byRole', 'trend'));
    }

    /**
     * GET /api/admin/dashboard/rendez-vous
     * Statistiques rendez-vous
     */
    public function rdvStats(Request $req)
    {
        $byStatus = RendezVous::select('statut', DB::raw('count(*) as cnt'))
            ->groupBy('statut')
            ->get()
            ->pluck('cnt', 'statut');

        $today = RendezVous::whereDate('date_rdv', now()->toDateString())->count();

        // Trend 7 derniers jours
        $weekTrend = collect();
        for ($i = 6; $i >= 0; $i--) {
            $d = now()->subDays($i)->toDateString();
            $weekTrend->push([
                'day'   => $d,
                'count' => RendezVous::whereDate('date_rdv', $d)->count(),
            ]);
        }

        return response()->json(compact('byStatus', 'today', 'weekTrend'));
    }

    /**
     * GET /api/admin/dashboard/paiements
     * Statistiques paiements
     */
    public function paiementsStats(Request $req)
    {
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        $paidThis = Paiement::where('statut', 'effectué')
            ->where('date_paiement', '>=', $thisMonth)
            ->sum('montant');

        $paidLast = Paiement::where('statut', 'effectué')
            ->whereBetween('date_paiement', [$lastMonth, $thisMonth])
            ->sum('montant');

        $changeRev = $paidLast
            ? round((($paidThis - $paidLast) / $paidLast) * 100, 1)
            : null;

        $totalCount   = Paiement::count();
        $successCount = Paiement::where('statut', 'effectué')->count();
        $successRate  = $totalCount ? round(($successCount / $totalCount) * 100, 1) : null;

        $byMode = Paiement::select('mode', DB::raw('sum(montant) as sumMontant'))
            ->groupBy('mode')
            ->get()
            ->pluck('sumMontant', 'mode');

        $latest = Paiement::with('consultation.rendezVous.patient.user')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return response()->json(compact(
            'paidThis',
            'changeRev',
            'successRate',
            'byMode',
            'latest'
        ));
    }
}

