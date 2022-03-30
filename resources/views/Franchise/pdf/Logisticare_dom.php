<head>
	<style>
		body {
			font-family: 'Helvetica Neue', Arial, Helvetica, Geneva, sans-serif;
		}

		table {
			width: 800px;
		}

		table,
		th,
		td {
			border: solid 1px #000;
			border-collapse: collapse;
			padding: 4px 3px;
			text-align: center;
			font-size: 12px;
			word-break: break-all;
		}

		.header_name {
			width: 100%;
			font-weight: bold;
			font-size: 18px;
			margin-bottom: 0px;
			text-align: center;
			text-decoration: underline;
		}

		.headernor_name {
			font-size: 14px;
			font-weight: normal;
			width: 100%;
			text-align: center;
			width: 100%;
		}

		.box_laout {
			width: 100%;
			display: block;
			margin-top: 35px;
		}

		.half_width_left {
			width: 60%;
			display: inline-block;
		}

		.half_width_new {
			width: 40%;
			display: inline-block;
			text-align: right;
		}

		.top_sec {
			display: block;
			width: 100%;
		}

		.lt_sec {
			font-weight: 600;
			font-size: 13px;
		}

		.space_bottom {
			margin-bottom: 5px;
		}

		.border-bottom_left {
			text-align: left;
		}

		.full_width {
			width: 100%;
			text-align: left;
			display: block;
		}

		.half_width {
			width: 45%;
			display: inline-block;
			margin-top: 15px;
		}

		.space_top {
			margin-top: 14px;
		}

		.table_undertxt {
			font-size: 11px;
			margin-top: 10px;
			display: inline-block;
			width: 100%;
		}

		.leftmin_space {
			margin-left: 20px;
		}

		.mar0 {
			margin-bottom: 0px;
		}

		table img {
			float: none;
			margin: 0 auto;
			width: 75px;
			margin-left: 7px;
		}

		tr {
			page-break-inside: avoid !important;
		}

		td {
			page-break-inside: avoid !important;
		}

		.table_undertxt {
			page-break-inside: avoid !important;
		}
	</style>
</head>

