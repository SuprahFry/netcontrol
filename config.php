<?php
set_time_limit(15);
$settings = array();

/* TERMINAL SETTINGS */
$settings['terminal']['enabled'] = true; // commands allowed?
$settings['terminal']['commands'] = array('RCON' => 'commands/rcon.php',
											'MPing' => 'commands/mping.php',
											'CRCON' => 'commands/crcon.php',
											'QQuery' => 'commands/qquery.php');

/* COLOR SETTINGS */
$settings['colors']['minecraft'] = array('0' => '000000', '1' => '0000aa', '2' => '00aa00', '3' => '00aaaa',
											'4' => 'aa0000', '5' => 'aa00aa', '6' => 'ffaa00', '7' => 'aaaaaa',
											'8' => '555550', '9' => '5555ff', 'a' => '55ff55', 'b' => '55ffff',
											'c' => 'ff5555', 'd' => 'ff55ff', 'e' => 'ffff55', 'f' => 'ffffff');
$settings['colors']['cod4'] = array('1' => 'f15757', '2' => '00fb00', '3' => 'e8e803', '4' => '0000fe',
										'5' => '02e5e5', '6' => 'ff5cff', '7' => 'aaaaaa', '8' => '000000',
										'9' => '000000', '0' => '000000');

/* RCON SETTINGS */
$settings['rcon']['quirks'] = false; // quirks mode off by default, turn on if you get errors.
$settings['rcon']['colors'] = $settings['colors']['minecraft']; // reference to the color array
$settings['rcon']['timeout'] = 3; // in seconds, the timeout for an rcon connection

/* MASTER SERVERS */
$settings['masters']['cod4'] = array('cod4master.activision.com', 'cod4authorize.activision.com',
										'cod4master.infinityward.com', 'cod4update.activision.com',
										'master.gamespy.com:28960', 'master0.gamespy.com',
										'master1.gamespy.com', 'clanservers.net');
$settings['masters']['cod5'] = array('cod5master.activision.com', 'cod5authorize.activision.com',
										'cod5master.infinityward.com', 'cod5update.activision.com',
										'master.gamespy.com:28960', 'master0.gamespy.com',
										'master1.gamespy.com', 'clanservers.net');
?>