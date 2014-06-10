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
require_once 'config.inc';
require_once 'ez_sql.php';
require_once 'phpLinkCheck.php';

global $db;
$db = new db(EZSQL_DB_USER, EZSQL_DB_PASSWORD, EZSQL_DB_NAME, EZSQL_DB_HOST);
if(!$db->alive()) die();

function genAuctionfile($artnr,$bid) {
    $fn=TMP_FOLDER."/".$artnr.".ebaysnipe";
    $text="$artnr $bid\n";
    $fp=fopen($fn,"w");
    fwrite($fp,$text);
    fclose($fp);
    chmod($fn, 0666);
}

function startEsniper($artnr) {
    $fn=TMP_FOLDER."/".$artnr.".ebaysnipe";
    $fnl=TMP_FOLDER."/".$artnr.".ebaysnipelog";
    touch($fnl);
    chmod($fnl, 0666);
    $pid = exec("./esniperstart.sh $fn $fnl ".PATH_TO_ESNIPER." ".PATH_TO_ESNIPERCONFIG." > /dev/null & echo \$!", $results,$status);
    return($pid);
}

function calcEndTime($deltas){
		$now = time();
		$time = new stdClass();
		$time->Y = date("Y",$now);
		$time->M = date("n",$now);
		$time->D = date("j",$now);
		$time->h = date("G",$now);
		$time->m = date("i",$now);
		$time->s = date("s",$now);
		$time->t = date("t",$now);
		foreach($deltas as $delta){
				if(preg_match("/^([0-9]+)([dhms])$/",$delta,$match)){
						switch($match[2]){
								Case "d":
										$time->D += $match[1];
										break;
								Case "h":
										$time->h += $match[1];
										break;
								Case "m":
										$time->m += $match[1];
										break;
								Case "s":
										$time->s += $match[1];
										break;
						}
						if($time->s > 59){
								$time->s -= 60;
								$time->m++;
						}
						if($time->m > 59){
								$time->m -= 60;
								$time->h++;
						}
						if($time->h > 23){
								$time->h -= 24;
								$time->D++;
						}
						if($time->D > $time->t){
								$time->D -= $time->t;
								$time->M++;
						}
						if($time->M > 12){
								$time->M -= 12;
								$time->Y++;
						}
				}
		}
		return mktime($time->h,$time->m,$time->s,$time->M,$time->D,$time->Y);
}

function getWatchlist($db) {
		exec(PATH_TO_ESNIPER." -m -c ".PATH_TO_ESNIPERCONFIG, $output, $status);
		$articles = null;
		$a= 0;
		for($i=0; $i < count($output); $i+=8){
				if(preg_match("/^Time[ ]left:[[:blank:]]+([0-9]+d)?[ ]?([0-9]+h)?[ ]?([0-9]+m)?[ ]?([0-9]+s)?[ ]left$/",$output[$i+3],$match)){
						$articles[$a] = new stdClass();
						$articles[$a]->endtime = calcEndTime(array_slice($match,1));
				}
				else{
					 continue;
				}
				if(preg_match("/^ItemNr:[[:blank:]]+([0-9]+)$/",$output[$i],$match))
						$articles[$a]->artnr = $match[1];
				if(preg_match("/^Description:[[:blank:]]+(.+)$/",$output[$i+1],$match))
						$articles[$a]->text = $match[1];
				if(preg_match("/^Seller:[[:blank:]]+(.+)$/",$output[$i+2],$match)){
						$articles[$a]->text .= "\n".$match[1];
						$articles[$a]->seller = $match[1];
				}
				if(preg_match("/^Price:[[:blank:]]+EUR[ ]([0-9\.]+)$/",$output[$i+4],$matchPrice))
						$articles[$a]->highestBid = $matchPrice[1];
				if(preg_match("/^Shipping:[[:blank:]\+]+EUR[ ]([0-9\.]+)$/",$output[$i+6],$matchShipping))
						$articles[$a]->shipping = "(+ ".$matchShipping[1].")";
				$sql = "SELECT * FROM snipe WHERE artnr = '".$articles[$a]->artnr."'";
				$row = $db->get_row($sql);
				if($row){
						$articles[$a]->status = $row->status;
						$articles[$a]->bid = $row->bid;
						$articles[$a]->gruppe = $row->gruppe;
						$articles[$a]->pid = $row->pid;
				}else{
						$articles[$a]->bid = "";
						$articles[$a]->guppe = 0;
						$articles[$a]->status = -1;
						$articles[$a]->watch = true;
				}
				$a++;
		}
		return $articles;
}