<body>
	<div id="tab">
		<div class="header_name">Pennsylvania Non-Emergency Transportation Trip Log</div> <br> <br>
		<div class="headernor_name">LogistiCare Solutions,LLC</div>
		<div class="box_laout">
			<div class="half_width_left">
				<div class="top_sec space_bottom">
					<div class="border-bottom_left">
						<div class="lt_sec">Broker Name : <span><?php echo $pdf[0]->payor_name ?></span></div>
					</div>
				</div>
				<div class="top_sec space_bottom">
					<div class="border-bottom_left">
						<div class="lt_sec">Broker Id : <span>Thre0042</span></div>
					</div>
				</div>
				<div class="top_sec space_bottom">
					<div class="border-bottom_left">
						<div class="lt_sec">Driver's Name (as it appears on drivers license) : <span><?php echo $pdf[0]->name ?></span></div>
					</div>
				</div>

				<div class="top_sec space_bottom">
					<div class="border-bottom_left">
						<div class="lt_sec">Timezone #: <span> <?php echo $timezone ?> </span></div>
					</div>
				</div>
				<div class="top_sec space_bottom">
					<div class="border-bottom_left">
						<div class="lt_sec">Attendant/Escort Full Name(as it appears on drivers license) : <span>&nbsp;</span></div>
					</div>
				</div>
				<div class="top_sec space_bottom">
					<div class="border-bottom_left">
						<div class="lt_sec">Week Ending : <span><?php
                                                                echo $custom_end_date
                                                                // echo date("m/d/Y", strtotime('next sunday'));
                                                                // echo date("m/d/Y", strtotime('next sunday'));
                                                                // date('Y-m-d', strtotime('next tuesday'));
                                                                ////echo date('Y-m-d', strtotime('next sunday', strtotime($pdf[0]['date_of_service'])));
                                                                ?></span>
						</div>
					</div>
				</div>
				<div class="top_sec space_bottom">
					<div class="border-bottom_left">
						<div class="lt_sec">Vehicle Number (List last six digit of the VIN) : <span><?php echo $pdf[0]->VIN ?></span></div>
					</div>
				</div>
			</div>
			<div class="half_width_new">
				<div class="top_sec">
					<div class="lt_sec">
						Mail To: <br>
						LogistiCare Claims Department <br>
						798 Park Avenue NW <br>
						Norton, VA 27273
					</div>
				</div>
			</div>
		</div>
		<div class="full_width">
			<table style="width: 100%;">
				<tr>
					<th>Date of </br>Service</th>
					<th>LogistiCare </br>Job # </br>A or B</th>
					<th width="10%">Member's </br>Name</th>
					<th width="5%">A</br>W</th>
					<th width="5%">RNS</th>
					<th>Pick-up</br> Time</th>
					<th>Drop-Off </br>Time</th>
					<th>Will Call </br>Time</th>
					<th>Total Trip </br>Mileage </th>
					<th>Wait </br>Time </th>
					<th>Per Trip </br>Billed </br>Amount </th>
					<th>Attendant </br>Provided </th>
					<th width="17%">Member's Signature </br>or Attendant/Escort </br>SIgnature (If applicable) </th>
				</tr>

				<?php
				$dirverimg = '';
				if ($pdf[0]->upload_signature_original) {
					$exists = Storage::disk('s3')->exists($pdf[0]->upload_signature_original);
					if($exists)
					{
						$dirverimg = '<img src="' . awsasset($pdf[0]->upload_signature_original) . '" width="90px">';
					}
				}
				
                $pagebreak = 'y';

                foreach ($pdf as $row) {
                    if ($row->pagebreak == 'n') {
                        $pagebreak = 'n';
                    }

                    if ($row->member_sign != '') {
                        /*$class = "";
                        if (strtotime(date('Y-m-d', strtotime($row['created_at']))) > strtotime('2020-11-17')) {
                            $class = "rotate90";
                        }*/

                        $img = '';
                        if ($row->member_sign) {
							$exists = Storage::disk('s3')->exists($row->member_sign);
               				if($exists)
							{
                            	$img = '<div style="height:55px;"><img src="' .awsasset($row->member_sign) . '" height="55px"></div>';
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

                    $tripTime1 = App\Model\ReportModel::getTripTime($row->id, $row->Driver_id, 4);
                    if (!empty($tripTime1)) {
                        // $pickup_time = date("H:i", strtotime($tripTime1[0]["date_time"]));
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
                        if ($tripTime2[0]["timezone"] != '') {
                            $timezone_name = $tripTime2[0]["timezone"];
                            $dropoff_time =  modifyDriverLogTimeNew(date("Y-m-d", strtotime($tripTime2[0]["date_time"])), date("H:i", strtotime($tripTime2[0]["date_time"])), $timezone_name)->format('H:i');
                        } else {
                            $dropoff_time = '';
                        }
                        // $dropoff_time = date("H:i", strtotime($tripTime2[0]["date_time"]));
                    } else {
                        $dropoff_time = '';
                    }

                    if ($row->status_id == 5) {
                        $checkmark = '<p>&#10004;</p>';
                    } else {
                        $checkmark = '';
                    } ?>
					<tr>
						<td><?php echo date("m/d/Y", strtotime($row->date_of_service)); ?></td>
						<td><?php echo $row->TripID; ?></td>
						<td><?php echo $row->Member_name; ?></td>
						<td><?php echo $row->level_of_service; ?></td>
						<td><?php echo $checkmark; ?></td>
						<td><?php echo $pickup_time; ?></td>
						<td><?php echo $dropoff_time; ?></td>
						<td>&nbsp;</td>
						<td><?php echo round($row->period3_miles, 4); ?></td>
						<td>&nbsp;</td>
						<td>$ <?php echo decimal2digit_number(round($row->trip_price, 4)); ?></td>
						<td>&nbsp;</td>
						<td><?php echo $img; ?></td>
					</tr>
				<?php
                } ?>
			</table>

			<?php
            // echo "txtx".count($pdf);
            if (count($pdf) >= 11) {
                ?>
				<div style="height:5px; display: inline-block; width:100%;">&nbsp;</div>
			<?php
            } ?>

			<div class="table_undertxt">
				**Note***: Leg of transport a leg of transport is the point of pick-up to the destination.
				Example : Picking recipient up at residence ans transporting to the doctor's office would be considered one leg plcking the reclpient up at the doctor's office and transporting back to the residence would be considered the secound leg of the tripEach leg of the transport must be documented on separats lines A signalure is required for each leg of the transport Pick-up and drop-off times Must be documented and in military time
			</div>
			<div class="top_sec space_top">
				<div class="border-bottom_left">
					<div class="lt_sec" style="font-weight: bolder;">Driver's Comments :</div>
				</div>
			</div>
			<div class="table_undertxt">
				I Understand the LogistiCare Solution, LLC Will verify the accuracy of the mileage being reported and i hereby certify the information herein is true, correct, and accurate.
			</div>
		</div>
		<div class="top_wrapper mar0">
			<div class="half_width">
				<div class="top_sec space_top">
					<div class="border-bottom_left">
						<div class="lt_sec">Driver Name (Must Print) : <span><?php echo $pdf[0]->name ?></span></div>
					</div>
				</div>
				<div class="top_sec space_top">
					<div class="border-bottom_left">
						<div class="lt_sec">Driver's Signature : <span><?php echo $dirverimg; ?></span></div>
					</div>
				</div>
			</div>
			<div class="half_width leftmin_space">
				<div class="top_sec space_top">
					<div class="border-bottom_left">
						<div class="lt_sec">Attendant/Escort Name(must Print) : <span>&nbsp;</span></div>
					</div>
				
					<div class="border-bottom_left">
						<div class="lt_sec">Attendant/Escort Signature : </div>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>