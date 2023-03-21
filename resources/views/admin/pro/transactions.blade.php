@extends('layouts.adminLayout')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>

<section class="content">
    <div class="row">
        <div class="col-md-12" style="padding-top: 10px; padding-bottom: 10px">
            <a href="{{ route('admin.proUsers') }}">Back to PRO Users list</a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">PRO User: <a href="{{ route('admin.users.view', $user->id) }}" data-toggle="modal" data-target="#modal-box-div">{{ $user->name }}</a></h3>
                </div>
                <div class="box-body row">
                    <div class="form-group col-md-4">
                        <label>Product</label>
                        <div>{{ $user->getProPlan() }}</div>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Service</label>
                        <div>{{ $user->getIssuer() }}</div>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Transaction ID</label>
                        <div>{{ $user->getProTransactionId() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="box box-solid">
                <div class="box-header with-border">
                  <h3 class="box-title">Segpay</h3>
                </div>
                <!-- /.box-header -->
                @if ($user->allSegpayTransactions->count() > 0)
                <div class="box-body">
                    <div class="box-group" id="segpay">
                        @foreach ($user->allSegpayTransactions as $transaction)
                        <div class="panel box box-primary">
                            <div class="box-header with-border" data-toggle="collapse" data-parent="#segpay" href="#segpay-{{ $transaction->id }}" aria-expanded="false" class="collapsed">
                                <h4 class="box-title">{{ $transaction->getPackageName() }}: {{ $transaction->approved == 1 ? 'Approved' : 'Pending' }} ({{ $transaction->amount }} {{ $transaction->currency }})</h4>
                                <h4 class="box-title pull-right">{{ $transaction->created_at }}</h4>
                            </div>
                            <div id="segpay-{{ $transaction->id }}" class="panel-collapse collapse" aria-expanded="false" style="height: 0px;">
                                <div class="box-body">
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label>Email</label>
                                            <div>{{ $transaction->email }}</div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>IP</label>
                                            <div>{{ $transaction->ip }}</div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label>Transaction ID</label>
                                            <div>{{ $transaction->transaction_id }}</div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Transaction Global ID</label>
                                            <div>{{ $transaction->transaction_global_id }}</div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label>Transaction Date</label>
                                            <div>{{ $transaction->transaction_at }}</div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Next Bill Date</label>
                                            <div>{{ $transaction->next_bill_at }}</div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-md-12">
                                            <a href="{{ route('admin.proUsers.segpay.logs', ['userId' => $user->id, 'transactionId' => $transaction->id]) }}" class="btn btn-xs btn-primary"><i class="fa fa-info"></i> Check Transaction Logs</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="box-body">No Segpay payment transactions for this user</div>
                @endif
                <!-- /.box-body -->
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">2000 Charge</h3>
                </div>
                <!-- /.box-header -->
                @if ($user->allTwokTransactions->count() > 0)
                <div class="box-body">
                    <div class="box-group" id="twokcharge">
                        @foreach ($user->allTwokTransactions as $transaction)
                        <div class="panel box box-primary">
                            <div class="box-header with-border" data-toggle="collapse" data-parent="#twokcharge" href="#twokcharge-{{ $transaction->id }}" aria-expanded="false" class="collapsed">
                                <h4 class="box-title">{{ $transaction->getPackageName() }}: {{ $transaction->status }} ({{ $transaction->amount }} {{ $transaction->currency }})</h4>
                                <h4 class="box-title pull-right">{{ $transaction->created_at }}</h4>
                            </div>
                            <div id="twokcharge-{{ $transaction->id }}" class="panel-collapse collapse" aria-expanded="false" style="height: 0px;">
                                <div class="box-body">
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label>Payment Option</label>
                                            <div>{{ $transaction->payment_option }}</div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Email</label>
                                            <div>{{ $transaction->email }}</div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label>Transaction ID</label>
                                            <div>{{ $transaction->transaction_id }}</div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Customer ID</label>
                                            <div>{{ $transaction->customer_id }}</div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-md-12">
                                            <a href="{{ route('admin.proUsers.twok.logs', ['userId' => $user->id, 'transactionId' => $transaction->id]) }}" class="btn btn-xs btn-primary"><i class="fa fa-info"></i> Check Transaction Logs</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="box-body">No 2000 Charge payment transactions for this user</div>
                @endif
                <!-- /.box-body -->
            </div>
        </div>
    </div>
</section>
<!-- /.content -->
@endsection