function auktionBeendet($artnr) {
    $fn=TMP_FOLDER."/".$artnr.".ebaysnipelog";
    if (file_exists($fn)) {
		$fp=fopen($fn,"r");
		$text=fread($fp, filesize ($fn));
		fclose($fp);
		if (preg_match("/won[ ][1-9][0-9]*[ ]item.s./", $text)) {return(1);}
		elseif (ueberbotenStatus($text)) {return(2);}
		if (preg_match("/won[ ][0[ ]item.s./", $text)) {return(2);}
		else {return(0);}
    }
}

function auktionEndtime($text) {
	preg_match("/End time: [0-9]{2}\/[0-9]{2}\/[0-9]{4} [0-2][0-9]:[0-5][0-9]:[0-5][0-9]/",$text, $zeitArr);
	$zeitStr = $zeitArr[count($zeitArr)-1];
	$tag = substr($zeitStr,10,2);
	$monat = substr($zeitStr,13,2);
	$jahr = substr($zeitStr,16,4);

	$stunde = substr($zeitStr,21,2);
	$minute = substr($zeitStr,24,2);
	$sekunde = substr($zeitStr,27,2);

	$unixzeit = mktime($stunde,$minute,$sekunde,$monat,$tag,$jahr);
	return($unixzeit);
}

function ueberbotenStatus($text) {
	//True meldet, dass überboten wurde.
    $bidFound = preg_match_all("/bid: [0-9]+\.?[0-9]*/",$text,$meineGebote,PREG_PATTERN_ORDER);

    $bidMinimum = preg_match("/Bid[ ]price[ ]less[ ]than[ ]minimum[ ]bid[ ]price/",$text);
    if (($bidFound != 0 && substr($meineGebote[0][count($meineGebote[0])-1],5) - getHighestBid($text) <= 0) || $bidMinimum != false) {
		return(true);
    } else {
		return(false);
    }
}

function statusPruefen($artnr,$db) {
    $status = auktionBeendet($artnr);
    if ($status != 0) {
				$sql = "UPDATE snipe SET status = ".$status." WHERE artnr=".$artnr;
				$db->query($sql);
				if ($status == 1) {
				//Andere zur Gruppe gehörende Auktionen beenden/updaten.
						$sql = "SELECT g.gruppeID FROM snipe s, gruppen g WHERE s.artnr = ".$artnr." AND s.gruppe = g.gruppeID";
						$gruppennr = $db->get_var($sql);
						if (!is_null($gruppennr) && $gruppennr!=0) {
								$sql = "UPDATE snipe SET status = 3 WHERE gruppe = ".$gruppennr." AND artnr <> ".$artnr;
								$db->query($sql);
						}
				}
    }
}

function snipeEinstellen($artnr,$bid,$db) {
    $bid = str_replace(",",".",$bid);
    $sql = "SELECT * FROM snipe WHERE artnr=".$artnr;
    $snipe = $db->get_row($sql);
    if (empty($snipe)) {
		genAuctionfile($artnr,$bid);
        //PID auslesen und in Datenbank schreiben
        $pid = startEsniper($artnr);
        $sql = "INSERT INTO snipe (artnr,bid,pid,status) VALUES (\"$artnr\",\"$bid\",\"$pid\",0)";
        $db->query($sql);
    } else {
		//Snipe bereits in Datenbank vorhanden
		if ($bid != $snipe->bid) {
				killSniper($artnr,$db);
				genAuctionfile($artnr,$bid);
				$pid = startEsniper($artnr);
				$sql="UPDATE snipe SET bid = ".$bid.",pid = ".$pid.",status = 0 WHERE artnr = ".$snipe->artnr;
				$db->query($sql);
		} elseif (!snipeRunCheck($snipe->pid)) {
				genAuctionfile($artnr,$bid);
				$pid = startEsniper($artnr);
				$sql = "UPDATE snipe SET pid = ".$pid." WHERE artnr = ".$artnr;
				$db->query($sql);
		}
    }
    exec("./updateDB.php &");  //Nach 10 Sekunden aus den Logs die Endtime in der DB updaten - multi Thread
}

function killSniper($artnr,$db) {
    $sql = "SELECT * FROM snipe WHERE artnr=".$artnr;
    $snipe = $db->get_row($sql);

    if (snipeRunCheck($snipe->pid) == true) {
        //Sicherheitsabfrag eeinbauen, ob PID auch ein esniper Programm
				//	printf("Sniperprozess mit PID ".$snipe->pid."beendet.");
				exec("kill -15 ".getEsniperPid($snipe->pid));
    }
    exec("rm \"".TMP_FOLDER."/".$artnr.".*\"");
}

function getPids() {
    $output = shell_exec("pidof -x esniperstart.sh");
    if ($output != "\n") {
    	$pids = explode(" ",rtrim($output));
    }
    return($pids);
}


