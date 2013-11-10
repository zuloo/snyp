<?php
?>

<script language="JavaScript" type="text/javascript">

//globale Variable f¸r die Countdowntimer
var artliste = new Array();

function editSnipe(artNr,bid,group){
		$('#gh-ac').val(artNr);
		$('#gh-bid').val(bid);
		$('#gh-cat').val(group);
		$('#gh-bid').focus();
}

$( document ).ready(function() {
		function getTime(artliste) {
			now = new Date();
			for(i=0;i<artliste.length;i++) {	
				later = new Date(artliste[i][3],artliste[i][2]-1,artliste[i][1],artliste[i][4],artliste[i][5],artliste[i][6]);
				if (later>now) {
					days = (later - now) / 1000 / 60 / 60 / 24;
					daysRound = Math.floor(days);
					hours = (later - now) / 1000 / 60 / 60 - (24 * daysRound);
					hoursRound = Math.floor(hours);
					minutes = (later - now) / 1000 /60 - (24 * 60 * daysRound) - (60 * hoursRound);
					minutesRound = Math.floor(minutes);
					seconds = (later - now) / 1000 - (24 * 60 * 60 * daysRound) - (60 * 60 * hoursRound) - (60 * minutesRound);
					secondsRound = Math.round(seconds);
					erg = "";
					if(daysRound > 0) erg += daysRound +"d ";
					if(daysRound > 0 || hoursRound > 0)erg+= hoursRound+"h ";
					if(daysRound > 0 || hoursRound > 0 || minutesRound > 0)erg+= minutesRound+"m ";
					erg+= secondsRound+"s ";
				  if(daysRound == 0 && hoursRound == 0)
						erg = "<span style='color:#e43137'>"+erg+"</span>";
					else if (daysRound==0 && hoursRound < 12)
						erg = "<span style='color:#f4ae01'>"+erg+"</span>";
					$("#count_"+artliste[i][0]).html(erg);
				}else{
					$("#count_"+artliste[i][0]).html("<span style='color:gray'>beendet</span>");
				}
			}
		}
		function initCounter() {
				//Countdowntimer werden initalisiert
				getTime(artliste);
				newtime = window.setTimeout(function(){initCounter()}, 1000);
		}
		initCounter();
		
		function addSnipe(that){
				that.parent().prev().html("<img src='load.gif'/> <span style='color:gray; position:relative; top:-2px;'>Starte Snipe ...</span>");
				fdata = that.serialize();
				furl = "add.php";
				$.ajax({
						url:furl,
						data:fdata,
						success:function(data,state,xhr){
								if(data.length > 0){
										that.parent().prev().html(data);
								}
						}
				});

		}
		
		$(".adder").on( "submit", function( event ) {
				event.preventDefault();
				addSnipe($( this ));
		});
		
		$(".changer").on("submit",function(event){
				bid = $(this).find("input[name='bid']");
				if(bid.val() != bid.attr("oldvalue")){
						event.preventDefault();
						addSnipe($( this ));
						bid.attr("oldvalue",bid.val());
				}
		});
});
</script>

<table class="Inhaltstabelle">
		<tr>
				<th width="1%"></th>
				<th width="4%"></th>
				<th width="12%"></th>
				<th width="13%"></th>
				<th width="25%"></th>
				<th width="<?=($zutun == 6)?40:32?>%"></th>
		<?php if($zutun != 6): ?>
				<th width="8%"></th>
		<?php endif ?>
		</tr>
<?php
function read_tree ($dir) {
    global $dateien;
    $fp = opendir($dir);
    while($datei = readdir($fp)) {
		if (substr($datei,-12) == "ebaysnipelog") {
    	    $dateien[] = "$datei";
		}
    }
    closedir($fp);
}


if($zutun == 6)
		$snipelist = getWatchlist($db);
else
		$snipelist = $db->get_results($auktionenSQL);

