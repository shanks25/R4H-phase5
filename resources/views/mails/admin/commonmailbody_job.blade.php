<!DOCTYPE html>
<html lang="en">
<head>
	<title>Ride4Health</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link rel="stylesheet" type="text/css" href="{{asset('assets/bootstrap/css/bootstrap.min.css')}}" />
	<style type="text/css">
		* {margin: 0;padding: 0;font-size: 100%;
			font-family: "montserratlight", sans-serif}
			img {max-width: 100%;margin: 0 auto;display: block; }
			body, 
			.body-wrap {width: 100% !important;height: 100%;background: #efefef;
				-webkit-font-smoothing: antialiased;-webkit-text-size-adjust: none; }
				a {color: #71bc37;text-decoration: none; }
				.text-center {text-align: center; }
				.text-right {text-align: right; }
				.text-left {text-align: left; }
				.container {display: block !important;clear: both !important;margin: 0 auto !important;max-width: 40% !important;background: #e5e5e5; padding: 40px 40px 30px 40px; }
				.container.footsect {background:#0680be; margin:0px auto 0 auto !important;padding: 15px 40px 15px 40px;}
				.container table {width: 100% !important;border-collapse: collapse; }
				.container .masthead {padding: 10px 0;background:#fff;color: #fff;border-bottom: 1px solid #cccccc;}
				.container .masthead img{width:270px;}
				.container .content {background:#fff;padding: 10px 20px; }
				.container .content.footer {background: none; padding: 10px 0px 0px 0px;}
				.botm-sect .content.footer p{margin: 0px 0 0px 0px; line-height:20px; font-size: 15px; float: left; width: 100%; color: #fff;}
				.botm-sect .content.footer p a{color: #fff;text-decoration: underline;  }
				.botm-sect .content.footer p a:hover{text-decoration: underline; color: #71bc37;}
				.para h1{font-size: 22px; font-weight: normal; float: left; width: 100%; margin:10px 0px 15px 0px; color:#0680be;}
				.para h2{font-size: 18px; font-weight: normal; float: left; width: 100%; margin: 0px 0px 10px 0px;}
				.para p{margin: 0px 0 10px 0px; line-height:22px; font-size: 14px; float: left; width: 100%;}
				.para p.actlink{margin: 0px 0 10px 0px;}
				.para p.emt-p{margin: 0px 0 4px 0px;}
				.clickbtn{margin-bottom: 10px; float:left;}
				.clickbtn a{color: #fff; border: none; border-radius: 0px; padding: 10px 26px; text-align: center; font-size: 16px;
					background: #0680be; float: left; font-weight: bold; margin: 0px 20px 10px 0px;}
					.clickbtn a:hover{background: #458d0d;}
					.formlist{list-style: none; margin: 0px 0px 15px 0px;float: left;width: 100%;}
					.formlist li{float: left; width: 100%; padding-bottom: 8px;}
					.formlist li label{float: left; width: 20%;font-size: 14px; font-weight: bold;}
					.formlist li span{float: left; width: 50%;font-size: 14px;}
					/*Media css*/
					@media only screen and ( max-width:1366px ){
						.container{max-width: 60% !important;}
					}  
					@media only screen and ( max-width:980px ){
						.container{padding:20px 20px 20px 20px; max-width: 80% !important; } 
						.container .content {padding: 30px 20px;}
						.container.footsect{padding: 15px 15px 15px 15px;}
						.para h2{margin: 0px 0px 10px 0px;}
						.formlist li label{float: left; width: 40%;}
						.formlist li span{float: left; width: 50%;}
						.clickbtn a{padding: 8px 10px;margin: 0px 10px 10px 0px;font-weight: normal;}
					}
					@media only screen and ( max-width:520px ){
						.container{padding:20px 10px 20px 10px;   max-width: 100% !important; } 
						.container .content {padding: 20px 15px;}
						.container.footsect{padding: 15px 10px 15px 10px;}
						.formlist li label{float: left; width: 80%; padding-bottom:6px}
						.formlist li span{float: left; width: 60%;}

					}
					/*Media css*/
				</style>
			</head>
			<body>
				<body>
					<table class="body-wrap">
						<tr>
							<td class="container">
								<table>
									<tr>
										<td align="center" class="masthead">
											<a href="{{ url('/') }}" target="_blank"><img src="{{asset('assets/img/main_logo.png')}}" alt="Ride4Health"></a>
										</td>
									</tr>
									<tr>
										<td class="content para">
											{!!$data['bodytext']!!}
										</td>
									</tr>
									<tr>
										<td class="content para">
											<p><strong>Regards,<br>Ride4Health Team</strong></p>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr class="botm-sect">
							<td class="container footsect">
								<table>
									<tr>
										<td class="content footer" align="center">
											<p>Copyright &copy; {{date('Y')}} Ride4Health. All Rights Reserved. </p>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</body>
				</html>