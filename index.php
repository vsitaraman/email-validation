<html>
<head>
<title>PHP E-mail validator</title>

<!-- Core Dependencies -->
<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" type="text/css" rel="stylesheet" />
<link href="//use.fontawesome.com/releases/v5.0.12/css/all.css" type="text/css" rel="stylesheet" />

</head>
<body>
<h1 align="center">PHP E-mail validator</h1>
<hr>
<div class="container">
	<div class="row">
		<div class="col-md-6 col-xs-12">
			<code>
<?php
	require( "email_validation.php" );

	$validator=new email_validation_class;

	/*
	 * If you are running under Windows or any other platform that does not
	 * have enabled the MX resolution function GetMXRR() , you need to
	 * include code that emulates that function so the class knows which
	 * SMTP server it should connect to verify if the specified address is
	 * valid.
	 */
	if( !function_exists( "GetMXRR" ) )
	{
		/*
		 * If possible specify in this array the address of at least on local
		 * DNS that may be queried from your network.
		 */
		$_NAMESERVERS=array();
		include( "getmxrr.php" );
	}
	/*
	 * If GetMXRR function is available but it is not functional, you may
	 * use a replacement function.
	 */
	/*
	else
	{
		$_NAMESERVERS=array();
		if( count( $_NAMESERVERS )==0 )
			Unset( $_NAMESERVERS );
		include( "rrcompat.php" );
		$validator->getmxrr="_getmxrr";
	}
	*/

	/* how many seconds to wait before each attempt to connect to the
	   destination e-mail server */
	$validator->timeout=10;

	/* how many seconds to wait for data exchanged with the server.
	   set to a non zero value if the data timeout will be different
		 than the connection timeout. */
	$validator->data_timeout=0;

	/* user part of the e-mail address of the sending user
	   ( info@phpclasses.org in this example ) */
	$validator->localuser="info";

	/* domain part of the e-mail address of the sending user */
	$validator->localhost="phpclasses.org";

	/* Set to 1 if you want to output of the dialog with the
	   destination mail server */
	$validator->debug=1;

	/* Set to 1 if you want the debug output to be formatted to be
	displayed properly in a HTML page. */
	$validator->html_debug=1;


	/* When it is not possible to resolve the e-mail address of
	   destination server ( MX record ) eventually because the domain is
	   invalid, this class tries to resolve the domain address ( A
	   record ). If it fails, usually the resolver library assumes that
	   could be because the specified domain is just the subdomain
	   part. So, it appends the local default domain and tries to
	   resolve the resulting domain. It may happen that the local DNS
	   has an * for the A record, so any sub-domain is resolved to some
	   local IP address. This  prevents the class from figuring if the
	   specified e-mail address domain is valid. To avoid this problem,
	   just specify in this variable the local address that the
	   resolver library would return with gethostbyname() function for
	   invalid global domains that would be confused with valid local
	   domains. Here it can be either the domain name or its IP address. */
	$validator->exclude_address="";
	$validator->invalid_email_domains_file = 'invalidemaildomains.csv';
	$validator->invalid_email_servers_file = 'invalidemailservers.csv';
	$validator->email_domains_white_list_file = 'emaildomainswhitelist.csv';
	$output = array();

	if( IsSet( $_GET["email"] ) ){
		$emails = $_GET["email"];
		$emails = explode( "," , $emails);
		foreach ($emails as $email ){
			$out = array();
			if( strcmp( $email,"" ) ) {
				$out['email'] = $email;
				if( strlen( $error = $validator->ValidateAddress( $email, $valid ) ) ) {
					$out['valid'] = NULL;
					$out['msg'] = $error;
					// echo "<h2 align=\"center\">Error: ".HtmlSpecialChars( $error )."</h2>\n";
				}
				elseif( !$valid ) {
					$out['valid'] = FALSE;
					// echo "<h2 align=\"center\"><tt>$email</tt> is not a valid deliverable e-mail box address.</h2>\n";
					if( count( $validator->suggestions ) )
					{
						$suggestion = $validator->suggestions[0];
						$out['msg'] = $suggestion;
						// $link = '?email='.UrlEncode( $suggestion );
						// echo "<H2 align=\"center\">Did you mean <a href=\"".HtmlSpecialChars( $link )."\"><tt>".HtmlSpecialChars( $suggestion )."</tt></a>?</H2>\n";
					}
				}
				elseif( ( $result=$validator->ValidateEmailBox( $email ) )<0 )
					$out['valid'] = NULL;
					// echo "<h2 align=\"center\">It was not possible to determine if <tt>$email</tt> is a valid deliverable e-mail box address.</h2>\n";
				else
					$out['valid'] = ( $result ? TRUE : FALSE);
					// echo "<h2 align=\"center\"><tt>$email</tt> is ".( $result ? "" : "not " )."a valid deliverable e-mail box address.</h2>\n";
			} else {
				// email is empty
				$out['valid'] = NULL;
				$out['msg'] = "empty";
			}
			$output[] = $out;
		}
	}
	else {
		// no input
		$email = 'your@test.email.here';
		$link = '?email='.$email;
		echo "<h2 align=\"center\">Access this page using passing the email to validate here: <a href=\"".HtmlSpecialChars( $link )."\"><tt>".$email."</tt></a></h2>\n";
	}
?>
			</code>
		</div>
		<div class="col-md-6 col-xs-12">
			<textarea id="results" class="form-control">
<?php echo json_encode($output); ?>
			</textarea>
			<button onClick="copyResults()" class="btn btn-primary btn-lg"><i class="fa fa-clipboard"></i>&nbsp; Copy</button>
		</div>
	</div><!-- ./row -->
</div><!-- ./container -->
<hr>

<!-- Core Dependencies -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js" type="text/javascript"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" type="text/javascript"></script>
<script>
// Copy to Clipboard
function copyTextToClipboard(e) {
	var o = document.createElement("textarea");
	o.style.position = "fixed", o.style.top = 0, o.style.left = 0, o.style.width = "2em", o.style.height = "2em", o.style.padding = 0, o.style.border = "none", o.style.outline = "none", o.style.boxShadow = "none", o.style.background = "transparent", o.value = e, document.body.appendChild(o), o.select();
	try {
		var t = document.execCommand("copy"),
			n = t ? "successful" : "unsuccessful";
		console.log("Copying text command was " + n)
	} catch (l) {
		console.log("Oops, unable to copy")
	}
	document.body.removeChild(o)
}
function copyResults() {
	var text = $("#results").val();
	copyTextToClipboard(text);
}
</script>
</body>
</html>
