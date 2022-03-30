<head>
	<style>
		body {
			font-family: 'Helvetica Neue', Arial, Helvetica, Geneva, sans-serif;
		}

		table {
			width: 100%;
		}

		table,
		th,
		td {
			border: solid 1px #000;
			border-collapse: collapse;
			padding: 6px 3px;
			text-align: center;
			font-size: 12px;
			word-break: break-all;
		}

		.top_wrapper {
			width: 100%;
			display: inline-block;
			margin-bottom: 10px;
			margin-top: 5px;
		}

		.lt_sec {
			display: inline-block;
			margin-right: 4px;
			font-weight: 600;
			font-size: 13px;
		}

		.lt_sec span {
			font-weight: normal;
		}

		.header_name {
			width: 100%;
			font-weight: bold;
			font-size: 18px;
			margin-top: 25px;
			text-align: center;
			text-decoration: underline;
		}

		.text_bold {
			font-weight: bold;
		}

		.full_width {
			width: 100%;
			text-align: left;
		}

		.border-bottom_left {
			text-align: left;
		}

		.border-bottom_right {
			text-align: left;
		}

		.space_bottom {
			margin-bottom: 6px;
		}

		.space_top {
			margin-top: 22px;
		}

		.table_undertxt {
			font-size: 14px;
			margin-top: 10px;
		}

		.minfif_padding {
			width: 100%;
			margin-top: 15px;
		}

		thead {
			display: table-header-group
		}

		tfoot {
			display: table-footer-group
		}

		/* table img{float:none;max-width: 50% !important;margin: 0 auto;display: inline-block;width: 100%;margin-left:5px; } */
		tr {
			page-break-inside: always !important;
			display: table-row;
			page-break-after: auto;
		}
	</style>
</head>

