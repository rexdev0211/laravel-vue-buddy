@extends('layouts.adminLayout')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>

<section class="content">
    <div class="row">
        <div class="col-md-12" style="padding-top: 10px; padding-bottom: 10px">
            <a href="{{ route('admin.proUsers.transactions', $user->id) }}">Back to PRO User information</a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">PRO User: <a href="{{ route('admin.users.view', $user->id) }}" data-toggle="modal" data-target="#modal-box-div">{{ $user->name }}</a></h3>
                </div>
                <div class="box-body row">
                    @if ($type == 'segpay')
                    <div class="form-group col-md-6">
                        <label>Transaction ID</label>
                        <div>{{ $transaction->transaction_id }}</div>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Transaction Global ID</label>
                        <div>{{ $transaction->transaction_global_id }}</div>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Transaction Date</label>
                        <div>{{ $transaction->transaction_at }}</div>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Next Bill Date</label>
                        <div>{{ $transaction->next_bill_at }}</div>
                    </div>
                    @elseif ($type == '2000charge')
                    <div class="form-group col-md-4">
                        <label>Payment Option</label>
                        <div>{{ $transaction->payment_option }}</div>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Transaction ID</label>
                        <div>{{ $transaction->transaction_id }}</div>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Customer ID</label>
                        <div>{{ $transaction->customer_id }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ $type == 'segpay' ? 'Segpay ' : ($type == '2000charge' ? '2000 Charge ' : '') }}Logs</h3>
                </div>
                <!-- /.box-header -->
                @if ($transaction->logs->count() > 0)
                <div class="box-body">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                @if ($type == 'segpay')
                                <th>Approved</th>
                                <th>Country</th>
                                <th>Purchase ID</th>
                                <th>Bill Name</th>
                                <th>Transaction Time</th>
                                @elseif ($type == '2000charge')
                                <th>Status</th>
                                <th>Country</th>
                                <th>Payment Holder</th>
                                <th>Payment Option</th>
                                <th>Created At</th>
                                @endif
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($transaction->logs AS $log)
                            <tr>
                                @php
                                $data = json_decode($log->data);
                                if (isset($data->resource) && $type == '2000charge') $data = $data->resource;
                                @endphp
                                @if ($type == 'segpay')
                                <td>{{ isset($data->approved) ? $data->approved : 'Undefined' }}</td>
                                <td>{{ isset($data->billcntry) ? $data->billcntry : 'Undefined' }}</td>
                                <td>{{ isset($data->purchaseid) ? $data->purchaseid : 'Undefined' }}</td>
                                <td>{{ isset($data->billname) ? $data->billname : 'Undefined' }}</td>
                                <td>{{ isset($data->transtime) ? $data->transtime : 'Undefined' }}</td>
                                @elseif ($type == '2000charge')
                                <td>{{ isset($data->status) ? $data->status : 'Undefined'}}</td>
                                <td>{{ isset($data->customer) && isset($data->customer->country) ? $data->customer->country : 'Undefined'}}</td>
                                <td>{{ isset($data->payment) && isset($data->payment->holder) ? $data->payment->holder : 'Undefined'}}</td>
                                <td>{{ isset($data->payment) && isset($data->payment->paymentOption) ? $data->payment->paymentOption : 'Undefined'}}</td>
                                <td>{{ isset($data->created) ? $data->created : 'Undefined'}}</td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="box-body">No Payment Transaction logs found</div>
                @endif
                <!-- /.box-body -->
            </div>
        </div>
    </div>
</section>
<!-- /.content -->
@endsection
