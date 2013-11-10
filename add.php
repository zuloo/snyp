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
$gruppe = array_key_exists("gruppe",$_GET)?(preg_match('/^[0-9]+$/',$_GET["gruppe"])?$_GET["gruppe"]:""):"";

//Eintrag erstellen
if ($artnr != "" && $bid != "") {
    snipeEinstellen($artnr,$bid,$db);
		if ($gruppe != "") {
				$sql = "UPDATE snipe SET gruppe = ".$gruppe." WHERE artnr = ".$artnr;
				$db->query($sql);
		}
		$sql = "SELECT * FROM snipe WHERE artnr = '".$artnr."'";
		if($snipe = $db->get_row($sql)){
?>
				<?=html_snipestatus($snipe->status)?>
				<span style="color:<?=($snipe->bid > $snipe->highestBid)?"#85b716":"#e43137"?>">&euro; <?=$snipe->bid?></span>
				<span style="color:gray">(PID: <span style='color:<?=snipeRunCheck($snipe->pid)?"#85b716":"#e43137"?>'><?=$snipe->pid?></span>)</span>
<?php
		}
}


?>
