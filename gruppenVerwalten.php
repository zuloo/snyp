<?php
/*
 * Copyright (c) 2005 Nils Rottgardt <nils@rottgardt.org>
 * All rights reserved
 *
 * Published under BSD-licence
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *   notice, this list of conditions and the following disclaimer in the
 *   documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
 * TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
require 'utils.php';
require 'htmlutil.php';
require 'language.php';

$name = $_GET["name"];
$notizen   = $_GET["notizen"];
$farbe = $_GET["farbe"];
$nureins = $_GET["nureins"];
$zutun = $_GET["zutun"];
$gid = $_GET["gid"];



switch($zutun) {
    Case 1:
    if ($name != "") {
        $sql = "INSERT INTO gruppen (name,notizen,farbe,nureins) VALUES ('".
						mysql_real_escape_string($name)."','".
						mysql_real_escape_string($notizen)."','".
						mysql_real_escape_string($farbe)."','".
						mysql_real_escape_string($nureins)."')";
        $db->query($sql);
    }
    break;
    Case 2:
    if ($gid != "") {
        $sql = "UPDATE gruppen SET name = '".
						mysql_real_escape_string($name)."',
						notizen = '".
						mysql_real_escape_string($notizen)."',
						farbe = '".
						mysql_real_escape_string($farbe)."',
						nureins = '".
						mysql_real_escape_string($nureins)."'
						WHERE gruppeID = '".
						mysql_real_escape_string($gid)."'";
        $db->query($sql);
    }
    break;
    Case 3:
    if ($gid != "") {
				$sql = "UPDATE snipe SET gruppe = 0 WHERE gruppe = '".mysql_real_escape_string($gid)."'";
				$db->query($sql);
				$sql = "DELETE FROM gruppen WHERE gruppeID = '".mysql_real_escape_string($gid)."'";
				$db->query($sql);
    }
    break;
}
if($zutun != 3 && $name != ""){
		$gruppe = $db->get_row("SELECT * FROM gruppen WHERE name = '".mysql_real_escape_string($name)."'");
		?>
		<tr class="mainrow">
				<td>
						<form class="changer">
								<table class="Inhaltstabelle">
										<tr class="liner stealth">
												<td style="width:51px;">
														<input type="color" name="farbe" value="<?=$gruppe->farbe?>" oldvalue="<?=$gruppe->farbe?>" />
												</td>
												<td style="width:156px;"><input type="text" name="name" value="<?=$gruppe->name?>" oldvalue="<?=$gruppe->name?>" placeholder="Name ..." style="width:150px;" /></td>
												<td><input type="text" name="notizen" value="<?=$gruppe->notizen?>" oldvalue="<?=$gruppe->notizen?>" placeholder="Notizen ..." style="width:100%;" /></td>
												<td style="width:160px;"><div class="switch-wrapper"><input name="nureins" type="checkbox" value="<?=$gruppe->nureins?>" oldvalue="<?=$gruppe->nureins?>" <?=($gruppe->nureins == 1)?"checked='checked'":""?>/></div></td>
												<td style="width:60px; text-align:right;">
																<input type="hidden" value="<?=$gruppe->gruppeID?>" name="gid" />
																<button style="display: none" class="button-change ui-color-green editorbuttons" title="Ändern">Ändern</button>
																<button style="display: none" class="button-cancel ui-color-red editorbuttons" title="Abbrechen">Abbrechen</button>
																<button class="button-delete ui-color-red stdbuttons" title="Löschen">Löschen</button>
												</td>
										</tr>
								</table>
						</form>
				</td>
		</tr>
		<?php
}

?>