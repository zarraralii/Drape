@include('admin.layouts.header', ['title' => 'User Details'])
@include('admin.layouts.menu')

@php($user = $data['user'] ?? null)

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            @if (!$user)
                <div class="alert alert-warning">User not found.</div>
            @else
                <div class="row">
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body text-center">
                                @if ($user->profile_image)
                                    <img src="{{ $user->profile_image }}" class="rounded-circle mb-3" width="90" height="90" style="object-fit:cover;">
                                @endif
                                <h5 class="mb-1">{{ $user->name }}</h5>
                                <p class="text-muted mb-1">{{ $user->email }}</p>
                                <p class="text-muted mb-0">{{ $user->phone ?? '—' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header"><h5 class="mb-0">Order History</h5></div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>UID</th>
                                                <th>Date</th>
                                                <th>Total</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($user->orders as $order)
                                                <tr>
                                                    <td>{{ $order->uid ?? $order->id }}</td>
                                                    <td>{{ $order->created_at->format('M d, Y') }}</td>
                                                    <td>{{ money($order->total_amount) }}</td>
                                                    <td>{{ $order->status ?? '—' }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="4" class="text-center text-muted">No orders yet.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@include('admin.layouts.footer')
