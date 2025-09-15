@extends('layouts.master')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="mb-2">
            <div class="col-12">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Senior List</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        {{-- <div class="row"> --}}
                <h3>Senior List</h3>
            <div class="col-12">
                <div class="d-flex justify-content-end mb-3">
                    <a href="{{ route('admin.senior.index.create') }}" class="btn btn-success" style="width: 100px;">
                        <i class="fas fa-plus text-white mr-2"></i>Create
                    </a>
                </div>

                <div class="card" style="border-radius: 20px;">
                    {{-- <div class="card-header">
                        <h3>Senior List</h3>
                    </div> --}}
                    <div class="card-body">
                          <div class="table-responsive">
                                <table id="mytable" class="table table-bordered table-hover ">
                            <thead class="text-center">
                                <th>#</th>
                                <th>SeniorName</th>
                                <th>SeniorID</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Balance</th>
                                <th>Action</th>
                                <th>Transaction</th>
                            </thead>
                            <tbody>
                                @if (isset($seniors) && count($seniors) > 0)
                                    @foreach ($seniors as $senior)
                                        <tr class="text-center">
                                            <td>{{ $loop->iteration + ($seniors->currentPage() - 1) * $seniors->perPage() }}</td>
                                            <td>{{ $senior->name }}</td>
                                            <td>{{ $senior->user_name }}</td>
                                            <td>{{ $senior->phone }}</td>
                                            <td>
                                                <small class="badge bg-gradient-{{ $senior->status == 1 ? 'success' : 'danger' }}">
                                                    {{ $senior->status == 1 ? 'active' : 'inactive' }}
                                                </small>
                                            </td>
                                            <td class="text-bold">{{ number_format($senior->balanceFloat) }}</td>
                                            <td>
                                                @if ($senior->status == 1)
                                                    <a onclick="event.preventDefault(); document.getElementById('banUser-{{ $senior->id }}').submit();"
                                                        class="me-2" href="#" data-bs-toggle="tooltip"
                                                        data-bs-original-title="Active Agent">
                                                        <i class="fas fa-user-check text-success" style="font-size: 20px;"></i>
                                                    </a>
                                                @else
                                                    <a onclick="event.preventDefault(); document.getElementById('banUser-{{ $senior->id }}').submit();"
                                                        class="me-2" href="#" data-bs-toggle="tooltip"
                                                        data-bs-original-title="InActive Agent">
                                                        <i class="fas fa-user-slash text-danger" style="font-size: 20px;"></i>
                                                    </a>
                                                @endif
                                                <form class="d-none" id="banUser-{{ $senior->id }}" action="{{ route('admin.agent.ban', $senior->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                </form>
                                                <a class="me-1" href="{{ route('admin.senior.getChangePassword', $senior->id) }}"
                                                    data-bs-toggle="tooltip" data-bs-original-title="Change Password">
                                                    <i class="fas fa-lock text-info" style="font-size: 20px;"></i>
                                                </a>
                                                <a class="me-1" href="{{ route('admin.senior.profile', $senior->id) }}"
                                                    data-bs-toggle="tooltip" data-bs-original-title="Edit Senior">
                                                    <i class="fas fa-edit text-info" style="font-size: 20px;"></i>
                                                </a>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.senior.getCashIn', $senior->id) }}"
                                                    data-bs-toggle="tooltip" data-bs-original-title="Deposit To Agent"
                                                    class="btn btn-info btn-sm">
                                                    <i class="fas fa-plus text-white me-1"></i>Deposit
                                                </a>
                                                <a href="{{ route('admin.senior.getCashOut', $senior->id) }}"
                                                    data-bs-toggle="tooltip" data-bs-original-title="Withdraw From Agent"
                                                    class="btn btn-info btn-sm">
                                                    <i class="fas fa-minus text-white me-1"></i>Withdraw
                                                </a>
                                                <a href="{{ route('admin.logs', $senior->id) }}"
                                                    data-bs-toggle="tooltip" data-bs-original-title="Logs"
                                                    class="btn btn-info btn-sm">
                                                    <i class="fas fa-right-left text-white me-1"></i>Logs
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="text-center">
                                        <td colspan="9">There were no Agents.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-end mt-3">
    {{ $seniors->links() }}
</div>
                          </div>
                    </div> <!-- /.card-body -->
                </div> <!-- /.card -->
            </div> <!-- /.col -->
        {{-- </div> <!-- /.row --> --}}
    </div> <!-- /.container-fluid -->
</section>
@endsection

@section('script')
<script>
    var successMessage = @json(session('successMessage'));
    var username = @json(session('username'));
    var password = @json(session('password'));
    var amount = @json(session('amount'));

    @if (session()->has('successMessage'))
        toastr.success(successMessage +
            `<div>
                <button class="btn btn-primary btn-sm" data-toggle="modal"
                    data-username="${username}"
                    data-password="${password}"
                    data-amount="${amount}"
                    onclick="copyToClipboard(this)">Copy</button>
            </div>`, { allowHtml: true });
    @endif

    function copyToClipboard(button) {
        var username = $(button).data('username');
        var password = $(button).data('password');
        var amount = $(button).data('amount');
        var textToCopy = "Username: " + username + "\nPassword: " + password + "\nAmount: " + amount;

        navigator.clipboard.writeText(textToCopy).then(function () {
            toastr.success("Credentials copied to clipboard!");
        }).catch(function (err) {
            toastr.error("Failed to copy text: " + err);
        });
    }
</script>
@endsection