function getEsniperPid($shpid) {
//Workaround
	$output = shell_exec("pstree -p|grep ".$shpid);
	if (preg_match_all("/\([0-9]+\)/",$output,$pids,PREG_PATTERN_ORDER)) {
		return(substr($pids[0][1],1,strlen($pids[0][1])-2));
	}
}


function snipeRunCheck($pid) {
    $pids = getPids();
    if (!empty($pids)) {
    	return(in_array($pid,$pids));
    } else {
    	return(false);
    }
}


function fileList($dir) {
    $fp = opendir($dir);
    while($datei = readdir($fp)) {
        if (substr($datei,-12) == "ebaysnipelog" || substr($datei,-9) == "ebaysnipe") {
            $dateien[] = "$datei";
        }
    }
    closedir($fp);
    return($dateien);
}


function getLogData($artnr) {
	$fn=TMP_FOLDER."/".$artnr.".ebaysnipelog";
	if (file_exists($fn)) {
		$fp=fopen($fn,"r");
		$text=fread($fp, filesize ($fn));
		fclose($fp);
	} else {
		$text = false;
	}
	return($text);
}




function getHighestBid($logData) {
//Filtert das höchste Gebot aus den Logs
	$status = preg_match_all("/Currently: [0-9]+\.?[0-9]*/",$logData,$aktGebote,PREG_PATTERN_ORDER);
	if ($status == 0) {
		return(0);
	} else {
    	return(substr($aktGebote[0][count($aktGebote[0])-1],11));
    }
}


function updateHighestBid($db) {
	$sql = "SELECT * FROM snipe WHERE status = 0";
	$snipelist = $db->get_results($sql);
	if (!empty($snipelist)) {
		foreach($snipelist as $snipe) {
			$logData = getLogData($snipe->artnr);
			$sql = "UPDATE snipe SET highestBid = \"".getHighestBid($logData)."\" WHERE artnr = ".$snipe->artnr;
			$db->query($sql);
		}
	}
}


function updateEndtime($db) {
	$sql = "SELECT * FROM snipe WHERE endtime <= 0";
	$snipelist = $db->get_results($sql);
	if (!empty($snipelist)) {
		foreach($snipelist as $snipe) {
			$logData = getLogData($snipe->artnr);
			$unixtime = auktionEndtime($logData);
			$sql = "UPDATE snipe SET endtime = ".$unixtime." WHERE artnr = ".$snipe->artnr;
			$db->query($sql);
		}
	}
}


function snipeGenerate($db) {
//Generiert anhand der Datenbankdaten esniper Prozesse
    $msg = "";
    $sql = "SELECT * FROM snipe WHERE status = 0";
    $snipelist = $db->get_results($sql);
    if (!empty($snipelist)) {
    	foreach($snipelist as $snipe) {
			if (!snipeRunCheck($snipe->pid)) {
			//Prozess läuft nicht
				snipeEinstellen($snipe->artnr,$snipe->bid,$db);
				$msg = $msg ."Snipe für ".$snipe->artnr." gestartet.\n";
			} else {
				$msg = $msg ."Snipe für ".$snipe->artnr." läuft bereits.\n";
			}
    	}
    }
    return($msg);
}

function collectGarbage($db) {
	//$msg = "";
    //Pids abschiessen, welche nicht laufen dürfen
    $sql = "SELECT pid FROM snipe WHERE status = 0";
    $snipePids = $db->get_col($sql);
    $pids = getPids();
    if (!empty($pids)) {
		foreach($pids as $pid) {
			if (!in_Array($pid,$snipePids)) {
				$msg = $msg ."Prozess ".$pid." wurde beendet";
				exec("kill -15 ".getEsniperPid($pid));
			}
		}
    }

	//Logs löschen, von Snipes, welche nicht in der Datenbank sind
    $dateien = fileList(TMP_FOLDER);
    if (!empty($dateien)) {
	    foreach($dateien as $datei) {
		    	$artnrDatei = explode(".",$datei);
			$sql = "SELECT artnr FROM snipe WHERE artnr = \"".$artnrDatei[0]."\"";
			$snipeArtnr = $db->get_var($sql);
			if (empty($snipeArtnr)) {
			    exec("rm \"".TMP_FOLDER."/".$artnrDatei."\"");
			}
	    }
    }

	$sql = "SELECT artnr FROM snipe";
	$snipeArtnr = $db->get_col($sql);
    if (!empty($snipeArtnr)) {
    	foreach($snipeArtnr as $artnr) {
			statusPruefen($artnr,$db);
    	}
    }

    return($msg);
}
?>