<body>
	<div id="tab" style="width: 100%; margin:0 auto; text-align: center;">
		<div class="header_name">MTM, INC. DAILY TRIP LOG</div>
		<div class="top_wrapper">
			<div class="half_width">
				<div class="top_sec space_bottom">
					<div class="border-bottom_left">
						<div class="lt_sec">Transportation Broker : <span><?php echo $pdf[0]->payor_name ?></span></div>
					</div>
				</div>
				<div class="top_sec space_bottom">
					<div class="border-bottom_left">
						<div class="lt_sec">Date of Service : <span><?php echo date("m/d/Y", strtotime($pdf[0]->date_of_service)) ?></span></div>
					</div>
				</div>
				<div class="top_sec space_bottom">
					<div class="border-bottom_left">
						<div class="lt_sec">Driver's License Number : <span><?php echo $pdf[0]->license_no ?></span></div>
					</div>
				</div>

				<div class="top_sec space_bottom">
					<div class="border-bottom_left">
						<div class="lt_sec">Timezone #: <span><?php echo $timezone ?></span></div>
					</div>
				</div>
				<div class="top_sec space_bottom">
					<div class="border-bottom_left">
						<div class="lt_sec">Vehicle ID Number (VIN, Last Five Digits) : <span><?php echo $pdf[0]->VIN ?></span></div>
					</div>
				</div>
			</div>
		</div>
		<div class="full_width">
			<table style="width: 100%; text-align: center;">
				<tr>
					<th width="17%">MTM Trip Number</th>
					<th width="20%">Member's Printed Name</th>
					<th width="9%">AM/PM of</br> Pickup</th>
					<th width="9%">Scheduled </br>Pickup Time</th>
					<th width="9%">Pickup </br>Arrival</th>
					<th width="9%">Pickup </br>Departure</th>
					<th width="10%">Drop Off </br>Time</th>
					<th width="17%">Member Signature</th>
				</tr>

				<?php
				$dirverimg = '';
				if ($pdf[0]->upload_signature_original) {
					$exists = Storage::disk('s3')->exists($pdf[0]->upload_signature_original);
                    if($exists){
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
						if ($row->member_sign) {
							$exists = Storage::disk('s3')->exists($row->member_sign);
                            if($exists){
								$img = '<div style="height:90px;width:100%;"><img src="' .awsasset($row->member_sign) . '" height="55px"></div>'; //class="'.$class.'"
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

					/*$tripTime = App\Model\ReportModel::getTripTime($row->id, $row->Driver_id, 6);
					if (!empty($tripTime)) {
						// $despature_time = date("H:i", strtotime($tripTime[0]["date_time"]));
						if ($tripTime[0]["timezone"] != '') {
							$timezone_name = $tripTime[0]["timezone"];
							$despature_time =  modifyDriverLogTimeNew(date("Y-m-d", strtotime($tripTime[0]["date_time"])), date("H:i", strtotime($tripTime[0]["date_time"])), $timezone_name)->format('H:i');
						} else {
							$despature_time = '';
						}
					} else {
						$despature_time = '';
					}

					$tripTime1 = App\Model\ReportModel::getTripTime($row->id, $row->Driver_id, 4);
					if (!empty($tripTime1)) {
						// $pickup_time = date("H:i",strtotime($tripTime1[0]["date_time"]));	
						if ($tripTime1[0]["timezone"] != '') {
							$timezone_name = $tripTime1[0]["timezone"];
							$pickup_time =  modifyDriverLogTimeNew(date("Y-m-d", strtotime($tripTime1[0]["date_time"])), date("H:i", strtotime($tripTime1[0]["date_time"])), $timezone_name)->format('H:i');
						} else {
							$pickup_time = '';
						}
					} else {
						$pickup_time = '';
					}

					$tripTime2 = App\Model\ReportModel::getTripTime($row->id, $row->Driver_id, 9);
					if (!empty($tripTime2)) {
						// $dropoff_time = date("H:i", strtotime($tripTime2[0]["date_time"]));
						if ($tripTime2[0]["timezone"] != '') {
							$timezone_name = $tripTime2[0]["timezone"];
							$dropoff_time =  modifyDriverLogTimeNew(date("Y-m-d", strtotime($tripTime2[0]["date_time"])), date("H:i", strtotime($tripTime2[0]["date_time"])), $timezone_name)->format('H:i');
						} else {
							$dropoff_time = '';
						}
					} else {
						$dropoff_time = '';
					}*/

					preg_match('/\(([^\)]*)\)/', $row->timezone, $timezone_extract);
					$timezone_name =  $timezone_extract[1];

					// $shedule_pickup_time = date("g:i A", strtotime($log->shedule_pickup_time));
					// date("A", strtotime($row->shedule_pickup_time));

					/*if ($row->shedule_pickup_time == "00:00:00" || $row->shedule_pickup_time == NULL || $row->shedule_pickup_time == '') {
						
						$shedule_pickup_time = 'NA';
						$shedule_pickup_time_am_pm = 'NA';
					} else {
						$shedule_pickup_time_am_pm = modifyDriverLogTime($row->date_of_service, date("g:i A", strtotime($row->shedule_pickup_time)), $timezone_name)->format('A');

						$shedule_pickup_time = modifyDriverLogTime($row->date_of_service, date("g:i A", strtotime($row->shedule_pickup_time)), $timezone_name)->format('H:i');
					}
				
					if ($row->will_call == '1') {
						$shedule_pickup_time = 'Will Call';
						$shedule_pickup_time_am_pm = 'Will Call';
					} 
	
					if ($row->return_pick_time_type == 'Yes' ||  $row->na_apply == '1') {
						$shedule_pickup_time = 'NA';
						$shedule_pickup_time_am_pm = 'NA';
					} */

					$trip_arr = (array) $row;
					$res = gettripdata($trip_arr, 'pdflog');
					$pickup_time = $res['pickup_time'];
					$dropoff_time = $res['dropoff_time'];
					$despature_time = $res['member_on_board'];
					$shedule_pickup_time_am_pm = $res['shedule_pickup_time_am_pm'];
					$shedule_pickup_time_hr_min = $res['shedule_pickup_time_hr_min'];
				?>
					<tr>
						<td><?php echo $row->TripID; ?></td>
						<td><?php echo $row->Member_name; ?></td>
						<td><?php echo $shedule_pickup_time_am_pm; ?></td>
						<td><?php echo $shedule_pickup_time_hr_min; ?></td>
						<td><?php echo $pickup_time; ?></td>
						<td><?php echo $despature_time; ?></td>
						<td><?php echo $dropoff_time; ?></td>
						<td><?php echo $img; ?></td>
					</tr>
				<?php } ?>
			</table>

			<?php
			// echo "txtx".count($pdf);
			if (count($pdf) >= 11) {
			?>
				<div style="height:10px; display: inline-block; width:100%;">&nbsp;</div>
			<?php } ?>

			<div class="table_undertxt">
				Each leg of the transport must be documented on separate lines. A Signture is required for each leg of the transport. AM/PM is indicated for the time of the Scheduled Pickup. No shows will be indicated with NS in the Drop-Off Time.
			</div>
		</div>
		<div class="minfif_padding">
			<div class="full_width text_bold" style="margin:15px 0 15px 0px;font-size: 14px;">
				I Certify that all information contained herein is true and accurate, and understand that this statement is made subject to the applicable penalties under federal and state law for making false declarations.
			</div>
			<div class="top_sec space_top">
				<div class="border-bottom_left">
					<div class="lt_sec">DRIVER'S SIGNATURE : <?php echo $dirverimg; ?></div>
				</div>
			
				<div class="border-bottom_left">
					<div class="lt_sec">DRIVER'S PRINTED NAME : <span><?php echo $pdf[0]->name ?></span></div>
				</div>
			</div>
		</div>
	</div>
</body>