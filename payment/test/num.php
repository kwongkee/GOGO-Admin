<?php

// 数字输出网页计数器
$max_len = 9;
$CounterFile = "conter.txt";



if(!file_exists($CounterFile)) {
	/*$counter = 1;
	$cf = fopen($CounterFile,"w");
	fputs($cf,$counter);
	fclose($cf);*/
	
	$counter = file_get_contents($CounterFile);
	echo $counter;
	
} else {
	/*$cf = fopen($CounterFile,"r");
	$counter = trim(fgets($cf,$max_len));
	fclose($cf);*/
	$counter = file_put_contents($CounterFile,$counter);
}

/*$counter++;
$cf = fopen($CounterFile,"w");
fputs($cf,$counter);
fclose($cf);*/


// 输出
echo '<br>';
echo '当前访问量为：'.$counter;
?>