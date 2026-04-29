@extends('layouts/layoutMaster')

@section('title', 'Loyalty Program')

@section('page-style')
<style>
  .tier-badge-silver   { background: linear-gradient(135deg, #c0c0c0, #e8e8e8); color: #555; }
  .tier-badge-gold     { background: linear-gradient(135deg, #f5a623, #f8d07a); color: #7a4f00; }
  .tier-badge-platinum { background: linear-gradient(135deg, #6ec6f5, #b3e5fc); color: #01579b; }
  .tier-badge {
    display: inline-block;
    padding: 3px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: .75rem;
    text-transform: uppercase;
    letter-spacing: .5px;
  }
  .progress-thin { height: 6px; border-radius: 3px; }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

  {{-- ── Page header ──────────────────────────────────────────────────────── --}}
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h4 class="fw-bold mb-1"><i class="ti ti-star me-2 text-warning"></i>Loyalty Program</h4>
      <p class="text-muted mb-0">Monitor customer blue-points, tiers, and redemptions</p>
    </div>
  </div>

  {{-- ── Summary stat cards ───────────────────────────────────────────────── --}}
  <div class="row g-4 mb-4">

    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div>
              <span class="text-muted">Total Customers</span>
              <h3 class="mt-1 mb-0">{{ number_format($totalCustomers) }}</h3>
              <small class="text-muted">Registered users</small>
            </div>
            <div class="avatar"><span class="avatar-initial rounded bg-label-primary"><i class="ti ti-users ti-sm"></i></span></div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div>
              <span class="text-muted">Total Points in System</span>
              <h3 class="mt-1 mb-0">{{ number_format($totalPoints) }}</h3>
              <small class="text-muted">Blue points balance</small>
            </div>
            <div class="avatar"><span class="avatar-initial rounded bg-label-info"><i class="ti ti-diamond ti-sm"></i></span></div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div>
              <span class="text-muted">Points Redeemed</span>
              <h3 class="mt-1 mb-0">{{ number_format($totalRedeemed) }}</h3>
              <small class="text-muted">All-time redemptions</small>
            </div>
            <div class="avatar"><span class="avatar-initial rounded bg-label-success"><i class="ti ti-discount ti-sm"></i></span></div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div>
              <span class="text-muted">Tier Distribution</span>
              <div class="mt-1">
                <span class="tier-badge tier-badge-silver me-1">{{ $silverCount }} Silver</span>
                <span class="tier-badge tier-badge-gold me-1">{{ $goldCount }} Gold</span>
                <span class="tier-badge tier-badge-platinum">{{ $platinumCount }} Platinum</span>
              </div>
            </div>
            <div class="avatar"><span class="avatar-initial rounded bg-label-warning"><i class="ti ti-trophy ti-sm"></i></span></div>
          </div>
        </div>
      </div>
    </div>

  </div>

  {{-- ── Tier info banner ─────────────────────────────────────────────────── --}}
  <div class="card mb-4">
    <div class="card-body">
      <h6 class="fw-semibold mb-3"><i class="ti ti-info-circle me-1"></i>Program Rules</h6>
      <div class="row g-3">
        <div class="col-md-4">
          <div class="d-flex align-items-center gap-3 p-3 rounded" style="background:#f5f5f5">
            <div class="avatar"><span class="avatar-initial rounded tier-badge-silver" style="width:44px;height:44px;"><i class="ti ti-medal ti-sm"></i></span></div>
            <div>
              <div class="fw-bold">Silver Tier</div>
              <div class="text-muted small">0 – 20,000 pts · <strong>2 pts</strong> per $0.01</div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="d-flex align-items-center gap-3 p-3 rounded" style="background:#fffbf0">
            <div class="avatar"><span class="avatar-initial rounded tier-badge-gold" style="width:44px;height:44px;"><i class="ti ti-medal-2 ti-sm"></i></span></div>
            <div>
              <div class="fw-bold">Gold Tier</div>
              <div class="text-muted small">20,001 – 65,000 pts · <strong>3 pts</strong> per $0.01</div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="d-flex align-items-center gap-3 p-3 rounded" style="background:#f0f8ff">
            <div class="avatar"><span class="avatar-initial rounded tier-badge-platinum" style="width:44px;height:44px;"><i class="ti ti-crown ti-sm"></i></span></div>
            <div>
              <div class="fw-bold">Platinum Tier</div>
              <div class="text-muted small">65,001+ pts · <strong>5 pts</strong> per $0.01</div>
            </div>
          </div>
        </div>
        <div class="col-12">
          <div class="alert alert-info mb-0 py-2">
            <i class="ti ti-gift me-1"></i>
            Every <strong>9,000 points</strong> = <strong>10% discount</strong> on the next order.
            A second <strong>10% bonus</strong> is granted on the following order if placed within <strong>3 days</strong>.
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ── Filter ───────────────────────────────────────────────────────────── --}}
  <div class="card mb-4">
    <div class="card-body">
      <form class="row g-2" method="GET" action="{{ route('loyalty.index') }}">
        <div class="col-md-4">
          <input type="text" name="q" class="form-control" placeholder="Search by name or phone" value="{{ request('q') }}">
        </div>
        <div class="col-md-3">
          <select name="tier" class="form-select">
            <option value="">All Tiers</option>
            <option value="silver"   {{ request('tier') === 'silver'   ? 'selected' : '' }}>Silver</option>
            <option value="gold"     {{ request('tier') === 'gold'     ? 'selected' : '' }}>Gold</option>
            <option value="platinum" {{ request('tier') === 'platinum' ? 'selected' : '' }}>Platinum</option>
          </select>
        </div>
        <div class="col-auto">
          <button class="btn btn-primary">Filter</button>
        </div>
        <div class="col-auto">
          <a href="{{ route('loyalty.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
      </form>
    </div>
  </div>

  {{-- ── Customer table ───────────────────────────────────────────────────── --}}
  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Customer</th>
            <th>Points</th>
            <th>Tier</th>
            <th>Progress to Next Tier</th>
            <th>Bonus Expires</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($customers as $i => $c)
            @php
              $points = $c->loyalty_points ?? 0;
              $tier   = $loyaltyService->getTier($points);
              $next   = $loyaltyService->nextTierInfo($points);
              $bonusActive = $c->loyalty_bonus_expires_at && now()->lessThan($c->loyalty_bonus_expires_at);

              // Progress bar calculation
              [$barVal, $barMax] = match($tier) {
                'silver'   => [$points, 20000],
                'gold'     => [$points - 20001, 65000 - 20001],
                'platinum' => [1, 1],
              };
              $pct = $tier === 'platinum' ? 100 : min(100, round($barVal / $barMax * 100));

              $badgeClass = match($tier) {
                'gold'     => 'tier-badge-gold',
                'platinum' => 'tier-badge-platinum',
                default    => 'tier-badge-silver',
              };
            @endphp
            <tr>
              <td>{{ $i + $customers->firstItem() }}</td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <img src="{{ $c->img ? asset($c->img) : asset('no-avatar.png') }}"
                       alt="" width="36" class="rounded-circle">
                  <div>
                    <div class="fw-semibold">{{ $c->name }}</div>
                    <div class="text-muted small">{{ $c->phone }}</div>
                  </div>
                </div>
              </td>
              <td>
                <strong>{{ number_format($points) }}</strong>
                @if ($c->loyalty_points >= 9000)
                  <span class="badge bg-label-success ms-1" title="Can redeem"><i class="ti ti-gift ti-xs"></i></span>
                @endif
              </td>
              <td><span class="tier-badge {{ $badgeClass }}">{{ ucfirst($tier) }}</span></td>
              <td style="min-width:160px">
                @if ($tier === 'platinum')
                  <div class="text-muted small">Max tier reached</div>
                @else
                  <div class="d-flex justify-content-between mb-1">
                    <small>{{ number_format($barVal) }}</small>
                    <small>{{ number_format($barMax) }} pts to {{ ucfirst($next['tier']) }}</small>
                  </div>
                  <div class="progress progress-thin">
                    <div class="progress-bar {{ $tier === 'gold' ? 'bg-warning' : 'bg-secondary' }}"
                         style="width:{{ $pct }}%"></div>
                  </div>
                @endif
              </td>
              <td>
                @if ($bonusActive)
                  <span class="badge bg-success">
                    <i class="ti ti-clock me-1"></i>
                    Expires {{ $c->loyalty_bonus_expires_at->format('d M H:i') }}
                  </span>
                @else
                  <span class="text-muted small">—</span>
                @endif
              </td>
              <td>
                <a href="{{ route('loyalty.show', $c) }}" class="btn btn-sm btn-outline-primary">
                  <i class="ti ti-eye me-1"></i>View
                </a>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center p-4 text-muted">No customers found</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">{{ $customers->links() }}</div>
  </div>

</div>
@endsection
