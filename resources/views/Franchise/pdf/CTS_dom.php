<head>
	<style>
		body {
			font-family: 'Helvetica Neue', Arial, Helvetica, Geneva, sans-serif;
		}

		table {
			width: 800px;
			text-align: center;
			font-size: 13px;
			word-break: break-all;
			padding: 4px 3px;
			border-collapse: collapse;
		}

		th,
		td {
			border: 1px solid #000;
			border-collapse: collapse;
			padding: 4px 3px;
			text-align: center;
			font-size: 13px;
			word-break: break-all;
		}

		.box_laout {
			width: 100%;
			display: inline-block;
		}

		.header_name {
			text-align: left;
			font-weight: bold;
			font-size: 20px;
			margin-bottom: 5px;
			width: 100%;
		}

		.logo {
			width: 100%;
			text-align: center;
			margin: 15px 0 15px 0;
		}

		.logo img {
			width: 250px;
		}

		.top_wrapper {
			width: 100%;
			display: inline-block;
			margin-bottom: 0px;
			margin-top: 15px;
		}

		.fst_sec {
			margin-bottom: 15px;
		}

		.lt_sec {
			display: inline-block;
			margin-right: 4px;
			font-weight: 600;
			font-size: 14px;
		}

		.lt_sec span {
			font-weight: normal;
		}

		.bot_wrapper {
			width: 100%;
			display: inline-block;
			margin-top: 24px;
		}

		.border_box {
			border: 2px solid #000;
			display: block;
		}

		.min_padding {
			padding: 0 10px;
		}

		.full_width {
			text-align: left;
		}

		tr th:first-child {
			border-left: none !important;
		}

		tr td:first-child {
			border-left: none !important;
		}

		tr th:last-child {
			border-right: none !important;
		}

		tr td:last-child {
			border-right: none !important;
		}

		thead {
			display: table-header-group
		}

		tfoot {
			display: table-footer-group
		}

		.mar0 {
			margin-bottom: 0px;
		}

		table img {
			float: none;
			margin: 0 auto;
			display: inline-block;
			width: 75px;
			margin-left: 10px;
		}

		.lt_sec_driver {
			float: left;
			margin-right: 10px;
			font-weight: 600;
			font-size: 14px;
			line-height: 55px;
		}

		.lt_sec_img {
			float: left;
		}

		/* #tab  {page-break-inside: avoid !important;display: table-row-group;page-break-after: always;display: inline-table;} */
	</style>
</head>

<body>
	<div id="tab">
		<div class="header_name" style="text-align: center;margin: 15px 0;">CTS Driver Log</div>
		<div class="top_wrapper">
			<div class="top_sec">
				<div class="fst_sec">
					<div class="lt_sec">Driver Name : <span><?php echo $pdf[0]->name ?></span></div>
				</div>
			</div>
			<div class="top_sec">
				<div class="fst_sec">
					<div class="lt_sec">CTS Vehicle Number (not VIN) : <span><?php echo $pdf[0]->VIN ?></span></div>
				</div>
			</div>
			<div class="top_sec">
				<div class="fst_sec">
					<div class="lt_sec">Date : <span><?php echo date("m/d/Y", strtotime($pdf[0]->date_of_service)) ?></span></div>
				</div>
			</div>

			<div class="top_sec">
				<div class="fst_sec">
					<div class="lt_sec">Timezone #: <span> <?php echo $timezone ?> </span></div>
				</div>
			</div>
		</div>
		<div class="full_width">
			<table style="width: 100%; text-align: center;border:1px solid #000;">
				<tr>
					<th width="15%">Trip Number </th>
					<th width="10%">Amby</br>or WC</th>
					<th width="29%">Members Name</th>
					<th width="12%">Pick Up </br>Time </th>
					<th width="12%">Drop Off</br> Time </th>
					<th width="22%">Members Signature</th>
				</tr>

				<?php
				$dirverimg = '';
				if ($pdf[0]->upload_signature_original) {
					$exists = Storage::disk('s3')->exists($pdf[0]->upload_signature_original);
					if($exists)
					{
						$dirverimg = '<img src="' .awsasset($pdf[0]->upload_signature_original) . '" height="55px" width="150px">';
					}
				}
				//$pagebreak = 'y';

				foreach ($pdf as $row) {
					/*if ($row['pagebreak'] == 'n') {
						$pagebreak = 'n';
					}*/

					if ($row->member_sign != '') {
						$img = '';
						if ($row->member_sign){
							$exists = Storage::disk('s3')->exists($row->member_sign);
							if($exists)
							{
								$img = '<div style="width:100%;height:50px;"><img src="' .awsasset($row->member_sign) . '" height="55px"></div>';
							}
						}
					} else if ($row->reason_text != '') {
						$img = $row->reason_text;
						if ($row->reason_text == 'Physical Limitation') {
							$img = 'U.T.S';
						}
					} else {
						$img = '';
					}

					$trip_arr = (array) $row;
					$res = gettripdata($trip_arr, 'pdflog');
					$pickup_time = $res['pickup_time'];
					$dropoff_time = $res['dropoff_time'];
				?>
					<tr>
						<td><?php echo $row->TripID; ?></td>
						<td><?php echo $row->level_of_service; ?></td>
						<td><?php echo $row->Member_name; ?></td>
						<td><?php echo $pickup_time; ?></td>
						<td><?php echo $dropoff_time; ?></td>
						<td><?php echo $img; ?></td>
					</tr>
				<?php } ?>
			</table>
		</div>

		<div class="top_wrapper">
			<div class="top_sec">
				<div class="fst_sec mar0">
					<div class="lt_sec_driver">Drivers Signature :</div>
					<div class="lt_sec_img"><?php echo $dirverimg; ?></div>
				</div>
			</div>
		</div>
	</div>
</body>