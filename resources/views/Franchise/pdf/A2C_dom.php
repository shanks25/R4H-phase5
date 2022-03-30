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
            display: block;
            margin-top: 24px;
        }

        .border_box {
            border: 2px solid #000;
            display: block;
            padding-bottom: 3px;
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

        .bottom_divd {
            page-break-inside: avoid !important;
            display: table-row-group;
            page-break-after: always;
            display: inline-table;
        }

        /* #tab  {page-break-inside: avoid !important;display: table-row-group;page-break-after: always;display: inline-table;} */
    </style>
</head>

<body>
    <div id="tab">
        <div class="box_laout">
            <div class="header_name">ACCESS TO CARE</div>
            <div class="border_box">
                <div class="min_padding ">
                    <div class="top_wrapper">
                        <div class="top_sec">
                            <div class="fst_sec">
                                <div class="lt_sec" style="margin-right:20px;">Company Name : <span>Ride4Health</span></div>
                                <div class="lt_sec">Driver Log VIN: <span class="text_bold">(FULL VIN REQUIRED)</span> <?php echo $pdf[0]->VIN ?></div>
                            </div>
                        </div>
                        <div class="mid_sec">
                            <div class="fst_sec" style="margin-bottom:8px;">
                                <div class="lt_sec" style="margin-right:22px;">Date : <span>
                                        <?php
                                        echo date("m/d/Y", strtotime($pdf[0]->date_of_service));
                                        ?>
                                    </span></div>
                                <div class="lt_sec" style="margin-right:22px;">Drivers Name: <span><?php echo $pdf[0]->name ?></span></div>
                                <div class="lt_sec">Driver License #: <span><?php echo $pdf[0]->license_no ?></span></div>
                                <div class="lt_sec">Timezone #: <span> <?php echo $timezone ?> </span></div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="full_width ">
                    <table style="width: 100%; text-align: center; ">
                        <tr>
                            <th width="11%">Trip # </br>Ending in T (trip)</br> or R (return)</th>
                            <th width="10%">Member's </br>Name</th>
                            <th width="8%">Level of</br> Service </br>(Amby or WC)</th>
                            <th width="8%">Pickup </br>Time </br>(Use </br>Military </br>Time)</th>
                            <th width="14%">Pickup </br>Address</th>
                            <th width="10%">Drop Off </br>Time (Use </br>Military </br>Time)</th>
                            <th width="14%">Destination </br>Address</th>
                            <th width="17%">Member </br>Signature</th>
                        </tr>

                        <?php
                        $dirverimg = '';
                        $diversignstyle = '';

                        if ($pdf[0]->upload_signature_original) {
                            $exists = Storage::disk('s3')->exists($pdf[0]->upload_signature_original);
                            if($exists)
                            {
                                $dirverimg = '<img src="' .awsasset($pdf[0]->upload_signature_original) . '" height="55px" width="150px">';
                                $diversignstyle = 'style="margin-top:15px;padding-left:10px;"';
                            }
                        }
                        //$pagebreak = 'y';

                        foreach ($pdf as $row) {
                            if ($row->member_sign != '') {
                                $img = '';
                                if ($row->member_sign) {
                                    $exists = Storage::disk('s3')->exists($row->member_sign);
                                    if($exists){
                                        $img = '<div style="width:auto;height:55px;overflow: hidden;white-space: nowrap;text-overflow: ellipsis;"><img src="' .awsasset($row->member_sign) . '" height="55px"></div>';
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

                            $trip_arr = (array) $row;
                            $res = gettripdata($trip_arr, 'pdflog');
                            $pickup_time = $res['pickup_time'];
                            $dropoff_time = $res['dropoff_time']; ?>
                            <tr>
                                <td><?php echo $row->TripID; ?></td>
                                <td><?php echo $row->Member_name; ?></td>
                                <td><?php echo $row->level_of_service; ?></td>
                                <td><?php echo $pickup_time; ?></td>
                                <td><?php echo $row->pickup_address; ?></td>
                                <td><?php echo $dropoff_time; ?></td>
                                <td><?php echo $row->drop_address; ?></td>
                                <td><?php echo $img; ?></td>
                            </tr>
                        <?php
                        } ?>
                    </table>
                </div>
                <div class="full_width " style="padding: 15px 0 15px 15px;width: auto;page-break-inside: avoid !important; ">
                    I understand that the information above will be verified and I certify the information provided on this form is true, correct and accurate
                </div>
                <div class="min_padding bottom_divd">
                    <div class="top_sec">
                        <div class="fst_sec mar0">
                            <div class="lt_sec" <?php echo $diversignstyle; ?>>Drivers Signature: <span style="height:45px;width:55px;"><?php echo $dirverimg; ?></span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>