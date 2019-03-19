<?php
	class planet{
		public $name;
		public $radius;
		public $step;
		public $posx;
		public $posy;
		private $tolerance = 3;// how many decimals

		function __construct($name, $distance, $speed){
			$this->name = $name;
			$this->radius = $distance;
			$this->step = $speed;
		}

		public function position_setter($position){
			$this->posx = round($position['x'],$this->tolerance);
			$this->posy = round($position['y'],$this->tolerance);
		}
	}

	function position($radius, $step, $day){// give the position on the cartesian plane, the sun is on the 0,0 coordinates
		$angle = deg2rad($day * $step);
		$pos['x'] = $radius * cos($angle);
		$pos['y'] = $radius * sin($angle);
		return $pos;
	}

	function aligned($x1, $y1, $x2, $y2, $x3, $y3){
		if(($x1 == $x2 && $x1 == $x3) || ($y1 == $y2 && $y1 == $y3)){
			return true;
		}
		else{
			$abscissa = ($x2-$x1)/($x3-$x2);
			$ordinate = ($y2-$y1)/($y3-$y2);
			return ($abscissa == $ordinate);
		}
	}

	function vect_product($xa, $ya, $xb, $yb, $xc, $yc){
		$product = (($xa-$xc)*($yb-$yc))-(($ya-$yc)*($xb-$xc));
		return ($product >= 0);
	}

	function into_triangle($x1, $y1, $x2, $y2, $x3, $y3, $px, $py){
		$ext = vect_product($x1, $y1, $x2, $y2, $x3, $y3);
		$int1 = vect_product($x1, $y1, $x2, $y2, $px, $py);
		$int2 = vect_product($x2, $y2, $x3, $y3, $px, $py);
		$int3 = vect_product($x3, $y3, $x1, $y1, $px, $py);
		return ($int1 && $int2 && $int3 && $ext);
	}

	function distance($xa, $ya, $xb, $yb){
		$square = pow(($xb - $xa),2) + pow(($yb - $ya),2);
		return sqrt($square);
	}

	function perimeter($x1, $y1, $x2, $y2, $x3, $y3){
		$perimeter = distance($x1, $y1, $x2, $y2) + distance($x1, $y1, $x3, $y3) + distance($x2, $y2, $x3, $y3);
		return round($perimeter,2);
	}

	function days_years($step, $years){
		return ceil(abs(360/$step)*$years);
	}


	// --------------- Datos iniciales -------------------

	$sun_position = ["x" => 0, "y" => 0];
	$ferengi = new planet('ferengi', 500, -1);
	$betasoide = new planet('betasoide', 2000, -3);
	$vulcano = new planet('vulcano', 1000, 5);
	$years = 10;
	$total_days = days_years($ferengi->step, $years);//años para el planeta Ferengi
	$output = array();

	$optimum = 0;
	$drought = 0;
	$rain_period = 0;
	$rainiest = ['day' => 0, 'quantity' => 0];

	for($i=1; $i <= $total_days; $i++){
		$actual_day = $i;
		$perimeter = 0;
		$ferengi->position_setter(position($ferengi->radius, $ferengi->step, $actual_day));
		$betasoide->position_setter(position($betasoide->radius, $betasoide->step, $actual_day));
		$vulcano->position_setter(position($vulcano->radius, $vulcano->step, $actual_day));

		$ferengi_array = ['name' => $ferengi->name, 'angle' => $actual_day*$ferengi->step, 'x' => $ferengi->posx, 'y' => $ferengi->posy];
		$betasoide_array = ['name' => $betasoide->name, 'angle' => $actual_day*$betasoide->step, 'x' => $betasoide->posx, 'y' => $betasoide->posy];
		$vulcano_array = ['name' => $vulcano->name, 'angle' => $actual_day*$vulcano->step, 'x' => $vulcano->posx, 'y' => $vulcano->posy];

		if(aligned($ferengi->posx, $ferengi->posy, $betasoide->posx, $betasoide->posy, $vulcano->posx, $vulcano->posy)) {
			if(aligned($sun_position['x'], $sun_position['y'], $betasoide->posx, $betasoide->posy, $vulcano->posx, $vulcano->posy)){
				$weather = "alineado con sol: periodo de sequía";
				$drought++;
			}
			else{
				$weather = "alineados: Condiciones óptimas";
				$optimum++;
			}
		}
		else{
			$rainy = into_triangle($ferengi->posx, $ferengi->posy, $betasoide->posx, $betasoide->posy, $vulcano->posx, $vulcano->posy, $sun_position['x'], $sun_position['y']);
			if ($rainy){
				$weather = "Lluvioso";
				$perimeter = perimeter($ferengi->posx, $ferengi->posy, $betasoide->posx, $betasoide->posy, $vulcano->posx, $vulcano->posy);
				if($perimeter > $rainiest['quantity']){
					$rainiest['quantity'] = $perimeter;
					$rainiest['day'] = $actual_day;
				}
				if($output[$i-1]['perimeter'] == 0){
					$rain_period++;
				}
			}
			else{
				$weather = "No data";
			}
		}

		$output[$i] = ['weather' => $weather, 'perimeter' => $perimeter, 'ferengi' => $ferengi_array, 'betasoide' => $betasoide_array, 'vulcano' => $vulcano_array ];
	}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	 <meta name="author" content="Sergio Londono">
	<title>Test Meli</title>
	<style>
		table, th, td {
			border: 1px solid black;
		}

		table {
			border-collapse: collapse
		}
	</style>
</head>
<body>
	<h2>Test Mercado Libre - Predicción del clima</h2>
	<p><b>Años:</b> <?= $years ?> <b>Días:</b> <?= $total_days ?></p>
	<p><b>Periodos de sequía:</b> <?= $drought ?></p>
	<p><b>Periodos de lluvia:</b> <?= $rain_period ?></p>
	<p><b>Día pico máximo de lluvia:</b> <?= $rainiest['day'] ?></p>
	<p><b>Periodos de condiciones óptimas:</b> <?= $optimum ?></p>
	<hr>
	<h3>Tabla general</h3>
	<table>
		<tr>
			<td>Día</td>
			<td>Clima</td>
			<td>Perímetro</td>
			<td>Ferengi ángulo</td>
			<td>Coordenadas (Km)</td>
			<td>Betasoide ángulo</td>
			<td>Coordenadas (Km)</td>
			<td>Vulcano ángulo</td>
			<td>Coordenadas (Km)</td>
		</tr>
	<?php foreach($output as $day => $data): ?>
		<tr>
			<td><?= $day ?></td>
			<td><?= $data['weather'] ?></td>
			<td><?= $data['perimeter'] ?></td>
			<td><?= $data['ferengi']['angle'] ?></td>
			<td><?= 'x: '.$data['ferengi']['x'].' - y:'.$data['ferengi']['y'] ?></td>
			<td><?= $data['betasoide']['angle'] ?></td>
			<td><?= 'x: '.$data['betasoide']['x'].' - y:'.$data['betasoide']['y'] ?></td>
			<td><?= $data['vulcano']['angle'] ?></td>
			<td><?= 'x: '.$data['vulcano']['x'].' - y:'.$data['vulcano']['y'] ?></td>
		</tr>
	<?php endforeach ?>
	</table>
</body>
</html>