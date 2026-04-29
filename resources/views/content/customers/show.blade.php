@extends('layouts/layoutMaster')

@section('title', 'Customer Details')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
@endsection

@section('page-style')
<style>
  .tier-badge-silver   { background: linear-gradient(135deg, #c0c0c0, #e8e8e8); color: #555; }
  .tier-badge-gold     { background: linear-gradient(135deg, #f5a623, #f8d07a); color: #7a4f00; }
  .tier-badge-platinum { background: linear-gradient(135deg, #6ec6f5, #b3e5fc); color: #01579b; }
  .tier-badge {
    display: inline-block;
    padding: 3px 12px;
    border-radius: 20px;
    font-weight: 700;
    font-size: .78rem;
    text-transform: uppercase;
    letter-spacing: .5px;
  }
  .progress-thin { height: 8px; border-radius: 4px; }
</style>
@endsection

@section('content')

  {{-- Back button --}}
  <a href="{{ url()->previous() }}" class="btn btn-outline-primary mb-4">
    <i class="ti ti-arrow-left"></i> Back
  </a>

  {{-- Nav tabs --}}
  <ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item">
      <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile-tab" role="tab">
        <i class="ti ti-user me-1"></i>Profile
      </button>
    </li>
    <li class="nav-item">
      <button class="nav-link" data-bs-toggle="tab" data-bs-target="#loyalty-tab" role="tab">
        <i class="ti ti-star me-1 text-warning"></i>Loyalty
      </button>
    </li>
    <li class="nav-item">
      <button class="nav-link" data-bs-toggle="tab" data-bs-target="#reviews-tab" role="tab">
        <i class="ti ti-message me-1"></i>Reviews
      </button>
    </li>
  </ul>

  <div class="tab-content">

    {{-- ========== Profile tab ========== --}}
    <div class="tab-pane fade show active" id="profile-tab" role="tabpanel">
      <div class="card">
        <div class="card-body d-flex gap-4">
          <img src="{{ $customer->img ? asset($customer->img) : asset('no-avatar.png') }}"
               alt="avatar" width="120" class="rounded-circle">
          <div class="flex-fill">
            <h4 class="mb-2">{{ $customer->name }}</h4>
            <p class="mb-1"><strong>Email :</strong> {{ $customer->email }}</p>
            <p class="mb-0"><strong>Phone :</strong> {{ $customer->phone }}</p>
          </div>
        </div>
      </div>
    </div>

    {{-- ========== Loyalty tab ========== --}}
    <div class="tab-pane fade" id="loyalty-tab" role="tabpanel">
      @php
        use App\Services\LoyaltyService;
        $ls           = new LoyaltyService();
        $points       = $customer->loyalty_points ?? 0;
        $tier         = $ls->getTier($points);
        $nextTier     = $ls->nextTierInfo($points);
        $bonusActive  = $customer->loyalty_bonus_expires_at && now()->lessThan($customer->loyalty_bonus_expires_at);

        [$barVal, $barMax] = match($tier) {
          'silver'   => [$points, 20000],
          'gold'     => [$points - 20001, 65000 - 20001],
          'platinum' => [1, 1],
        };
        $pct = $tier === 'platinum' ? 100 : min(100, round($barVal / $barMax * 100));
        $tierBadge = match($tier) {
          'gold'     => 'tier-badge-gold',
          'platinum' => 'tier-badge-platinum',
          default    => 'tier-badge-silver',
        };
        $tierColor = match($tier) {
          'gold'     => '#f5a623',
          'platinum' => '#6ec6f5',
          default    => '#c0c0c0',
        };
        $tierEmoji = match($tier) { 'gold' => '🥇', 'platinum' => '💎', default => '🥈' };
      @endphp

      {{-- ── Stat row ── --}}
      <div class="row g-3 mb-4">
        <div class="col-sm-3">
          <div class="card text-center h-100">
            <div class="card-body py-4">
              <div class="mb-2" style="font-size:2rem;">{{ $tierEmoji }}</div>
              <span class="tier-badge {{ $tierBadge }}">{{ ucfirst($tier) }}</span>
              <p class="text-muted small mt-2 mb-0">Current Tier</p>
            </div>
          </div>
        </div>
        <div class="col-sm-3">
          <div class="card text-center h-100">
            <div class="card-body py-4">
              <h3 class="mb-1">{{ number_format($points) }}</h3>
              <p class="text-muted small mb-0">Blue Points Balance</p>
            </div>
          </div>
        </div>
        <div class="col-sm-3">
          <div class="card text-center h-100">
            <div class="card-body py-4">
              @if ($points >= 9000)
                <span class="badge bg-success fs-6 mb-1"><i class="ti ti-gift me-1"></i>Ready</span>
                <p class="text-muted small mb-0">10% Discount Available</p>
              @else
                <h3 class="mb-1">{{ number_format(9000 - $points) }}</h3>
                <p class="text-muted small mb-0">pts until next redemption</p>
              @endif
            </div>
          </div>
        </div>
        <div class="col-sm-3">
          <div class="card text-center h-100">
            <div class="card-body py-4">
              @if ($bonusActive)
                <span class="badge bg-success fs-6 mb-1"><i class="ti ti-check me-1"></i>Active</span>
                <p class="text-muted small mb-0">Bonus expires {{ $customer->loyalty_bonus_expires_at->format('d M H:i') }}</p>
              @else
                <span class="badge bg-label-secondary fs-6 mb-1">None</span>
                <p class="text-muted small mb-0">No active bonus</p>
              @endif
            </div>
          </div>
        </div>
      </div>

      {{-- ── Tier progress ── --}}
      <div class="card mb-4">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-semibold mb-0">Tier Progress</h6>
            @if ($tier !== 'platinum')
              <small class="text-muted">{{ number_format($nextTier['points_needed']) }} pts to {{ ucfirst($nextTier['tier']) }}</small>
            @endif
          </div>

          <div class="d-flex gap-2 align-items-center mb-3">
            @foreach (['silver' => [0, 20000], 'gold' => [20001, 65000], 'platinum' => [65001, null]] as $t => [$lo, $hi])
              @php
                $tb = match($t) { 'gold' => 'tier-badge-gold', 'platinum' => 'tier-badge-platinum', default => 'tier-badge-silver' };
                $em = match($t) { 'gold' => '🥇', 'platinum' => '💎', default => '🥈' };
              @endphp
              <span class="tier-badge {{ $tb }} {{ $tier === $t ? 'shadow' : 'opacity-50' }}">
                {{ $em }} {{ ucfirst($t) }}
              </span>
              @if ($t !== 'platinum') <i class="ti ti-chevron-right text-muted"></i> @endif
            @endforeach
          </div>

          @if ($tier !== 'platinum')
            <div class="progress progress-thin">
              <div class="progress-bar" style="width:{{ $pct }}%; background:{{ $tierColor }}"></div>
            </div>
            <div class="d-flex justify-content-between mt-1">
              <small class="text-muted">{{ number_format($barVal) }} pts</small>
              <small class="text-muted">{{ number_format($barMax) }} pts</small>
            </div>
          @else
            <div class="alert alert-info mb-0 py-2">💎 Maximum tier reached!</div>
          @endif
        </div>
      </div>

      {{-- ── Tier rules ── --}}
      <div class="card mb-4">
        <div class="card-body">
          <h6 class="fw-semibold mb-3">How Points Work</h6>
          <div class="row g-3">
            <div class="col-md-4">
              <div class="p-3 rounded {{ $tier==='silver' ? 'border border-2 border-secondary' : '' }}" style="background:#f8f8f8">
                <div class="fw-bold mb-1">🥈 Silver (0–20,000)</div>
                <div class="text-muted small">For every <strong>$0.01</strong> spent<br>you earn <strong>2 blue points</strong></div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="p-3 rounded {{ $tier==='gold' ? 'border border-2 border-warning' : '' }}" style="background:#fffbf0">
                <div class="fw-bold mb-1">🥇 Gold (20,001–65,000)</div>
                <div class="text-muted small">For every <strong>$0.01</strong> spent<br>you earn <strong>3 blue points</strong></div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="p-3 rounded {{ $tier==='platinum' ? 'border border-2 border-info' : '' }}" style="background:#f0f8ff">
                <div class="fw-bold mb-1">💎 Platinum (65,001+)</div>
                <div class="text-muted small">For every <strong>$0.01</strong> spent<br>you earn <strong>5 blue points</strong></div>
              </div>
            </div>
            <div class="col-12">
              <div class="alert alert-warning py-2 mb-0">
                <i class="ti ti-gift me-1"></i>
                Every <strong>9,000 points</strong> = <strong>10% discount</strong> on the next order,
                plus another <strong>10% bonus</strong> on the second order if placed within <strong>3 days</strong>.
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- ── Points history ── --}}
      <div class="card">
        <div class="card-header">
          <h6 class="fw-semibold mb-0"><i class="ti ti-history me-2"></i>Recent Points Transactions</h6>
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
              @forelse ($loyaltyTransactions as $tx)
                @php
                  [$badge, $icon] = match($tx->type) {
                    'earned'   => ['bg-success', 'ti-arrow-up'],
                    'redeemed' => ['bg-danger',  'ti-arrow-down'],
                    'bonus'    => ['bg-info',    'ti-gift'],
                    default    => ['bg-secondary','ti-circle'],
                  };
                @endphp
                <tr>
                  <td>
                    <div>{{ $tx->created_at->format('d M Y') }}</div>
                    <small class="text-muted">{{ $tx->created_at->format('H:i') }}</small>
                  </td>
                  <td><span class="badge {{ $badge }}"><i class="ti {{ $icon }} me-1"></i>{{ ucfirst($tx->type) }}</span></td>
                  <td class="{{ $tx->type==='redeemed' ? 'text-danger' : 'text-success' }} fw-bold">
                    {{ $tx->type === 'redeemed' ? '' : '+' }}{{ number_format($tx->points) }}
                  </td>
                  <td>{{ number_format($tx->balance_after) }}</td>
                  <td>
                    @if ($tx->order_id)
                      <a href="{{ route('orders.show', $tx->order_id) }}" class="badge bg-label-primary">#{{ $tx->order_id }}</a>
                    @else —
                    @endif
                  </td>
                  <td class="text-muted small">{{ $tx->description }}</td>
                </tr>
              @empty
                <tr><td colspan="6" class="text-center p-4 text-muted">No loyalty transactions yet</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer">{{ $loyaltyTransactions->links() }}</div>
      </div>

    </div>{{-- /loyalty-tab --}}

    {{-- ========== Reviews tab ========== --}}
    <div class="tab-pane fade" id="reviews-tab" role="tabpanel">
      <div class="card">
        <div class="table-responsive">
          <table class="table table-striped mb-0">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Bundle</th>
                <th>Rating</th>
                <th>Comment</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($reviews as $i => $r)
                <tr>
                  <td>{{ $i + $reviews->firstItem() }}</td>
                  <td>{{ $r->bundle->name ?? '—' }}</td>
                  <td>{{ $r->rating }}/5</td>
                  <td>{{ Str::limit($r->comment, 60) }}</td>
                  <td>
                    <span class="badge {{ $r->active ? 'bg-success' : 'bg-secondary' }}">
                      {{ $r->active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td>{{ $r->created_at->format('d M Y') }}</td>
                </tr>
              @empty
                <tr><td colspan="6" class="text-center p-4">No reviews</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer">{{ $reviews->links() }}</div>
      </div>
    </div>

  </div>{{-- /tab-content --}}
@endsection
