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

$zutun = array_key_exists("zutun",$_GET)?(preg_match('/^[0-9]$/',$_GET["zutun"])?$_GET["zutun"]:""):"";

?>
<html>
<head>
<title>
snyp
</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Pragma" content="no-cache" />
<?php if($zutun != 7 && $zutun != 6): ?>
<meta http-equiv="REFRESH" content="300;URL=index.php" />
<?php endif ?>
<link href="css/jqueryui/jquery-ui.min.css" rel="stylesheet" type="text/css">
<link href="css/spectrum.css" rel="stylesheet" type="text/css">
<link href="css/switch.css" rel="stylesheet" type="text/css">
<link href="css/main.css" rel="stylesheet" type="text/css">
<link href="css/tabs.css" rel="stylesheet" type="text/css">
<link href="css/ebay.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" media="screen" type="text/css" href="css/colorpicker.css" />
<script src="js/jquery.min.js" type="text/javascript" language="JavaScript"></script>
<script src="js/jquery-ui.min.js" type="text/javascript" language="JavaScript"></script>
<script src="js/spectrum.js" type="text/javascript" language="JavaScript"></script>
<script type="text/javascript" src="js/colorpicker.js"></script>
<script src="js/switch.js" type="text/javascript" language="JavaScript"></script>
<script src="js/jsutil.js" type="text/javascript" language="JavaScript"></script>
<script language="JavaScript" type="text/javascript">
$( document ).ready(function() {
		$( document ).tooltip();
});
</script>

</head>
<body style="font-family:Helvetica,Helv;">
<div id="myebaypage" style="width:1200px;padding-right:10px;padding-left:10px">
<div class="gh-w gh-site-77" id="gh">
		<table class="gh-tbl">
				<tbody>
						<tr>
								<td class="gh-td logo-td">
										<a href="index.php" style="text-decoration: none">
												<div id="logo"><span class="logo logo1">s</span><span class="logo logo2">n</span><span class="logo logo3">y</span><span class="logo logo4">p</span></div>
										</a>
								</td>
								<td class="gh-td-s">
										<form method="GET" action="index.php">
												<table class="gh-tbl2">
														<tbody>
																<tr>
																		<td class="gh-td">
																				&nbsp;
																		</td>
																		<td class="gh-td-s">
																				<div id="gh-ac-box" class="">
																						<div id="gh-ac-box2">
																								<label for="gh-ac" class="gh-hdn g-hdn">Geben Sie Ihren Suchbegriff ein</label>
																								<input type="text" class="gh-tb ui-autocomplete-input" size="50" maxlength="300" placeholder="Artikel Nummer oder URL ..." id="gh-ac" name="artnr" autocomplete="off">
																						</div>
																				</div>
																		</td>
																		<td id="gh-bid-td" class="gh-td">
																				<div id="gh-bid-box">
																						<input type="text" class="gh-tb ui-autocomplete-input" size="7" maxlength="10" placeholder="Gebot ..." id="gh-bid" name="bid" autocomplete="off">
																				</div>
																		</td>
																		<td id="gh-cat-td" class="gh-td">
																				<div id="gh-cat-box">
																						<select title="Wählen Sie eine Kategorie für die Suche aus" class="gh-sb" size="1" id="gh-cat" name="gruppe">
																								<?=html_GruppenlisteNeuerArt($db)?>
																						</select>
																				</div>
																		</td>
																		<td class="gh-td">
																				<input type="hidden" name="zutun" value="5">
																				<input type="submit" class="btn btn-prim" id="gh-btn" value="Snipe" style="display: inline-block;">
																		</td>
																</tr>
														</tbody>
												</table>
										</form>
								</td>
						</tr>
				</tbody>
		</table>
</div>
<br />
<br />
<?php

if(array_key_exists("artnr",$_GET)){
		$artnr = preg_match('/^[0-9]+$/',$_GET["artnr"])?$_GET["artnr"]:"";
		$match = null;
		if($artnr == "" &&	preg_match('/it[e]?m[=\/]([0-9]+)[\?&]/',$_GET["artnr"],$match)){
				$artnr = $match[1];
		}
}else{
		$artnr ="";
}
$bid = array_key_exists("bid",$_GET)?(preg_match('/^[0-9]*[,\.]?[0-9]{1,2}$/',$_GET["bid"])?$_GET["bid"]:""):"";
$delete = array_key_exists("delete",$_GET)?(preg_match('/^[0-9]+$/',$_GET["delete"])?$_GET["delete"]:""):"";
$gruppe = array_key_exists("gruppe",$_GET)?(preg_match('/^[0-9]+$/',$_GET["gruppe"])?$_GET["gruppe"]:""):"";
$filtergruppe = array_key_exists("fitlergruppe",$_GET)?(preg_match('/^[0-9]+$/',$_GET["filtergruppe"])?$_GET["filtergruppe"]:""):"";

