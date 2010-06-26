<?php
//TODO: implement javascript version of form

if (isset($_GET['action']) && $_GET['action']=='doedit') {

	// login check
	if (!isset($_SESSION['userid'])) {
		die();
	}

	// new password
	if (isset($_POST['password'])) {
		if (empty($_POST['password'])) {
			MessageManager::addWarning(tr('profile_emptypassword'));
		} else {
			ServerInterface::getInstance()->updateUserPw($_SESSION['serverid'], $_SESSION['userid'], $_POST['password']);
		}
	}

	// new username
	if (isset($_POST['name'])) {
		ServerInterface::getInstance()->updateUserName($_SESSION['serverid'], $_SESSION['userid'], $_POST['name']);
	}

	// new email
	if (isset($_POST['email'])) {
		ServerInterface::getInstance()->updateUserEmail($_SESSION['serverid'], $_SESSION['userid'], $_POST['email']);
	}

	// remove texture
	if (isset($_GET['remove_texture'])) {
		try {
			ServerInterface::getInstance()->updateUserTexture($_SESSION['serverid'], $_SESSION['userid'], array());
		} catch(Murmur_InvalidTextureException $exc) {
			MessageManager::addWarning(tr('profile_removetexturefailed'));
		}
	}

	// new texture
	if (isset($_FILES['texture'])) {
		if(!file_exists($_FILES['texture']['tmp_name'])){
			MessageManager::addWarning(tr('profile_texture_notempfile'));
		} else {
			$fileExtension = pathinfo($_FILES['texture']['name']);
			$fileExtension = isset($fileExtension['extension']) ? $fileExtension['extension'] : '';


function stringToByteArray($str)
{
	return unpack('C*', $str);
}
/**
 * @todo move to HelperFunctions class
 * @param $imgRes
 * @return string
 */
function imgToString($imgRes)
{
	$tex = '';
	for($y=0; $y<imagesy($imgRes); $y++){
		for($x=0; $x<imagesx($imgRes); $x++){
			$colorIndex = imagecolorat($imgRes, $x, $y);
			$colors = imagecolorsforindex($imgRes, $colorIndex);
			// alpha has to be converted to be 0 and 254 (255 would be better) instead of 0 to 127 and inverted
			$tex = $tex.pack('c4', $colors['blue'], $colors['green'], $colors['red'], abs(254-$colors['alpha']*2));
		}
	}
	return $tex;
}
/*
 * @todo move to HelperFunctions class
 * used for memory intensive image calculations
 * Checks that memory_limit is high enough and increases it if necessary.
 */
function checkMemoryLimit()
{
	// 40M should be enough, use 60M to be sure
	$tmp_memLim = ini_get('memory_limit');
	if (intval(substr($tmp_memLim, 0, strlen($tmp_memLim)-1)) < 60) {
		ini_set('memory_limit', '60M');
	}
}


			$tex = '';
			switch ($fileExtension) {
				case 'png':
					checkMemoryLimit();

					if (!$texImg = imagecreatefrompng($_FILES['texture']['tmp_name'])) {
						MessageManager::addWarning(tr('profile_texture_imgresfail'));
						break;
					}
					if (imagesx($texImg)!=600 || imagesy($texImg)!=60) {
						MessageManager::addWarning(tr('profile_texture_wrongresolution'));
						break;
					}
					//TODO: check if we even need those 2:
					imagealphablending($texImg, true);		// enablealpha blending
					imagesavealpha($texImg, true);			// save alphablending

					$tex = imgToString($texImg);
					imagedestroy($texImg);

					if (strlen($tex)!=144000) {
						MessageManager::addWarning(tr('profile_texture_conversionfail'));
						break;
					}

					$texArray = stringToByteArray($tex);

					if (ServerInterface::getInstance()->updateUserTexture($_SESSION['serverid'], $_SESSION['userid'], $texArray )) {
						MessageManager::addWarning(tr('profile_texture_success'));
					} else {
						MessageManager::addWarning(tr('profile_texture_fail'));
					}
					break;
				case 'jpg':
				case 'jpeg':
					checkMemoryLimit();

					if (!$texImg = imagecreatefromjpeg($_FILES['texture']['tmp_name'])) {
						MessageManager::addWarning(tr('profile_texture_imgresfail'));
						break;
					}
					if (imagesx($texImg)!=600 || imagesy($texImg)!=60) {
						MessageManager::addWarning(tr('profile_texture_wrongresolution'));
						break;
					}

					$tex = imgToString($texImg);
					imagedestroy($texImg);

					if (strlen($tex)!=144000){
						MessageManager::addWarning(tr('profile_texture_conversionfail'));
						break;
					}

					$texArray = unpack('C*', $tex);

					if (ServerInterface::getInstance()->updateUserTexture($_SESSION['serverid'], $_SESSION['userid'], $texArray )) {
						MessageManager::addWarning(tr('profile_texture_success'));
					} else {
						MessageManager::addWarning(tr('profile_texture_fail'));
					}

					break;
				case 'gif':
					checkMemoryLimit();

					if (!$texImg = imagecreatefromgif($_FILES['texture']['tmp_name'])) {
						MessageManager::addWarning(tr('profile_texture_imgresfail'));
						break;
					}
					if (imagesx($texImg)!=600 || imagesy($texImg)!=60) {
						MessageManager::addWarning(tr('profile_texture_wrongresolution'));
						break;
					}

					$tex = imgToString($texImg);
					imagedestroy($texImg);

					if (strlen($tex)!=144000) {
						MessageManager::addWarning(tr('profile_texture_conversionfail'));
						break;
					}

					$texArray = unpack('C*', $tex);

					if (ServerInterface::getInstance()->updateUserTexture($_SESSION['serverid'], $_SESSION['userid'], $texArray )) {
						MessageManager::addWarning(tr('profile_texture_success'));
					} else {
						MessageManager::addWarning(tr('profile_texture_fail'));
					}
					break;

				// RAW RGBA Image Data
				case '':
				case 'raw':
					checkMemoryLimit();

					if ($_FILES['texture']['size'] != 144000) {
						MessageManager::addWarning(tr('profile_texture_conversionfail'));
						break;
					}

					if (!$fd = fopen($_FILES['texture']['tmp_name'], 'r')) {
						MessageManager::addWarning(tr('profile_texture_tmpopenfail'));
						break;
					}
					$tex = fread($fd, 144000);
					fclose($fd);

					// RGBA to BGRA
					// for each pixel, swap R with B (36000 = 600*60)
					for ($i=0; $i<36000; $i++) {
						$red = $tex[$i*4];
						$tex[$i*4] = $tex[$i*4+2];
						$tex[$i*4+2] = $red;
					}

					//TODO compress in php to minimize size for ice call
					//$tex = gzcompress($tex);

					$texArray = stringToByteArray($tex);

					if (ServerInterface::getInstance()->updateUserTexture($_SESSION['serverid'], $_SESSION['userid'], $texArray)) {
						MessageManager::addWarning(tr('profile_texture_success'));
					} else {
						MessageManager::addWarning(tr('profile_texture_fail'));
					}
					break;

				default:
					MessageManager::addWarning(tr('profile_texture_unknownext'));
					break;
			}
		}
	}
}

