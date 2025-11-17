<?php

/**
 * This plugin allows forum administrators to reset the language and style
 * settings for all users. It also displays usage statistics for the different
 * languages and styles.
 *
 * Copyright (C) 2005  Connor Dunn (Connorhd@mypunbb.com)
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
    exit;

// Tell admin_loader.php that this is indeed a plugin and that it is loaded
define('PUN_PLUGIN_LOADED', 1);
define('PLUGIN_VERSION',1.0);

function RoundSigDigs($number, $sigdigs) {
   $multiplier = 1;
   while ($number < 0.1) {
       $number *= 10;
       $multiplier /= 10;
   }
   while ($number >= 1) {
       $number /= 10;
       $multiplier *= 10;
   }
   return round($number, $sigdigs) * $multiplier;
}

if (isset($_POST['lang']))
{
	// Basic CSRF protection via referer check
	$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
	if (empty($referer) || strpos($referer, 'admin_loader.php') === false) {
		generate_admin_menu($plugin);
		message('Security: Invalid form submission. Please submit the form from the admin panel.');
	}
	// Do Post
	$db->query('UPDATE '.$db->prefix.'users SET language=\''.$db->escape($_POST['form']['language']).'\' WHERE id>1') or error('Unable to set lang settings', __FILE__, __LINE__, $db->error());
	redirect('admin_loader.php?plugin=AP_Languages_and_styles.php', 'Languages Reset');
}
elseif (isset($_POST['style']))
{
	// Basic CSRF protection via referer check
	$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
	if (empty($referer) || strpos($referer, 'admin_loader.php') === false) {
		generate_admin_menu($plugin);
		message('Security: Invalid form submission. Please submit the form from the admin panel.');
	}
	// Do Post
	$db->query('UPDATE '.$db->prefix.'users SET style=\''.$db->escape($_POST['form']['style']).'\' WHERE id>1') or error('Unable to set style settings', __FILE__, __LINE__, $db->error());
	redirect('admin_loader.php?plugin=AP_Languages_and_styles.php', 'Styles Reset');
}
else	// If not, we show the form
{
	// Display the admin navigation menu
	generate_admin_menu($plugin);

?>
	<div id="exampleplugin" class="plugin blockform">
		<h2><span>Language and style statistics/resetter - v<?php echo PLUGIN_VERSION ?></span></h2>
		<div class="box">
			<div class="inbox">
				<p>This Plugin allows you to see the style and language settings of users and reset them.</p>
			</div>
		</div>
	</div>
	<div class="blockform">
		<h2 class="block2"><span>Languages</span></h2>
		<div class="box">
			<form id="lang" method="post" action="<?php echo pun_htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
				<div class="inform">
					<fieldset>
						<legend>Languages</legend>
						<div class="infldset">
						<table class="aligntop" cellspacing="0">
													<tr>
								<th scope="row">Language Usage</th>
								<td>
<?php
	$result = $db->query('SELECT language, count(*) as number FROM '.$db->prefix.'users WHERE id > 1 GROUP BY language  ORDER BY number') or error('Unable to fetch lang settings', __FILE__, __LINE__, $db->error());
	$number = $db->num_rows($db->query('SELECT username from '.$db->prefix.'users WHERE id > 1'));
	while ($cur_lang = $db->fetch_assoc($result)) {
		echo RoundSigDigs($cur_lang['number'] / $number * 100,3).'% '.str_replace('_',' ',$cur_lang['language']).'<br />';
	}
?>
								</td>
							</tr>

							<tr>
								<th scope="row">Language</th>
								<td>
<?php
		$languages = array();
		$d = dir(PUN_ROOT.'lang');
		while (($entry = $d->read()) !== false)
		{
			if ($entry != '.' && $entry != '..' && is_dir(PUN_ROOT.'lang/'.$entry))
				$languages[] = $entry;
		}
		$d->close();

?>
									<select name="form[language]">
<?php

		foreach($languages as $temp)
		{
				echo "\t\t\t\t\t\t\t\t".'<option value="'.pun_htmlspecialchars($temp).'">'.pun_htmlspecialchars($temp).'</option>'."\n";
		}

?>
									</select>
									<span>All users languages will be reset to this option.</span>
								</td>
							</tr>
						</table>
						</div>
					</fieldset>
				</div>
			<p class="submitend"><input type="submit" name="lang" value="Reset!" tabindex="2" /></p>
			</form>
		</div>
	</div>
	<div class="blockform">
		<h2 class="block2"><span>Styles</span></h2>
		<div class="box">
			<form id="style" method="post" action="<?php echo pun_htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
				<div class="inform">
					<fieldset>
						<legend>Styles</legend>
						<div class="infldset">
						<table class="aligntop" cellspacing="0">
													<tr>
								<th scope="row">Style Usage</th>
								<td>
<?php
	$result = $db->query('SELECT style, count(*) as number FROM '.$db->prefix.'users WHERE id > 1 GROUP BY style ORDER BY number') or error('Unable to fetch style settings', __FILE__, __LINE__, $db->error());
	$number = $db->num_rows($db->query('SELECT username from '.$db->prefix.'users WHERE id > 1'));
	while ($cur_lang = $db->fetch_assoc($result)) {
		echo RoundSigDigs($cur_lang['number'] / $number * 100,3).'% '.str_replace('_',' ',$cur_lang['style']).'<br />';
	}
?>
								</td>
							</tr>

							<tr>
								<th scope="row">Style</th>
								<td>
<?php
		$styles = array();
		$d = dir(PUN_ROOT.'style');
		while (($entry = $d->read()) !== false)
		{
			if (substr($entry, strlen($entry)-4) == '.css')
				$styles[] = substr($entry, 0, strlen($entry)-4);
		}
		$d->close();


?>
									<select name="form[style]">
<?php

		foreach($styles as $temp)
		{
			echo "\t\t\t\t\t\t\t\t".'<option value="'.pun_htmlspecialchars($temp).'">'.pun_htmlspecialchars(str_replace('_', ' ', $temp)).'</option>'."\n";
		}

?>
									</select>
									<span>All users styles will be reset to this option.</span>
								</td>
							</tr>
						</table>
						</div>
					</fieldset>
				</div>
			<p class="submitend"><input type="submit" name="style" value="Reset!" tabindex="2" /></p>
			</form>
		</div>
	</div>
<?php
}
?>