//Eintrag erstellen
$auktionenSQL = "SELECT * FROM snipe ORDER BY status,endtime ASC"; //Standard
switch($zutun) {
    Case 1:
				//Artikel l?schen
        killSniper($delete,$db);
        $sql = "DELETE FROM snipe WHERE artnr=".$delete;
        $snipe = $db->get_row($sql);
        break;
    Case 2:
				$sql = "UPDATE snipe SET gruppe = ".$gruppe." WHERE artnr = ".$artnr;
				$db->query($sql);
        break;
    Case 3:
				//Aufr?umen
				$sql = "DELETE FROM snipe WHERE status != 0";
				$db->query($sql);
				break;
    Case 4:
				//Auktionsliste nach Gruppe filtern
				if ($filtergruppe == -1) {
						//Alles anzeigen
						$auktionenSQL = "SELECT * FROM snipe ORDER BY status,endtime ASC";
				} else {
						$auktionenSQL = "SELECT * FROM snipe WHERE gruppe = \"".$filtergruppe."\" ORDER BY status,endtime ASC";
				}
				break;
		Case 5:
				//Eintrag erstellen
				if ($artnr != "" && $bid != "") {
				   snipeEinstellen($artnr,$bid,$db);
				}	
				if ($gruppe != "") {
						$sql = "UPDATE snipe SET gruppe = ".$gruppe." WHERE artnr = ".$artnr;
						$db->query($sql);
				}
				break;

}
?>
<div id="GlobalNavigation" class="gnDS2">
		<div class="bstab-tabListCnt">
				<table width="100%" cellspacing="0" cellpadding="0" border="0">
						<tbody>
								<tr>
										<td>
												<table cellspacing="0" cellpadding="0" border="0" class="bstab-tabs bstab-lt">
														<tbody>
																<tr>
																		<td class="bstab-cntrTabGp"></td>
																		<td class="bstab-dummy">&nbsp;</td>
																		<td class="bstab-<?=($zutun != 6 && $zutun != 7)?"act":"i"?>Lft"></td>
																		<td class="bstab-<?=($zutun != 6 && $zutun != 7)?"act":"i"?>Rgt"><a class="bstab-padd" href="index.php">Snipes</a></td>
																		<td class="bstab-dummy">&nbsp;</td>
																		<td class="bstab-<?=($zutun == 6)?"act":"i"?>Lft"></td>
																		<td class="bstab-<?=($zutun == 6)?"act":"i"?>Rgt"><a class="bstab-padd" href="index.php?zutun=6">Beobachtungsliste</a></td>
																		<td class="bstab-dummy">&nbsp;</td>
																		<td class="bstab-<?=($zutun == 7)?"act":"i"?>Lft"></td>
																		<td class="bstab-<?=($zutun == 7)?"act":"i"?>Rgt"><a class="bstab-padd" href="index.php?zutun=7">Gruppen</a></td>
																		<td class="bstab-dummy">&nbsp;</td>
																</tr>
														</tbody>
												</table>
										</td>
										<td align="right">
												<div class="m-tn_txt"><span><form action="index.php" method="get">
												<?php
if($zutun == "" || in_array($zutun, range(1,5))){
		//Zum filtern der Auktionenliste nach einer Gruppe
		if ($filtergruppe >= 0 && $zutun==4) {
			printf(html_GruppenFilternListe($filtergruppe,$db));
		} else {
			printf(html_GruppenFilternListe(-1,$db));
		}
		printf("<input type=\"submit\" value=\"Gruppe filtern\">");
		printf("<input type=\"hidden\" name=\"zutun\" value=\"4\">");
		if ($filtergruppe >= 0 && $zutun==4) {
			$sql="SELECT count(*) FROM snipe WHERE gruppe = \"".$filtergruppe."\"";
			$aAnzahl = $db->get_var($sql);
			printf(" gefiltert: ".$aAnzahl);
		}
		?> <big style="color:black">|</big> <?php
		//Auktion am laufen
		$sql = "SELECT count(*) FROM snipe WHERE status = 0";
		$anzahl = $db->get_var($sql);
		printf(" ".$GLOBALS["tSnipeListSummaryArray"][0].": ". $anzahl ." ");
		
		?> <big style="color:black">|</big> <?php
		
		//Auktion gewonnen
		$sql = "SELECT count(*) FROM snipe WHERE status = 1";
		$anzahl = $db->get_var($sql);
		printf($GLOBALS["tSnipeListSummaryArray"][1].": ". $anzahl ." ");
		
		?> <big style="color:black">|</big> <?php
		
		//Auktion ¸berboten
		$sql = "SELECT count(*) FROM snipe WHERE status = 2";
		$anzahl = $db->get_var($sql);
		printf($GLOBALS["tSnipeListSummaryArray"][2].": ". $anzahl);

		?>
														<big style="color:black">|</big> 											
														<a href="index.php?zutun=3" title="Auktionen aufräumen">Aufräumen</a>
												<?php
}
												?>
												</form></span></div>
										</td>
								</tr>
						</tbody>
				</table>
		</div>
</div>
<br /><br/>
<?php
if($zutun != 7)
		include("artlist.php");
else
		include("gruppen.php");
?>
</div>
</body>
</html>
