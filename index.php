<?php

include_once 'controller/AppController.php';
include_once 'controller/ApiController.php';
include_once 'models/Dice.php';
include_once 'models/Game.php';
include_once 'models/Player.php';
include_once 'models/Token.php';

$app = new controller\AppController();

$output = $app->run();
?>

<!doctype html>
<html lang="de">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Mensch ärgere dich nicht</title>
</head>
<body>
<h1>Mensch ärgere dich nicht!</h1>
<?php foreach ($output as $round): ?>
	<h2><?php echo $round[0];
		unset($round[0]); ?></h2>
	<ul>
		<?php foreach ($round as $event): ?>
			<li><?= $event ?></li>

		<?php endforeach; ?>
	</ul>
<?php endforeach; ?>
</body>
</html>


