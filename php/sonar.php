<?php
$baseDir = '/home/dev/svn/';
function checkoutServices($baseDir) {
	$svcBaseDir = $baseDir.'services/';
	system("curl http://deploy1-dev.bj1.haodf.net:88/current.php > {$svcBaseDir}a");
	$rows = file($svcBaseDir.'a');
	foreach ($rows as $row) {
		$row = rtrim($row);
		$info = explode(' ', $row);
		if (in_array($info[1], array('all', 'sparta', 'ssi', 'libs'))) {
			continue;
		}
		//echo $info[1]."\n";
		$projectDir = $svcBaseDir.$info[1];
		system("rm -Rf $projectDir");
		system("svn co http://svn.haodf.net/svn/services/$info[1]/tags/$info[3] $projectDir");
		makeSonarFile($baseDir, 'services', $info[1], $info[3]);
		runScanner($projectDir);
	}
}

function runScanner($projectDir) {
	system("cd $projectDir && /home/dev/sonarqube-6.1/sonar-scanner-2.8/bin/sonar-scanner");
}

function makeSonarFile($baseDir, $type, $name, $ver) {
	$string = file(__DIR__.'/sonar-project.properties');
	$patterns = array();
	$patterns[0] = '/#projectKey#/';
	$patterns[1] = '/#projectName#/';
	$patterns[2] = '/#projectVersion#/';
	$replacements = array();
	$replacements[0] = $type.'-'.$name;
	$replacements[1] = $type.'-'.$name;
	$replacements[2] = $ver;
	file_put_contents($baseDir.$type.'/'.$name.'/sonar-project.properties', preg_replace($patterns, $replacements, $string));
}

function checkoutGots($baseDir) {
	$gotBaseDir = $baseDir.'gots/';
	$gots = ['message' => 'v6.1.1', 'idgenter' => 'v6.1.1'];
	foreach ($gots as $project => $ver) {
		$projectDir = $gotBaseDir.$project;
		system("rm -Rf $projectDir");
		system("rm -Rf $project && svn co http://svn.haodf.net/svn/gots/$project/tags/$ver $projectDir");
		makeSonarFile($baseDir, 'gots', $project, $ver);
		runScanner($projectDir);
	}
}

checkoutServices($baseDir);
checkoutGots($baseDir);
