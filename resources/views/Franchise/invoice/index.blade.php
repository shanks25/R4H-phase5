<html>

<head>
	<style>
		body {
			font-family: 'Helvetica Neue', Arial, Helvetica, Geneva, sans-serif;
			color: #000;
		}

		table {
			width: 100%;
		}

		table,
		th,
		td {
			border: solid 1px #e4e4e4;
			border-collapse: collapse;
			padding: 4px 3px;
			text-align: center;
			font-size: 12px;
			word-break: break-all;
			word-wrap: break-word;
		}

		table tr th {
			background-color: #475053;
			color: #fff;
		}

		.top_section {
			display: inline-block;
			width: 100%;
			border-bottom: 1px solid #f0f3f6;
			margin-bottom: 10px;
			padding: 7px 15px 6px;
		}

		.top_section tr td {
			border: none;
			padding: 0px;
		}

		.logo {
			display: inline-block;
		}

		.logo a img {
			width: 220px;
		}

		.invoic_title {
			display: inline-block;
			width: 100%;
			text-align: center;
			font-size: 20px;
			font-weight: bold;
			text-transform: uppercase;
		}

		.mid_section {
			width: 100%;
			margin-bottom: 0px;
			margin-top: 0px;
			height: 155px;
		}

		.lt_sec_bx {
			width: 48%;
			margin-right: 3%;
			float: left;
		}

		.rt_sec_bx {
			width: 48%;
			float: left;
		}

		.mid_tit {
			width: 100%;
			color: #000;
			font-size: 16px;
			font-weight: bold;
			background-color: #f0f3f6;
			padding: 7px 20px;
			margin: 0 0 10px;
		}

		.mid_tit span {
			color: #E30416;
		}

		.txt_p {
			width: 100%;
			font-size: 13px;
			margin: 0 0 8px;
			line-height: 20px;
			padding-left: 20px;
		}

		.txt_p span {
			font-weight: bold;
			margin-right: 4px;
			font-size: 14px;
		}

		.table_section {
			width: 100%;
		}

		.invoi_bx {
			width: 100%;
			margin-bottom: 2px;
		}

		.invoic_no {
			text-align: center;
			color: #000;
			font-size: 15px;
			font-weight: bold;
		}

		.invoic_no span {
			color: #E30416;
			font-weight: bold;
			font-size: 16px;
		}

		.invoic_date {
			text-align: right;
			color: #000;
			font-size: 15px;
			font-weight: bold;
		}

		.amount_class {
			text-align: right;
			padding: 10px 10px;
			color: #000;
			font-size: 14px;
			border-right: 0px;
			font-weight: bold;
		}

		.amount_price {
			text-align: center;
			padding: 10px 10px;
			color: #000;
			font-size: 15px;
			border-right: 0px;
			font-weight: bold;
		}

		.thank_head {
			width: 100%;
			text-transform: uppercase;
			color: #000;
			font-size: 12px;
			margin-bottom: 5px;
			text-align: center;
			margin: 20px 0 4px;
		}

		.cpy {
			width: 100%;
			color: #000;
			font-size: 12px;
			text-align: center;
			font-weight: normal;
			margin: 0 0 14px;
		}

		.pdf_wrapp {
			border: 1px solid #f0f3f6;
			margin-top: 10px;
		}

		tbody tr {
			page-break-inside: avoid
		}

		.new_bx {
			width: 100%;
			padding: 10px 20px 10px;
			margin-bottom: 20px;
			border-bottom: 1px solid #f0f3f6;
			border-top: 1px solid #f0f3f6;
		}

		.label_table {
			border: none;
			margin-bottom: 8px;
		}

		.label_table th,
		.label_table td {
			border: solid 1px #e4e4e4;
			padding: 4px 3px;
			text-align: center;
			color: #000;
			font-size: 12px;
			word-break: break-word !important;
		}

		.label_table tr th {
			background-color: #f0f3f6;
			color: #fff;
			color: #000;
		}

		.total_remitt {
			color: #E30416;
			font-size: 14px;
			font-weight: bold;
		}

		.total_remitt span {
			color: #000;
		}
	</style>
</head>

