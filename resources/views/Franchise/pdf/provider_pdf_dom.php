<head>
	<style>
		body {
			font-family: 'Helvetica Neue', Arial, Helvetica, Geneva, sans-serif;
		}

		table {
			width: 100%;
			text-align: center;
			font-size: 13px;
			word-break: break-all;
			padding: 4px 3px;
			border-collapse: collapse;
			border: 1px solid #000;
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

		/* .bot_wrapper {width: 100%;margin-top: 24px;} */
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

		/* #tab  {page-break-inside: avoid !important;display: table-row-group;page-break-after: always;display: inline-table;} */
		tr {
			page-break-inside: always !important;
			display: table-row;
			page-break-after: auto;
		}
	</style>
</head>

<body>
	<div id="tab">
		<div class="logo">
			<?php echo '<img src="' . public_path() . '/assets/img/main_logo.png" height="55px">'; ?>
		</div>
		<div class="top_wrapper">
			<div class="top_sec">
				<div class="fst_sec">
					<div class="lt_sec">Broker: <span><?php echo $pdf[0]->payor_name ?></span></div>
					<div class="lt_sec">Vehicle Number: <span><?php echo $pdf[0]->CTS_no ?></span></div>
					<div class="lt_sec">VIN Number (Full): <span><?php echo $pdf[0]->VIN ?></span></div>
				</div>
			</div>
			<div class="mid_sec">
				<div class="fst_sec">
					<div class="lt_sec">Broker ID (If applicable): <span>NA</span></div>
					<div class="lt_sec">Date: <span>
							<?php
                            echo date("m/d/Y", strtotime($pdf[0]->date_of_service))
                            // echo $pdf[0]['date_of_serivce']; //date("m/d/Y");
                            ?>
						</span>
					</div>
					<div class="lt_sec">Week Ending: <span>
							<?php
                            echo $custom_end_date;
                            // echo date("m/d/Y", strtotime('next sunday'));
                            ?>
						</span>
					</div>
				</div>
			</div>
			<div class="last_sec">
				<div class="fst_sec">
					<div class="lt_sec">Driver Name: <span><?php echo $pdf[0]->name ?></span></div>
					<div class="lt_sec">Driver License Number: <span><?php echo $pdf[0]->license_no ?></span></div>
					<div class="lt_sec">Timezone #: <span> <?php echo $timezone ?> </span></div>

				</div>
			</div>
		</div>
		<table style="width: 100%;">
			<tr>
				<th width="10%">Date</th>
				<th width="8%">Trip</br>Number</th>
				<th width="10%">Passenger</br> Name</th>
				<th width="8%">LOS</th>
				<th width="8%">Pickup</br> Time</th>
				<th width="7%">Dropoff </br>Time</th>
				<th width="8%">Will </br>Call Time</th>
				<th width="8%">Trip </br>Mileage</th>
				<th width="8%">Wait </br>Time</th>
				<th width="8%">Billed </br>Amount</th>
				<th width="17%">Member </br>Signature</th>
			</tr>

			<?php
			$dirverimg = '';
			$diverstyle = '';

            if ($pdf[0]->upload_signature_original) {
				$exists = Storage::disk('s3')->exists($pdf[0]->upload_signature_original);
                if($exists){
                	$dirverimg = '<img src="' .awsasset($pdf[0]->upload_signature_original) . '" height="55px" width="150px">';
				}
            }

            //$pagebreak = 'y';

            foreach ($pdf as $row) {
                if ($row->member_sign != '') {
                    $img = '';
                    if ($row->member_sign) {
						$exists = Storage::disk('s3')->exists($row->member_sign);
                        if($exists)
						{
                        	$img = '<div style="width:auto;height:50px;"><img src="' .awsasset($row->member_sign) . '" height="55px"></div>'; //class="'.$class.'"
						}
                    }
                } elseif ($row->reason_text != '') {
                    $img = $row->reason_text;
                    if ($row->reason_text == 'Physical Limitation') {
                        $img = 'U.T.S';
                    }
                } else {
                    $img = '';
                }

                /*$tripTime1 = App\Model\ReportModel::getTripTime($row->id, $row->Driver_id, 4);
                if (!empty($tripTime1)) {
                    // $pickup_time = date("H:i",strtotime($tripTime1[0]["date_time"]));
                    if ($tripTime1[0]["timezone"] != '') {
                        $timezone_name = $tripTime1[0]["timezone"];
                        $pickup_time =  modifyDriverLogTimeNew(date("Y-m-d", strtotime($tripTime1[0]["date_time"])), date("H:i", strtotime($tripTime1[0]["date_time"])), $timezone_name)->format('H:i');
                    } else {
                        $pickup_time = '-';
                    }
                } else {
                    $pickup_time = '-';
                }

                $tripTime2 = App\Model\ReportModel::getTripTime($row->id, $row->Driver_id, 9);
                if (!empty($tripTime2)) {
                    // $dropoff_time = date("H:i", strtotime($tripTime2[0]["date_time"]));
                    if ($tripTime2[0]["timezone"] != '') {
                        $timezone_name = $tripTime2[0]["timezone"];
                        $dropoff_time =  modifyDriverLogTimeNew(date("Y-m-d", strtotime($tripTime2[0]["date_time"])), date("H:i", strtotime($tripTime2[0]["date_time"])), $timezone_name)->format('H:i');
                    } else {
                        $dropoff_time = '-';
                    }
                } else {
                    $dropoff_time = '-';
                }*/

                $trip_arr = (array) $row;
                $res = gettripdata($trip_arr, 'pdflog');
                $date_of_service = $res['date_of_service'];
                $pickup_time = $res['pickup_time'];
                $dropoff_time = $res['dropoff_time']; ?>
				<tr>
					<td><?php echo date("m/d/Y", strtotime($date_of_service)); ?></td>
					<td><?php echo $row->TripID; ?></td>
					<td><?php echo $row->Member_name ?></td>
					<td><?php echo $row->level_of_service ?></td>
					<td><?php echo $pickup_time ?></td>
					<td><?php echo $dropoff_time ?></td>
					<td>&nbsp;</td>
					<td><?php echo round($row->period3_miles, 4); ?></td>
					<td>&nbsp;</td>
					<td>$<?php echo decimal2digit_number(round($row->trip_price, 4)); ?></td>
					<td><?php echo $img; ?></td>
				</tr>
			<?php
            } ?>
		</table>
		<div style="padding-top:20px;" class="bot_wrapper">
			<div class="top_sec">
				<div class="fst_sec mar0">
					<div class="lt_sec">Driver Name : <span><?php echo $pdf[0]->name ?></span></div>
					<div class="lt_sec" <?php echo $diverstyle; ?>> Driver Signature: <span><?php echo $dirverimg; ?></span></div>
				</div>
			</div>
		</div>
	</div>
</body>