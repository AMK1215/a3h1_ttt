@extends('layouts.master')

@section('content')
<div class="container">
    <h2 class="mb-4">Senior Profile: <span class="text-primary">{{ $senior->name }}</span></h2>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <strong>Profile Details</strong>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-6"><strong>ID:</strong></div>
                        <div class="col-6">{{ $senior->id }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Username:</strong></div>
                        <div class="col-6">{{ $senior->user_name }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Email:</strong></div>
                        <div class="col-6">{{ $senior->email ?: '-' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Phone:</strong></div>
                        <div class="col-6">{{ $senior->phone ?: '-' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Status:</strong></div>
                        <div class="col-6">
                            @if($senior->status)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Created At:</strong></div>
                        <div class="col-6">{{ $senior->created_at }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Updated At:</strong></div>
                        <div class="col-6">{{ $senior->updated_at }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Last Login:</strong></div>
                        <div class="col-6">{{ $senior->last_login_at ?? '-' }}</div>
                    </div>
                    <hr>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Senior:</strong></div>
                        <div class="col-6">{{ $senior->agent->name ?? '-' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Agent ID:</strong></div>
                        <div class="col-6">{{ $senior->agent_id }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Agent Phone:</strong></div>
                        <div class="col-6">{{ $senior->agent->phone ?? '-' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Agent Balance:</strong></div>
                        <div class="col-6">
                            <span class="badge bg-info text-dark">
                                {{ $senior->agent ? number_format($senior->agent->balanceFloat, 2) : 'N/A' }}
                            </span>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Role:</strong></div>
                        <div class="col-6">
                            @foreach($senior->roles as $role)
                                <span class="badge bg-secondary">{{ $role->name }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Permissions:</strong></div>
                        <div class="col-6">
                            @foreach ($senior->permissions as $permission)
                                <span class="badge bg-light text-dark border mb-1">{{ $permission->title }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="mt-4">
                        @can('subagent_edit')
                            <a href="{{ route('admin.subacc.edit', $senior->id) }}" class="btn btn-warning me-2">Edit Profile</a>
                        @endcan

                        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