if (!empty($snipelist)) {

    $zaehler = 0;
    foreach($snipelist as $snipe) {

				$artnr = $snipe->artnr;
				if($snipe->status >= 0){
						statusPruefen($artnr,$db);
						$text = getLogData($artnr);
						if ($text != false) {
							$text = preg_split("/\n/",$text);  //in Textarea nicht ben?tigt, nimmt auch \n
						} else {
							$text = "<span style=\"color:#FF0000;font-weight:bold;\">Fehler - keine Datei zum Datenbankeintrag gefunden!</span>";
						}
				}else{
						$text = preg_split("/\n/",$snipe->text);
				}
		


?>
		<tr>
			<td rowspan="2" style="<?=css_gruppenfarbe($snipe->gruppe,$db)?>"><div style="width:100%; height:100%"></div></td>
			<td rowspan="2">
					<div style="width:80px; height:80px; vertical-align:middle; text-align:center; border: 1px solid gray;">
							<img src="http://thumbs.ebaystatic.com/pict/<?=$artnr?>8080_0.jpg" />
					</div>
		  </td>
			<td colspan="4"><a href="http://cgi.ebay.de/ws/eBayISAPI.dll?ViewItem&item=<?=$artnr?>&rd=1" title="<?=implode("<br />",array_slice($text,-10))?>"><?=$artnr?>: <?=preg_replace("/Auction[ ][0-9]+:/","",$text[0])?></a></td>
			</td>
		<?php if($zutun != 6): ?>
			<td>
<?php /*
			 	<a href="javascript:editSnipe('<?=$artnr?>','<?=$snipe->bid?>','<?=$snipe->gruppe?>')" style="text-decoration: none;"><span style="color: #f4ae00;">//</span> bearbeiten</a>
*/?>
			</td>
		<?php endif ?>
		</tr>
		<tr class="liner">
			<td>
				<?php
				if ($snipe->endtime != 0) {
					//Wenn noch am snipen, Timer anzeigen.
					printf(html_countdown($snipe->artnr,$zaehler,$snipe->endtime));
					$zaehler++;
				}
				?>
			</td>
			<td style="color: gray">&euro; <?=$snipe->highestBid?><?=isset($snipe->shipping)?" ".$snipe->shipping:""?></td>
			<td>
		<?php if($snipe->status >= 0): ?>
				<?=html_snipestatus($snipe->status)?>
				<span style="color:<?=($snipe->bid > $snipe->highestBid)?"#85b716":"#e43137"?>">&euro; <?=$snipe->bid?></span>
				<span style="color:gray">(PID: <span style='color:<?=snipeRunCheck($snipe->pid)?"#85b716":"#e43137"?>'><?=$snipe->pid?></span>)</span>
		<?php elseif($zutun == 6): ?>
				<?php
						if(preg_match("/^(.+)[ ]?\([ ]?([0-9]*)[ ]?\|[ ]?([0-9\.]*)[%]?[ ]?\)$/",$snipe->seller,$seller)){
								$sellerID = $seller[1];
								$reputation = array_key_exists(3,$seller)?$seller[3]."%":"";
								$sells = array_key_exists(2,$seller)?$seller[2]:"";
				?>
				<span style="color:gray">
						<a href="http://www.ebay.de/usr/<?=$sellerID?>" target="_blank"><?=$sellerID?></a> | 
						<a href="http://feedback.ebay.de/ws/eBayISAPI.dll?ViewFeedback2&userid=<?=$sellerID?>" target="_blank"><?=$sells?></a> |
						<?=$reputation?>
				</span>
				<?php
						}
				?>
		<?php endif ?>
		  </td>
			<td align="right" style="text-align: right;">
		<?php if($zutun != 6): ?>
				<form action="index.php" method="get" class="changer">
					<input type="hidden" name='zutun' value='2' />
					<input type="hidden" name="artnr" value="<?=$artnr?>" />
					<input type="text" name="bid" value="<?=$snipe->bid?>" oldvalue="<?=$snipe->bid?>" size="6" placeholder="Gebot ..." />
					<?=html_gruppenliste($snipe->artnr,$db)?>
					<input type="submit" value="&crarr;" />
				</form>
		<?php else: ?>
				<form action="add.php" method="get" class="adder">
						<input type="hidden" name="artnr" value="<?=$artnr?>" />
						<input type="text" name="bid" value="<?=$snipe->bid?>" size="6" placeholder="Gebot ..." />
						<?php if($snipe->status < 0): ?>
						<select name="gruppe" size="1">
								<?=html_GruppenlisteNeuerArt($db)?>
						</select>
						<?php else: ?>
								<?=html_gruppenliste($snipe->artnr,$db)?>
						<?php endif ?>
						<input type="submit" value="Snipe" />
				</form>
		<?php endif ?>
			</td>
		<?php if($zutun != 6): ?>
			<td>
				<a href="index.php?zutun=1&delete=<?=$artnr?>" style="text-decoration: none;"><span style="color: #e43137;">X</span> löschen</a>
			</td>
		<?php endif ?>
		</tr>
		<?php
				}
		} else {
				?><tr><td colspan="6"><?=$GLOBALS["tDbLeer"]?></td></tr><?php
		}
		?>
</table>