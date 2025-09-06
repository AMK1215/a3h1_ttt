@extends('layouts.master')
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Shan Report Transactions</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Shan Report Transactions</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Filter Options</h3>
                    </div>
                    <div class="card-body">
                        <form id="filterForm">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="agent_code">Agent Code *</label>
                                        <select id="agent_code" name="agent_code" class="form-control" required>
                                            <option value="">Select Agent</option>
                                            @foreach($agents as $agent)
                                                <option value="{{ $agent->shan_agent_code }}">{{ $agent->name }} ({{ $agent->shan_agent_code }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="date_from">Date From</label>
                                        <input type="date" id="date_from" name="date_from" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="date_to">Date To</label>
                                        <input type="date" id="date_to" name="date_to" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="member_account">Member Account</label>
                                        <input type="text" id="member_account" name="member_account" class="form-control" placeholder="Enter member account">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="group_by">Group By</label>
                                        <select id="group_by" name="group_by" class="form-control">
                                            <option value="both">Both (Agent & Member)</option>
                                            <option value="agent_id">Agent Only</option>
                                            <option value="member_account">Member Only</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="submit" id="fetchData" class="btn btn-primary btn-block">Fetch</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Loading indicator -->
                <div id="loading" class="text-center" style="display:none;">
                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                    <p>Loading data...</p>
                </div>

                <!-- Error message -->
                <div id="error-message" class="alert alert-danger" style="display:none;"></div>

                <!-- Agent Info Card -->
                <div id="agent-info-card" class="card" style="display:none;">
                    <div class="card-header">
                        <h3 class="card-title">Agent Information</h3>
                    </div>
                    <div class="card-body">
                        <div id="agent-info-content"></div>
                    </div>
                </div>

                <!-- Summary Card -->
                <div id="summary-card" class="card" style="display:none;">
                    <div class="card-header">
                        <h3 class="card-title">Summary</h3>
                    </div>
                    <div class="card-body">
                        <div id="summary-content"></div>
                    </div>
                </div>

                <!-- Report Data Card -->
                <div id="report-card" class="card" style="display:none;">
                    <div class="card-header">
                        <h3 class="card-title">Report Data</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="report-table" class="table table-bordered table-hover">
                                <thead id="report-table-head">
                                    <!-- Dynamic headers will be inserted here -->
                                </thead>
                                <tbody id="report-table-body">
                                    <!-- Dynamic data will be inserted here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('script')
<script>
$(document).ready(function() {
    // Set default date range (last 30 days)
    const today = new Date();
    const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
    
    $('#date_to').val(today.toISOString().split('T')[0]);
    $('#date_from').val(thirtyDaysAgo.toISOString().split('T')[0]);

    // Handle form submission
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        fetchReportData();
    });

    function fetchReportData() {
        const formData = {
            agent_code: $('#agent_code').val(),
            date_from: $('#date_from').val(),
            date_to: $('#date_to').val(),
            member_account: $('#member_account').val(),
            group_by: $('#group_by').val()
        };

        // Validate required fields
        if (!formData.agent_code) {
            showError('Please select an agent code.');
            return;
        }

        // Show loading
        showLoading(true);
        hideAllCards();

        $.ajax({
            url: '{{ route("admin.shan.report.transactions.fetch") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    displayReportData(response.data);
                } else {
                    showError(response.message || 'Failed to fetch data');
                }
            },
            error: function(xhr) {
                let message = 'An error occurred while fetching data.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showError(message);
            },
            complete: function() {
                showLoading(false);
            }
        });
    }

    function displayReportData(data) {
        // Display agent info
        displayAgentInfo(data.agent_info);
        
        // Display summary
        displaySummary(data.summary);
        
        // Display report data
        displayReportTable(data.report_data, data.filters.group_by);
    }

    function displayAgentInfo(agentInfo) {
        const content = `
            <div class="row">
                <div class="col-md-4">
                    <strong>Agent ID:</strong> ${agentInfo.agent_id}
                </div>
                <div class="col-md-4">
                    <strong>Agent Code:</strong> ${agentInfo.agent_code}
                </div>
                <div class="col-md-4">
                    <strong>Agent Name:</strong> ${agentInfo.agent_name}
                </div>
            </div>
        `;
        $('#agent-info-content').html(content);
        $('#agent-info-card').show();
    }

    function displaySummary(summary) {
        const content = `
            <div class="row">
                <div class="col-md-2">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-layer-group"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Groups</span>
                            <span class="info-box-number">${summary.total_groups}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-exchange-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Transactions</span>
                            <span class="info-box-number">${summary.total_transactions}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-dollar-sign"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Transaction Amount</span>
                            <span class="info-box-number">${summary.total_transaction_amount}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary"><i class="fas fa-coins"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Bet Amount</span>
                            <span class="info-box-number">${summary.total_bet_amount}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="info-box">
                        <span class="info-box-icon bg-secondary"><i class="fas fa-check-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Valid Amount</span>
                            <span class="info-box-number">${summary.total_valid_amount}</span>
                        </div>
                    </div>
                </div>
                ${summary.unique_agents ? `
                <div class="col-md-2">
                    <div class="info-box">
                        <span class="info-box-icon bg-dark"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Unique Agents</span>
                            <span class="info-box-number">${summary.unique_agents}</span>
                        </div>
                    </div>
                </div>
                ` : ''}
                ${summary.unique_members ? `
                <div class="col-md-2">
                    <div class="info-box">
                        <span class="info-box-icon bg-light"><i class="fas fa-user"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Unique Members</span>
                            <span class="info-box-number">${summary.unique_members}</span>
                        </div>
                    </div>
                </div>
                ` : ''}
            </div>
        `;
        $('#summary-content').html(content);
        $('#summary-card').show();
    }

    function displayReportTable(data, groupBy) {
        // Clear previous data
        $('#report-table-head').empty();
        $('#report-table-body').empty();

        // Set table headers based on group by
        let headers = '';
        if (groupBy === 'agent_id') {
            headers = `
                <tr>
                    <th>Agent ID</th>
                    <th>Agent Name</th>
                    <th>Total Transactions</th>
                    <th>Total Transaction Amount</th>
                    <th>Total Bet Amount</th>
                    <th>Total Valid Amount</th>
                    <th>Avg Before Balance</th>
                    <th>Avg After Balance</th>
                    <th>First Transaction</th>
                    <th>Last Transaction</th>
                </tr>
            `;
        } else if (groupBy === 'member_account') {
            headers = `
                <tr>
                    <th>Member Account</th>
                    <th>Total Transactions</th>
                    <th>Total Transaction Amount</th>
                    <th>Total Bet Amount</th>
                    <th>Total Valid Amount</th>
                    <th>Avg Before Balance</th>
                    <th>Avg After Balance</th>
                    <th>First Transaction</th>
                    <th>Last Transaction</th>
                </tr>
            `;
        } else {
            headers = `
                <tr>
                    <th>Agent ID</th>
                    <th>Agent Name</th>
                    <th>Member Account</th>
                    <th>Total Transactions</th>
                    <th>Total Transaction Amount</th>
                    <th>Total Bet Amount</th>
                    <th>Total Valid Amount</th>
                    <th>Avg Before Balance</th>
                    <th>Avg After Balance</th>
                    <th>First Transaction</th>
                    <th>Last Transaction</th>
                </tr>
            `;
        }
        $('#report-table-head').html(headers);

        // Add data rows
        let rows = '';
        data.forEach(function(item) {
            let row = '';
            if (groupBy === 'agent_id') {
                row = `
                    <tr>
                        <td>${item.agent_id}</td>
                        <td>${item.agent ? item.agent.name : 'N/A'}</td>
                        <td>${item.total_transactions}</td>
                        <td>${parseFloat(item.total_transaction_amount).toFixed(2)}</td>
                        <td>${parseFloat(item.total_bet_amount).toFixed(2)}</td>
                        <td>${parseFloat(item.total_valid_amount).toFixed(2)}</td>
                        <td>${parseFloat(item.avg_before_balance).toFixed(2)}</td>
                        <td>${parseFloat(item.avg_after_balance).toFixed(2)}</td>
                        <td>${formatDateTime(item.first_transaction)}</td>
                        <td>${formatDateTime(item.last_transaction)}</td>
                    </tr>
                `;
            } else if (groupBy === 'member_account') {
                row = `
                    <tr>
                        <td>${item.member_account}</td>
                        <td>${item.total_transactions}</td>
                        <td>${parseFloat(item.total_transaction_amount).toFixed(2)}</td>
                        <td>${parseFloat(item.total_bet_amount).toFixed(2)}</td>
                        <td>${parseFloat(item.total_valid_amount).toFixed(2)}</td>
                        <td>${parseFloat(item.avg_before_balance).toFixed(2)}</td>
                        <td>${parseFloat(item.avg_after_balance).toFixed(2)}</td>
                        <td>${formatDateTime(item.first_transaction)}</td>
                        <td>${formatDateTime(item.last_transaction)}</td>
                    </tr>
                `;
            } else {
                row = `
                    <tr>
                        <td>${item.agent_id}</td>
                        <td>${item.agent ? item.agent.name : 'N/A'}</td>
                        <td>${item.member_account}</td>
                        <td>${item.total_transactions}</td>
                        <td>${parseFloat(item.total_transaction_amount).toFixed(2)}</td>
                        <td>${parseFloat(item.total_bet_amount).toFixed(2)}</td>
                        <td>${parseFloat(item.total_valid_amount).toFixed(2)}</td>
                        <td>${parseFloat(item.avg_before_balance).toFixed(2)}</td>
                        <td>${parseFloat(item.avg_after_balance).toFixed(2)}</td>
                        <td>${formatDateTime(item.first_transaction)}</td>
                        <td>${formatDateTime(item.last_transaction)}</td>
                    </tr>
                `;
            }
            rows += row;
        });
        $('#report-table-body').html(rows);
        $('#report-card').show();
    }

    function formatDateTime(dateTimeString) {
        if (!dateTimeString) return 'N/A';
        const date = new Date(dateTimeString);
        return date.toLocaleString();
    }

    function showLoading(show) {
        if (show) {
            $('#loading').show();
        } else {
            $('#loading').hide();
        }
    }

    function showError(message) {
        $('#error-message').text(message).show();
        setTimeout(function() {
            $('#error-message').hide();
        }, 5000);
    }

    function hideAllCards() {
        $('#agent-info-card').hide();
        $('#summary-card').hide();
        $('#report-card').hide();
        $('#error-message').hide();
    }
});
</script>
@endsection