?>
<div id="content">
	<h1><?php echo TranslationManager::getText('profile_head'); ?></h1>
	<form action="?page=profile&amp;action=doedit" method="post" style="width:420px;"<?php if(isset($_GET['action'])&&$_GET['action']=='edit_texture') echo ' enctype="multipart/form-data"'; ?>>
		<table class="fullwidth">
			<tr><?php // SERVER Information (not changeable) ?>
				<td class="formitemname"><?php echo tr('server'); ?>:</td>
				<td>
					<?php
						echo SettingsManager::getInstance()->getServerName($_SESSION['serverid']);
					?>
				</td>
				<td></td>
			</tr>
			<tr><?php // USERNAME ?>
				<td class="formitemname"><?php echo tr('username'); ?>:</td>
				<td><?php
					if (isset($_GET['action']) && $_GET['action']=='edit_uname') {
						?><input type="text" name="name" value="<?php echo ServerInterface::getInstance()->getUsername($_SESSION['serverid'], $_SESSION['userid']); ?>" /><?php
					} else {
						echo ServerInterface::getInstance()->getUsername($_SESSION['serverid'], $_SESSION['userid']);
					} ?></td>
				<td class="alignl">
					<a href="?page=profile&amp;action=edit_uname" id="profile_uname_edit"<?php if(isset($_GET['action']) && $_GET['action']=='edit_uname'){ echo 'class="hidden"'; } ?>><?php echo tr('edit'); ?></a>
					<?php if(isset($_GET['action']) && $_GET['action']=='edit_uname'){ echo '<input type="submit" value="update"/>'; } ?><a href="?page=profile&amp;action=doedit_uname" id="profile_uname_update" class="hidden"><?php echo tr('update'); ?></a>
					<a href="?page=profile" id="profile_uname_cancel"<?php if(!isset($_GET['action']) || $_GET['action']!='edit_uname'){ ?> class="hidden"<?php } ?>><?php echo tr('cancel'); ?></a>
				</td>
			</tr>
			<tr><?php // PASSWORD ?>
				<td class="formitemname"><?php echo tr('password'); ?>:</td>
				<td><?php if(isset($_GET['action']) && $_GET['action']=='edit_pw'){ ?><input type="text" name="password" id="password" value="" /><?php }else{ echo '<span class="info" title="password is not displayed">*****</span>'; } ?></td>
				<td class="alignl">
					<a href="?page=profile&amp;action=edit_pw" id="profile_pw_edit"<?php if(isset($_GET['action']) && $_GET['action']=='edit_pw'){ ?> class="hidden"<?php } ?>><?php echo tr('edit'); ?></a>
					<?php if(isset($_GET['action']) && $_GET['action']=='edit_pw'){ echo '<input type="submit" value="update"/>'; } ?><a id="profile_pw_update" class="hidden"><?php echo tr('update'); ?></a>
					<a href="?page=profile" id="profile_pw_cancel"<?php if(!isset($_GET['action']) || $_GET['action']!='edit_pw'){ ?> class="hidden"<?php } ?>><?php echo tr('cancel'); ?></a></td>
			</tr>
			<tr><?php // E-MAIL ?>
				<td class="formitemname"><?php echo tr('email'); ?>:</td>
				<td><?php
					if (isset($_GET['action']) && $_GET['action']=='edit_email') {
						?><input type="text" name="email" id="email" value="<?php echo ServerInterface::getInstance()->getUserEmail($_SESSION['serverid'], $_SESSION['userid']); ?>" /><?php
					} else {
						echo ServerInterface::getInstance()->getUserEmail($_SESSION['serverid'], $_SESSION['userid']);
					}
				?></td>
				<td class="alignl">
					<a href="?page=profile&amp;action=edit_email" id="profile_email_edit"<?php if(isset($_GET['action']) && $_GET['action']=='edit_email'){ ?> class="hidden"<?php } ?>><?php echo tr('edit'); ?></a>
					<?php if(isset($_GET['action']) && $_GET['action']=='edit_email'){ echo '<input type="submit" value="update"/>'; } ?><a id="profile_email_update" class="hidden"><?php echo tr('update'); ?></a>
					<a href="?page=profile" id="profile_email_cancel"<?php if(!isset($_GET['action']) || $_GET['action']!='edit_email'){ ?> class="hidden"<?php } ?>><?php echo tr('cancel'); ?></a></td>
			</tr>
			<tr>
				<?php
					// Texture
					$isTextureSet = (count(ServerInterface::getInstance()->getUserTexture($_SESSION['serverid'], $_SESSION['userid'])) > 0);
				?>
				<td class="formitemname">
					<?php echo tr('texture'); ?>:
				</td>
				<td>
					<?php
						if ($isTextureSet) {
							echo tr('texture_set');
						} else {
							echo tr('texture_none');
						}
					?>
				</td>
				<td class="alignl">
					<?php
						if ($isTextureSet) {
							echo '<a href="?page=profile&amp;action=doedit&amp;remove_texture" id="profile_texture_remove" onclick="return confirm(\'Are you sure you want to remove your user-avatar?\');"';
							if (isset($_GET['action']) && ($_GET['action'] == 'edit_texture')) {
								echo ' class="hidden"';
							}
							echo '>';
							echo tr('remove');
							echo '</a>';
						}
					?>
				</td>
			</tr>
		</table>

		<script type="text/javascript">
			<!--
				<![CDATA[
					$('#profile_uname_edit').click(
						function(event) {
							$('#profile_uname_*').toggle(
									function(){
										$(this).removeClass('hidden');
									},
									function() {
										$(this).addClass('hidden');}
								);
							}
						);
				]]>
			-->
		</script>
	</form>
	<p <?php if(!isset($_GET['action']) || $_GET['action']!='edit_texture'){ ?> class="hidden"<?php } ?>>
		<?php echo tr('profile_note_texture'); ?>
	</p>
</div>