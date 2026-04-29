@extends('layouts/layoutMaster')

@section('title', 'Loyalty — ' . $customer->name)

@section('page-style')
<style>
  .tier-badge-silver   { background: linear-gradient(135deg, #c0c0c0, #e8e8e8); color: #555; }
  .tier-badge-gold     { background: linear-gradient(135deg, #f5a623, #f8d07a); color: #7a4f00; }
  .tier-badge-platinum { background: linear-gradient(135deg, #6ec6f5, #b3e5fc); color: #01579b; }
  .tier-badge {
    display: inline-block;
    padding: 4px 14px;
    border-radius: 20px;
    font-weight: 700;
    font-size: .8rem;
    text-transform: uppercase;
    letter-spacing: .5px;
  }
  .progress-thin { height: 8px; border-radius: 4px; }
  .stat-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; font-size: 1.4rem; }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

  {{-- ── Back button ──────────────────────────────────────────────────────── --}}
  <a href="{{ route('loyalty.index') }}" class="btn btn-outline-secondary mb-4">
    <i class="ti ti-arrow-left me-1"></i>Back to Loyalty Overview
  </a>

  @php
    $points     = $customer->loyalty_points ?? 0;
    $tier       = $loyaltyService->getTier($points);
    $bonusActive = $customer->loyalty_bonus_expires_at && now()->lessThan($customer->loyalty_bonus_expires_at);

    [$barVal, $barMax] = match($tier) {
      'silver'   => [$points, 20000],
      'gold'     => [$points - 20001, 65000 - 20001],
      'platinum' => [1, 1],
    };
    $pct = $tier === 'platinum' ? 100 : min(100, round($barVal / $barMax * 100));

    $tierBg = match($tier) {
      'gold'     => 'tier-badge-gold',
      'platinum' => 'tier-badge-platinum',
      default    => 'tier-badge-silver',
    };
    $tierColor = match($tier) {
      'gold'     => '#f5a623',
      'platinum' => '#6ec6f5',
      default    => '#c0c0c0',
    };
  @endphp

  <div class="row g-4">

    {{-- ── Left column: customer card + tier card ───────────────────────── --}}
    <div class="col-xl-4">

      {{-- Customer profile card --}}
      <div class="card mb-4">
        <div class="card-body text-center py-4">
          <img src="{{ $customer->img ? asset($customer->img) : asset('no-avatar.png') }}"
               alt="avatar" width="90" class="rounded-circle mb-3">
          <h5 class="mb-1">{{ $customer->name }}</h5>
          <p class="text-muted mb-2">{{ $customer->email }}</p>
          <p class="text-muted small mb-3">{{ $customer->phone }}</p>
          <span class="tier-badge {{ $tierBg }} fs-6">
            @if ($tier === 'silver')   🥈 Silver
            @elseif ($tier === 'gold') 🥇 Gold
            @else                      💎 Platinum
            @endif
          </span>
        </div>
      </div>

      {{-- Points summary card --}}
      <div class="card mb-4">
        <div class="card-body">
          <h6 class="fw-semibold mb-3">Points Balance</h6>

          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="text-muted">Current Points</span>
            <strong class="fs-5">{{ number_format($points) }}</strong>
          </div>

          <div class="d-flex align-items-center justify-content-between mb-3">
            <span class="text-muted">Can Redeem?</span>
            @if ($points >= 9000)
              <span class="badge bg-success"><i class="ti ti-check me-1"></i>Yes — 10% off ready</span>
            @else
              <span class="badge bg-label-secondary">{{ number_format(9000 - $points) }} pts needed</span>
            @endif
          </div>

          @if ($tier !== 'platinum')
            <div class="mb-1 d-flex justify-content-between">
              <small class="text-muted">Progress to {{ ucfirst($nextTier['tier']) }}</small>
              <small>{{ number_format($nextTier['points_needed']) }} pts left</small>
            </div>
            <div class="progress progress-thin mb-1">
              <div class="progress-bar" style="width:{{ $pct }}%; background:{{ $tierColor }}"></div>
            </div>
            <small class="text-muted">{{ $pct }}% of the way to {{ ucfirst($nextTier['tier']) }}</small>
          @else
            <div class="alert alert-info py-2 mb-0">
              <i class="ti ti-crown me-1"></i>Maximum tier reached!
            </div>
          @endif
        </div>
      </div>

      {{-- Bonus discount card --}}
      <div class="card mb-4 {{ $bonusActive ? 'border-success' : '' }}">
        <div class="card-body">
          <h6 class="fw-semibold mb-3"><i class="ti ti-gift me-1 text-success"></i>Bonus Discount</h6>
          @if ($bonusActive)
            <div class="alert alert-success py-2 mb-2">
              <i class="ti ti-clock me-1"></i>
              <strong>10% bonus</strong> active on next order
            </div>
            <p class="text-muted small mb-0">
              Expires: <strong>{{ $customer->loyalty_bonus_expires_at->format('d M Y, H:i') }}</strong>
            </p>
          @else
            <p class="text-muted mb-0">No active bonus discount. The customer earns one by redeeming 9,000 points.</p>
          @endif
        </div>
      </div>

    </div>

    {{-- ── Right column: stats + transaction history ────────────────────── --}}
    <div class="col-xl-8">

      {{-- Impact stats --}}
      <div class="row g-3 mb-4">
        <div class="col-sm-4">
          <div class="card text-center h-100">
            <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
              <div class="stat-icon bg-label-info mb-2"><i class="ti ti-shopping-bag"></i></div>
              <h3 class="mb-0">{{ number_format($bagsSaved) }}</h3>
              <p class="text-muted mb-0 small">Bags Saved</p>
            </div>
          </div>
        </div>
        <div class="col-sm-4">
          <div class="card text-center h-100">
            <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
              <div class="stat-icon bg-label-success mb-2"><i class="ti ti-currency-dollar"></i></div>
              <h3 class="mb-0">${{ number_format($moneySaved, 2) }}</h3>
              <p class="text-muted mb-0 small">Money Saved</p>
            </div>
          </div>
        </div>
        <div class="col-sm-4">
          <div class="card text-center h-100">
            <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
              <div class="stat-icon bg-label-warning mb-2"><i class="ti ti-star"></i></div>
              <h3 class="mb-0">{{ number_format($points) }}</h3>
              <p class="text-muted mb-0 small">Blue Points</p>
            </div>
          </div>
        </div>
      </div>

      {{-- Tier rules reminder --}}
      <div class="card mb-4">
        <div class="card-body pb-2">
          <h6 class="fw-semibold mb-3">Loyalty Tiers</h6>
          <div class="row g-2">
            <div class="col-md-4">
              <div class="p-2 rounded {{ $tier === 'silver' ? 'border border-secondary' : '' }}" style="background:#f8f8f8">
                <div class="d-flex align-items-center gap-2 mb-1">
                  <span class="tier-badge tier-badge-silver">🥈 Silver</span>
                  @if ($tier === 'silver') <span class="badge bg-secondary">Current</span> @endif
                </div>
                <div class="text-muted small">0 – 20,000 pts</div>
                <div class="small"><strong>2 pts</strong> per $0.01 spent</div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="p-2 rounded {{ $tier === 'gold' ? 'border border-warning' : '' }}" style="background:#fffbf0">
                <div class="d-flex align-items-center gap-2 mb-1">
                  <span class="tier-badge tier-badge-gold">🥇 Gold</span>
                  @if ($tier === 'gold') <span class="badge bg-warning text-dark">Current</span> @endif
                </div>
                <div class="text-muted small">20,001 – 65,000 pts</div>
                <div class="small"><strong>3 pts</strong> per $0.01 spent</div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="p-2 rounded {{ $tier === 'platinum' ? 'border border-info' : '' }}" style="background:#f0f8ff">
                <div class="d-flex align-items-center gap-2 mb-1">
                  <span class="tier-badge tier-badge-platinum">💎 Platinum</span>
                  @if ($tier === 'platinum') <span class="badge bg-info">Current</span> @endif
                </div>
                <div class="text-muted small">65,001+ pts</div>
                <div class="small"><strong>5 pts</strong> per $0.01 spent</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Transaction history --}}
      <div class="card">
        <div class="card-header d-flex align-items-center">
          <h6 class="mb-0 fw-semibold"><i class="ti ti-history me-2"></i>Points History</h6>
        </div>
        <div class="table-responsive">
          <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
              <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Points</th>
                <th>Balance After</th>
                <th>Order</th>
                <th>Note</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($transactions as $tx)
                @php
                  [$badge, $icon, $sign] = match($tx->type) {
                    'earned'   => ['bg-success',   'ti-arrow-up',       '+'],
                    'redeemed' => ['bg-danger',    'ti-arrow-down',     ''],
                    'bonus'    => ['bg-info',       'ti-gift',          '+'],
                    default    => ['bg-secondary',  'ti-circle',        ''],
                  };
                @endphp
                <tr>
                  <td>
                    <div class="fw-semibold">{{ $tx->created_at->format('d M Y') }}</div>
                    <div class="text-muted small">{{ $tx->created_at->format('H:i') }}</div>
                  </td>
                  <td>
                    <span class="badge {{ $badge }}">
                      <i class="ti {{ $icon }} me-1"></i>{{ ucfirst($tx->type) }}
                    </span>
                  </td>
                  <td class="{{ $tx->type === 'redeemed' ? 'text-danger' : 'text-success' }} fw-bold">
                    {{ $sign }}{{ number_format(abs($tx->points)) }}
                  </td>
                  <td>{{ number_format($tx->balance_after) }}</td>
                  <td>
                    @if ($tx->order_id)
                      <a href="{{ route('orders.show', $tx->order_id) }}" class="badge bg-label-primary">
                        #{{ $tx->order_id }}
                      </a>
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td class="text-muted small">{{ $tx->description }}</td>
                </tr>
              @empty
                <tr><td colspan="6" class="text-center p-4 text-muted">No transactions yet</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer">{{ $transactions->links() }}</div>
      </div>

    </div>
  </div>

</div>
@endsection
