<?PHP
Header("Content-Type: image/png");

$Map = "400";
$Gridsize = 40;
$Offset = 20;
$Dimensions = 400;
$Division = 400/10;

$startx = 9;
$starty = 81;

# Map is 400x400

function convert ($v) {
	return $v+200;
}

$handle = ImageCreate ($Dimensions, $Dimensions) or die("Cannot Create Image");
$bg_color = ImageColorAllocate ($handle, 0, 255, 0);
$line_color = ImageColorAllocate ($handle, 0, 0, 0);

for ($i=0; $i<=400; $i=$i+16) {
  $draw = ($i);
  $number = $Gridsize-$i;
  ImageLine($handle, 0, $draw, 400, $draw, $line_color);
  ImageLine($handle, $draw, 0, $draw, 400, $line_color);
  if ($number > 0) { 
  	#ImageString($handle, 2, 5, $draw+25, $number, $line_color);
	#ImageString($handle, 2, $draw+25, $Map, $i+1, $line_color);
  }
}
#ImageString($handle, 5, 0, 10, $temp, $line_color);
function boxy ($x, $y, $color) {
	global $handle,$line_color,$startx,$starty;
	$Size = 16;
	$x = ($x - $startx) * $Size;
	$y = ($y - $starty) * $Size;
	$Halfsize = $Size/2;
	$Quadsize = $Halfsize/2;
	ImageFilledRectangle($handle, convert($x)-$Halfsize, convert($y)-$Halfsize, convert($x)+$Halfsize, convert($y)+$Halfsize, $color);
	ImageString($handle, 3, convert($x)-$Quadsize, convert($y)-$Quadsize, "3", $line_color);
}
boxy(9,81,ImageColorAllocate($handle, 0, 0, 255));
boxy(9,83,ImageColorAllocate($handle, 255, 0, 0));
#boxy(9,83);
ImagePng ($handle);


?>
