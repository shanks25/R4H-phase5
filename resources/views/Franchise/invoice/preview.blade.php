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
			word-break: break-word !important;
		}

		table tr th {
			background-color: #475053;
			color: #fff;
			word-break: normal !important;
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
			font-size: 16px;
		}

		.txt_p {
			width: 100%;
			font-size: 13px;
			margin: 0 0 8px;
			line-height: 20px;
			padding-left: 20px;
			color: #000;
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
							<a href="{{url('/')}}" target="_blank">
								<img src="{{asset('assets/img/logo_horizontal.png')}}" alt="Logo" border="0">
							</a>
						</div>
					</td>
					<td width="33%">
						<div class="invoic_no"><span>ID</span> - {{$current_invoice_dtl['invoice_no']}}</div>
					</td>
					<td width="33%">
						<div class="invoic_date">
							{{
								date("m/d/Y",strtotime($current_invoice_dtl['created_at']))
							}}
						</div>
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
					<span>Phone:</span>
					<?php
					$data = $admin->phone_number;
					$result = $admin->phone_number;
					if (preg_match('/(\d{3})(\d{3})(\d{4})$/', $data,  $matches)) {
						$result = '(' . $matches[1] .  ') ' . $matches[2] . '-' . $matches[3];
						echo  $result;
					} else {
						echo $result;
					}
					?>
				</p>
			</div>
			<div class="rt_sec_bx">
				<h2 class="mid_tit"><span>To</span> - {{$franchise->provider_name ?? ''}}</h2>
				<p class="txt_p">
					<span>Email:</span>{{$franchise->email ?? ''}}<br>
					<span>Address:</span>{{$franchise->address ?? ''}}<br>
					<span>State:</span>{{$franchise->state ?? ''}}<br>
					<span>Phone:</span>
					<?php
					if (isset($franchise->phone_number)) {
						$data = $franchise->phone_number;
						$result = $franchise->phone_number;
						if (preg_match('/(\d{3})(\d{3})(\d{4})$/', $data,  $matches)) {
							$result = '(' . $matches[1] .  ') ' . $matches[2] . '-' . $matches[3];
							echo  $result;
						} else {
							echo $result;
						}
					}
					?>
				</p>
			</div>
		</div>



		<div class="table_section">
			<table style="width: 100%;text-align: center; padding: 0 20px;" cellpadding="0">
				<tr>
					<th>Sr. No.</th>
					<th>Date Of Service</th>
					<th>Trip<br>ID</th>
					<th>Member<br>Name</th>
					<th>Level Of Service</th>
					<th>Unloaded Miles</th>
					<th>Loaded <br> Miles</th>
					<th>Unloaded Miles Times<br>(HH:MM:SS)</th>
					<th>Loaded Miles Times (HH:MM:SS)</th>
					<th>Wait Time (HH:MM:SS)</th>
					<th>Adjusted Price($)</th>
					<th>Adjusted Price Details</th>
					<th>Total Price</th>
					<th>Invoice Amount</th>
					<th>Previous Payment Details</th>
				</tr>

				@foreach($invoiceDetail as $key => $inv_dtl)
				<tr>
					<td>{{ ++$key }}</td>
					<td>{{date("m/d/Y", strtotime($inv_dtl['date_of_service']))}}</td>
					<td>{{ $inv_dtl['trip_no'] }}</td>
					<td>{{ $inv_dtl['member_name'] ?? '-' }}</td>
					<td>{{ $inv_dtl['level_of_service'] }}</td>
					<td>{{ $inv_dtl['unloaded_miles'] ?? 0 }}</td>
					<td>{{ $inv_dtl['loaded_miles']?? 0 }}</td>
					<td>{{ $inv_dtl['unloaded_miles_duration']?? 0 }}</td>
					<td>{{ $inv_dtl['loaded_miles_duration']?? 0 }}</td>
					<td>
						<?php if ($inv_dtl['wait_time'] != NULL || $inv_dtl['wait_time'] != 0) {
							echo gmdate("H:i:s", $inv_dtl['wait_time']);
						} else {
							echo 'NA';
						}
						?>
					</td>
					<td>${{ decimal2digit_number($inv_dtl['price_adjustment']) }}</td>
					<td>{{ $inv_dtl['price_adjustment_detail'] ?? '-' }}</td>
					<td>${{ decimal2digit_number($inv_dtl['total_amount']) }}</td>
					<td>${{ decimal2digit_number($inv_dtl['invoice_amount']) }}</td>
					<td>
						@php
						$detail = '';
						if(isset($inv_dtl['providerRemmitanceLogTrips']))
						{
						foreach($inv_dtl['providerRemmitanceLogTrips'] as $log)
						{
						$detail .= '$'.decimal2digit_number($log->paid_amount).' paid on '.date("m/d/Y",strtotime($log->created_at)).'<br /><br />';
						}
						}

						if($detail == ''){ echo '-'; }else{
						echo $detail; }
						@endphp
					</td>
				</tr>
				@endforeach
				<tr>
					<td colspan="13" class="amount_class">Total Amount to be Paid: </td>
					<td colspan="2" class="amount_price">${{decimal2digit_number($current_invoice_dtl['provider_total_amount'])}}</td>
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