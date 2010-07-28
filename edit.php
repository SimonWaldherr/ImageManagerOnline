<?php

require_once('fakemagic.php');

session_start();

if (isset($_POST["f"])) {
	$FileName = $_POST["f"];
} else if (isset($_GET["f"])) {
	$FileName = $_GET["f"];
}

if(isset($_REQUEST['saveName'])) $ExhibitName = urldecode($_REQUEST['saveName']);
else $ExhibitName = 'MyExhibit';

@mkdir("./uploads/".session_id()."/imgset/".$ExhibitName.'/', 0755);

$valid_chars_regex = '.A-Z0-9_ !@#$%^&()+={}\[\]\',~`-';// Characters allowed in the file name (in a Regular Expression format)
$FileName = preg_replace('/[^'.$valid_chars_regex.']|\.+$/i', "", $FileName);
$FilePath = './uploads/'.session_id().'/'.$FileName;

$TempPath = './uploads/'.session_id().'/tmp/'.$FileName;

if(isset($_POST['x1']) && isset($_POST['x2']) && isset($_POST['y1']) &&isset($_POST['y2']) && isset($_POST['width']) && isset($_POST['height']) && isset($_POST['newWidth']) && isset($_POST['newHeight'])) {
	$X1 = (float)$_POST['x1'];
	$X2 = (float)$_POST['x2'];
	$Y1 = (float)$_POST['y1'];
	$Y2 = (float)$_POST['y2'];
	$Width = (float)$_POST['width'];
	$Height = (float)$_POST['height'];
	$NewWidth = (float)$_POST['newWidth'];
	$NewHeight = (float)$_POST['newHeight'];
	
	$Info = pathinfo($FilePath);

	$SavePath = './uploads/'.session_id().'/imgset/'.$ExhibitName.'/'.$ExhibitName.$NewWidth.'x'.$NewHeight.'.'.$Info['extension'];
	
	$image = new Imagick($FilePath);
	$image->readImage($FilePath);
	$dif = $image->getImageHeight() / 400;
	//echo 'width: '.$Width.' height: '.$Height.' x: '.$X1*$dif.' y: '.$Y1*$dif;
	if( $image->cropImage( (float)$Width, (float)$Height, $X1, $Y1) )
	{
		// $image->writeImage($TempPath);
		// no longer necessary with fakemagic
	}
	else
	{
		die('Error cropping image');
	}
	// now reads the cropped image to resize
	$image->readImage($TempPath);
	if($image->resizeImageWithQuadraticFilter( (float)$NewWidth, (float)$NewHeight))
	{
		// no longer necessary with fakemagic
		// $image->writeImage($TempPath);
	}
	else
	{
		die('Error resizing image');
	}
	
	$image->writeImage($SavePath);
	
	$Info = pathinfo($FilePath);
	
/*	header('Content-Description: File Transfer');
   header('Content-Type: application/octet-stream');
   header('Content-Disposition: attachment; filename='.$ExhibitName.$NewWidth.'x'.$NewHeight.'.'.$Info['extension']);
   header('Content-Transfer-Encoding: binary');
   header('Expires: 0');
   header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
   header('Pragma: public');
   header('Content-Length: ' . filesize($FilePath));
   ob_clean();
   flush();
   readfile($SavePath);
//	unlink($SavePath);
   exit; */
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Crop Image</title>
<link rel="stylesheet" href="css/default.css" type="text/css" media="screen" charset="utf-8">
<link rel="stylesheet" href="css/imgareaselect-0.9.2-animated.css" type="text/css" media="screen" charset="utf-8">
<link rel="stylesheet" href="css/jquery.lightbox-0.5.css" type="text/css" media="screen" />
<!-- includes to image area select and jquery -->
<script type="text/javascript" src="js/jquery-1.4.2.min.js"></script> 
<script type="text/javascript" src="js/jquery.lightbox-0.5.min.js"></script>
<script type="text/javascript" src="js/jquery.imgareaselect-0.9.2.min.js"></script> 
<script type="text/javascript" src="js/img.settings.js"></script> 
<script type="text/javascript">
$(window).load(function () { 
	//defining editing as selectable img area starting with the scale in the choiceSize list.
	$('#editing').imgAreaSelect({ 
		aspectRatio: sizes[$('#choiceSize').val()]['w'] + ':' + sizes[$('#choiceSize').val()]['h'], 
		onSelectChange: preview, 
		onSelectEnd: setSizes
	});
	//creating the SWFUpload to manage all about uploads 
	
	$('a.lightbox').lightBox( { containerResizeSpeed: 0 } );
});
</script>
</head>
<body style="margin-left: 5%; margin-right: 5%; width: 90%; text-align: center;">
		<!-- list of availables sizes -->
		<div style="clear: both; margin-bottom: 10px;">
			<label for="choiceSize" style="font-weight: bold;">
				New Image Size: 
			</label>
			<select id="choiceSize" >
				<script type="text/javascript">
					for(size in sizes)
						document.write("<option value='"+size+"'>"+size+"</option>");
				</script>
			</select>
		</div>
	 
	<!-- when all done -->
	<form name="finished" action="edit.php" method="post" onsubmit="return false;" style="display: inline;">
		<input type="hidden" name="f" value="<? echo $FileName; ?>" id="f">
		<input type="hidden" name="x1" value="" id="x1">
		<input type="hidden" name="x2" value="" id="x2">
		<input type="hidden" name="y1" value="" id="y1">
		<input type="hidden" name="y2" value="" id="y2">
		<input type="hidden" name="width" value="" id="width">
		<input type="hidden" name="height" value="" id="height">
		<input type="hidden" name="newWidth" value="" id="newWidth">
		<input type="hidden" name="newHeight" value="" id="newHeight">
		<label for="saveName" style="font-weight: bold;">Image Set Name: </label><input value="<? echo urlencode($ExhibitName); ?>" name="saveName" type="text" id="setname" />
		<br/>
		<br/>
		<div style="margin-top: 10px; display: inline;">
		<input disabled type="submit" name="save" value="Save Image To Exhibit" id="save">
		</div>
	</form>
	<?
		if(count(scandir('./uploads/'.session_id().'/imgset/'.$ExhibitName.'/')) >= 1) {
			?>
				<form name="dlaszip" action="dlaszip.php" method="post" style="display: inline;">
				<input type="submit" name="zipbutton" value="Download Exhibit as Zip" id="zipbutton">
				<input type="hidden" name="exhibit_name" value="<? echo htmlspecialchars($ExhibitName); ?>" id="exhibit_name">
				</form>
			<?
		}
	?>
	<div class="container">
		<p>
			<img id="editing" src="<? echo $FilePath; ?>" alt="Images to Edit"
			title="Image to Edit" />
		</p>
	</div>
	<table>
		<tr><th>Images in <? echo $ExhibitName; ?></th></tr>
		<?
		
		$Files = scandir('./uploads/'.session_id().'/imgset/'.$ExhibitName);
		
		
		foreach($Files as $File) {
			if(substr($File, 0, strlen($ExhibitName)) === $ExhibitName) {
				?>
					<tr><td><a href="<? echo 'uploads/'.session_id().'/imgset/'.$ExhibitName.'/'.$File; ?>" title="<? echo $File; ?>" class="lightbox"><? echo $File; ?></a></td></tr>
				<?
			}
		}
		
		?>
	</table>
</body>
</html>