<body>

	<div class="invoic_title">
		INVOICE
	</div>

	<div class="pdf_wrapp">

		<div class="top_section">
			<table style="width: 100%;text-align: center; border:none;" cellpadding="0">
				<tr>
					<td width="33%">
						<div class="logo">
							<a href="{{url('/')}}" target="_blank"><?php echo '<img src="' . public_path() . '/assets/img/logo_horizontal.png" alt="Logo"  border="0">'; ?></a>
						</div>
					</td>
					<td width="33%">
						<div class="invoic_no"><span>ID</span> - {{$current_invoice_dtl->invoice_no}}</div>
					</td>
					<td width="33%">
						<div class="invoic_date">{{date("m/d/Y",strtotime($current_invoice_dtl->created_at))}}</div>
					</td>
				</tr>
			</table>
		</div>

		<div class="mid_section">
			<div class="lt_sec_bx">
				<h2 class="mid_tit"><span>From</span> - Ride4Health</h2>
				<p class="txt_p">
					<span>Email:</span>{{$admin->email ?? ''}}<br>
					<span>Address:</span>{{$admin->address ?? ''}}<br>
					<span>State:</span>{{$admin->state ?? ''}}<br>
					<span>Phone:</span>{{formatPhoneNumber($admin->phone_number) ?? ''}}
				</p>
			</div>
			<div class="rt_sec_bx">
				<h2 class="mid_tit"><span>To</span> - {{$franchise->provider_name ?? ''}}</h2>
				<p class="txt_p">
					<span>Email:</span>{{$franchise->email}}<br>
					<span>Address:</span>{{$franchise->address ?? ''}}<br>
					<span>State:</span>{{$franchise->state ?? ''}}<br>
					<span>Phone:</span>{{formatPhoneNumber($admin->phone_number) ?? ''}}
				</p>
			</div>
		</div>

		<div class="new_bx">
			<table class="label_table" cellpadding="0">
				<thead>
					<tr>
						<th>Total Trips</th>
						<th>Total Invoice Amount</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>{{count($current_invoice_dtl->invoiceDetail)}}</td>
						<td>${{decimal2digit_number($current_invoice_dtl->provider_total_amount)}}</td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="table_section">
			<table style="width: 100%;text-align: center; padding: 0 20px;" cellpadding="0">
				<tr>
					<th width="3%">Sr.No.</th>
					<th width="7%">Date <br>Of <br>Service</th>
					<th width="8%">Trip<br>ID</th>
					<th width="6%">Member<br>Name</th>
					<th width="5%">Level<br>Of<br>Service</th>
					<th width="5%">Unloaded<br>Miles</th>
					<th width="5%">Loaded<br>Miles</th>
					<th width="7%">Unloaded Miles<br>Times<br>(HH:MM:SS)</th>
					<th width="7%">Loaded Miles<br>Times<br>(HH:MM:SS)</th>
					<th width="6%">Wait Time<br>(HH:MM:SS)</th>
					<th width="6%">Adjusted<br>Price($)</th>
					<th width="6%">Adjusted<br>Price<br> Details</th>
					<th width="7%">Total<br>Price</th>
					<th width="7%">Invoice<br> mount</th>
					<th width="8%">Previous Payment<br> Details</th>
					<th width="7%">Status</th>
				</tr>

				@foreach($current_invoice_dtl->invoiceDetail as $key => $inv_dtl)
				<tr>
					<td width="3%">{{ ++$key }}</td>
					<td width="7%">{{modifyTripTime($inv_dtl->date_of_service, date("g:i A", strtotime($inv_dtl->shedule_pickup_time)),$inv_dtl->short_timezone)}}</td>
					<td width="8%">{{ $inv_dtl->trip_no }}</td>
					<td width="6%">{{ $inv_dtl->member_name ?? '-' }}</td>
					<td width="5%">{{ $inv_dtl->level_of_service }}</td>
					<td width="5%">{{ $inv_dtl->unloaded_miles ?? 0  }}</td>
					<td width="5%">{{ $inv_dtl->loaded_miles ?? 0  }}</td>
					<td width="7%">{{ $inv_dtl->unloaded_miles_duration ?? 0  }}</td>
					<td width="7%">{{ $inv_dtl->loaded_miles_duration ?? 0 }}</td>
					<td width="6%">
						<?php
						if ($inv_dtl->wait_time != NULL || $inv_dtl->wait_time != 0) {
							echo gmdate("H:i:s", $inv_dtl->wait_time);
						} else {
							echo 'NA';
						}
						?></td>
					<td width="6%">${{ decimal2digit_number($inv_dtl->price_adjustment) }}</td>
					<td width="6%">{{ $inv_dtl->price_adjustment_detail ?? '-' }}</td>
					<td width="7%">${{ decimal2digit_number($inv_dtl->total_amount) }}</td>
					<td width="7%">${{ decimal2digit_number($inv_dtl->invoice_amount) }}</td>
					<td width="8%">
						@php
						$detail = '';
						if(isset($inv_dtl->remittancelog))
						{
						foreach($inv_dtl->remittancelog as $log)
						{
						if($log->rel_invoice_item_id < $inv_dtl->id){
							$detail .= '$'.decimal2digit_number($log->paid_amount).' paid on '.date("m/d/Y",strtotime($log->created_at)).'<br /><br />';
							}
							}
							}
							if($detail == ''){ echo '-'; }else{ echo $detail; }
							@endphp
					</td>
					<td width="7%">
						@if($inv_dtl->is_deleted == 1)
						Removed
						@else
						{{ $inv_dtl->trip->status->status_description ?? '' }}
						@endif
					</td>
				</tr>
				@endforeach
				<tr>
					<td colspan="13" class="amount_class">Total Amount to be Paid:</td>
					<td colspan="3" class="amount_price">${{decimal2digit_number($current_invoice_dtl->provider_total_amount)}}</td>
				</tr>
			</table>
		</div>

		<div>
			<p class="thank_head">THANK YOU FOR YOUR BUSINESS</p>
			<p class="cpy">Copyright &copy; {{date('Y')}} Ride4Health. All rights reserved.</p>
		</div>

	</div>

</body>

</html>